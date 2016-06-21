<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class MyinfoController extends controller {
		public function __construct(){
			parent::__construct();	

			$this->_navi_menu = "myinfo";
		}

		public function checkPriv($action, $utype)
		{
			switch($action) {
				default:
					parent::checkPriv($action, UTYPE_ADMIN);
					break;
			}
		}

		public function index($page = 0, $size = 10) {
			$this->_navi_menu = "myinfo";
			$me = _user();
			if ($me == null)
				$this->showError(ERR_NODATA);

			$this->mUser = $me;
		}

		public function save_ajax() {
			$this->start();

			$me = _user();
			if ($me == null)
				$this->showError(ERR_NODATA);

			$me->load($this);

			$this->checkError($err = $me->save());
			
			// update editor_type
			_editor_type($me->editor_type);
		
			// update_avartar
			$me->update_avartar($this->photo);

			_first_logined(2);	

			$this->finish(null, $err);
		}

		public function password_ajax() {
			$this->start();

			$me = _user();
			if ($me == null)
				$this->showError(ERR_NODATA);

			if ($this->old_password != null && md5($this->old_password) == $me->password && 
				$this->new_password != null) {
				$me->password = md5($this->new_password);

				$this->checkError($err = $me->save());
			}
			else {
				$this->checkError(ERR_INVALID_OLDPWD);
			}
										
			$this->finish(null, $err);
		}
	}
?>