<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class MissionController extends APIController {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_LOGINUSER);
		}

		public function add()
		{
			$param_names = array("home_id", "mission_name", "private_flag");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();
			$plan = _user_plan();

			$db = db::getDB();
  			$mission_count = $db->scalar("SELECT COUNT(mission_id) FROM t_mission 
  				WHERE home_id=" . _sql($params->home_id) . " 
  					AND private_flag IN (" . CHAT_PUBLIC . ", " . CHAT_PRIVATE . ")
  					AND client_id=" . _sql($my_id) . " AND del_flag=0");
  			if ($plan->max_missions != -1 && $plan->max_missions <= $mission_count)
  				$this->checkError(ERR_OVER_MAX_MISSIONS, $plan->max_missions);

			$mission = mission::add_mission($params->home_id, $params->mission_name, $params->private_flag);

			if ($mission != null)
				$this->finish(array("mission_id" => $mission->mission_id, "home_id" => $mission->home_id), ERR_OK);
			else 
				$this->checkError(ERR_SQL);

		}

		public function edit()
		{
			$param_names = array("mission_id", "mission_name", "summary", "private_flag", "push_flag");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id"));
			$params = $this->api_params;

			$my_id = _user_id();

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);

			if ($mission == null)
				return ERR_NOTFOUND_MISSION;

			if ($params->private_flag !== null) {
				$change_private_flag = ($mission->private_flag != $params->private_flag);
				if ($change_private_flag && 
					$mission->private_flag != CHAT_PUBLIC && 
					$mission->private_flag != CHAT_PRIVATE)
				{
					// illegal parameter
					$params->private_flag = $mission->private_flag;
					$change_private_flag = false;
				}
			}
			$mission->load($params);

			$err = $mission->save();

			if ($change_private_flag) {
				$err = $mission->refresh_mission_member();
			}

			if ($params->push_flag !== null) {
				$mission_member = new mission_member;
				$err = $mission_member->select("mission_id=" . _sql($params->mission_id) . " 
					AND user_id=" . _sql($my_id));
				if ($err == ERR_OK)
				{
					$mission_member->push_flag = $params->push_flag;
					if ($mission_member->push_flag > PUSH_TO || $mission_member->push_flag < PUSH_OFF)
						$mission_member->push_flag = PUSH_TO;
					$err = $mission_member->save();
				}
			}

			$this->finish(array("mission_id" => $mission->mission_id, "home_id" => $mission->home_id), $err);
		}

		public function remove()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$err = ERR_OK;

			$mission = mission::getModel($params->mission_id);
			if ($mission != null) {
				$err = $mission->remove();
				$this->checkError($err);
				
				$mission->delete_back_image(0);
				$mission->delete_back_image(1);

				$this->finish(array("mission_id" => $mission->mission_id, "home_id" => $mission->home_id), ERR_OK);
			}
			else 
				$this->checkError(ERR_NOTFOUND_MISSION);
		}

		public function complete()
		{
			$param_names = array("mission_ids", "complete_flag");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$err = ERR_OK;

			$mission_ids = preg_split("/,/", $params->mission_ids);
			foreach($mission_ids as $mission_id) {
				$mission = mission::getModel($mission_id);
				if ($mission != null) {
					$mission->complete_flag = $params->complete_flag;
					$mission->complete_time = "##NOW()";
					$err = $mission->save();
					$this->checkError($err);
				}
			}

			$this->finish(null, $err);
		}

		public function search()
		{
			$param_names = array("home_id", "search_string", "include_completed", "sort_field", "sort_order");
			$this->setApiParams($param_names);
			$this->checkRequired(array("home_id"));
			$params = $this->api_params;
			
			$my_id = _user_id();
			$home_id = $params->home_id;

			$home = home::getModel($home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);

            $mine = home_member::get_member($home_id, $my_id);
            if ($mine == null)
				$this->checkError(ERR_NOPRIV);
			
			$this->start();
			$bot = mission::get_bot($home_id);
			$this->commit();

			$missions = array();
			$mission = new mission;

			$sql = "SELECT m.*, 
					mm.pinned, mm.unreads, mm.to_unreads, 
					DATEDIFF(NOW(), m.last_date) pass_date,
					mm.last_date mm_last_date
				FROM t_mission m 
				LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
				WHERE m.home_id=" . _sql($home_id) . " AND 
					mm.user_id=" . _sql($my_id) . " AND
					m.private_flag != " . CHAT_MEMBER . " AND
					m.del_flag=0";

			if ($params->include_completed != 1)
				$sql .= " AND m.complete_flag=0";

			if (!_is_empty($params->search_string)) {
				$ss = _sql("%" . $params->search_string . "%");
				$sql .= " AND m.mission_name LIKE " . $ss;
			}

			/*
			if ($params->sort_field != null)
				$order = $params->sort_field . " " . $params->sort_order;
			else 
				$order = "mm.pinned DESC";
			*/
			$order = "mm.unreads DESC, m.last_date DESC, mm.pinned DESC";
			$err = $mission->query($sql,
				array("order" => $order));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				// get full url
				$mission->load_other_info();

				$m = array(
					"home_id" => $home_id,
					"mission_id" => $mission->mission_id,
					"mission_name" => $mission->mission_name,
					"job_back_url" => $mission->job_back_url,
					"job_back_pos" => $mission->job_back_pos,
					"prc_back_url" => $mission->prc_back_url,
					"prc_back_pos" => $mission->prc_back_pos,
					"complete_flag" => $mission->complete_flag,
					"private_flag" => $mission->private_flag,
					"pinned" => $mission->pinned,
					"unreads" => $mission->unreads,
					"to_unreads" => $mission->to_unreads,
					"last_date" => $mission->last_date,
					"last_text" => $mission->last_text,
					"client_id" => $mission->client_id,
					"visible" => $mission->visible
				); 

				array_push($missions, $m);

				$err = $mission->fetch();
			}

			// メンバーチャットルーム
            $where = '';
            if ($mine->priv == HPRIV_GUEST) {
                // 参加しているルームのメンバーとのみ
                $where = "AND hm.user_id IN (
                        SELECT DISTINCT mm.user_id FROM t_mission_member mm
                        WHERE mm.mission_id IN (
                            SELECT DISTINCT mm.mission_id FROM t_mission_member mm
                            LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
                            WHERE mm.del_flag=0 AND m.del_flag=0 AND m.complete_flag!=1 
                            	AND m.home_id=" . _sql($home_id) . " 
                                AND mm.user_id=" . _sql($my_id) . " 
                                AND m.private_flag=" . CHAT_PRIVATE . "
                                )
                        )";
            }
			$sql = "SELECT m.*, 
					DATEDIFF(NOW(), m.last_date) pass_date,
					m.last_date mm_last_date,
					hm.user_id, u.user_name, u.email, u.login_id, hm.priv, hm.accepted
                FROM t_home_member hm 
                INNER JOIN m_user u ON hm.user_id=u.user_id 
                LEFT JOIN (SELECT m.*, mm.pinned, mm.unreads, mm.to_unreads, mm.opp_user_id FROM t_mission m 
					LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
					WHERE m.del_flag=0 AND m.private_flag=" . CHAT_MEMBER . " AND mm.user_id=" . _sql($my_id) . ") m ON m.opp_user_id=u.user_id
                WHERE hm.home_id=" . _sql($home_id) . " AND hm.del_flag=0 " . $where . "
                ORDER BY m.last_date DESC, hm.priv DESC";

			$err = $mission->query($sql);
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				// get full url
				$mission->load_other_info();

				$m = array(
					"home_id" => $home_id,
					"mission_id" => $mission->mission_id,
					"mission_name" => $mission->user_name,
					"job_back_url" => $mission->job_back_url,
					"job_back_pos" => $mission->job_back_pos,
					"prc_back_url" => $mission->prc_back_url,
					"prc_back_pos" => $mission->prc_back_pos,
					"complete_flag" => $mission->complete_flag,
					"private_flag" => CHAT_MEMBER,
					"pinned" => $mission->pinned,
					"unreads" => $mission->unreads,
					"to_unreads" => $mission->to_unreads,
					"last_date" => $mission->last_date,
					"last_text" => $mission->last_text,
					"client_id" => $mission->client_id,
					"visible" => $mission->visible,
					"opp_user_id" => $mission->opp_user_id,
					"avartar" => _avartar_full_url($mission->user_id),

					"user_id" => $mission->user_id,
					"user_name" => $mission->user_name,
					"email" => $mission->email,
					"priv" => $mission->priv,
					"login_id" => $mission->login_id,
					"accepted" => $mission->accepted
				); 

				array_push($missions, $m);

				$err = $mission->fetch();
			}


			$this->finish(array("missions" => $missions), ERR_OK);
		}

		public function unpinned_missions()
		{
			$param_names = array("home_id", "private_flag", "sort_field", "sort_order");
			$this->setApiParams($param_names);
			$params = $this->api_params;
			
			$my_id = _user_id();

			$missions = array();
			$mission = new mission;

			if ($params->private_flag == CHAT_MEMBER) {
				$home_member = home_member::get_member($params->home_id, $my_id);
				if ($home_member == null)
					$this->checkError(ERR_NOPRIV);

				$add_member_chat = $home_member->priv != HPRIV_GUEST;

				// 個別チャット
				$sql = "SELECT u.user_id, u.user_name 
					FROM (
						(SELECT mm.opp_user_id user_id
						FROM t_mission m 
						LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
						WHERE m.home_id=" . _sql($params->home_id) . " 
							AND mm.user_id=" . _sql($my_id) . " AND m.del_flag=0
							AND (mm.pinned IS NULL OR mm.pinned !=1) AND m.private_flag=" . CHAT_MEMBER . "
						) " . ($add_member_chat ? "
						UNION ALL 
						(SELECT hm.user_id
						FROM t_home_member hm
						WHERE hm.user_id!=" . _sql($my_id) . " AND hm.home_id=" . _sql($params->home_id) . " AND 
						hm.user_id NOT IN (
							SELECT mm.opp_user_id
							FROM t_mission m 
							LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
							WHERE m.home_id=" . _sql($params->home_id) . " AND 
								mm.user_id=" . _sql($my_id) . " AND m.private_flag=" . CHAT_MEMBER . " AND m.del_flag=0)
						) " : "" ) . "
					) a
					INNER JOIN m_user u ON a.user_id=u.user_id
					ORDER BY u.user_name ASC";

				$err = $mission->query($sql);
				if ($err != ERR_NODATA)
					$this->checkError($err);
					
				while ($err == ERR_OK)
				{
					array_push($missions, 
						array(
							"user_id"=> $mission->user_id,
							"user_name"=> $mission->user_name,
							"avartar"=> _avartar_full_url($mission->user_id)
						));

					$err = $mission->fetch();
				}
				$this->finish(array("missions" => $missions), ERR_OK);
			}

			// 全メンバー用 & 特定メンバー用
			$sql = "SELECT m.mission_id, m.mission_name, m.last_date FROM t_mission m 
				LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
				WHERE m.home_id=" . _sql($params->home_id) . " 
					AND mm.user_id=" . _sql($my_id) . " AND m.del_flag=0
					AND (mm.pinned IS NULL OR mm.pinned !=1) AND m.complete_flag=0
					AND m.private_flag=" . $params->private_flag;

			if ($params->sort_field != null)
				$order = $params->sort_field . " " . $params->sort_order;
			else 
				$order = "m.last_date DESC";

			$err = $mission->query($sql,
				array("order" => $order));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				array_push($missions, 
					array(
						"mission_id"=> $mission->mission_id,
						"mission_name"=> $mission->mission_name,
						"last_date"=> $mission->last_date
					));

				$err = $mission->fetch();
			}
			$this->finish(array("missions" => $missions), ERR_OK);
		}
		
		public function invite()
		{	
			$param_names = array("email", "user_id", "content", "mission_id", "signup_url", "signin_url");
			$this->setApiParams($param_names);
            $this->checkRequired(array("mission_id"));
			$params = $this->api_params;

            $mission = mission::getModel($params->mission_id);
            if ($mission == null)
                $this->checkError(ERR_NOTFOUND_MISSION);

			$home = home::getModel($mission->home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);

            if (_is_empty($params->signup_url))
                $params->signup_url = DEFAULT_APP_URL . "#/signup";

            if (_is_empty($params->signin_url))
                $params->signin_url = DEFAULT_APP_URL . "#/signin";

            if (_is_empty($params->content)) 
                $params->content = MAIL_HEADER . "\n" . _user_name() . "様より、チャットルーム「" . $mission->mission_name . "」へ招待されました。";
            else 
                $params->content = MAIL_HEADER . "\n" . $params->content;
			
			// start transaction
			$this->start();

			if (!_is_empty($params->email))
				$user = user::getModel($params->email);
           	else if (!_is_empty($params->user_id))
           		$user = user::getModel($params->user_id);
           	else
                $this->checkError(ERR_NOTFOUND_USER);

			if ($user == null) {
				$title = "～" . _user_name() . "より～　「ハンドクラウド」のルームへのご招待";
				$params->content .= "\n下記のURLにアクセスして、会員登録を行ってください。
" . $params->signup_url . "?invite_mission_id=" . $params->mission_id . "&email=" . $params->email . "&key=" . _key($params->mission_id . $params->email) . "
" . MAIL_FOOTER;
				_send_mail($params->email, $params->email, $title, $params->content);

				$this->finish(array("user_id" => null,
					"mission_id" => $mission->mission_id, 
					"home_id" => $mission->home_id), ERR_OK);
			}
			else {
                if ($mission->is_member($user->user_id)) {
                    $this->finish(null, ERR_OK);
                }
				else {
					$is_home_member = $home->is_member($user->user_id);

					$err = $mission->add_member($user->user_id);
					$this->checkError($err);

					$title = "～" . _user_name() . "より～　「ハンドクラウド」のルームへのご招待";
					if ($is_home_member) {
						// グループメンバーの場合
						$params->content = MAIL_HEADER . "
" . _user_name() . "様より、チャットルーム「" . $mission->mission_name . "」へ招待されました。
下記のURLにアクセスして、ログインしてください。
" . $params->signin_url . "
" . MAIL_FOOTER;
					}
					else {
                	    $params->content .= "\n下記のURLにアクセスして、招待を承認してください。
" . $params->signin_url . "
" . MAIL_FOOTER;
					}

					_send_mail($user->email, $user->user_name, $title, $params->content);

					$this->finish(array("user_id" => $user->user_id,
						"user_name" => $user->user_name,
						"mission_id" => $mission->mission_id, 
						"home_id" => $mission->home_id), ERR_OK);
				}
			}
		}

		public function self_invite()
		{
			$param_names = array("mission_id", "invite_key");
			$this->setApiParams($param_names);
            $this->checkRequired($param_names);
			$params = $this->api_params;

			$user_id = _user_id();
     		$user = _user();
     		if ($user == null)
            	$this->checkError(ERR_NOTFOUND_USER);

            $mission = mission::getModel($params->mission_id);
            if ($mission == null)
                $this->checkError(ERR_NOTFOUND_MISSION);

            if ($mission->private_flag != CHAT_PRIVATE)
                $this->checkError(ERR_NOPRIV);

            if ($mission->invite_key != $params->invite_key)
				$this->checkError(ERR_INVALID_INVITE_KEY);            	

			$home = home::getModel($mission->home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);
			
			// start transaction
			$this->start();

            if (!$mission->is_member($user->user_id)) {
				$is_home_member = $home->is_member($user_id);

				$err = $mission->add_member($user_id, 1);
				$this->checkError($err);
			}

			$this->finish(array(
				"mission_id" => $mission->mission_id, 
				"home_id" => $mission->home_id), ERR_OK);
		}

		public function remove_member()
		{
			$param_names = array("mission_id", "user_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$home_member = home_member::get_member($mission->home_id, $my_id);
            if ($home_member == null || ($home_member->priv != HPRIV_HMANAGER && $home_member->priv != HPRIV_RMANAGER && $home->client_id != $my_id))
                $this->checkError(ERR_NOPRIV);

			$err = $mission->remove_member($params->user_id);

			$this->finish(array("mission_id" => $mission->mission_id, "home_id" => $mission->home_id), $err);
		}

        public function get_name() 
        {
            $param_names = array("mission_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $mission_name = "";
            $mission = mission::getModel($params->mission_id);
            if ($mission)
                $mission_name = $mission->mission_name;

            $this->finish(array("mission_name" => $mission_name), ERR_OK);
        }

		public function get()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();
			$my_name = _user_name();

			$members = array();

			$mission = new mission;

			$sql = "SELECT m.*, 
					mm.pinned, mm.unreads, mm.to_unreads, ou.user_name opp_user_name, mm.opp_user_id, 
					DATEDIFF(NOW(), m.last_date) pass_date,
					mm.last_date mm_last_date, mm.push_flag, mm.priv, ou.email, ou.login_id
				FROM t_mission m 
				LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
				LEFT JOIN m_user ou ON mm.opp_user_id=ou.user_id
				WHERE m.mission_id=" . _sql($params->mission_id) . " AND 
					mm.user_id=" . _sql($my_id) . " AND
					m.del_flag=0";

			$err = $mission->query($sql);
			if ($err == ERR_NODATA)
				$this->checkError(ERR_NOTFOUND_MISSION);
			
			$mission->load_other_info();	

			if ($mission->private_flag == CHAT_MEMBER) {
				$mission->user_id = $mission->opp_user_id;
				$mission->user_name = $mission->opp_user_name;
				if ($mission->user_id)
					$mission->avartar = _avartar_full_url($mission->opp_user_id);

				array_push($members, array("user_id" => $mission->opp_user_id, 
						"user_name" => $mission->opp_user_name,
						"avartar" => _avartar_full_url($mission->opp_user_id)));
			}
			else {
				$mission_member = new model;
				$err = $mission_member->query("SELECT mm.user_id, u.user_name, u.email, u.login_id, mm.push_flag, mm.priv
					FROM t_mission_member mm 
					INNER JOIN m_user u ON mm.user_id=u.user_id 
					WHERE mm.mission_id=" . _sql($params->mission_id) . " AND mm.del_flag=0
					ORDER BY mm.create_time ASC");

				if ($err == ERR_NODATA)
					$this->checkError(ERR_NOTFOUND_MISSION);

				while ($err == ERR_OK)
				{
					$mission_member->avartar = _avartar_full_url($mission_member->user_id);
					array_push($members, $mission_member->props);

					$err = $mission_member->fetch();
				}
			}
			$mission->members = $members;

			$sql = "SELECT IFNULL(SUM(plan_budget), 0) FROM t_task WHERE mission_id=" . _sql($params->mission_id) . " AND del_flag=0";
			$db = db::getDB();
			$mission->total_budget = $db->scalar($sql);

			$sql = "SELECT IFNULL(SUM(plan_hours), 0) FROM t_task WHERE mission_id=" . _sql($params->mission_id) . " AND del_flag=0";
			$mission->total_hours = $db->scalar($sql);

			$sql = "SELECT MAX(plan_end_date) FROM t_task WHERE mission_id=" . _sql($params->mission_id) . " AND del_flag=0";
			$mission->end_date = $db->scalar($sql);

            $mission->emoticons = emoticon::all($mission->home_id);

			$this->finish(array("mission" => $mission->props), ERR_OK);
		}

		public function open()
		{
			$param_names = array("mission_id", "home_id", "user_id");
			$this->setApiParams($param_names);
			$params = $this->api_params;

			$mission_id = $params->mission_id;

			$my_id = _user_id();

			$mission_member = new mission_member;

			if (!_is_empty($params->mission_id)) {
				$err = $mission_member->select("mission_id=" . _sql($params->mission_id) . " 
					AND user_id=" . _sql($my_id));
				if ($err == ERR_OK)
				{
					$mission_member->pinned = 1;
					$err = $mission_member->save();
				}
				else {
					$err = ERR_NOPRIV;
				}
			}
			else if (!_is_empty($params->user_id)) {
				$sql = "SELECT mm.* FROM t_mission_member mm
					LEFT JOIN t_mission m ON mm.mission_id=m.mission_id 
					WHERE m.del_flag=0
						AND mm.user_id=" . _sql($my_id) . "
						AND mm.opp_user_id=" . _sql($params->user_id);
					
				$err = $mission_member->query($sql);
				
				if ($err == ERR_NODATA)
				{
					$mission = mission::add_mission(null, "個別", CHAT_MEMBER, $my_id, $params->user_id);
					if ($mission != null) {
						$err = $mission_member->query("SELECT mm.* FROM t_mission_member mm
							LEFT JOIN t_mission m ON mm.mission_id=m.mission_id 
							WHERE m.del_flag=0
								AND mm.user_id=" . _sql($my_id) . "
								AND mm.opp_user_id=" . _sql($params->user_id));	
						$mission_id = $mission->mission_id;	
					}
				}
				else {
					$mission_id = $mission_member->mission_id;
				}

				if ($err == ERR_OK)
				{
					$mission_member->pinned = 1;
					$err = $mission_member->save();
				}
				else {
					$err = ERR_NOPRIV;
				}

			}
			else {
				$err = ERR_INVALID_PARAMS;
			}

			$this->finish(array("mission_id" => $mission_id), $err);
		}

		public function pin()
		{
			$param_names = array("mission_id", "pinned");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$user_id = _user_id();

			$mission_member = new mission_member;

			$err = $mission_member->select("mission_id=" . _sql($params->mission_id) . " AND user_id=" . _sql($user_id));
			if ($err == ERR_OK)
			{
				$mission_member->pinned = $params->pinned;
				$err = $mission_member->save();
			}
			else {
				$err == ERR_NOPRIV;
			}

			$this->finish(null, $err);
		}

		public function upload_back_image()
		{
			$param_names = array("mission_id", "type");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);	

			$err = $mission->upload_back_image('file', $params->type);

			if ($err == ERR_OK) {
				$this->finish(array(
				"job_back" => $mission->job_back, 
				"job_back_url" => $mission->job_back_url,
				"job_back_pos" => $mission->job_back_pos,
				"prc_back" => $mission->prc_back, 
				"prc_back_url" => $mission->prc_back_url,
				"prc_back_pos" => $mission->prc_back_pos), ERR_OK);
			}
			else {
				$this->checkError($err);
			}
		}

		public function delete_back_image()
		{
			$param_names = array("mission_id", "type");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$mission->delete_back_image($params->type);

			$err = $mission->save();

			// refresh download full url
			$mission->load_other_info(true);
			
			$this->finish(array(
				"job_back" => $mission->job_back, 
				"job_back_url" => $mission->job_back_url,
				"job_back_pos" => $mission->job_back_pos,
				"prc_back" => $mission->prc_back, 
				"prc_back_url" => $mission->prc_back_url,
				"prc_back_pos" => $mission->prc_back_pos), ERR_OK);
		}

		public function set_back_pos()
		{
			$param_names = array("mission_id", "type", "back_pos");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			if ($params->type == 0)
				$mission->job_back_pos = $params->back_pos;
			else
				$mission->prc_back_pos = $params->back_pos;

			$err = $mission->save();

			$this->finish(array(
				"job_back_pos" => $mission->job_back_pos,
				"prc_back_pos" => $mission->prc_back_pos), ERR_OK);
		}

		public function upload_attach()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$file_size = _get_uploaded_filesize('file');
			$plan = _user_plan();

  			$total_file_size = user::get_total_file_size() + $file_size; // (MB)
  			if ($plan->max_upload != -1 && $plan->max_upload * 1024 <= $total_file_size)
  				$this->checkError(ERR_OVER_MAX_UPLOAD, $plan->max_upload);

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);	

			$err = $mission->upload_attach('file');

			if ($err == ERR_OK) {
				$this->finish(array(
					"mission_attach_id" => $mission->new_attach->mission_attach_id, 
					"mission_attach_url" => $mission->new_attach->attach_name, 
					"width" => $mission->image_width,
					"height" => $mission->image_height,
					"create_time"=>date("Y-m-d H:i:s")), ERR_OK);
			}
			else {
				$this->checkError($err);
			}
		}

		public function delete_attach()
		{
			$param_names = array("mission_id", "mission_attach_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$mission->delete_attach($params->mission_attach_id);
			
			$this->finish(array(), ERR_OK);
		}

		public function attaches()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$params = $this->api_params;

			$attaches = array();
			$mission_attach = new mission_attach;

			$sql = "SELECT mission_attach_id, attach_name, file_size, create_time FROM t_mission_attach
				WHERE mission_id=" . _sql($params->mission_id) . " AND del_flag=0 ORDER BY create_time DESC";

			$err = $mission_attach->query($sql);
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				// get full url
				$mission_attach->get_url();

				array_push($attaches, array(
					"mission_attach_id" => $mission_attach->mission_attach_id, 
					"attach_url" => $mission_attach->attach_url, 
					"file_name" => $mission_attach->file_name, 
					"file_size" => $mission_attach->file_size,
					"create_time" => $mission_attach->create_time));

				$err = $mission_attach->fetch();
			}

			$this->finish(array("attaches" => $attaches), ERR_OK);
		}

		public function set_repeat()
		{
			$param_names = array("mission_id", "repeat_type", "repeat_weekday", "repeat_month", "repeat_monthday");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id", "repeat_type"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$mission->repeat_type = $params->repeat_type;

			switch ($mission->repeat_type) {
				case REPEAT_NONE:
					$mission->repeat_day = null;
					break;
				case REPEAT_EVERYDAY:
					$mission->repeat_day = null;
					break;
				case REPEAT_WORKDAY:
					$mission->repeat_day = null;
					break;
				case REPEAT_WEEK:
					if ($params->repeat_weekday < 0 || $params->repeat_weekday > 6)
						$this->checkError(ERR_INVALID_PARAMS);
					$mission->repeat_day = $params->repeat_weekday;
					break;
				case REPEAT_MONTH:
					if ($params->repeat_monthday < 1 || $params->repeat_monthday > 31)
						$this->checkError(ERR_INVALID_PARAMS);
					$mission->repeat_day = $params->repeat_monthday;
					break;
				case REPEAT_YEAR:
					if ($params->repeat_month < 1 || $params->repeat_month > 12)
						$this->checkError(ERR_INVALID_PARAMS);
					if ($params->repeat_monthday < 1 || $params->repeat_monthday > 31)
						$this->checkError(ERR_INVALID_PARAMS);
					$mission->repeat_day = $params->repeat_month . "-" . $params->repeat_monthday;
					break;
				default:
					$this->checkError(ERR_INVALID_PARAMS);
					break;
			}

			$err = $mission->save();

			$this->finish(array(), $err);
		}

		public function import_csv()
		{
			$param_names = array("home_id");
			$this->setApiParams($param_names);
			$params = $this->api_params;

			$file_name = _get_uploaded_filename('file');
			$ext = _extname($file_name);

			if ($ext != 'csv')
				$this->checkError(ERR_INVALID_CSV);

			$tmppath = TMP_PATH . _newId() . ".csv";
			if (!_upload('file', $tmppath))
				$this->checkError(ERR_INVALID_CSV);

			// start transaction
			$this->start();

			$imported = 0;
			$first = true;
			$valid = false;
			ini_set('auto_detect_line_endings',TRUE);
			if (($fp = fopen($tmppath, "r")) !== FALSE) {
			    while (($row = fgetcsv($fp, 1000, ",")) !== FALSE) {
			        $num = count($row);
			        if ($first) {
			        	$first = false;

			        	if ($num == 12) {
			        		if (_sjis2utf8($row[0]) == 'No' &&
			        			_sjis2utf8($row[1]) == 'チャットルーム名' &&
			        			_sjis2utf8($row[2]) == 'タスク名' &&
			        			_sjis2utf8($row[3]) == 'タスクの概要' &&
			        			_sjis2utf8($row[4]) == 'コメント' &&
			        			_sjis2utf8($row[5]) == 'ファイブスター' &&
			        			_sjis2utf8($row[6]) == '担当者' &&
			        			_sjis2utf8($row[7]) == '開始日' &&
			        			_sjis2utf8($row[8]) == '期限' &&
			        			_sjis2utf8($row[9]) == '工数' &&
			        			_sjis2utf8($row[10]) == '予算' &&
			        			_sjis2utf8($row[11]) == 'スキル')	
			        			$valid = true;
			        	}
			        }
			        else if ($valid) {
				        if ($num == 12) {
				        	$task = task::insert_csv_row($params->home_id, $row);
				        	if ($task != null)
					        	$imported ++;
				        }
			        }
			    }
			    fclose($fp);
			}
			ini_set('auto_detect_line_endings',FALSE);

			if (!$valid)
				$this->checkError(ERR_INVALID_CSV);

			$this->finish(array("imported" => $imported), ERR_OK);
		}

		public function break_mission()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$my_id = _user_id();

			// start transaction
			$this->start();

			$task = new task;
			$err = $task->select("mission_id=" . _sql($params->mission_id) . " AND performer_id=" . _sql($my_id));
			while($err == ERR_OK)
			{
				$task->performer_id = null; // set performer to all
				$err = $task->save();

				$err = $task->fetch();
			}

			$mission_member = new mission_member;
			$err = $mission_member->select("mission_id=" . _sql($params->mission_id) . " AND user_id=" . _sql($my_id));
			if ($err == ERR_OK) {
				$err = $mission_member->remove(true);
			}

			$this->finish(null, ERR_OK);
		}

		public function invitable_members()
		{
			$param_names = array('mission_id');
			$this->setApiParams($param_names);
			$params = $this->api_params;
			
			$my_id = _user_id();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);
				
			$mmembers = array();
			if ($params->mission_id != null) {
				$mission_member = new mission_member;
				$err = $mission_member->select("mission_id=" . _sql($params->mission_id));

				while ($err == ERR_OK)
				{
					array_push($mmembers, $mission_member->user_id);
					$err = $mission_member->fetch();
				}
			}

			$members = array();
			$home_member = new home_member;

			$sql = "SELECT hm.user_id, u.user_name, u.email, u.login_id
				FROM t_home_member hm INNER JOIN m_user u ON hm.user_id=u.user_id
				WHERE hm.del_flag=0 AND hm.home_id=" . _sql($mission->home_id);

			$err = $home_member->query($sql,
				array("order" => "user_name ASC"));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			$home_member_id = -1;
			$old_member = null;
			while ($err == ERR_OK)
			{
				$member_user_id = $home_member->user_id;

				$found = false;
				for($i = 0; $i < count($mmembers); $i ++) 
				{
					$muser_id = $mmembers[$i];
					if ($muser_id == $member_user_id)
					{
						array_splice($mmembers, $muser_id);
						$found = true;
						break;
					}
				}

				if ($found == false) {
					array_push($members, array("user_id" => $member_user_id, 
						"user_name" => $home_member->user_name,
						"email" => $home_member->email,
						"login_id" => $home_member->login_id,
						"avartar" => _avartar_full_url($member_user_id)
						));
				}

				$err = $home_member->fetch();
			}

			$this->finish(array("users" => $members), ERR_OK);
		}

        public function priv()
        {
            $param_names = array("mission_id", "user_id", "priv");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $this->start();

            $my_id = _user_id();
            $mission_id = $params->mission_id;

            $mission = mission::getModel($mission_id);
            if ($mission == null)
                $this->checkError(ERR_NOTFOUND_MISSION);

            $home = home::getModel($mission->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);
            $home_id = $home->home_id;

            $mine_home_member = home_member::get_member($home_id, $my_id);
            if ($mine_home_member == null)
            	$this->checkError(ERR_NOPRIV);

            $mine_mission_member = mission_member::get_member($mission_id, $my_id);
            if (!($mine_home_member->priv == HPRIV_HMANAGER || $mine_home_member->priv == HPRIV_RMANAGER || $mine_mission_member != null && $mine_mission_member->priv == RPRIV_MANAGER))
            	$this->checkError(ERR_NOPRIV);

            $mission_member = mission_member::get_member($mission_id, $params->user_id);
            if ($mission_member == null)
                $this->checkError(ERR_NOTFOUND_USER);

            $mission_member->priv = $params->priv;
            $err = $mission_member->save();
            $this->checkError($err);

            $this->finish(array("priv" => $mission_member->priv), ERR_OK);
        }

		public function upload_emoticon()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);	

			$home = home::getModel($mission->home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);	

			$image = emoticon::upload('file');
			if ($image == null)
				$this->checkError(ERR_FAIL_UPLOAD);

			$this->finish(array(
				"image" => $image), ERR_OK);
		}

		public function add_emoticon()
		{
			$param_names = array("mission_id", "title", "alt", "image");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$home = home::getModel($mission->home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);

			$emoticon = new emoticon;
			$emoticon->home_id = $home->home_id;
			$emoticon->title = $params->title;
			$emoticon->alt = $params->alt;
			$emoticon->image = basename($params->image);

			if (emoticon::is_exist_by_alt($home->home_id, $emoticon->alt))
				$this->checkError(ERR_ALREADY_USING_EMOTICON);

			$tmppath = TMP_PATH . $emoticon->image;
			$emoticonpath = emoticon_PATH . $emoticon->image;

			if (!file_exists($tmppath))
				$this->checkError(ERR_FAIL_UPLOAD);

			rename(TMP_PATH . $emoticon->image, EMOTICON_PATH . $emoticon->image);

			$err = $emoticon->save();

			$emoticon->image = EMOTICON_URL . $emoticon->image;

			$this->finish(array("emoticon" => $emoticon->props), $err);
		}

		public function save_emoticon()
		{
			$param_names = array("emoticon_id", "mission_id", "title", "alt", "image");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$home = home::getModel($mission->home_id);
			if ($home == null)
				$this->checkError(ERR_NOTFOUND_HOME);

			$emoticon = emoticon::getModel($params->emoticon_id);
			if ($emoticon == null)
				$this->checkError(ERR_NOTFOUND_EMOTICON);

			$emoticon->home_id = $home->home_id;
			$emoticon->title = $params->title;
			$emoticon->alt = $params->alt;
			$emoticon->image = basename($params->image);

			if (emoticon::is_exist_by_alt($home->home_id, $emoticon->alt, $emoticon->emoticon_id))
				$this->checkError(ERR_ALREADY_USING_EMOTICON);

			$tmppath = TMP_PATH . $emoticon->image;
			$emoticonpath = emoticon_PATH . $emoticon->image;

			if (file_exists($tmppath)) {
				rename(TMP_PATH . $emoticon->image, EMOTICON_PATH . $emoticon->image);
			}

			$err = $emoticon->save();

			$emoticon->image = EMOTICON_URL . $emoticon->image;

			$this->finish(array("emoticon" => $emoticon->props), $err);
		}

		public function remove_emoticon()
		{
			$param_names = array("emoticon_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$emoticon = emoticon::getModel($params->emoticon_id);
			if ($emoticon == null)
				$this->checkError(ERR_NOTFOUND_EMOTICON);

			$emoticon->remove();

			$this->finish(null, ERR_OK);
		}
	}
?>