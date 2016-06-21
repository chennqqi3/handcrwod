<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:				Ken
        Date:               2015/03/15
    ---------------------------------------------------*/

    class FacebookController extends APIController {
        public $err_login;

        public function __construct(){
            parent::__construct();  
        }

        public function checkPriv($action, $utype)
        {
            parent::checkPriv($action, UTYPE_NONE);
        }

        public function login()
        {
            $param_names = array("base", "redirect_url", "signup_url");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $facebook = new Facebook(array(
              'appId' => FACEBOOK_APP_ID,
              'secret' => FACEBOOK_APP_SECRET
            ));

            _session("facebook_redirect_url", $params->base . "#/" . $params->redirect_url);
            _session("facebook_signup_url", $params->base . "#/" . $params->signup_url);

            if (!FACEBOOK_ENABLE) {
                print "Facebookと連携することができません。";
                exit;
            }

            $params = array(
              'display' => "page",
              'redirect_uri' => SITE_BASEURL . "api/facebook/login_success",
              'scope' => "publish_actions",
            );

            $facebook->setAccessToken(null);

            $login_url = $facebook->getLoginUrl($params);

            _goto($login_url);
        }

        public function login_success()
        {
            $param_names = array();
            $this->setApiParams($param_names);
            $params = $this->api_params;

            $redirect_url = _session("facebook_redirect_url");
            $signup_url = _session("facebook_signup_url");

            if ($this->code != null) {
                $user_info = $this->get_facebook_info();

                if ($user_info != null) {
                    $facebook_id = $user_info->id;

                    $user = user::getFromFacebookId($facebook_id);
                    if ($user == null) {
                        // goto sign up
                        $user_name =$user_info->name;

                        _goto($signup_url . "/" . session_id());
                    }
                    else {
                        // login & goto home
                        user::init_session_data($user);
                        _goto($redirect_url . "/" . session_id() . "/facebook");
                    }   
                }
            }
            else {
                print "Facebookへログインすることができません。<br/>";
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
            $user_info = $this->get_facebook_info();

            if ($user_info == null)
                $err = ERR_NOT_LOGINED_FACEBOOK;

            $this->finish(array("facebook_id" => $user_info->id, "user_name" => $user_info->name), $err);
        }

        public function register()
        {
            $param_names = array("token", "email");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            @session_write_close();
            session_id($params->token);
            session_start();

            $err = ERR_OK;
            $user_info = $this->get_facebook_info();

            if ($user_info == null)
                $this->checkError(ERR_NOT_LOGINED_FACEBOOK);

            // start transaction
            $this->start();

            $user = user::getFromFacebookId($user_info->id);
            if ($user == null) {
                if (user::is_exist_by_email($params->email, null))
                    $this->checkError(ERR_ALREADY_USING_EMAIL);

                $user = new user;
                $user->user_name = $user_info->name;
                $user->email = $params->email;
                $user->facebook_id = $user_info->id;
                $user->user_type = UTYPE_USER;
                $user->language = DEFAULT_LANGUAGE;
                $user->time_zone = TIME_ZONE;
                $user->activate_flag = ACTIVATED;
                $user->activate_key = null;
                $user->activate_until = null;
                $user->plan_type = PLAN_FREE;
                $err = $user->save();
            }

            if ($err == ERR_OK) {
                // login
                user::init_session_data($user);
            }

            $planconfig = new planconfig($user->plan_type);

            $this->finish(array(
                "session_id" => session_id(),
                "user_id" => $user->user_id, 
                "user_name" => $user->user_name,
                "email" => $user->email,
                "avartar" => _avartar_full_url($user->user_id),
                "language" => $user->language,
                "time_zone" => $user->time_zone,
                "plan" => $planconfig->props,
                "plan_end_date" => $user->plan_end_date
            ), $err);   
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

            $planconfig = new planconfig($user->plan_type);

            $last_home = home::last_home();

            $this->finish(array(
                "session_id" => session_id(),
                "user_id" => $user->user_id, 
                "user_name" => $user->user_name,
                "email" => $user->email,
                "avartar" => _avartar_full_url($user->user_id),
                "language" => $user->language,
                "time_zone" => $user->time_zone,
                "priority_tasks" => task_user::getPriorityTasks($user->user_id),
                "inbox_tasks" => task::getInboxTasks($user->user_id),
                "plan" => $planconfig->props,
                "plan_end_date" => $user->plan_end_date,
                "cur_home" => $last_home,
                "alerts" => user::get_alerts($my_id),
                "chat_uri" => _chat_uri()
            ), $err);  
        }

        private function get_facebook_info()
        {
            $facebook = new Facebook(array(
              'appId' => FACEBOOK_APP_ID,
              'secret' => FACEBOOK_APP_SECRET
            ));

            //$uid = $facebook->getUser();
            $access_token = $facebook->getAccessToken();

            if (_is_empty($access_token)) {
                $access_token = _session("facebook_access_token");
                if (_is_empty($access_token))
                    return null;
            }
            else {
                _session("facebook_access_token", $access_token);   
            }

            $user_details = "https://graph.facebook.com/me?fields=id,name,email&access_token=" .$access_token;

            $user_info = file_get_contents($user_details);
            if ($user_info != null)
                $user_info = json_decode($user_info);

            return $user_info;
        }
    }
?>