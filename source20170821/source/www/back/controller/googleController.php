<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/12/03
	---------------------------------------------------*/

	class GoogleController extends Controller {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_LOGINUSER);
		}

		public function connect()
		{
			$redirect_url = $this->redirect_url;

			_session("app_redirect_url", $redirect_url);

			if (!GOOGLE_ENABLE) {
				print "Googleと連携することができません。";
				exit;
			}

			try {
				$client = google_token::get_client();

				$authUrl = $client->createAuthUrl();

				_goto($authUrl);
			}
			catch(Exception $e) {
				print "Google API Error : " . $e->getMessage();
			}
			exit;
		}

		public function connect_finish()
		{
			$redirect_url = _session("app_redirect_url");

			$my_id = _user_id();

			if (!GOOGLE_ENABLE) {
				print "Googleと連携することができません。";
				exit;
			}

			try {
				if ($this->error == null) {				
					$client = google_token::get_client();
					$client->authenticate($this->code);
					$access_token = $client->getAccessToken();

					google_token::set_token($my_id, $access_token);

					google_token::create_handcrowd_calendar($my_id);
				}

				_goto($redirect_url);
			}
			catch(Exception $e) {
				print "Google API Error" . $e->getMessage();
			}
			exit;
		}
	}
?>