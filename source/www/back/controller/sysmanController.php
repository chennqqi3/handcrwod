<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class SysmanController extends controller {
		public function __construct(){
			$this->_page_id = "sysman";
			parent::__construct();	

			$this->_navi_menu = "sysman";
			$this->_subnavi_menu = "setting";
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_ADMIN);
		}

		public function setting() {
			$sysconfig = new sysconfig;

			$this->oConfig = $sysconfig;
			$this->oPlanConfig0 = new planconfig(PLAN_FREE);
			$this->oPlanConfig1 = new planconfig(PLAN_STUFF);
			$this->oPlanConfig2 = new planconfig(PLAN_MANAGER);
			$this->oPlanConfig3 = new planconfig(PLAN_PRESIDENT);
		}

		public function testdb_ajax() {
			$sysconfig = new sysconfig;

			$sysconfig->load($this);

			$this->response($this->json(array("err_code" => $sysconfig->connect())));
		}

		public function testldap_ajax() {
			$sysconfig = new sysconfig;

			$sysconfig->load($this);

			$this->response($this->json(array("err_code" => $sysconfig->connect_ldap())));
		}

		public function setting_save_ajax() {
			$sysconfig = new sysconfig;

			$sysconfig->load($this);

			$err = $sysconfig->save();

			//_install_batch();

			$this->finish(null, $err);
		}

		public function version_history() {
			$patch = new patch;

			$patched = array();
			$err = $patch->select('', array('order' => 'patch_id DESC'));
			while($err == ERR_OK)
			{
				$new = clone $patch;
				$patched[] = $new;

				$err = $patch->fetch();
			}

			$this->oPatched = $patched;
		}

		public function connections() {
			$this->addjs('js/json2.js');
		}
	}
?>