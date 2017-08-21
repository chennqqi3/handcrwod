<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class task_user extends model 
	{
		public function __construct()
		{
			parent::__construct("t_task_user",
				"task_user_id",
				array(
					"task_id",
					"user_id",
					"sort",
					"priority"),
				array("auto_inc" => true));
		}

		static public function getByTaskAndUserId($task_id, $user_id)
		{
			$task_user = new task_user;

			$err = $task_user->select("task_id=" . _sql($task_id) . " AND user_id=" . _sql($user_id));
			if ($err == ERR_NODATA)
			{
				$task_user->task_id = $task_id;
				$task_user->user_id = $user_id;
			}

			return $task_user;
		}

		static public function getPriorityTasks($user_id)
		{
			$sql = "SELECT COUNT(*) FROM t_task_user tu 
				INNER JOIN t_task t ON tu.task_id=t.task_id 
				LEFT JOIN t_mission m ON t.mission_id=m.mission_id
				WHERE t.performer_id=" . _sql($user_id) . 
				" AND tu.user_id=" . _sql($user_id) . 
				" AND tu.priority=1 
				AND t.complete_flag!=1 AND t.del_flag=0 AND tu.del_flag=0 
				AND (t.mission_id IS NULL OR t.mission_id IS NOT NULL AND m.complete_flag!=1 AND m.del_flag=0)";

			$db = db::getDB();
			return $db->scalar($sql);
		}
	};
?>