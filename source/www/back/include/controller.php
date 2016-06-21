<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class controller {
		public $_layout;
		public $_view, $_modview;
		public $_js, $_css;
		private $_request;
		public $_navi_menu;
		public $_subnavi_menu;
		public $_page_id;
		public $_action_type;
		public $_params;
		public $_script_mode;

		public function __construct(){

			$this->inputs();
			$this->_js = array();
			$this->_css = array();
			$this->_modview = array();
			$this->_script_mode = false;

			if ($this->_page_id != "install" && !defined('DB_HOSTNAME')) {
				$this->forward("install");
			}

			// auto_login
			if (_user_id() == null && defined('DB_HOSTNAME')) {
				$user = new user;
				$user->login(true);
			}
		}
		
		public function get_referer(){
			return $_SERVER['HTTP_REFERER'];
		}
		
		public function response($data,$status = 200){
			$this->_code = ($status)?$status:200;
			$this->set_headers();
			echo $data;
			exit;
		}
		
		private function get_status_message(){
			$status = array(
						100 => 'Continue',  
						101 => 'Switching Protocols',  
						200 => 'OK',
						201 => 'Created',  
						202 => 'Accepted',  
						203 => 'Non-Authoritative Information',  
						204 => 'No Content',  
						205 => 'Reset Content',  
						206 => 'Partial Content',  
						300 => 'Multiple Choices',  
						301 => 'Moved Permanently',  
						302 => 'Found',  
						303 => 'See Other',  
						304 => 'Not Modified',  
						305 => 'Use Proxy',  
						306 => '(Unused)',  
						307 => 'Temporary Redirect',  
						400 => 'Bad Request',  
						401 => 'Unauthorized',  
						402 => 'Payment Required',  
						403 => 'Forbidden',  
						404 => 'Not Found',  
						405 => 'Method Not Allowed',  
						406 => 'Not Acceptable',  
						407 => 'Proxy Authentication Required',  
						408 => 'Request Timeout',  
						409 => 'Conflict',  
						410 => 'Gone',  
						411 => 'Length Required',  
						412 => 'Precondition Failed',  
						413 => 'Request Entity Too Large',  
						414 => 'Request-URI Too Long',  
						415 => 'Unsupported Media Type',  
						416 => 'Requested Range Not Satisfiable',  
						417 => 'Expectation Failed',  
						500 => 'Internal Server Error',  
						501 => 'Not Implemented',  
						502 => 'Bad Gateway',  
						503 => 'Service Unavailable',  
						504 => 'Gateway Timeout',  
						505 => 'HTTP Version Not Supported');
			return ($status[$this->_code])?$status[$this->_code]:$status[500];
		}
		
		public function get_request_method(){
			return $_SERVER['REQUEST_METHOD'];
		}
		
		private function inputs(){
			switch($this->get_request_method()){
				case "POST":	
					$this->_request = array_merge($this->cleanInputs($_GET), $this->cleanInputs($_POST));
					break;
				case "GET":
				case "DELETE":
					$this->_request = $this->cleanInputs($_GET);
					break;
				case "PUT":
					parse_str(file_get_contents("php://input"),$this->_request);
					$this->_request = $this->cleanInputs($this->_request);
					break;
				default:
					$this->response('',406);
					break;
			}
		}		
		
		private function cleanInputs($data){
			$clean_input = array();
			if(is_array($data)){
				foreach($data as $k => $v){
					$clean_input[$k] = $this->cleanInputs($v);
				}
			}else{
				/*
				if(get_magic_quotes_gpc()){
					$data = trim(stripslashes($data));
				}
				$data = strip_tags($data);
				$clean_input = trim($data);
				*/
				$clean_input = $data;
			}
			return $clean_input;
		}		
		
		private function set_headers(){
			if ($this->_code != 200) {
				header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
			}
			header("Content-Type:".$this->_content_type);
		}

		public function json($data){
			if(!is_array($data))
				$data = array($data);
			return _json_encode($data, JSON_NUMERIC_CHECK);
		}

		public function start()
		{
			$db = db::getDB();
			$db->begin();
		}

		public function commit()
		{
			$db = db::getDB();
			$db->commit();
		}

		public function rollback()
		{
			$db = db::getDB();
			$db->rollback();
		}

		public function finish($data, $err, $status=200, $err_p1=null, $err_p2=null)
		{
			global $g_err_msg;

			$db = db::getDB();
			if ($err == ERR_OK)
				$db->commit();
			else
				$db->rollback();

			if ($err == ERR_OK)
				$ret = array("err_code" => $err, "err_msg" => "");
			else {
				if ($g_err_msg == null)
					$g_err_msg = _err_msg($err, $err_p1, $err_p2);
				$ret = array("err_code" => $err, "err_msg" => $g_err_msg);
			}
			if ($err === ERR_OK) {
				if ($data != null) {
					$data = is_array($data) ? $data : array($data);
					$ret = array_merge($ret , $data);
				}
				$this->response($this->json($ret), $status);
			}
			else {
				$this->response($this->json($ret), $status);
			}
		}

		public function checkError($err, $err_p1=null, $err_p2=null)
		{
			if ($err != ERR_OK)
				$this->finish(null, $err, 200, $err_p1, $err_p2);
		}

		public function checkRequired($params)
		{
			global $g_err_msg;

			$err = ERR_OK;
			$params = is_array($params) ? $params : array($params);
			foreach($params as $param)
			{
				if ($this->$param === null) {
					$g_err_msg .= "Request parameter \"$param\" is required.\n";
					$err = ERR_INVALID_REQUIRED;
				}
			}

			$this->checkError($err);
		}

		public function __get($prop) {
			if ($prop == "request") {
				return $this->_request;
			}
			else {
				return isset($this->_request[$prop]) ? $this->_request[$prop] : null;
			}
		}

		public function __set($prop, $val) {
			$this->_request[$prop] = $val;
		}

		public function __call($method, $params) {

		}

		public function existProp($prop)
		{
			$keys = array_keys($this->_request);
			foreach($keys as $key)
			{
				if ($key == $prop)
					return true;
			}
			return false;
		}

		public function process($_controller, $_action, $_params){
			$this->_params = $_params;
			if((int)method_exists($this,$_action) > 0) 
			{
				$this->_action_type = ACTIONTYPE_HTML;
				if (strstr($_action, "_ajax"))
					$this->_action_type = ACTIONTYPE_AJAXJSON;
				else if (strstr($_action, "_refresh"))
					$this->_action_type = ACTIONTYPE_AJAXHTML;

				$this->checkPriv($_action, UTYPE_NONE);

				$ret = @call_user_func_array(array($this, $_action), $_params);

				$ret = preg_split("/\//", $ret, 2);

				$vw = "";
				if (count($ret) <= 1) {
					$this->_layout = _template("layout/main.php");
					if (count($ret) == 1) 
						$vw = $ret[0];
				}
				else {
					$this->_layout = _template("layout/" . $ret[0] . ".php");
					$vw = $ret[1];
				}

				if ($vw == "")  {
					$this->_view = _template("view/" . $_controller . "_" . $_action . ".php");
				}
				else {
					$this->_view = _template("view/" . $vw . ".php");
				}

				if (file_exists($this->_layout))
					require_once($this->_layout);
				else
					$this->showError(ERR_NOTFOUND_PAGE);
			}
			else
				$this->showError(ERR_NOTFOUND_PAGE);
		}

		public function showError($err_code, $title = "エラー発生")
		{
			$this->err_title = $title;
			$this->err_msg = _err_msg($err_code);
			switch ($err_code) {
				case ERR_NODATA:
				case ERR_NOTFOUND_PAGE:
					$this->_code = 404;
					break;
				case ERR_NOPRIV:
					$this->_code = 403;
					break;
				case ERR_ALREADYLOGIN:
				case ERR_FAILLOGIN:
				case ERR_NOT_LOGINED:
					$this->_code = 401;
					break;
				default:
					$this->_code = 400;
					break;
			}
			$this->set_headers();
			require_once(_template("layout/error.php"));
			exit;
		}

		public function forward($url) {
			_goto(SITE_BASE . HOME_BASE . $url);
		}

		public function setActive($menu) {
			print $menu == $this->_navi_menu ? "active" : "";
		}

		public function setSubActive($submenu) {
			print $submenu == $this->_subnavi_menu ? "active" : "";
		}

		public function outJson($result, $err) {
			$ret = array("err_code" => $err, "err_msg" => $g_err_msg);
			if ($err === ERR_OK) {
				if ($result != null) {
					$result = is_array($result) ? $result : array($result);
					$ret = array_merge($ret , $result);
				}
			}

			print $this->json($ret);
			exit;
		}

		public function checkPriv($_action, $utype)
		{
			if(_in_blacklist()) {
				if ($this->_action_type == ACTIONTYPE_AJAXJSON) {
					$this->checkError(ERR_BLACKIP);
				}
				else { // ACTIONTYPE_AJAXHTML, ACTIONTYPE_HTML
					$this->showError(ERR_BLACKIP);
				}
			}

			if ($utype == UTYPE_NONE)
				return;

			$cur_utype = _utype();
			if ($cur_utype == null)
			{ 
				if ($this->_action_type == ACTIONTYPE_AJAXJSON) {
					$this->checkError(ERR_NOT_LOGINED);
				}
				else if ($this->_action_type == ACTIONTYPE_HTML) {
					global $_SERVER;
					_session("request_uri", $_SERVER["REQUEST_URI"]);
					$this->forward("login");
				}
				else { // ACTIONTYPE_AJAXHTML
					print "";
					exit;
				}

			}
			if ($cur_utype & UTYPE_ADMIN ||
				$utype & UTYPE_ADMIN && $cur_utype & UTYPE_ADMIN ||
				$utype & UTYPE_USER && $cur_utype & UTYPE_USER)
			{
				return;
			}
			
			if ($this->_action_type == ACTIONTYPE_AJAXJSON) {
				$this->checkError(ERR_NOPRIV);
			}
			else if ($this->_action_type == ACTIONTYPE_HTML) {
				$this->forward("login");
			}
			else { // ACTIONTYPE_AJAXHTML, ACTIONTYPE_HTML
				$this->showError(ERR_NOPRIV);
			}
		}

		public function addjs($jsfile) {
			$this->_js[] = $jsfile;
		}

		public function addcss($cssfile) {
			$this->_css[] = $cssfile;
		}

		public function addmodview($modview) {
			$this->_modview[] = $modview;
		}

		function include_view()
		{
			include($this->_view);
		}

		function include_viewjs()
		{
			$this->script_mode(true); 
			include($this->_view);
			foreach( $this->_modview as $view) {
				include($view);
			}
		}

		function include_js()
		{
			foreach( $this->_js as $js) {
			?>
				<script src="<?php p($js); ?>"></script>
			<?php
			}
		}

		function include_css()
		{ 
			foreach( $this->_css as $css) {
			?>
				<link rel="stylesheet" type="text/css" href="<?php p($css); ?>"/>
			<?php
			}
		}

		function script_mode($mode = null)
		{
			if ($mode == null)
				return $this->_script_mode;
			else 
				$this->_script_mode = $mode;
		}
	}
?>