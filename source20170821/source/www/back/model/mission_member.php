<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class mission_member extends model 
	{
		public function __construct()
		{
			parent::__construct("t_mission_member",
				"mission_member_id",
				array(
					"mission_id",
					"user_id",
					"pinned",
					"unreads",
					"to_unreads",
					"opp_user_id",
					"last_date",
					"push_flag",
					"priv"),
				array("auto_inc" => true));
		}

		public static function user_ids($mission_id, $except_user_id=null, $only_accepted=true)
		{
			$user_ids = array();
			$mission_member = new model;
			$accepted_join = "";
			$accepted_where = "";
			if ($only_accepted) {
				$accepted_join = "INNER JOIN t_mission m ON mm.mission_id=m.mission_id
				LEFT JOIN t_home_member hm ON m.home_id=hm.home_id AND mm.user_id=hm.user_id";
				$accepted_where = " AND (m.home_id IS NULL OR m.home_id IS NOT NULL AND hm.accepted=1)";
			}
			$err = $mission_member->query("SELECT DISTINCT mm.user_id 
				FROM t_mission_member mm 
				INNER JOIN m_user u ON mm.user_id=u.user_id AND u.del_flag=0 " . $accepted_join . " 
				WHERE mm.mission_id=" . _sql($mission_id) . " AND mm.del_flag=0 " . $accepted_where . "
				ORDER BY mm.create_time ASC");

			while ($err == ERR_OK)
			{
				if ($except_user_id != $mission_member->user_id)
					array_push($user_ids, $mission_member->user_id);

				$err = $mission_member->fetch();
			}

			return $user_ids;
		}

		public static function set_last_date($mission_id, $user_id)
		{
			$db = db::getDB();
            
            $sql = "UPDATE t_mission_member SET last_date=NOW()";
            $sql .= " WHERE mission_id=" . _sql($mission_id) . " AND user_id=" . _sql($user_id);

            $db->execute($sql);
		}

		public static function is_push($mission_id, $user_id, $content)
		{
			$db = db::getDB();
            
            $member = new model;
            $sql = "SELECT mm.push_flag, m.private_flag FROM t_mission_member mm 
            	LEFT JOIN t_mission m ON m.mission_id=mm.mission_id
            	WHERE mm.mission_id=" . _sql($mission_id) . " AND user_id=" . _sql($user_id);

            $err = $member->query($sql);
            if ($err == ERR_NODATA)
            	return false;

            $push_flag = $member->push_flag;
            $private_flag = $member->private_flag;

            if ($push_flag < PUSH_OFF || $push_flag > PUSH_TO)
            	$push_flag = PUSH_TO;

            $to = "[to:" . $user_id . "]";
            $to_all = "[to:all]";

            $is_to = (strstr($content, $to) !== FALSE || strstr($content, $to_all) !== FALSE);
            $none_to = (strstr($content, "[to:") === FALSE);

            if ($private_flag == CHAT_MEMBER) {
            	return $push_flag == PUSH_ALL;
            }
            else {
	            // ルームの通知設定（全通知）、
	            if ($push_flag == PUSH_ALL) {
	            	// メッセージのTO指定（無し）　→　ルーム内の全員に通知
	            	// メッセージのTO指定（有り）　→　ルーム内の指定者にのみ通知
	            	if ($none_to || $is_to) 
	            		return true;
	            	return false;
	            }
	            // ルームの通知設定（TOのみを通知）
	            else if ($push_flag == PUSH_TO) {
	            	// メッセージのTO指定（無し）　→　通知無し
	            	// メッセージのTO指定（有り）　→　TO指定者のみ通知
	            	if ($is_to)
	            		return true;
	            	return false;
	            }
	            // ルームの通知設定（通知OFF）、メッセージのTO指定（無し）　→　通知無し
	            // ルームの通知設定（通知OFF）、メッセージのTO指定（有り）　→　通知無し
	            return false;	
            }
        }

        public static function get_member($mission_id, $user_id)
        {
            $mission_member = new mission_member;

            $err = $mission_member->select("mission_id=" . _sql($mission_id) . " AND user_id=" . _sql($user_id));
            if ($err == ERR_NODATA)
                return null;

            return $mission_member;
        }
	};
?>