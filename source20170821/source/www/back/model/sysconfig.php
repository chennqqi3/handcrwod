<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/
	define('MIN_PHP_VER', '5.4.7');

	class sysconfig
	{
		private $_props;
		private $conn;
		private $sql_result;

		private $_viewHelper;

		public function __construct()
		{
			$this->initProps(array(
				'version',

				'time_zone',

				/* db related */
				'db_hostname',
				'db_user',
				'db_password',
				'db_name',
				'db_port',

				/* e-mail related */
				"mail_enable",
				"mail_from",
				"mail_fromname",
				"mail_smtp_auth",
				"mail_smtp_server",
				"mail_smtp_user",
				"mail_smtp_password",
				"mail_smtp_port",

				/* google related */
				"google_enable",
				"google_client_id",
				"google_client_secret",
				"google_redirect_uris",
				"google_javascript_origins",
				"google_homepage_url",
				"google_product_logo",	

				/* facebook related */
				"facebook_enable",
				"facebook_app_id",
				"facebook_app_secret",
				"facebook_site_url",

				/* other */
				"admin_name", 
				"admin_password", 
				"admin_email", 
				"install_sample",

				/* security */
				"password_min_length",
				"password_strength",
				"login_fail_lock",

				/* entrance */
				"entrance_email",

				/* memcache server */
				"memcache_server",
				"memcache_port",
				"memcache_uri",

				/* chat server */
				"cserver_host",
				"cserver_port",
				"cserver_ssl",
				"cserver_debug",
				"cserver_cert_pem",
				"cserver_cert_passphrase",
				"apns_production",
				"apns_cert_pem",
				"apns_cert_passphrase",
				"gcm_api_key",

				"default_language"
				));

			$this->_viewHelper = new viewHelper($this);
			$this->init();
		}

		public function init() {
			$option = array(
				"version" => "2.0",

				"db_hostname" => "localhost",
				"db_user" => "root",
				"db_name" => "handcrowd",
				"db_port" => 3306,

				"admin_name" => "admin",
				"install_sample" => true,

				"time_zone" => "Asia/Tokyo",

				"mail_enable" => 1,
				"mail_fromname" => "ハンドクラウド",
				"mail_smtp_server" => "mail",
				"mail_smtp_auth" => true,
				"mail_smtp_port" => 25,

				"google_enable" => true,
				"google_redirect_uris" => SITE_BASEURL . "google/connect_finish<br/>" . SITE_BASEURL . "api/google/login_success",
				"google_javascript_origins" => SITE_ORIGIN,
				"google_homepage_url" => SITE_ORIGIN . "/app",
				"google_product_logo" => SITE_BASEURL . "resource/ico/favicon.png",

				"facebook_enable" => true,
				"facebook_site_url" => SITE_ORIGIN,

				"password_min_length" => "5",
				"password_strength" => "1",
				"login_fail_lock" => "5",

				"entrance_email" => "request@reflux.jp",

				"memcache_server" => "localhost",
				"memcache_port" => "11211",
				"memcache_uri" => "https://%s/cache/",

				"cserver_host" => "www.handcrowd.com",
				"cserver_port" => "9000",
				"cserver_ssl" => true,
				"cserver_debug" => true,
				"cserver_cert_pem" => SITE_ROOT . 'cert/cserver.pem',
				"cserver_cert_passphrase" => "handcrowd",
				"apns_production" => true,
				"apns_cert_pem" => SITE_ROOT . 'cert/push.pem',
				"apns_cert_passphrase" => "handcrowd2015",
				"gcm_api_key" => "AIzaSyByrbuzR9z_xDPMbXznjTucWsU0J-gKXKY",

				"default_language" => "ja_jp"
			);

			foreach($this->_props as $prop_name => $val) {
				$this->init_prop($prop_name, $option[$prop_name]);
			}
		}

		public function save() 
		{
			$path = SITE_ROOT . "/config.inc";

			$fp = fopen($path, "w+");
			if (!$fp)
				return ERR_FAILOPENFILE;

			$config = "<?php\n";
			$config .= $this->define_string("version");
			$config .= "\n";
			$config .= $this->define_string("time_zone");
			$config .= "\n";
			$config .= "/* DB related */\n";
			$config .= $this->define_string("db_hostname");
			$config .= $this->define_string("db_user");
			$config .= $this->define_string("db_password");
			$config .= $this->define_string("db_name");
			$config .= $this->define_number("db_port");
			$config .= "\n";

			$config .= "/* e-mail related */\n";
			$config .= "define('MAIL_ENABLE',			1); // 0: disable 1:enable\n";
			$config .= $this->define_string("mail_from");
			$config .= $this->define_string("mail_fromname");
			$config .= "define('MAIL_SMTP_AUTH',		" . ($this->mail_smtp_auth == ENABLED ? "true" : "false") . ");\n";
			$config .= $this->define_string("mail_smtp_server");
			$config .= $this->define_string("mail_smtp_user");
			$config .= $this->define_string("mail_smtp_password");
			$config .= $this->define_string("mail_smtp_port");
			$config .= "\n";

			$config .= "/* google related */\n";
			$config .= "define('GOOGLE_ENABLE',		" . ($this->google_enable == ENABLED ? "true" : "false") . ");\n";
			$config .= $this->define_string("google_client_id");
			$config .= $this->define_string("google_client_secret");
		
			$config .= "/* facebook related */\n";
			$config .= "define('FACEBOOK_ENABLE',	" . ($this->facebook_enable == ENABLED ? "true" : "false") . ");\n";
			$config .= $this->define_string("facebook_app_id");
			$config .= $this->define_string("facebook_app_secret");

			$config .= "/* security policy */\n";
			$config .= $this->define_number("password_min_length");
			$config .= $this->define_number("password_strength");
			$config .= $this->define_number("login_fail_lock");
			$config .= "\n";

			$config .= "/* entrance */\n";
			$config .= $this->define_string("entrance_email");
			$config .= "\n";

			$config .= "/* memcached */\n";
			$config .= $this->define_string("memcache_server");
			$config .= $this->define_number("memcache_port");
			$config .= $this->define_string("memcache_uri");
			$config .= "\n";

			$config .= "/* chat server */\n";
			$config .= $this->define_string("cserver_host");
			$config .= $this->define_string("cserver_port");
			$config .= "define('CSERVER_SSL',		" . ($this->cserver_ssl == ENABLED ? "true" : "false") . ");\n";
			$config .= "define('CSERVER_DEBUG',		" . ($this->cserver_debug == ENABLED ? "true" : "false") . ");\n";
			$config .= $this->define_string("cserver_cert_pem");
			$config .= $this->define_string("cserver_cert_passphrase");
			$config .= "define('APNS_PRODUCTION',		" . ($this->apns_production == ENABLED ? "true" : "false") . ");\n";
			$config .= $this->define_string("apns_cert_pem");
			$config .= $this->define_string("apns_cert_passphrase");
			$config .= $this->define_string("gcm_api_key");
			$config .= "\n";

			$config .= "/* language */\n";
			$config .= $this->define_string("default_language");

			$plan_config1 = new planconfig(PLAN_FREE);
			$config .= $plan_config1->config_string();
			$plan_config2 = new planconfig(PLAN_STUFF);
			$config .= $plan_config2->config_string();
			$plan_config3 = new planconfig(PLAN_MANAGER);
			$config .= $plan_config3->config_string();
			$plan_config4 = new planconfig(PLAN_PRESIDENT);
			$config .= $plan_config4->config_string();

			$config .= "?>";

			fwrite($fp, $config);

			fclose($fp);

			return ERR_OK;

		}

		public function initProps($arr)
		{
			foreach($arr as $item) {
				$this->$item = null;
			}
		}

		public function __get($prop) {
			if ($prop == "props")
				return $this->_props;
			else
			{
				return isset($this->_props[$prop]) ? $this->_props[$prop] : null ;
			}
		}

		public function __set($prop, $val) {
			if ($prop == "props") {
				if (is_array($val))
					$this->_props = $val;
			}
			else {
				$this->_props[$prop] = $val;
			}
		}

		public function __call($method, $params) {
			if (method_exists($this->_viewHelper, $method)) {
				call_user_func_array(array($this->_viewHelper, $method), $params);
			}
		}

		private function init_prop($prop, $init_val = null) {
			$const_name = strtoupper($prop);

			if (defined($const_name)) 
				$this->$prop = constant($const_name);
			else 
				$this->$prop = $init_val;

		}

		public function load($load_object)
		{
			foreach ($this->_props as $field_name => $val)
			{
				if ($load_object->existProp($field_name)) {
					if (is_array($load_object->$field_name)) {
						$this->$field_name = 0;
						foreach($load_object->$field_name as $v)
							$this->$field_name |= $v;
					}
					else {
						$this->$field_name = $load_object->$field_name;
					}
				}
			}
		}

		public function define_string($prop, $comment = "")
		{
			if ($comment != "")
				$comment = "// " . $comment;
			return "define('" . strtoupper($prop) . "',		'" . $this->$prop . "');" . $comment. "\n";
		}

		public function define_number($prop, $comment = "")
		{
			if ($comment != "")
				$comment = "// " . $comment;
			$val = ($this->$prop == null) ? 0 : $this->$prop;
			return "define('" . strtoupper($prop) . "',		" . $val . ");" . $comment. "\n";
		}

		public function connect($create_db=false) {
			$conn = @mysql_pconnect ($this->db_hostname . ":" . $this->db_port, $this->db_user, $this->db_password);
			if (!$conn) {
				return ERR_NODB;
			}

			$this->conn = $conn;

			if ($create_db) {
				$this->query("DROP DATABASE IF EXISTS " . $this->db_name . ";");
				$this->query("CREATE DATABASE " . $this->db_name . " CHARSET=utf8 COLLATE=utf8_general_ci;");
			}

			$this->query("SET NAMES utf8;");

			@mysql_select_db ($this->db_name, $this->conn);

			return ERR_OK;
		}

		public function query($sql) {
			$this->sql_result = mysql_query($sql, $this->conn);
	 		return  $this->sql_result ? ERR_OK : ERR_SQL;
		}

		public function query_file($sqlpath) {
			$sqlfile = fopen($sqlpath, "r");
			$sql = fread($sqlfile, filesize($sqlpath));
			fclose($sqlfile);

			$sql = preg_replace('/\/\*([^*]+)\*\//', '', $sql);
			$sql = preg_replace('/\-\-([^\n]+)\n/', '', $sql);

			$sqls = preg_split('/;\r/', $sql);

			foreach($sqls as $sql) {
				$this->query($sql);
			}
		}

		public function parse_blacklist() {
			$blacklists = array();
			if ($this->blacklist != "")
			{
				$ll = @preg_split("/;/", $this->blacklist);
				foreach ($ll as $l)
				{
					$blacklists[] = preg_split("/,/", $l);
				}
			}
			$this->blacklists = $blacklists;
		}

		public function check_envir() {
			$min_php_ver = preg_split('/\./', MIN_PHP_VER);
			$php_ver = preg_split('/\./', phpversion());

			$this->require_php_ver = false;
			for ($i = 0; $i < 3; $i ++) 
			{
				if ($min_php_ver[$i] < $php_ver[$i]) {
					$this->require_php_ver = true;
					break;
				}
				else if ($min_php_ver[$i] > $php_ver[$i]) {
					$this->require_php_ver = false;
					break;
				}
				else {
					$this->require_php_ver = true;
				}
			}

			$this->installed_mysql = extension_loaded('mysql');
			$this->installed_mbstring = extension_loaded('mbstring');
			$this->installed_simplexml = extension_loaded('SimpleXML');
			$this->installed_gd = extension_loaded('gd');
		}
	};
?>