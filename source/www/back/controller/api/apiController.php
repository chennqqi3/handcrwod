<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	if (isset($_SERVER['HTTP_ORIGIN'])) {
		header("Access-Control-Allow-Origin:*");
		header("Access-Control-Allow-Headers:accept, content-type");
		header("Access-Control-Allow-Methods:GET, POST, OPTIONS");
	}

	// Access-Control headers are received during OPTIONS requests
	if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
			header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

		if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
			header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

		exit(0);
	}

	class APIController extends controller {
		protected $api_name = "";
		protected $api_url = "", $apitest_url = "";
		protected $apis = null;
		protected $api_methods = null;
		protected $api_params = null, $api_param_names = null;

		public function __construct(){
			parent::__construct();
			$this->api_params = new model;
		}

		public function process($_controller, $_action, $_params){
			global $_SERVER;
			if ($_controller == "api")
			{
				if (defined("API_TEST")) {
						$this->apis = array();
						$this->apitest_url = SITE_BASEURL . "apitest/";
						$this->api_url = SITE_BASEURL . "api/";
						$dir = SITE_ROOT . '/controller/api/';
						if ($dh = opendir($dir)) {
							while (($file = readdir($dh)) !== false) {
								if (filetype($dir . $file) == "file" && strpos($file, "Controller.php") !== false) {
									$api = str_replace('Controller.php', '', $file);
									if ($api != "api")
										array_push($this->apis, $api);
								}
							}
							closedir($dh);
						}
						$this->showTestView("api_list");
				}
			}
			else if ($_action == "_methods")
			{
				if (defined("API_TEST")) {
					$this->api_name = $_controller;
					$this->apitest_url = SITE_BASEURL . "apitest/" . $_controller . "/";
					$this->api_url = SITE_BASEURL . "api/" . $_controller . "/";
					$this->api_methods = get_this_class_methods(get_class($this));

					$this->showTestView("api_methods");
				}
			}
			else {
				$this->apitest_url = SITE_BASEURL . "apitest/" . $_controller . "/" . $_action;
				$this->api_url = SITE_BASEURL . "api/" . $_controller . "/" . $_action;
				$this->_params = $_params;
				if((int)method_exists($this,$_action) > 0) 
				{
					$request_body = file_get_contents('php://input');
					$data = json_decode($request_body);
					if ($data != null) {
						foreach($data as $key => $value)
						{
							$this->$key = $value;
						}
					}

					if (defined("API_TEST"))
						$this->_action_type = ACTIONTYPE_HTML;
					else {
						$this->_action_type = ACTIONTYPE_AJAXJSON;
						if ($this->TOKEN != null) {
							_load_session_from_token($this->TOKEN);

							$db = db::getDB();
							$db->set_time_zone(_time_zone());
						}
						$this->checkPriv($_action, UTYPE_NONE);
					}

					@call_user_func_array(array($this, $_action), $_params);
					// exit response
				}
			}

			$this->showError(ERR_NOTFOUND_PAGE);
		}

		public function setApiParams($params)
		{
			$this->api_param_names = $params;
			foreach($params as $param)
			{
				if ($this->existProp($param))
					$this->api_params->$param = $this->$param;
			}
			
			if (defined("API_TEST"))
				$this->showTestView("api_test");
		}

		private function showTestView($view) {
			$this->_layout = _template("layout/api.php");
			$this->_view = _template("view/" . $view . ".php");

			if (file_exists($this->_layout)) {
				require_once($this->_layout);
				exit;
			}
			else
				$this->showError(ERR_NOTFOUND_PAGE);
		}
	}
?>