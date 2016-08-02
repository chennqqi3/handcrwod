<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class UserController extends APIController {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function signin()
		{
			$this->setApiParams(array("email", "password"));
			
			$user = new user;
			$user->load($this->api_params);

			$err = $user->login();

			$ret = null;
			if ($err == ERR_OK) {
				$planconfig = new planconfig($user->plan_type);	

				$last_home = home::last_home();

				$ret = array(
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
					"alerts" => user::get_alerts($user->user_id),
					"chat_uri" => _chat_uri()
				);
			}

			$this->finish($ret, $err);
		}

		public function signout()
		{
			$this->setApiParams(array());

			$me = _user();
			if ($me != null)
				$me->logout();

			$this->finish(null, ERR_OK);
		}

		public function signup()
		{
			$param_names = array("user_name", "email", "password", "activate_url", "invite_mission_id", "invite_home_id", "key");
			$this->setApiParams($param_names);
			$this->checkRequired(array("user_name", "email", "password"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$other = new user;
			$where = "email=" . _sql($params->email) . " AND user_type!=" . UTYPE_ADMIN;
			$err = $other->select($where);
			if ($err == ERR_OK) {
				if ($other->activate_flag == 1)
					$this->checkError(ERR_ALREADY_USING_EMAIL);
				else {
					$other->remove(true);
				}
			}

			$err = ERR_OK;

			$user = new user;
			$user->load($params);
			$user->user_type = UTYPE_USER;
			$user->language = DEFAULT_LANGUAGE;
			$user->time_zone = TIME_ZONE;
			$user->activate_flag = UNACTIVATED;
			$user->activate_key = _newId();
			$user->activate_until = "##DATE_ADD(NOW(),INTERVAL 1 DAY)";
			$user->plan_type = PLAN_FREE;

			$same_email_user = new user;
			$same_email_user->remove_where("email=" . _sql($params->email) . " AND activate_flag=0", true);

			$user->password = md5($user->password);

			$err = $user->save();

			if ($err == ERR_OK)
			{
				$user->send_activate_mail($params->activate_url, $params->app_mode);

				if ($params->invite_mission_id != null)
				{
					$key = _key($params->invite_mission_id . $params->email);
					if ($key == $params->key) {
						$mission = mission::getModel($params->invite_mission_id);
						if ($mission != null) {
							$err = $mission->add_member($user->user_id, 1);
						}
					}
				}
				else if ($params->invite_home_id != null)
				{
					$key = _key($params->invite_home_id . $params->email);
					if ($key == $params->key) {
			            $home = home::getModel($params->invite_home_id);
			            if ($home != null) {
							$err = $home->add_member($user->user_id, 1);
			            }
			        }
				}
			}

			$this->finish(array("user_id" => $user->user_id), $err);
		}

		public function resend_activate_mail()
		{
			$param_names = array("email", "activate_url");
			$this->setApiParams($param_names);
			$this->checkRequired(array("email"));
			$params = $this->api_params;

			$user = user::getModel($params->email);
			if ($user == null)
				$this->checkError(ERR_NOTFOUND_USER);

			$user->send_activate_mail($params->activate_url);

			$this->finish(null, ERR_OK);
		}

		public function activate()
		{
			$param_names = array("mobile", "user_id", "activate_key");
			$this->setApiParams($param_names);
			$this->checkRequired(array("user_id", "activate_key"));
			$params = $this->api_params;

			if ($params->mobile == 1) {
				global $_SERVER;
				$useragent= strtolower($_SERVER['HTTP_USER_AGENT']);

				if(preg_match('/android|ip(hone|od)/i',$useragent))
				{
					$url = "handcrowd://?signup=1&user_id=" .  $params->user_id . "&activate_key=" . $params->activate_key;				
					ob_clean();
					header('Location: ' . $url);					
				}
				else {
					print '
<!doctype html>
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <title>ハンドクラウド</title>
    </head>
    <body>
    	<h1>携帯から登録が行われたので、お手持ちの携帯からメールのリンクを開いてください</h1>
    </body>
 </html>
					';	
				}

				exit;
			}

			// start transaction
			$this->start();

			$user = user::getModel($params->user_id);
			if ($user == null)
				$this->checkError(ERR_NODATA);

			if (_now() > strtotime($user->activate_until))
				$this->checkError(ERR_ACTIVATE_EXPIRED);

			if ($user->activate_flag || substr($user->activate_key, 0, 5) == strtolower(substr($params->activate_key, 0, 5)))
			{
				$user->activate_flag = ACTIVATED;
				$user->activate_key = null;
				$user->activate_until = null;
				$err = $user->save();

				if ($err == ERR_OK) {
					// login
					user::init_session_data($user);

					$planconfig = new planconfig($user->plan_type);	

					$last_home = home::last_home();

					$ret = array(
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
						"alerts" => user::get_alerts($user->user_id),
						"chat_uri" => _chat_uri()
					);		
				}

				$this->finish($ret, $err);
			}
			else {
				$this->checkError(ERR_INVALID_ACTIVATE_KEY);
			}
		}

		public function reactivate()
		{
			$param_names = array("user_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$user = user::getModel($params->user_id);
			if ($user == null)
				$this->checkError(ERR_NODATA);

			$user->activate_flag = UNACTIVATE;
			$user->activate_key = _newId();
			$user->activate_until = "##DATE_ADD(NOW(),INTERVAL 1 DAY)";

			$err = $user->save();

			$this->finish(null, $err);
		}

		public function send_reset_password()
		{
			$param_names = array("email", "reset_url");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$user = user::getModel($params->email);
			if ($user == null)
				$this->checkError(ERR_INVALID_EMAIL);

			if ($user->activate_flag != ACTIVATED)
				$this->checkError(ERR_USER_UNACTIVATED);

			$user->activate_key = _newId();
			$user->activate_until = "##DATE_ADD(NOW(),INTERVAL 5 MINUTE)";

			$err = $user->save();

			if ($err == ERR_OK)
			{
				$title = "「ハンドクラウド」パスワードリセット";
				$body = $user->email . "様 

下記のURLにアクセスして、パスワードをリセットしてください。
" . $params->reset_url . "?user_id=" .  $user->user_id . "&activate_key=" . $user->activate_key . "

※URLの有効期限は発行から5分間です。 \n";
				$body .= MAIL_FOOTER;

				_send_mail($user->email, $user->user_name, $title, $body);
			}

			$this->finish(null, $err);
		}

		public function reset_password()
		{
			$param_names = array("user_id", "activate_key", "password");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$user = user::getModel($params->user_id);
			if ($user == null)
				$this->checkError(ERR_NODATA);

			if ($user->activate_flag != ACTIVATED)
				$this->checkError(ERR_USER_UNACTIVATED);

			if (_now() > strtotime($user->activate_until))
				$this->checkError(ERR_ACTIVATE_EXPIRED);

			if ($user->activate_key != null && $user->activate_key == $params->activate_key)
			{
				$user->activate_key = null;
				$user->activate_until = null;
				$user->password = md5($params->password);
				$err = $user->save();

				$this->finish(null, $err);
			}
			else {
				$this->checkError(ERR_INVALID_ACTIVATE_KEY);
			}
		}

		public function update_profile() 
		{
			$param_names = array("user_name", "login_id", "email", "hourly_amount", "time_zone", "old_password", "new_password", "skills", "alarm_mail_flag", "alarm_time");
			$this->setApiParams($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			$user = user::getModel($my_id);
			if ($user == null)
				$this->checkError(ERR_NODATA);
			
			$user->load($params);
			if ($params->new_password != "")
			{
				// change password
				if (md5($params->old_password) == $user->password || $params->old_password == '' && $user->password == '') {
					$user->password = md5($params->new_password);	
				}
				else {
					$this->checkError(ERR_INVALID_OLDPWD);
				}
			}
			if (user::is_exist_by_email($user->email, $my_id))
				$this->checkError(ERR_ALREADY_USING_EMAIL);
			if (user::is_exist_by_login_id($user->login_id, $my_id))
				$this->checkError(ERR_ALREADY_USING_LOGIN_ID);

			$err = $user->save();

			if ($err == ERR_OK) {
				if ($params->skills != null) {
					$skill = new user_skill;

					$where = "";
					foreach($params->skills as $skill_name)
					{
						$skill_name = trim($skill_name);
						if ($where != "")
							$where .= ",";
						$where .= _sql($skill_name);
					}
					if ($where != "")
						$where = " AND skill_name NOT IN (" . $where . ")";

					$this->checkError($skill->remove_where("user_id=" . _sql($my_id) . $where, true));

					foreach($params->skills as $skill_name)
					{
						$skill_name = trim($skill_name);
						$skill = new user_skill;
						$err = $skill->select("user_id=" . _sql($my_id) . " AND skill_name=" . _sql($skill_name));
						if ($err == ERR_NODATA) {
							$skill->user_id = $my_id;
							$skill->skill_name = $skill_name;

							$this->checkError($skill->save());
						}
					}

					$err = ERR_OK;
				}
			}

			if ($err == ERR_OK) {
				_time_zone($user->time_zone);
			}

			$this->finish(null, $err);
		}

		public function get_profile()
		{
			$this->setApiParams(array("user_id", "cur_home_id"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			if ($params->user_id != null)
				$user_id = $params->user_id;
			else
				$user_id = $my_id;

			$user = user::getModel($user_id);
			if ($user == null)
				$this->checkError(ERR_NOTFOUND_USER);

			$user->avartar = _avartar_full_url($user_id);

			$skills = array();
			$skill = new user_skill;

			$err = $skill->select("user_id=" . _sql($user_id),
				array("order" => "skill_name ASC"));

			while ($err == ERR_OK)
			{
				array_push($skills, $skill->skill_name);

				$err = $skill->fetch();
			}

			$planconfig = new planconfig($user->plan_type);

			if ($user_id == $my_id) {
				if ($params->cur_home_id != null)
					$last_home = home::last_home($params->cur_home_id);
				else
					$last_home = null;

				$alerts = user::get_alerts($user_id);	
			}

			$this->finish(array("user" => array(
				"user_id" => $user->user_id,
				"user_name" => $user->user_name, 
				"login_id" => $user->login_id, 
				"email" => $user->email, 
				"hourly_amount" => $user->hourly_amount,
				"language" => $user->language,
				"time_zone" => $user->time_zone,
				"avartar" => $user->avartar,
				"skills" => $skills,
				"alarm_mail_flag" => $user->alarm_mail_flag,
				"alarm_time" => $user->alarm_time,
				"plan" => $planconfig->props,
				"plan_end_date" => $user->plan_end_date,
				"cur_home" => $last_home,
				"alerts" => $alerts,
				"chat_uri" => _chat_uri())
			), ERR_OK);
		}

		public function upload_avartar()
		{
			$this->setApiParams(array());

			// start transaction
			$this->start();

			$my_id = _user_id();		

			$user = user::getModel($my_id);
			if ($user == null)
				$this->checkError(ERR_NODATA);	
			
			$ext = _get_uploaded_ext("file");
			if ($ext != null) {
				$tmppath = _tmp_path("jpg");
				$tmpfile = _basename($tmppath);
				
				_upload("file", $tmppath); 

				_resize_userphoto($tmppath, $ext, 240, 240);

				_erase_old(TMP_PATH);

				$user->update_avartar("tmp/" . $tmpfile);

				$user->avartar = _avartar_full_url($user->user_id, true);

				$this->finish(array("avartar" => $user->avartar), ERR_OK);
			}
			else {
				$this->checkError(ERR_INVALID_IMAGE);
			}
		}

		public function get_daily_amount($user_id)
		{
			$param_names = array("user_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$daily_amount = 0;

			$user = user::getModel($params->user_id);

			if ($user != null)
			{
				$daily_amount = $user->hourly_amount * 8;
			}

			$this->finish(array("daily_amount" => $daily_amount), ERR_OK);
		}

		public function set_push_token()
		{
			$param_names = array("user_id", "device_type", "device_token");
			$this->setApiParams($param_names);
			$this->checkRequired(array("device_type", "device_token"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$push_token = new push_token;

			$err = $push_token->select("device_type=" . _sql($params->device_type) . " AND device_token=" . _sql($params->device_token));

			$push_token->load($params);

			$err = $push_token->save();

			$this->finish(null, ERR_OK);
		}

		public function alerts()
		{
			$this->setApiParams(array());
			$params = $this->api_params;

			$my_id = _user_id();
			$this->finish(array("alerts" => user::get_alerts($my_id)), ERR_OK);
		}
	}
?>