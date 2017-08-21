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

            if (!FACEBOOK_ENABLE) {
                print "Facebookと連携することができません。";
                exit;
            }

            $login_url = $this->get_url_redict();

            _goto($login_url);
        }

        public function login_success()
        {
            $param_names = array();
            $this->setApiParams($param_names);
            $params = $this->api_params;

            $redirect_url = _app_url("#/signin"); 
            $signup_url = _app_url("#/signup_facebook");

            if ($this->code != null) {
             $user_info = $this->get_facebook_info($this->code);
                if ($user_info != null) {
                    $facebook_id = $user_info['id'];
                    $user = user::getFromFacebookId($facebook_id);
                    if ($user == null) {
                        // goto sign up
                        $user_name =$user_info['name'];

                        _goto($signup_url . "/" . session_id());
                    }
                    else {
                        // login & goto home
//                        user::init_session_data($user);
                        _user_id($user->user_id);
                        _goto($redirect_url . "/" . session_id() . "/facebook?facebook_id=".$facebook_id);
                    }   
                }
            }
            else {
                print "Facebookへログインすることができません。<br/>";
                print $this->error_message;
            }

            exit;
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
            user::init_session_data($user);

            // post login success
            $ret = $user->post_login();
            $this->finish($ret, ERR_OK);
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
            $user_info = $this->get_facebook_info(null);

            if ($user_info == null)
                $err = ERR_NOT_LOGINED_FACEBOOK;

            $this->finish(array("facebook_id" => $user_info['id'], "user_name" => $user_info['name']), $err);
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
            $user_info = $this->get_facebook_info(null);
            if ($user_info == null)
                $this->checkError(ERR_NOT_LOGINED_FACEBOOK);
            $this->start();
            $user = user::getFromFacebookId($user_info['id']);
            if ($user == null) {
                if (user::is_exist_by_email($params->email, null))
                    $this->checkError(ERR_ALREADY_USING_EMAIL);

                $user = new user;
                $user->login_id = $params->login_id;
                $user->user_name = $params->user_name;
                $user->facebook_id = $user_info['id'];
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

        public static function get_url_redict(){
            require_once("include/plugins/Facebook/autoload.php");

            $facebook = new \Facebook\Facebook(array('app_id' => FACEBOOK_APP_ID,
                'app_secret' => FACEBOOK_APP_SECRET));
            $helper_fb = $facebook->getRedirectLoginHelper();
            $permission_fb = ['email','public_profile','user_photos','user_birthday','user_location'];

            $login_url = $helper_fb->getLoginUrl(SITE_BASEURL . "api/facebook/login_success", $permission_fb);
            unset($facebook);
            return $login_url;
        }

        public static function get_facebook_info($code)
        {
            $arrUser = array();
            if ($code != null){
                $access_token = null;
                require_once("include/plugins/Facebook/autoload.php");

                $facebook = new \Facebook\Facebook(array('app_id' => FACEBOOK_APP_ID,
                    'app_secret' => FACEBOOK_APP_SECRET));
                $helper_fb = $facebook->getRedirectLoginHelper();
                $access_token = (string) $helper_fb->getAccessToken();
                $profile = $facebook->get('/me?fields=id,name,email', $access_token);
                $user_fb= $profile->getGraphUser();
                if(!_is_empty( $user_fb['id'])){
                    $arrUser['id'] = $user_fb['id'];
                }
                if(!_is_empty($user_fb['name'])){
                    $arrUser['name'] = $user_fb['name'];
                }
                if(!_is_empty($user_fb['email'])){
                    $arrUser['email'] =$user_fb['email'];
                }
            }
            if (count($arrUser) == 0) {
                $arrUser = _session("facebook_user");
                if (count($arrUser) == 0)
                    return null;
            }
            else {
                _session("facebook_user", $arrUser);
            }
            return  $arrUser;
        }


    }
?>
