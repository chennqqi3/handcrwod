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
					"opp_user_id",
					"last_date",
					"push_flag"),
				array("auto_inc" => true));
		}

		public static function user_ids($mission_id, $except_user_id=null)
		{
			$user_ids = array();
			$mission_member = new model;
			$err = $mission_member->query("SELECT mm.user_id 
				FROM t_mission_member mm 
				INNER JOIN m_user u ON mm.user_id=u.user_id AND u.del_flag=0
				WHERE mm.mission_id=" . _sql($mission_id) . " AND mm.del_flag=0
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

		public static function is_push($mission_id, $user_id)
		{
			$db = db::getDB();
            
            $sql = "SELECT push_flag FROM t_mission_member";
            $sql .= " WHERE mission_id=" . _sql($mission_id) . " AND user_id=" . _sql($user_id);

            return $db->scalar($sql) == 1;
        }
	};
?>