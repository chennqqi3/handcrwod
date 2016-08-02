<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/09/01
	---------------------------------------------------*/

	define('PRODUCT_NAME',		'ハンドクラウド管理ページ');

	define('DEFAULT_APP_URL',	'https://www.handcrowd.com/app/');

	define('SITE_BASE',			preg_replace('/\/'. DEFAULT_PHP . '/i', '', $_SERVER["SCRIPT_NAME"]) . "/");
	define('SITE_ROOT',			preg_replace('/\/'. DEFAULT_PHP . '/i', '', $_SERVER["SCRIPT_FILENAME"]) . "/");

	$http_schema = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https" : "http");
	define("SITE_ORIGIN",		isset($_SERVER["HTTP_HOST"]) ? ($http_schema . "://" . $_SERVER["HTTP_HOST"]) : '');
	define("SITE_BASEURL",		SITE_ORIGIN . SITE_BASE);

	@include_once("config.inc");

	define("SITE_MODE",			0); // 0:Standard 1:CreateMockup 2:Mockup
	define("IS_NOMOCKUP",		SITE_MODE == 0);
	define("IS_CREATEMOCKUP",	SITE_MODE == 1);
	define("IS_MOCKUP",			SITE_MODE == 2);

	define('LOG_MODE',			1); // 0:NONE, 1:DEBUG
	define('LOG_PATH',			SITE_ROOT . 'log/');

	define('TMP_PATH',			SITE_ROOT . 'tmp/');
	define('DATA_PATH',			SITE_ROOT . 'data/');

	define('AVARTAR_URL',		'avartar/');
	define('AVARTAR_PATH',		DATA_PATH . AVARTAR_URL);
	define('ATTACH_URL',		'mattach/');
	define('ATTACH_PATH',		DATA_PATH . ATTACH_URL);
	define('THUMB_URL',			'thumb/');
	define('THUMB_PATH',		DATA_PATH . ATTACH_URL);
	define('HOMELOGO_URL',		'homelogo/');
	define('HOMELOGO_PATH',		DATA_PATH . HOMELOGO_URL);

	define('LANG_PATH',			SITE_ROOT . '/lang/');

	define('CAT_ABOUTSITE',		1);
		
	define('PAD_SIZE', 4);

	define("BATCH_INTERVAL", 30);

	define('BOT_USER_ID',		0);
	define('BOT_USER_NAME',		'Bot');

	// browser flag
	define('ISIE',				(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], "MSIE")) ? true : false);
	define('ISIE6',				(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 6.0")) ? true : false);
	define('ISIE7',				(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 7.0")) ? true : false);
	define('ISIE8',				(isset($_SERVER['HTTP_USER_AGENT']) && strstr($_SERVER['HTTP_USER_AGENT'], "MSIE 8.0")) ? true : false);

	define("BATCH_TIME_LIMIT", 30);
	define("UNREAD_PUSH_LIMIT", 10); // 10 seconds

	if (!defined('DEFAULT_LANGUAGE')) 
		define('DEFAULT_LANGUAGE', 'ja_jp');

	if (!LOG_MODE) {
		error_reporting(0);
		ini_set('display_errors', '0');
	}

	include_once("consts.php");

	include_once("resource/lang/" . _lang() . ".php");

	if (_request("TOKEN") != null)
		_load_session_from_token(_request("TOKEN"));
	else
		session_start();

	if (defined('OB_DISABLE'))
    	ob_implicit_flush();
	else
		ob_start("mb_output_handler");
	header("Content-Type: text/html; charset=UTF-8");
	// setting mbstring
	mb_internal_encoding("UTF-8");

	// global error message
	$g_err_msg = "";

	if (_time_zone() != null) 
		date_default_timezone_set(_time_zone());

	include_once("include/controller.php");
	include_once("include/module.php");
	include_once("include/timezones.php");

	//---------------------
	// 1. Auto loadding class
	//---------------------
	function __autoload($class_name)
	{
		if ($class_name == "db")
			include_once("db/db.php");
		if ($class_name == "model")	
			include_once("db/model.php");
		if (preg_match('/Module$/', $class_name)) {
			include_once(HOME_BASE . "module/" . $class_name . ".php");
		}
		else if (preg_match('/Helper$/', $class_name)) {
			include_once("include/helpers/" . $class_name . ".php");
		}
		else if (file_exists("model/" . $class_name . ".php")){
			include_once("model/" . $class_name . ".php");
		}
		else if ($class_name == "PHPMailer") {
			include_once("include/plugins/mail/" . strtolower($class_name) . ".php");
		}
		else if ($class_name == "xml") {
			include_once("include/plugins/xml/" . $class_name . ".php");
		}
		else if ($class_name == "ldap") {
			include_once("include/plugins/ldap/" . $class_name . ".php");
		}
		else if ($class_name == "APIController") {
			include_once("controller/api/apiController.php");
		}
		else if ($class_name == "Facebook") {
			include_once("include/plugins/facebooksdk/facebook.php");
		}
		else {
			$classPath = explode('_', $class_name);
			if ($classPath[0] == 'Google') {
				if (count($classPath) > 3) {
					// Maximum class file path depth in this project is 3.
					$classPath = array_slice($classPath, 0, 3);
				}
				$filePath = 'include/plugins/googleapi/src/' . implode('/', $classPath) . '.php';
				if (file_exists($filePath)) {
					require_once($filePath);
					return;
				}
			}

			$classPath = explode('\\', $class_name);
			if ($classPath[0] == 'WebSocket')
			{
				$filePath = 'include/plugins/' . implode('/', $classPath) . '.php';
				if (file_exists($filePath)) {
					require_once($filePath);
				}
				return;
			}
		}
	}

	//---------------------
	// 2. HTTP related
	//---------------------
	
	// get data of Query
	function _request($name)
	{
		$ret = _post($name);
		if ($ret != null)
			return $ret;

		return _get($name);
	}

	// get POST data
	function _post($txt, $key=null)
	{
		global $_POST;
		
		if ($key == null)
			$ret = isset($_POST[$txt]) ? $_POST[$txt] : null;
		else
			$ret = isset($_POST[$txt][$key]) ? $_POST[$txt][$key] : null;

		if(!isset($ret))
			return $ret;

		$ret = str_replace("\\\\", "\\", $ret);
		$ret = str_replace("\\\"", "\"", $ret);
		$ret = str_replace("\\'", "'", $ret);

		return $ret;
	}
	
	// get GET data
	function _get($name)
	{
		global $_GET;
		
		return isset($_GET[$name]) ? $_GET[$name] : null;
	}
	
	// clear/get/set Session data
	function _session($name=null, $value="@no_val@")
	{
		global $_SESSION;
		if ($name == null && $value == "@no_val@")
		{
			global $_COOKIE;
			if (isset($_COOKIE[session_name()]))
				setcookie(session_name(), '', time()-42000, '/');
			session_destroy();
		}
		else if ($value == "@no_val@") {
			if (!is_array($_SESSION) || !array_key_exists($name, $_SESSION))
				return null;

			return $_SESSION[$name];
		}
		else 
			$_SESSION[$name] = $value;
	}

	// get/set Cookie data
	function _cookie($name=null, $value="@no_val@")
	{
		global $_COOKIE;
		if ($value == "@no_val@") {
			if (!array_key_exists($name, $_COOKIE))
				return null;

			return $_COOKIE[$name];
		}
		else 
			setcookie($name, $value, time() + 3600 * 24 * 30, '/');
	}
	
	function _load_ip_session()
	{
		$session_id = str_replace(".", "a", _ip());

		session_write_close();
		session_id($session_id);
		session_start();
	}

	function _load_session_from_token($token)
	{
		if ($token != null) {
			$tokens = @preg_split("/:/", $token);
			if (count($tokens) == 2) {
				$org_session_id = session_id();
				$user_id = $tokens[0];
				$session_id = $tokens[1];
				@session_write_close();
				session_id($session_id);
				session_start();
				$session = session::getModel(array($session_id, $user_id));
				if ($session == null || $session->user_id != $user_id) {
					session_write_close();
					session_id($org_session_id);
					session_start();
					return false;
				}

				if ($session->user_id == $user_id) {
					if (_user_id() != $user_id) {
						$user = user::getModel($user_id);
						if ($user == null)
							return false;
						user::init_session_data($user);
					}
					else {
						return true;
					}
				}
				return false;
			}
		}

		return false;
	}
	
	// clear/get/set Server data
	function _server($name=null, $value="@no_val@")
	{
		global $_SESSION;

		$old_session_id = session_id();
		session_write_close();

		session_id("SERVER");
		session_start();
		
		$ret = null;
		if ($name == null && $value == "@no_val@")
		{
			$_SESSION = array();
		}
		else if ($value == "@no_val@") {
			if (!array_key_exists($name, $_SESSION))
				$ret = null;
			else
				$ret = $_SESSION[$name];
		}
		else 
			$_SESSION[$name] = $value;
		session_write_close();

		session_id($old_session_id);
		session_start();

		return $ret;
	}
	
	// goto URL
	function _goto($url)
	{
		ob_clean();
		header('Location: ' . filter_var($url, FILTER_SANITIZE_URL));
		exit;
	}

	function _nocache()
	{
		header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");  // Date in the past
		header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");   // always modified
		header ("Cache-Control: no-cache, must-revalidate");  // HTTP/1.1
		header ("Pragma: no-cache");  // HTTP/1.0
	}
	
	// convert relative url to absolute url
	function _url($url)
	{
		return SITE_BASEURL . $url;
	}

	function _page_url($page)
	{
		$utype = _utype();
		switch($page) {
			case PAGE_HOME:
				return "home";
				break;
		}
		return "home";
	}

	//---------------------
	// 3. String & Number & Date related
	//---------------------
	
	function _is_empty($str)
	{
		return $str == null || $str == "";
	}

	// generate sql safe string
	function _sql($txt)
	{
		if ($txt === null || $txt === "")
			return "NULL";

		if (substr($txt, 0, 2) == "##")
			return substr($txt, 2);

		// 特殊文字
		$txt = str_replace("", "", $txt);

		//$txt = str_replace("'", "''", $txt);		
		$txt = mysql_real_escape_string($txt);
		return "'" . $txt . "'";
	}

	function _sql_date($d=null)
	{
		if ($d == null)
			$d = time();
		return _sql(date('Y-m-d', $d));
	}

	function _sql_datetime($d=null)
	{
		if ($d == null)
			$d = time();
		return _sql(date('Y-m-d H:i:s', $d));
	}

	function _date($d=null, $format="Y-m-d")
	{
		if ($d == null)
			$d = time();
		return date($format, $d);
	}

	function _datetime($d=null, $format="Y-m-d H:i:s")
	{
		if ($d == null)
			$d = time();
		return date($format, $d);
	}

	function _google_date($d=null)
	{
		if ($d == null)
			$d = time();
		return date("Y-m-d", $d);
	}

	function _google_datetime($d=null)
	{
		if ($d == null)
			$d = time();
		return date("Y-m-d", $d) . "T" . date("H:i:s", $d);
	}

	function _first_weekday($date)
	{
		$time = strtotime($date);

		return date('Y-m-d', strtotime('Last Sunday', $time));
	}

	function _last_weekday($date)
	{
		$time = strtotime($date);

		return date('Y-m-d', strtotime('Next Saturday', $time));
	}

	function _weekday($d=null)
	{
		if ($d == null)
			$d = time();
		$date = getdate($d);
		return $date["wday"];
	}

	function _time($d=null, $format="H:i")
	{
		if ($d == null)
			$d = time();
		return date($format, $d);
	}

	function _trim_all($s)
	{
		return str_replace(" ", '', $s);
	}

	function _is_valid_number($val)
	{
		if (intval($val) == NULL)
			return false;

		return true;
	}

	function _is_valid_date($val)
	{
		$ret = date_parse($val);

		if ($ret["error_count"] > 0)
			return false;

		return true;
	}

	function _time_zone($time_zone = null)
	{
		if ($time_zone == null) { // read
			$time_zone = _session('TIME_ZONE');
			if ($time_zone != null)
				return $time_zone;
			else {
				if (defined('TIME_ZONE'))
					return TIME_ZONE;
				else
					null;
			}
		}
		else { // write
			_session('TIME_ZONE', $time_zone);
		}
	}
	
	function _str2html($str)
	{
		$str = htmlspecialchars($str);
		$str = preg_replace('/ /i', '&nbsp;', $str);
		return nl2br($str);
	}

	function _str2paragraph($str)
	{
		$str = htmlspecialchars($str);

		$ps = preg_split("/\n/", $str);
		
		$str = "";

		foreach($ps as $p)
		{
			$str .= "<p>" . $p . "</p>";
		}

		return $str;
	}

	function _shift_space($str, $shift=1)
	{
		$ps = preg_split("/\n/", $str);
		
		$str = array();
		
		$space = "";
		for ($i = 0; $i < $shift; $i ++) {
			$space .= "   ";
		}
		foreach($ps as $p)
		{
			$str[] = $space . $p;
		}


		return implode("\n", $str);
	}

	function _str2firstparagraph($str)
	{
		$str = htmlspecialchars($str);

		$ps = preg_split("/\n/", $str);
		
		if (count($ps) > 0) 
			$str = "<p>" . $ps[0] . "</p>";
		else
			$str = "<p></p>";

		return $str;
	}

	function _str2json($str) 
	{
		$str = str_replace("\\", "\\\\", $str);
		$str = str_replace("\r", "", $str);
		$str = str_replace("\n", "\\n", $str);
		$str = str_replace("\"", "\\\"", $str);
		return $str;
	}

	function _number($v) 
	{
		if ($v == null)
			return "0";
		return number_format($v);
	}

	function _currency($v) 
	{
		if ($v == null)
			return "0.00";
		return number_format($v, 2, '.', ',');
	}

	function _now()
	{
		$db = db::getDB();
		$now = $db->scalar("SELECT NOW()");

		return strtotime($now);
	}

	function _sjis2utf8($str)
	{
		return mb_convert_encoding($str, "UTF-8", "SJIS-win");
	}

	function _number_from_csv($str)
	{
		$str = str_replace(",", "", strval($str));
		$str = str_replace("\\", "", strval($str));
		return $str;
	}

	function _datetime_from_csv($str)
	{
		if (_is_empty($str))
			return null;
		
		$dt = strtotime($str);
		if ($dt === FALSE)
			return null;

		return _datetime($dt);
	}

	//---------------------
	// 4. Uploading related
	//---------------------
	function _mkdir($dir)
	{
		if (!file_exists($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	function _upload($field, $dest_path)
	{
		global $_FILES;
		if ($_FILES[$field]["error"] != 0)
			return null;

		$dir = dirname($dest_path);
		_mkdir($dir);

		if (!move_uploaded_file($_FILES[$field]["tmp_name"], $dest_path))
			return null;

		$ext = _get_uploaded_ext($field);
		if ($ext == 'jpg')
		{
			$exif = exif_read_data($dest_path);
			if ($exif !== FALSE) {
				$orientation = $exif['Orientation'];
				switch ($orientation) {
				   	case 3:
				   		$source = imagecreatefromjpeg($dest_path);
				    	$rotated = imagerotate($source, 180, 0);
				      	break;
				   	case 6:
				   		$source = imagecreatefromjpeg($dest_path);
				    	$rotated = imagerotate($source, -90, 0);
				      	break;
				   	case 8:
				   		$source = imagecreatefromjpeg($dest_path);
				    	$rotated = imagerotate($source, 90, 0);
				      	break;
				}
				imagejpeg($rotated, $dest_path, 100);
			}
		}

		return $_FILES[$field]["name"];
	}

	function _get_uploaded_filename($field)
	{
		global $_FILES;
		$filename = $_FILES[$field]["name"];

		return _basename($filename);
	}

	function _get_uploaded_filesize($field, $unit = 2)
	{
		global $_FILES;

		$file_size = filesize($_FILES[$field]["tmp_name"]);
		
		switch ($unit) {
			case 1: // KB
				return round($file_size / 1024, 2);
				break;

			case 2: // MB
				return round($file_size / pow(1024, 2), 2);
				break;

			case 3: // GB
				return round($file_size / pow(1024, 3), 2);
				break;

			case 4: // TB
				return round($file_size / pow(1024, 4), 2);
				break;
			
			default: // byte
				return $filesize;
				break;
		}
	}

	function _get_uploaded_ext($field)
	{
		global $_FILES;
		if ($_FILES[$field]["error"] != 0)
			return null;
		$parts = pathinfo($_FILES[$field]["name"]);
  		$ext = strtolower($parts['extension']);
		if ($ext == "png" || 
			$_FILES[$field]["type"] == "image/png" ||
			$_FILES[$field]["type"] == "image/x-png")
			return "png";
		if ($ext == "jpg" || 
			$_FILES[$field]["type"] == "image/jpeg" ||
			$_FILES[$field]["type"] == "image/pjpeg")
			return "jpg";
		if ($ext == "gif" || 
			$_FILES[$field]["type"] == "image/gif")
			return "gif";
		
		return null;
	}

	function _extname($path)
	{
		$parts = pathinfo($path);
		$ext = strtolower($parts['extension']);
		return $ext;
	}

	function _full_url($url, $renew=false)
	{
		if ($url == null)
			return null;

		if ($renew)
			_renew_url_cache_id();

		return SITE_BASEURL . $url . "?" . _url_cache_id();
	}

	function _url_cache_id()
	{
		$cache_id = _session("URL_CACHE_ID");
		if ($cache_id == null) {
			return session_id();
		}
		else {
			return $cache_id;
		}
	}

	function _renew_url_cache_id()
	{
		_session("URL_CACHE_ID", _newId());
	}

	//---------------------
	// 5. Image Processing
	//---------------------
	function _resize_image($path, $source_ext, $w, $h=null){
		$path_parts = pathinfo($path);
		$ext = strtolower($path_parts['extension']);
		if ($ext == "")
			$ext = $source_ext;

		if ($source_ext == "png")
			$src_img = imagecreatefrompng($path); 
		else if ($source_ext == "jpg")
			$src_img = imagecreatefromjpeg($path); 
		else if ($source_ext == "gif")
			$src_img = imagecreatefromgif($path);  

		$ow = imagesx($src_img);
		$oh = imagesy($src_img);

		if ($h == null)
			$h = intval($oh * $w / $ow);

		$dst_img = imagecreatetruecolor($w, $h); 
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $w, $h, $ow, $oh); 

		if ($ext == "png")
			imagepng($dst_img, $path); 
		else if ($ext == "jpg")
			imagejpeg($dst_img, $path);  
		else if ($ext == "gif")
			imagegif($dst_img, $path);

		imagedestroy($src_img);
		imagedestroy($dst_img);
	}

	function _resize_photo($path, $dst_ext, $maxw, $maxh){
		$path_parts = pathinfo($path);
		
		$src_img = imagecreatefromstring(file_get_contents($path));
		/*	
		$ext = strtolower($path_parts['extension']);
		if ($ext == "png")
			$src_img = imagecreatefrompng($path); 
		else if ($ext == "jpg")
			$src_img = imagecreatefromjpeg($path); 
		else if ($ext == "gif")
			$src_img = imagecreatefromgif($path);  
		*/

		$ow = imagesx($src_img);
		$oh = imagesy($src_img);

		if ($ow < $maxw && $oh < $maxh)
			return;
		
		$w = $maxw;
		$h = intval($oh * $maxw / $ow);
		if ($h > $maxh) {
			$h = $maxh;
			$w = intval($ow * $maxh / $oh);
		}

		$dst_img = imagecreatetruecolor($w, $h); 
		imagecopyresampled($dst_img, $src_img, 0, 0, 0, 0, $w, $h, $ow, $oh); 
		if ($dst_ext == "png")
			imagepng($dst_img, $path); 
		else if ($dst_ext == "jpg")
			imagejpeg($dst_img, $path);  
		else if ($dst_ext == "gif")
			imagegif($dst_img, $path);

		imagedestroy($src_img);
		imagedestroy($dst_img);
	}

	function _resize_thumb($path, $source_ext, $maxw=PHOTO_MAX_WIDTH, $maxh=PHOTO_MAX_HEIGHT){
		if ($source_ext == "png")
			$src_img = imagecreatefrompng($path); 
		else if ($source_ext == "jpg")
			$src_img = imagecreatefromjpeg($path); 
		else if ($source_ext == "gif")
			$src_img = imagecreatefromgif($path);  

		$ow = imagesx($src_img);
		$oh = imagesy($src_img);
		
		$w = $maxw;
		$h = intval($oh * $maxw / $ow);
		if ($h > $maxh) {
			$h = $maxh;
			$w = intval($ow * $maxh / $oh);
		}

		$dst_img = imagecreatetruecolor($maxw, $maxh); 
		imagealphablending($dst_img, true);
		$back = imagecolorallocatealpha($dst_img, 255, 255, 255, 0);
		imagefilledrectangle($dst_img, 0, 0, $maxw - 1, $maxh - 1, $back);
		imagecopyresampled($dst_img, $src_img, ($maxw - $w) / 2, ($maxh - $h) / 2, 0, 0, $w, $h, $ow, $oh); 
		imagesavealpha($dst_img, true);
		imagepng($dst_img, $path); 

		imagedestroy($src_img);
		imagedestroy($dst_img);
	}

	function _resize_userphoto($path, $source_ext, $width, $height){
		$path_parts = pathinfo($path);
		$ext = strtolower($path_parts['extension']);
		if ($ext == "")
			$ext = $source_ext;

		if ($source_ext == "png")
			$src_img = imagecreatefrompng($path); 
		else if ($source_ext == "jpg")
			$src_img = imagecreatefromjpeg($path);  
		else if ($source_ext == "gif")
			$src_img = imagecreatefromgif($path);  

		$ow = imagesx($src_img);
		$oh = imagesy($src_img);
		
		$w = $width;
		$h = intval($oh * $width / $ow);
		if ($h < $height) {
			$h = $height;
			$w = intval($ow * $height / $oh);
		}
		$x = - ($w - $width) / 2;
		$y = - ($h - $height) / 2;

		$dst_img = imagecreatetruecolor($width, $height); 
		if ($ext == "png") {
			imagealphablending($dst_img, false);
			/*
			$back = imagecolorallocatealpha($dst_img, 255, 255, 255, 0);
			imagefilledrectangle($dst_img, 0, 0, $w - 1, $h - 1, $back);
			*/
		}
		imagecopyresampled($dst_img, $src_img, $x, $y, 0, 0, $w, $h, $ow, $oh); 
		if ($ext == "png") {
			imagesavealpha($dst_img, true);
			imagepng($dst_img, $path); 
		}
		else if ($ext == "jpg")
			imagejpeg($dst_img, $path);  
		else if ($ext == "gif")
			imagegif($dst_img, $path); 

		imagedestroy($src_img);
		imagedestroy($dst_img);
	}

	//---------------------
	// 6. CSV related
	//---------------------
	function _csvheader($filename)
	{		
		header("ContentType: application/text-csv; charset=UTF-8");
		header("Content-Disposition:attachment; filename=" . $filename);
		header("Content-Transfer-Encoding: binary");
		header("Cache-Control: private, must-revalidate");  // HTTP/1.1
		header("Expires: 0");
	}

	//---------------------
	// 7. Log related
	//---------------------
	function _log($log_type, $msg)
	{
		jlog::write($log_type, $msg);
	}

	function _access_log($msg, $url = "")
	{
		if ($url != "")
			$url = " url:" . $url;
		_log(LOGTYPE_ACCESS, $msg . $url);
	}

	function _opr_log($msg)
	{
		_log(LOGTYPE_OPERATION, $msg);
	}

	function _warn_log($msg)
	{
		_log(LOGTYPE_WARNING, $msg);
	}

	function _err_log($msg)
	{
		_log(LOGTYPE_ERROR, $msg);
	}

	function _debug_log($msg)
	{
		_log(LOGTYPE_DEBUG, $msg);
	}

	function _batch_log($msg)
	{
		_log(LOGTYPE_BATCH, $msg);
	}


	//---------------------
	// 8. File related
	//---------------------
	function _fwrite($path, $str)
	{
		$fp = @fopen($path,"wb");
		if ($fp != null) {
			@fputs($fp, $str);
			@fclose($fp);
		}
	}

	function _fread($path)
	{
		$fp = @fopen($path,"rb");
		if ($fp != null) {
			$str = '';
			while (!feof($fp)) {
			  $str .= fread($fp, 8192);
			}
			@fclose($fp);
		}
		return $str;
	}

	function _basename($file) 
	{ 
		if (strpos($file,'\\') !== false || strpos($file,'/') !== false) {
	    	return end(explode('/',$file)); 	
		}
		else
			return $file;
	} 

	//---------------------
	// 9. User Session Related
	//---------------------
	function _utype($utype = null) {
		if ($utype == null)
			return _session("utype");
		else
			_session("utype", $utype);
	}
	function _user_plan() {
		$user = user::getModel(_user_id());
		if ($user == null)
			$plan_type = PLAN_FREE;
		else
			$plan_type = $user->plan_type;

		return new planconfig($plan_type);
	}

	function _user_id($user_id = null) {
		if ($user_id == null)
			return _session("user_id");
		else
			_session("user_id", $user_id);
	}

	function _user_name($user_name = null) {
		if ($user_name == null)
			return _session("user_name");
		else
			_session("user_name", $user_name);
	}

	$_cur_user = null;
	function _user() {
		global $_cur_user;
		if ($_cur_user == null) {
			$user = user::getModel(_user_id());
			if ($user != null) {
				$_cur_user = $user;
			}
		}
		return $_cur_user;
	}

	function _login_ip($login_ip = null) {
		if ($login_ip == null)
			return _session("login_ip");
		else
			_session("login_ip", $login_ip);
	}

	function _first_logined($first = null) {
		if ($first == null)
			return _session("first_logined") == 1;
		else
			_session("first_logined", $first);
	}

	function _auto_login_token($token = null) {
		if ($token == null)
			return _cookie("hc_token");
		else
			_cookie("hc_token", $token);
	}
	
	function _editor_type($editor_type = null) {
		if ($editor_type == null)
			return _session("editor_type");
		else
			_session("editor_type", $editor_type);
	}

	//---------------------
	// 10. File Path Related
	//---------------------
	function _tmp_path($ext = null)
	{
		$tmppath = "";
		$seed = time();
		while(1) {
			$tmpfile = substr(md5($seed), 0, 10);
			if ($ext != null)
				$tmpfile .= "." . $ext;
			$tmppath = TMP_PATH . $tmpfile;
			
			if (!file_exists($tmppath))
				break;
			
			$seed += 12345;
		}
		
		return $tmppath;
	}

	function _avartar_url($id)
	{
		if ($id == null)
			$id = "all";
		return AVARTAR_URL . $id . ".jpg";
	}

	function _avartar_full_url($id, $renew=false)
	{
		if ($id == null)
			$id = "all";
		if ($renew)
			_renew_avartar_cache_id();

		return SITE_BASEURL . AVARTAR_URL . $id . ".jpg?" . _avartar_cache_id();
	}

	function _avartar_cache_id()
	{
		$cache_id = _session("AVARTAR_CACHE_ID");
		if ($cache_id == null) {
			return session_id();
		}
		else {
			return $cache_id;
		}
	}

	function _renew_avartar_cache_id()
	{
		_session("AVARTAR_CACHE_ID", _newId());
	}

	function _siteimg_url($id)
	{
		return SITEIMG_URL . $id . ".jpg";
	}

	//---------------------
	// 11. Template Related
	//---------------------
	function _set_template($t)
	{
		if (HOME_BASE == "backend/")
			_session("backend_template", "backend/templates/" . $t . "/");
		else
			_session("template", "templates/" . $t . "/");
	}

	function _template($path)
	{
		if (HOME_BASE == "backend/")
			$template = _session("backend_template");
		else
			$template = _session("template");
		if ($template == null)
			$template = HOME_BASE . "templates/normal/";
		return $template . $path;
	}

	//---------------------
	// 12. View Output Related
	//---------------------
	function p($d)
	{
		print $d;
	}

	function _nodata_message($data)
	{
		if (count($data) == 0) {
			?>
			<div class="alert alert-block">
				<?php p(_err_msg(ERR_NODATA));?>
			</div>
			<?php
		}
	}

	function _code_label($code, $val) 
	{
		global $g_codes;
		if (isset($g_codes) && isset($g_codes[$code])) {
			$codes = $g_codes[$code];
			if (isset($codes) && isset($codes[$val]))
				return $codes[$val];
		}
		return '';
	}

	function _err_msg($err, $param1=null, $param2=null)
	{
		global $g_err_msg, $g_err_msgs;
		$err_msg = "";
		if ($g_err_msg != "")
			$err_msg = $g_err_msg;
		else
			$err_msg = $g_err_msgs[$err];

		$err_msg = @sprintf($err_msg, $param1, $param2);
		return $err_msg;
	}

	//---------------------
	// 13. Mail & SMS Related
	//---------------------

	// send email
	function _send_mail($to_address, $to_name, $title, $body, $html=false)
	{
		if (MAIL_ENABLE == ENABLED) {
			$mailer = new PHPMailer();
			$mailer->From = MAIL_FROM;
			$mailer->FromName = MAIL_FROMNAME;
			$mailer->SMTPAuth = MAIL_SMTP_AUTH;
			$mailer->Host 	= MAIL_SMTP_SERVER;
			$mailer->Username = MAIL_SMTP_USER;
			$mailer->Password = MAIL_SMTP_PASSWORD;
			$mailer->Port     = MAIL_SMTP_PORT;
			$mailer->IsSMTP();
			$mailer->Subject = $title;
			$mailer->Body = $body;
			$mailer->IsHTML($html);   
			$mailer->AddAddress($to_address, $to_name);
			$mailer->Send();
		}
	}

	//---------------------
	// 13. Mail & SMS Related
	//---------------------
	// set/get current language
	function _lang($lang = null) {
		if ($lang == null)
		{
			$lang = _session("LANGUAGE");
			return $lang == null ? DEFAULT_LANGUAGE : $lang;
		}
		else 
			_session("LANGUAGE", $lang);
	}

	function _l($str) {
		global $g_string;
		$lstr = isset($g_string[$str]) ? $g_string[$str] : null;
		return $lstr == null ? $str : $lstr;
	}

	function l($str) {
		print _l($str);
	}

	//---------------------
	// 14. Batch service related
	//---------------------
	function _install_batch() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$exe_path = SITE_ROOT . "/resource/service/batch.exe";
			$ini_path = SITE_ROOT . "/resource/service/batch.ini";

			_fwrite($ini_path, BATCH_INTERVAL . "000\n" . SITE_BASEURL . "batch");

			exec($exe_path . " -i");
			exec($exe_path . " -r");
		}
		else if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
			$ini_path = SITE_ROOT . "/resource/service/batch.ini";

			_fwrite($ini_path, BATCH_INTERVAL . "\n" . SITE_BASEURL . "batch");

			$install_batch = SITE_ROOT . "/resource/service/install_batch.sh";

			exec($install_batch);
		}
	}

	function _uninstall_batch() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$exe_path = SITE_ROOT . "/resource/service/batch.exe";

			exec($exe_path . " -k");
			exec($exe_path . " -u");
		}
		else if (strtoupper(substr(PHP_OS, 0, 5)) === 'LINUX') {
			$uninstall_batch = SITE_ROOT . "/resource/service/uninstall_batch.sh";

			exec($uninstall_batch);
		}
	}

	function _run_batch() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			$exe_path = SITE_ROOT . "/resource/service/batch.exe";

			echo exec($exe_path . " -r");
		}
	}

	//---------------------
	// 15. Other
	//---------------------
	function _newId($seed=null) {
		return md5($seed == null ? microtime() : microtime() . $seed);
	}

	function _key($k) {
		return md5("handcrowd_" . $k . "_2014");
	}

	function _erase_old($dir) {
		$files = scandir($dir);
		if (count($files) == 0)
			return;

		$now = time();
		foreach ($files as $file)
		{
			$tm = filectime($dir . $file);
			if ($now - $tm > 3600) // before 1 hour
				@unlink($dir . $file);
		}
	}

	function _ip()
	{
		global $_SERVER;
		if (isset($_SERVER["REMOTE_ADDR"]))
			return $_SERVER["REMOTE_ADDR"];
		return '';
	}

	function _in_blacklist()
	{
		$ip = _ip();
		if ($ip == "::1")
			return false;

		$ip = preg_split("/\./", $ip);
		if (count($ip) != 4)
			return false;


		if (defined("BLACKLIST") && BLACKLIST != "")
		{
			$ll = @preg_split("/;/", BLACKLIST);
			foreach ($ll as $l)
			{
				$bl = preg_split("/,/", $l);
				$addr = preg_split("/\./", $bl[1]);
				$mask = preg_split("/\./", $bl[2]);

				$check = true;
				for($i = 0; $i < 4; $i ++)
				{
					$ii = ($ip[$i] + 0) & ($mask[$i] + 0);
					if ($ii != $addr[$i])
						$check = false;
				}
				if ($check)
					return true;
			}
		}

		return false;
	}

	function _ifnull($val, $default)
	{
		return $val == null ? $default : $val;
	}

	function _path2id($path)
	{
		$ps = preg_split("/\//", $path);
		if ($ps == null)
			return $path;

		return $ps[count($ps) - 1] + 0;
	}

	function _rating($sum, $count)
	{
		if ($count > 0)
			return floor($sum / $count);
		else
			return 0;
	}
	
	// for sorting tree
	function _next_sort($sort)
	{
		$sorts = preg_split("/\//", $sort);

		if ($sorts != null) 
		{
			$last = count($sorts) - 1;
			$sorts[$last] = str_pad($sorts[$last] + 1, PAD_SIZE, "0", STR_PAD_LEFT);
			return join("/", $sorts);
		}
		else
			return null;
	}

	function _first_sort($sort)
	{
		$sorts = preg_split("/\//", $sort);

		if ($sorts != null) 
		{
			$last = count($sorts) - 1;
			$sorts[$last] = str_pad(0, PAD_SIZE, "0", STR_PAD_LEFT);
			return join("/", $sorts);
		}
		else
			return null;
	}

	// class utility
	function get_public_methods($className) {
		/* Init the return array */
		$returnArray = array();

		/* Iterate through each method in the class */
		foreach (get_class_methods($className) as $method) {

			/* Get a reflection object for the class method */
			$reflect = new ReflectionMethod($className, $method);

			/* For private, use isPrivate().  For protected, use isProtected() */
			/* See the Reflection API documentation for more definitions */
			if($reflect->isPublic()) {
				/* The method is one we're looking for, push it onto the return array */
				array_push($returnArray,$method);
			}
		}
		/* return the array to the caller */
		return $returnArray;
	}

	function get_this_class_methods($class){
		$array1 = get_public_methods($class);
		if($parent_class = get_parent_class($class)){
			$array2 = get_public_methods($parent_class);
			$array3 = array_diff($array1, $array2);
		}else{
			$array3 = $array1;
		}
		return($array3);
	}


	// pcntl related
	function _fork()
	{
		return -1;
		/*
		if (function_exists("pcntl_fork")) {
			$pid = pcntl_fork();
		}
		else {
			$pid = -1;
		}

		return $pid;
		*/
	}
	
	// json related
	function _json_encode($a=false)
	{
	    if (is_null($a)) return 'null';
	    if ($a === false) return 'false';
	    if ($a === true) return 'true';
	    if (is_scalar($a))
	    {
			if (is_float($a))
			{
				// Always use "." for floats.
				return floatval(str_replace(",", ".", strval($a)));
			}

			if (is_numeric($a))
			{
				$b = $a + 0;
				if (($a . "") === ($b . ""))
					return $a;
			}

			if (is_string($a))
			{
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '', '\\b', '\\f', '\"'));
				return '"' . str_replace($jsonReplaces[0], $jsonReplaces[1], $a) . '"';
			}
			else
				return $a;
	    }

	    $isList = true;
	    for ($i = 0, reset($a); $i < count($a); $i++, next($a))
	    {
	    	if (key($a) !== $i)
	    	{
	        	$isList = false;
	        	break;
	      	}
	    }

	    $result = array();
	    if ($isList)
	    {
	    	foreach ($a as $v) $result[] = _json_encode($v);
			return '[' . join(',', $result) . ']';
	    }
	    else
	    {
	      	foreach ($a as $k => $v) $result[] = '"' . $k . '":'._json_encode($v);
	      	return '{' . join(',', $result) . '}';
	    }
	}

	// push notification
	/*
	Development Phase:

	Step 1: Create Certificate .pem from Certificate .p12
	Command: openssl pkcs12 -clcerts -nokeys -out apns-dev-cert.pem -in apns-dev-cert.p12

	Step 2: Create Key .pem from Key .p12
	Command : openssl pkcs12 -nocerts -out apns-dev-key.pem -in apns-dev-key.p12

	Step 3: Optional (If you want to remove pass phrase asked in second step)
	Command : openssl rsa -in apns-dev-key.pem -out apns-dev-key-noenc.pem

	Step 4: Now we have to merge the Key .pem and Certificate .pem to get Development .pem needed for Push Notifications in Development Phase of App
	Command : cat apns-dev-cert.pem apns-dev-key-noenc.pem > apns-dev.pem (If 3rd step is performed )

	Command : cat apns-dev-cert.pem apns-dev-key.pem > apns-dev.pem (if not)

	Step 5: Check certificate validity and connectivity to APNS
	Command: openssl s_client -connect gateway.sandbox.push.apple.com:2195 -cert apns-dev-cert.pem -key apns-dev-key.pem (If 3rd step is not performed )
	Command: openssl s_client -connect gateway.sandbox.push.apple.com:2195 -cert apns-dev-cert.pem -key apns-dev-key-noenc.pem  (If performed )

	Production Phase:

	Step 1: Create Certificate .pem from Certificate .p12
	Command: openssl pkcs12 -clcerts -nokeys -out apns-pro-cert.pem -in apns-pro-cert.p12

	Step 2: Create Key .pem from Key .p12
	Command : openssl pkcs12 -nocerts -out apns-pro-key.pem -in apns-pro-key.p12

	Step 3: Optional (If you want to remove pass phrase asked in second step)
	Command : openssl rsa -in apns-pro-key.pem -out apns-pro-key-noenc.pem

	Step 4: Now we have to merge the Key .pem and Certificate .pem to get Production .pem needed for Push Notifications in Production Phase of App
	Command : cat apns-pro-cert.pem apns-pro-key-noenc.pem > apns-pro.pem (If 3rd step is performed ) Command : cat apns-pro-cert.pem apns-pro-key.pem > apns-pro.pem (if not)

	Step 5: Check certificate validity and connectivity to APNS
	Command: openssl s_client -connect gateway.push.apple.com:2195 -cert apns-pro-cert.pem -key apns-pro-key.pem  (If 3rd step is not performed )
	Command: openssl s_client -connect gateway.push.apple.com:2195 -cert apns-pro-cert.pem -key apns-pro-key-noenc.pem (If performed )
	*/

	$g_apns_context = null;
	function _send_push($device_type, $device_token, $message) {
        if ($device_type == 1) // iOS
        {
        	global $g_apns_context;
        	if ($g_apns_context == null) {
	            $g_apns_context = stream_context_create();

	            stream_context_set_option($g_apns_context, 'ssl', 'local_cert', APNS_CERT_PEM);
	            stream_context_set_option($g_apns_context, 'ssl', 'passphrase', APNS_CERT_PASSPHRASE);	
        	}
        	if (APNS_PRODUCTION)
	        	$fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $g_apns_context);
        	else
	            $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $g_apns_context);
            
            if (!$fp) {
                print "Failed to connect: $err $errstr" . PHP_EOL;
                return false;
            }

            $body['aps'] = array(
                'alert' => $message,
                'sound' => 'default',
                'badge' => '1'
            );

            $payload = json_encode($body);
            $msg = chr(0) . pack('n', 32) . pack('H*', $device_token) . pack('n', strlen($payload)) . $payload;
            $result = fwrite($fp, $msg, strlen($msg));
            if ($result)
                print "Push notification delivered to iOS. device_token:" . $device_token;
            fclose($fp);            
            
            return $result;
        }
        else if ($device_type == 2) // Android
        {
            $url='https://gcm-http.googleapis.com/gcm/send';
            $api_key = GCM_API_KEY; // change this by your API key

            /// --- Common PUSH Set ---
            $icon_url = "https://www.handcrowd.com/back/ico/favicon.png";

            $data=array(
            	"notification" => array("title" => "HandCrowd", "text" => $message, "icon"=> $icon_url, "color"=> "#3F51B5"),
            	"data" => array("title" => "HandCrowd", "message" => $message),
                "delay_while_idle"=> true,
            	"to" => $device_token
            );
            /*
            $data=array(
                'data' => $message,
                'dry_run'=>false,
                "delay_while_idle"=> true,
                'registration_ids' => array($device_token)
            );*/

            $curl = curl_init($url);
            $headers = array("Content-Type:" . "application/json", "Authorization:" . "key=" . $api_key);
            curl_setopt_array($curl, array (
                CURLOPT_HTTPHEADER =>$headers,      
                CURLOPT_ENCODING => "gzip" ,
                CURLOPT_FOLLOWLOCATION => true ,
                CURLOPT_RETURNTRANSFER => true ,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => json_encode($data)
            ));
            $result = curl_exec($curl); 
            try {
                $r = json_decode($result);
                $result = ($r->success == 1);
                if ($result)
                    print "Push notification delivered to Android. device_token:" . $device_token;
            }
            catch (Exception $e) {

            }
            curl_close ($curl); 

            return $result;
        }
	}

	// cserver
	function _chat_uri($app="chat")
	{
		$prot = CSERVER_SSL ? "wss" : "ws";
		return $prot . "://" . CSERVER_HOST . ":" . CSERVER_PORT . "/" . $app . "/";
	}	
?>