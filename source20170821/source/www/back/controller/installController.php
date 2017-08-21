<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class InstallController extends controller {
		public function __construct(){
			$this->_page_id = "install";
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function index() {
			if (!defined('DB_HOSTNAME')) {
				_session();

				$sysconfig = new sysconfig;

				$sysconfig->step = 0;

				$sysconfig->check_envir();

				$this->oConfig = $sysconfig;

				return "none/install_index";
			}
			else 
				return "none/install_removeconfig";
		}

		public function testdb_ajax() {
			if (!defined('DB_HOSTNAME')) {
				$sysconfig = new sysconfig;

				$sysconfig->load($this);

				$this->response($this->json(array("err_code" => $sysconfig->connect())));
			}
			else 
				$this->response($this->json(array("err_code" => ERR_ALREADYINSTALLED)));
		}

		public function testldap_ajax() {
			if (!defined('DB_HOSTNAME')) {
				$sysconfig = new sysconfig;

				$sysconfig->load($this);

				$this->response($this->json(array("err_code" => $sysconfig->connect_ldap())));
			}
			else 
				$this->response($this->json(array("err_code" => ERR_ALREADYINSTALLED)));
		}

		public function start_ajax() {
			global $_SERVER;
			if (!defined('DB_HOSTNAME')) {
				$sysconfig = new sysconfig;

				$sysconfig->load($this);
				
				$err = $sysconfig->connect($this->step == 0);
				if ($err != ERR_OK)
					$this->response($this->json(array("err_code" => $err)));

				switch($this->step) {
					case 0:
						$sysconfig->query_file(SITE_ROOT . "/include/sql/create_db.sql");
						$err = ERR_OK;
						break;
					case 1:
						if ($sysconfig->install_sample) {
							$sysconfig->query_file(SITE_ROOT . "/include/sql/sample.sql");
						}
						$err = ERR_OK;
						break;
					case 2:
						$insert_admin = "INSERT INTO `m_user`(`user_id`,`user_type`,`user_name`,`avartar`,`email`,`password`,`hourly_amount`,`curr_type`,`weekly_limit`,`language`,`time_zone`,`activate_flag`,`access_time`,`create_time`,`update_time`,`del_flag`)" . 
						" VALUES ('1', " . UTYPE_ADMIN . ", 'webmaster', NULL, " . _sql($sysconfig->admin_email) . ", " . _sql(md5($sysconfig->admin_password)) . ", NULL, 'USD', NULL, 'ja_jp', 'Asia/Tokyo', 1, NOW(), NOW(), NULL, '0');";
					
						$sysconfig->query($insert_admin);

						foreach (patch::$patches as $version => $p) {
							$sql = "INSERT INTO t_patch(version, description, create_time, del_flag) VALUES(" . _sql($version). ", " . _sql($p["description"]) . ", NOW(), 0)";
							$sysconfig->query($sql);
						}
						$err = ERR_OK;
						break;
					case 3:
						_install_batch();
						foreach (patch::$patches as $version => $p) {
						}
						$sysconfig->version = $version;
						$err = $sysconfig->save();
						break;
				}

				$this->response($this->json(array("err_code" => $err)));
			}
			else 
				$this->response($this->json(array("err_code" => ERR_ALREADYINSTALLED)));
		}
	}
?>