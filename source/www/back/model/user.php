<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class user extends model 
	{
		public function __construct()
		{
			parent::__construct("m_user",
				"user_id",
				array(
					"user_type",
					"user_name",
					"avartar",
					"email",
					"password",
					"facebook_id",
					"google_id",
					"login_id",
					"hourly_amount",
					"curr_type",
					"weekly_limit",
					"language",
					"time_zone",
					"alarm_mail_flag",
					"alarm_time",
					"activate_flag",
					"activate_key",
					"activate_until",
					"access_time",
					"plan_type",
					"plan_start_date",
					"plan_end_date", 
					"tutorial"),
				array("auto_inc" => true));
		}

		public function save()
		{
			$err = parent::save();
			if ($err == ERR_OK)
			{
				$cache_id = static::cache_id_of_user_name($this->user_id);
				_cache_set($cache_id, $this->user_name);
			}
			return $err;
		}

		public static function getModel($pkvals, $ignore_del_flag=false)
		{
			$model = new static;
			if (is_numeric($pkvals)) {
				$err = $model->get($pkvals, $ignore_del_flag);
				if ($err == ERR_OK)
					return $model;
			}

			if (is_string($pkvals)) {
				$err = $model->select("email = " . _sql($pkvals) . " OR login_id = " . _sql($pkvals));
				if ($err == ERR_OK)
					return $model;
			}

			return null;
		}

		public static function getFromFacebookId($facebook_id)
		{
			$model = new static;
			$err = $model->select("facebook_id = " . _sql($facebook_id));
			if ($err == ERR_OK)
				return $model;

			return null;
		}

		public static function getFromGoogleId($google_id)
		{
			$model = new static;
			$err = $model->select("google_id = " . _sql($google_id));
			if ($err == ERR_OK)
				return $model;

			return null;
		}

		static public function set_access_time($user_id)
		{
			$db = db::getDB();
			$db->execute("UPDATE m_user SET access_time=NOW() WHERE user_id=" . _sql($user_id));
		}

		static public function is_exist_by_email($email, $user_id=null)
		{
			$user = new user;
			$where = "email=" . _sql($email);
			$where .= " AND user_type!=" . UTYPE_ADMIN;
			$where .= " AND activate_flag=1";
			if ($user_id != null)
			{
				$where .= " AND user_id!=" . _sql($user_id);
			}
			$err = $user->select($where);
			return $err == ERR_OK;
		}

		static public function is_exist_by_login_id($login_id, $user_id=null)
		{
			$user = new user;
			$where = "login_id=" . _sql($login_id);
			$where .= " AND activate_flag=1";
			if ($user_id != null)
			{
				$where .= " AND user_id!=" . _sql($user_id);
			}
			$err = $user->select($where);
			return $err == ERR_OK;
		}

		public function update_avartar($uploaded_photo)
		{
			if ($uploaded_photo != "") {
				$photo = $this->user_id . ".jpg";
				if (substr($uploaded_photo, 0, 3) == "tmp") {
					@unlink(AVARTAR_PATH . $photo);
					@rename(SITE_ROOT . "/" . $uploaded_photo, AVARTAR_PATH . $photo);
				}
				else if (substr($uploaded_photo, 0, 1) == "r") {
					@unlink(AVARTAR_PATH . $photo);
					@rename(AVARTAR_PATH . $uploaded_photo, AVARTAR_PATH . $photo);
				}
			}
		}

		public function check_delete()
		{
			return true;
		}

		public function login($auto_login = false)
		{
			global $_SERVER;
			$logined = ERR_FAILLOGIN;

			if ($this->login_id != "") {
				$user = new user;
				$err = $user->select("email=" . _sql($this->login_id) . " OR login_id=" . _sql($this->login_id));
				if ($err == ERR_OK && $user->password == md5($this->password) && $this->password != null)
					$logined = $user->activate_flag == ACTIVATED ? ERR_OK : ERR_USER_UNACTIVATED;
			}
			else if ($this->email != "") {
				$user = new user;
				$err = $user->select("email=" . _sql($this->email) . " OR login_id=" . _sql($this->email));
				if ($err == ERR_OK && $user->password == md5($this->password) && $this->password != null)
					$logined = $user->activate_flag == ACTIVATED ? ERR_OK : ERR_USER_UNACTIVATED;
			}
			else if ($auto_login) {
				// auto login
				$token = _auto_login_token();
				$s = preg_split("/\//", $token);
				if (count($s) == 2) {
					$user = new user;
					$err = $user->select("email=" . _sql($s[0]));
					if ($err == ERR_OK && $token == $user->auto_login_token())
						$logined = ERR_OK;
				}				
			}

			if ($logined == ERR_OK)
			{
				$err = $user->checkPlan();
				if ($err != ERR_OK)
					return $err;

				if ($auto_login) {
					_auto_login_token($user->auto_login_token());
				}
				else {
					_auto_login_token("NOAUTO");
				}

				user::init_session_data($user);

				$this->load($user);

				_access_log("ログイン");
			}

			return $logined;
		}

		public function post_login($home_id=null)
		{
			$planconfig = new planconfig($this->plan_type);	

			if ($home_id != null)
				$last_home = home::last_home($home_id);
			else
				$last_home = home::last_home();

			$ret = array(
				"session_id" => session_id(),
				"user_id" => $this->user_id, 
				"user_name" => $this->user_name,
				"login_id" => $this->login_id,
				"email" => $this->email,
				"avartar" => _avartar_full_url($this->user_id),
				"language" => $this->language,
				"time_zone" => $this->time_zone,
				"plan" => $planconfig->props,
				"plan_end_date" => $this->plan_end_date,
				"cur_home" => $last_home,
				"alerts" => user::get_alerts($this->user_id),
				"unreads" => cunread::all($this->user_id),
				"chat_uri" => _chat_uri(),
				"cache_uris" => _cache_uris(),
				"tutorial" => $this->tutorial
			);

			return $ret;
		}

		public function checkPlan()
		{
			if ($this->plan_type > PLAN_FREE) {
				// 有償プラン
				$uu = new user;
				$err = $uu->select("user_id=" . $this->user_id . " AND 
					(plan_start_date IS NULL OR plan_start_date < NOW()) AND 
					(plan_end_date IS NULL OR plan_end_date > NOW())");

				if ($err == ERR_NODATA)
					return ERR_PLAN_EXPIRED;
			}

			return ERR_OK;
		}

		public static function init_session_data($user)
		{
			global $_SERVER;
			$user->access_time = "##NOW()";		
			$user->save();

			_utype($user->user_type);
			_user_id($user->user_id);
			_user_name($user->user_name);
			_lang($user->language);
			_login_ip($_SERVER["REMOTE_ADDR"]);
			_time_zone($user->time_zone);

			session::insert_session();
		}

		public function auto_login_token() 
		{
			return $this->email . "/" . md5($this->email . _ip() . $this->password);
		}

		public function logout()
		{
			_access_log("ログアウト");
			_session();
			_auto_login_token("NOAUTO");
		}

		public function send_activate_mail($activate_url)
		{
			$title = "「ハンドクラウド」新規会員登録";

			if ($activate_url == null) { // アプリモード
				$url = "https://www.handcrowd.com/back/api/user/activate?mobile=1&user_id=" .  $this->user_id . "&activate_key=" . $this->activate_key;
				$body = "<div>" . $this->user_name . "様</div>
<br/>
<div>
下記のリンクをクリックして、会員登録を続けてください。<br/>
※まだ会員登録は完了しておりません。<br/>
<a href='" . $url . "'>会員登録を完了</a>
</div>

<div>※リンクの有効期限は発行から24時間ですので、期限内にご登録を完了くださいますようお願いいたします。 </div>";
			}
			else {
				$body = "<div>" . $this->user_name . "様 </div>
<br/>
<div>
下記のリンクをクリックして、会員登録を続けてください。<br/>
※まだ会員登録は完了しておりません <br/>
<a href='" . $activate_url . "?user_id=" .  $this->user_id . "&activate_key=" . $this->activate_key . "'>会員登録を完了</a>
</div>

<div>※リンクの有効期限は発行から24時間ですので、期限内にご登録を完了くださいますようお願いいたします。 </div>";
			}

			_send_mail($this->email, $this->user_name, $title, $body, true);
		}

		public static function cache_id_of_user_name($user_id)
		{
			return "00un" . $user_id;
		}

		public static function get_user_name($user_id)
		{
			if ($user_id == null)
				return "";

			$cache_id = static::cache_id_of_user_name($user_id);
			$user_name = _cache_get($cache_id);

			if ($user_name != null)
				return $user_name;

			$user = user::getModel($user_id);
			$user_name = $user == null ? "" : $user->user_name;
			_cache_set($cache_id, $user_name);

			return $user_name;
		}

		public static function get_total_file_size()
		{
			$db = db::getDB();
			$sql = "SELECT SUM(ma.file_size) FROM t_mission_attach ma 
				INNER JOIN t_mission m ON ma.mission_id=m.mission_id
				WHERE m.del_flag=0 AND ma.del_flag=0 AND m.client_id=" . _sql(_user_id());
  			$ma_size = $db->scalar($sql);

			$sql = "SELECT SUM(tc.file_size) FROM t_task_comment tc
				INNER JOIN t_task t ON tc.task_id=t.task_id
				INNER JOIN t_mission m ON t.mission_id=m.mission_id
				WHERE tc.del_flag=0 AND t.del_flag=0 AND 
					m.del_flag=0 AND m.client_id=" . _sql(_user_id());
  			$ta_size = $db->scalar($sql);

  			return $ma_size + $ta_size;
		}

		public static function get_alerts($user_id)
		{
			$alerts = array();

			// invited
			$homes = home_member::unaccepted_homes($user_id);
			if (count($homes) > 0)
			{
				foreach($homes as $home) 
				{
					$msg = "「" . $home["home_name"] . "」に招待されました。";
					array_push($alerts, array(
						"type" => ALERT_INVITE_HOME,
						"data" => $home,
						"msg" => $msg
					));
				}
			}

			return $alerts;
		}
	};
?>