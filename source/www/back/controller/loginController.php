<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class LoginController extends controller {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function index() {
			patch::check_patch();

			_set_template("normal");
			
			$this->err_login = ERR_OK;

			if ($this->email != "") {
				$user = new user;
				$user->load($this);

				$this->err_login = $user->login($this->auto_login);
				if ($this->err_login == ERR_OK) {
					if ($user->user_type != UTYPE_ADMIN)
					{
						$user->logout();
						return "login/";
					}

					if (_first_logined()) {
						$this->commit();
						$this->forward("myinfo");
					}
					else {
						$uri = _session("request_uri");
						if ($uri == "")
							$this->forward(_page_url(PAGE_HOME));
						else {
							_session("request_uri", "");
							_goto($uri);
						}
					}
				}
			}
			return "login/";
		}

		public function logout() {
			$me = _user();
			if ($me != null)
				$me->logout();

			$this->forward("login");
		}
	}
?>