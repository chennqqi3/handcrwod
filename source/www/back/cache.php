<?php

	define("MEMCACHE_SERVER_NO", '00');

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

	define('SITE_BASE',			preg_replace('/\/cache.php/i', '', $_SERVER["SCRIPT_NAME"]) . "/");
	define('SITE_ROOT',			preg_replace('/\/cache.php/i', '', $_SERVER["SCRIPT_FILENAME"]) . "/");

	$http_schema = ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https" : "http");
	define("SITE_ORIGIN",		isset($_SERVER["HTTP_HOST"]) ? ($http_schema . "://" . $_SERVER["HTTP_HOST"]) : '');
	define("SITE_BASEURL",		SITE_ORIGIN . SITE_BASE);

	@include_once("config.inc");

	$_rurl = isset($_SERVER["REDIRECT_URL"]) ? $_SERVER["REDIRECT_URL"] : "";
	
	$_rurl = substr($_rurl, strlen(SITE_BASE));
	$_params = preg_split("/\//", $_rurl);	

	if (count($_params) <= 1) 
		exit;

	$_params = array_slice($_params, 1);

	$method = $_params[0];

	$request_body = file_get_contents('php://input');
	$_req = json_decode($request_body);

	switch ($method) {
		case 'mg':
			$cache_id = $_params[1];

			cache_connect();
			$content = cache_get($cache_id);

			if ($content === null) {
				db_connect();

				$sql = "SELECT content FROM t_cmsg WHERE del_flag=0 AND cache_id=" . _sql($cache_id);
				$content = db_scalar($sql);
				if ($content != null) {
					cache_set($cache_id, $content);
				}
			}

			$ret = array("content" => $content);
			finish($ret);
			break;
		
		case 'ms':
			$cache_id = $_params[1];
			$content = $_req->content;

			cache_connect();

			if ($cache_id == null) {
				do {
					$cache_id = new_cache_id("m");
				}
				while(cache_get($cache_id) != null);
			}

			cache_set($cache_id, $content);

			$ret = array("cache_id" => $cache_id);
			finish($ret);
			break;

		default:
			# code...
			break;
	}

	// Memcache related
	function cache_connect() {
		global $memcache;
		$memcache = new Memcache;
		$memcache->connect('localhost', 11211) or die ("Could not connect cache server");

		return $memcache;
	}

	function cache_get($key) {
		global $memcache;
		return $memcache->get($key);
	}

	function cache_set($key, $val) {
		global $memcache;
		$memcache->set($key, $val);
	}

	function new_cache_id($prefix) {
		return MEMCACHE_SERVER_NO . $prefix . md5(microtime());
	}

	// DB related
	function db_connect() {
		global $conn;
		$conn = @mysql_pconnect (DB_HOSTNAME . ":" . DB_PORT, DB_USER, DB_PASSWORD) or die("Database Connection Failed");
		
		mysql_select_db (DB_NAME, $conn) or die ("Could not select database");

		$sql = "SET NAMES utf8";
		@mysql_query($sql, $conn);

		return $conn;
	}

	function db_scalar($sql) {
		global $conn;

		$result = mysql_query($sql);

		if (!$result)
			return null;

		if (mysql_num_rows($result) != 1)
			return null;

		return mysql_result($result, 0, 0);
	}

	function _sql($txt)
	{
		if ($txt === null || $txt === "")
			return "NULL";

		// 特殊文字
		$txt = str_replace("", "", $txt);

		$txt = mysql_real_escape_string($txt);
		return "'" . $txt . "'";
	}

	function finish($json)
	{
		echo _json_encode($json);
		//header("Content-Type:".$this->_content_type);
		exit;
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
				static $jsonReplaces = array(array("\\", "/", "\n", "\t", "\r", "", "", "\b", "\f", '"'), array('\\\\', '\\/', '\\n', '\\t', '\\r', '', '', '\\b', '\\f', '\"'));
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

?>