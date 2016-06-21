<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class session extends model 
	{
		public function __construct()
		{
			parent::__construct("t_session",
				array("session_id", "user_id"),
				array("login_time",
					"access_time",
					"ip"),
				array("auto_inc" => false));
		}

		static public function update_session()
		{
			$me = _user();
			if ($me != null) {
				$me->access_time = "##NOW()";
				$err = $me->save();

				$db = db::getDB();
				$user_id = _user_id();
				$session_id = session_id();
				$db->execute("UPDATE t_session SET access_time=NOW() WHERE session_id=" . _sql($session_id) . " AND user_id=" . _sql($user_id));
			}
		}

		static public function insert_session()
		{
			$user_id = _user_id();
			$session_id = session_id();
			if ($user_id != null) {
				$session = session::getModel(array($session_id, $user_id));
				if ($session != null)
					$session->remove(true);

				$session = new session;
				$session->session_id = $session_id;
				$session->user_id = $user_id;
				$session->login_time = "##NOW()";
				$session->access_time = "##NOW()";
				$session->ip = _ip();

				$err = $session->insert();
			}

			session::clear_old_session();
		}

		static public function clear_old_session()
		{
			$db = db::getDB();
			$sql = "DELETE FROM t_session WHERE DATEDIFF(NOW(), access_time) > 30"; 
			$db->query($sql);
		}
	};
?>