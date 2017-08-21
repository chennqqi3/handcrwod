<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/12/03
	---------------------------------------------------*/

	class GoogleController extends APIController {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			if ($action == 'login' || $action == 'login_success' || $action == 'get_info' || $action == 'register' || $action == 'signin')
				parent::checkPriv($action, UTYPE_NONE);
			else
				parent::checkPriv($action, UTYPE_LOGINUSER);
		}

		public function is_connected()
		{
			$param_names = array();
			$this->setApiParams($param_names);
			$params = $this->api_params;
		
			$my_id = _user_id();
			
			$token = google_token::get_token($my_id);
			if ($token != null)
				$this->finish(null, ERR_OK);
			else 
				$this->checkError(ERR_NOT_CONNECTED_GOOGLE);
		}

		public function disconnect()
		{
			$param_names = array();
			$this->setApiParams($param_names);
			$params = $this->api_params;

			$my_id = _user_id();

			google_token::delete_handcrowd_calendar($my_id);

			$err = google_token::remove_token($my_id);

			$this->finish(null, $err);
		}

		public function init_handcrowd_calendar()
		{
			$param_names = array();
			$this->setApiParams($param_names);
			$params = $this->api_params;

			$my_id = _user_id();

			google_token::create_handcrowd_calendar($my_id);

			$err = ERR_OK;

			$this->finish(null, $err);
		}

		// for login
		public function login()
		{
            $param_names = array("base");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

			if (!GOOGLE_ENABLE) {
				print "Googleと連携することができません。";
				exit;
			}

			try {
				$client = $this->get_client();

				$authUrl = $client->createAuthUrl();

				_goto($authUrl);
			}
			catch(Exception $e) {
				print "Google API Error : " . $e->getMessage();
				exit;
			}
		}

        public function login_success()
        {
            $param_names = array();
            $this->setApiParams($param_names);
            $params = $this->api_params;

            $redirect_url = _app_url("#/signin"); 
            $signup_url = _app_url("#/signup_google");

            if ($this->code != null) {
				$client = $this->get_client();
				$client->authenticate($this->code);

			    $user_info = $this->get_google_info($client);
                if ($user_info != null) {
                    $google_id = $user_info->id;

                    $user = user::getFromGoogleId($google_id);
                    if ($user == null) {
                        // goto sign up
                        $user_name =$user_info->name;

                        _goto($signup_url . "/" . session_id());
                    }
                    else {
                        // login & goto home
                        user::init_session_data($user);
                        _goto($redirect_url . "/" . session_id() . "/google");
                    }   
                }
			}
            else {
                print "Googleへログインすることができません。<br/>";
                print $this->error_message;
            }

            exit;
        }

        public function get_info()
        {
            $param_names = array("token");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            @session_write_close();
            session_id($params->token);
            session_start();

            $err = ERR_OK;
            $client = $this->get_client();

            $user_info = $this->get_google_info($client);

            if ($user_info == null)
                $err = ERR_NOT_LOGINED_GOOGLE;

            $this->finish(array("google_id" => $user_info->id, "user_name" => $user_info->name, "email" => $user_info->email), $err);
        }

        public function register()
        {
            $param_names = array("token", "login_id", "user_name");
            $this->setApiParams($param_names);
            $this->checkRequired("token", "user_name");
            $params = $this->api_params;

            @session_write_close();
            session_id($params->token);
            session_start();

            $err = ERR_OK;
            $client = $this->get_client();

            $user_info = $this->get_google_info($client);

            if ($user_info == null)
                $this->checkError(ERR_NOT_LOGINED_GOOGLE);

            // start transaction
            $this->start();

            $user = user::getFromGoogleId($user_info->id);
            if ($user == null) {
                if (user::is_exist_by_email($params->email, null))
                    $this->checkError(ERR_ALREADY_USING_EMAIL);

                $user = new user;
                $user->login_id = $params->login_id;
                $user->user_name = $params->user_name;
                $user->google_id = $user_info->id;
                $user->user_type = UTYPE_USER;
                $user->language = DEFAULT_LANGUAGE;
                $user->time_zone = TIME_ZONE;
                $user->activate_flag = ACTIVATED;
                $user->activate_key = null;
                $user->activate_until = null;
                $user->plan_type = PLAN_FREE;
                $err = $user->save();

                $this->checkError($err);
            }

            // login
            user::init_session_data($user);

            // post login success
            $ret = $user->post_login();
            $this->finish($ret, ERR_OK); 
        }

        public function signin()
        {
            $param_names = array("token");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            @session_write_close();
            session_id($params->token);
            session_start();

            $err = ERR_OK;
            $my_id = _user_id();
            if ($my_id == null)
                $this->checkError(ERR_FAILLOGIN);

            $user = user::getModel($my_id);
            if ($user == null)
                $this->checkError(ERR_NODATA);

            $err = $user->checkPlan();
            if ($err != ERR_OK) {
                _session();
                $this->checkError($err);
            }
            
            // post login success
            $ret = $user->post_login();
            $this->finish($ret, ERR_OK);
        }

        private function get_client()
        {
			$client = new Google_Client();
			$client->setClientId(GOOGLE_CLIENT_ID);
			$client->setClientSecret(GOOGLE_CLIENT_SECRET);
			$client->setRedirectUri(_url("api/google/login_success"));
			$client->setAccessType('offline');
			$client->setApprovalPrompt('force');
			$client->addScope("https://www.googleapis.com/auth/userinfo.profile");
            $client->addScope("https://www.googleapis.com/auth/userinfo.email");

			return $client;
        }

        private function get_google_info($client)
        {
            $access_token = $client->getAccessToken();
            if (_is_empty($access_token)) {
                $access_token = _session("google_access_token");
                if (_is_empty($access_token))
                    return null;
                $client->setAccessToken($access_token);
            }
            else {
				_session("google_access_token", $access_token);  
            }

        	$url = "https://www.googleapis.com/oauth2/v1/userinfo?alt=json";
        	$httpRequest = new Google_Http_Request($url, 'GET');
        	$SignhttpRequest = $client->getAuth()->sign($httpRequest);
			$request = $client->getIo()->makeRequest($SignhttpRequest);
			$user_info = $request->getResponseBody();
			if ($user_info != null)
				$user_info = json_decode($user_info);

            return $user_info;
        }

	}
?>