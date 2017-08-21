<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2016/08/01
	---------------------------------------------------*/

	class react_user extends model 
	{
		public function __construct()
		{
			parent::__construct("t_react_user",
				"react_user_id",
				array(
					"cmsg_id",
					"emoticon_id",
					"user_id"),
				array("auto_inc" => true));
		}

		static public function set_react($cmsg_id, $emoticon_id, $user_id)
		{
			$react_user = new static;

			$err = $react_user->select("cmsg_id=" . _sql($cmsg_id) . 
				" AND emoticon_id=" . _sql($emoticon_id) . 
				" AND user_id=" . _sql($user_id));

			if ($err == ERR_OK) {
				// 削除
				$react_user->remove(true);
			}
			else {
				// 追加
				$react_user->cmsg_id = $cmsg_id;
				$react_user->emoticon_id = $emoticon_id;
				$react_user->user_id = $user_id;

				$err = $react_user->save();
			}

			return static::get_amount($cmsg_id, $emoticon_id);
		}

		static public function get_amount($cmsg_id, $emoticon_id)
		{
			$db = db::getDB();
			$sql = "SELECT COUNT(react_user_id) FROM t_react_user 
				WHERE cmsg_id=" . _sql($cmsg_id) . " AND emoticon_id=" . _sql($emoticon_id);

			return $db->scalar($sql);
		}

		static public function get_users($cmsg_id, $emoticon_id)
		{
			$react_user = new model;

			$users = array();
			$sql = "SELECT u.user_id, u.user_name 
				FROM t_react_user ru LEFT JOIN m_user u ON ru.user_id=u.user_id
				WHERE cmsg_id=" . _sql($cmsg_id) . " AND emoticon_id=" . _sql($emoticon_id) . "
				ORDER BY ru.create_time ASC";

			$err = $react_user->query($sql);
			while($err == ERR_OK) {
				array_push($users, array($react_user->user_id, $react_user->user_name));
				$err = $react_user->fetch();
			}

			return $users;
		}

		static public function remove_all($cmsg_id) {
			$db = db::getDB();
			$sql = "DELETE FROM t_react_user 
				WHERE cmsg_id=" . _sql($cmsg_id);

			$db->execute($sql);
		}
	}
?>