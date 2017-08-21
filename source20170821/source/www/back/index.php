<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
	header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
	header ("Cache-Control: no-cache, must-revalidate"); 
	header ("Pragma: no-cache"); 
	define('DEFAULT_PHP', 'index.php');

	require_once("include/utility.php");

	$_rurl = isset($_SERVER["REDIRECT_URL"]) ? $_SERVER["REDIRECT_URL"] : "";
	$_rurl = substr($_rurl, strlen(SITE_BASE));
	$_params = preg_split("/\//", $_rurl);
	/*
	if ($_params[0] == "backend") {
		define("HOME_BASE", "backend/");
		loadController(getParam(1, "home"), 
			getParam(2, "index"), 
			getParam(3), 
			HOME_BASE . "controller/");
	}
	else */ if ($_params[0] == "api" || $_params[0] == "apitest") {
		define("HOME_BASE", "");
		if ($_params[0] == "apitest")
			define("API_TEST", "1");
		loadController(
			getParam(1, "api"), 
			getParam(2, "_methods"), 
			getParam(3), 
			HOME_BASE . "controller/api/");
	}
	else {
		define("HOME_BASE", "");
		loadController(
			getParam(0, "home"), 
			getParam(1, "index"), 
			getParam(2), HOME_BASE . "controller/");
	}

	function getParam($i, $default = null) 
	{
		global $_params;
		if ($default == null) {
			return array_slice($_params, $i);
		}
		else {
			return count($_params) > $i && $_params[$i] != "" ? $_params[$i] : $default;
		}
	}

	function loadController($_controller, $_action, $_params, $_path)
	{
		$controller_class = stripslashes($_controller) . "Controller";
		$_path .= $controller_class . ".php";

		if (file_exists($_path))
		{
			require_once($_path);

			$curController = new $controller_class;
			$curController->process($_controller, $_action, $_params);
		}
		else
		{
			$curController = new controller;
			$curController->showError(ERR_NOTFOUND_PAGE);
		}
	}
?>