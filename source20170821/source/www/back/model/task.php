<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class task extends model 
	{
		public function __construct()
		{
			parent::__construct("t_task",
				"task_id",
				array(
					"mission_id",
					"user_id",
					"performer_id",
					"task_name",
					"plan_start_date",
					"plan_start_time",
					"plan_end_date",
					"plan_end_time",
					"plan_budget",
					"plan_hours",
					"level",
					"complete_flag",
					"complete_time",
					"progress",
					"summary",
					"x",
					"y",
					"processed",
					"proclevel",
					"start_alarm",
					"end_alarm"),
				array("auto_inc" => true));
		}

		public static function last_task($mission_id)
		{
			if ($mission_id == null)
				return null;

			$task = new task;
			$err = $task->select("mission_id=" . _sql($mission_id), 
				array("order" => "task_id DESC", 
					"limit" => 1));
			if ($err == ERR_OK)
				return $task;

			return null;
		}

		public function load_and_changed_data($params) {
			$changed_data = "";
			if ($params->existProp('mission_id') && $params->mission_id != $this->mission_id) {
				$changed_data .= "\nチャットルーム: " . mission::get_mission_name($this->mission_id) . " -> " . mission::get_mission_name($params->mission_id);
			}
			
			if ($params->existProp('performer_id') && $params->performer_id != $this->performer_id) {
				$changed_data .= "\n担当者: " . user::get_user_name($this->performer_id) . " -> " . user::get_user_name($params->performer_id);
			}
			
			if ($params->existProp('task_name') && $params->task_name != $this->task_name) {
				$changed_data .= "\nタスク名: " . $this->task_name . " -> " . $params->task_name;
			}
			
			if ($params->existProp('plan_start_date') && ($params->plan_start_date != $this->plan_start_date || $params->plan_start_time != $this->plan_start_time)) {
				$changed_data .= "\n開始予定日: ";
				$changed_data .= ($this->plan_start_time != null ? $this->plan_start_time : $this->plan_start_date);
				$changed_data .= " -> ";
				$changed_data .= ($params->plan_start_time != null ? $params->plan_start_time : $params->plan_start_date);
			}
			
			if ($params->existProp('plan_end_date') && ($params->plan_end_date != $this->plan_end_date || $params->plan_end_time != $this->plan_end_time)) {
				$changed_data .= "\n完了予定日: ";
				$changed_data .= ($this->plan_end_time != null ? $this->plan_end_time : $this->plan_end_date);
				$changed_data .= " -> ";
				$changed_data .= ($params->plan_end_time != null ? $params->plan_end_time : $params->plan_end_date);
			}
			
			if ($params->existProp('plan_budget') && $params->plan_budget != $this->plan_budget) {
				$changed_data .= "\n依頼金額: " . $this->plan_budget . "円 -> " . $params->plan_budget . "円";
			}
			
			if ($params->existProp('plan_hours') && $params->plan_hours != $this->plan_hours) {
				$changed_data .= "\n工数: " . $this->plan_hours . "時間 -> " . $params->plan_hours . "時間";
			}
			
			if ($params->existProp('summary') && $params->summary != $this->summary) {
				$changed_data .= "\n概要: \n" . $this->summary . "\n ------> \n" . $params->summary;
			}

			if ($changed_data != "")
			{
				$changed_data = "\n[更新内容 " . _datetime() . "]" . $changed_data . "\n";
			}

			$this->load($params);

			return $changed_data;
		}

		public function update_priority($user_id)
		{
			$today = _date();

			if ($this->plan_start_date != null && $today >= _date(strtotime($this->plan_start_date)) ||
				$this->plan_end_date != null && $today >= _date(strtotime($this->plan_end_date))) {
				if ($this->performer_id == $user_id || $this->user_id == $user_id) {
					$task_user = new task_user;
					$err = $task_user->select("task_id=" . _sql($this->task_id) . " AND user_id=" . _sql($user_id));
					// in the case that performer and creator of task is self
					if (($err == ERR_NODATA || $err == ERR_OK && $task_user->priority === null) && $this->complete_flag == 0) {
						$task_user->task_id = $this->task_id;
						$task_user->user_id = $user_id;
						$task_user->priority = 1;
						$this->priority = 1;

						$err = $task_user->save();
					}
				}
			}
		}

		public function upload_attach($field)
		{
			if ($this->task_id == null)
				return ERR_NOTFOUND_TASK;

			$file_name = _get_uploaded_filename($field);
			$file_size = _get_uploaded_filesize($field);

			$task_comment = new task_comment;

			$task_comment->task_id = $this->task_id;
			$task_comment->user_id = _user_id();
			$task_comment->attach = $file_name;
			$task_comment->comment_type = 2;
			$task_comment->file_size = $file_size;

			$err = $task_comment->save();
			if ($err != ERR_OK)
				return $err;

			$url = ATTACH_URL . date('Y/m/') . "j_a_" . $task_comment->task_comment_id;

			// real file location
			$path = DATA_PATH . $url;

			// upload
			if (_upload($field, $path) == null) {
				return ERR_FAIL_UPLOAD;
			}

			// download url
			$task_comment->attach = $url . "/" . $file_name;

			$err = $task_comment->save();

			$this->new_attach = $task_comment;

			return $err;
		}

		public static function insert_csv_row($home_id, $row)
		{
			$my_id = _user_id();
			if ($my_id == null)
				return null;

			# 0 No
			$mission_name 		= _sjis2utf8($row[1]); # 1 チャットルーム名
			$task_name 			= _sjis2utf8($row[2]); # 2 タスク名
			$summary 			= _sjis2utf8($row[3]); # 3 タスクの概要
			$comment 			= _sjis2utf8($row[4]); # 4 コメント
			$level	 			= _sjis2utf8($row[5]); # 5 ファイブスター
			$performer_name 	= _sjis2utf8($row[6]); # 6 担当者
			$plan_start_date 	= _sjis2utf8($row[7]); # 7 開始日
			$plan_end_date 		= _sjis2utf8($row[8]); # 8 期限
			$plan_hours 		= _sjis2utf8($row[9]); # 9 工数
			$plan_budget 		= _sjis2utf8($row[10]); # 10 予算
			$skills		 		= _sjis2utf8($row[11]); # 11 スキル

			$task = new task;
			if (!_is_empty($mission_name)) {
				$mission = mission::from_mission_name($mission_name);
				if ($mission == null)
					$mission = mission::add_mission($home_id, $mission_name, 0); // 全メンバー用
				$task->mission_id = $mission->mission_id;
			}
			else {
				$task->mission_id = null;
			}
			$task->user_id = $my_id;
			$task->performer_id = $my_id;
			$task->complete_flag = 0;
			$task->processed = 0;
			$task->task_name = $task_name;
			$task->summary = $summary;
			$task->level = $level;
			$task->plan_hours = $plan_hours == '' ? null : _number_from_csv($plan_hours);
			$task->plan_budget = $plan_budget == '' ? $plan_budget : _number_from_csv($plan_budget);
			$task->plan_start_date = _datetime_from_csv($plan_start_date);
			$task->plan_end_date = _datetime_from_csv($plan_end_date);

			if ($task->mission_id != null) {
				$mission_member = new mission_member;
				$err = $mission_member->query("SELECT mm.user_id FROM t_mission_member mm
					LEFT JOIN m_user u ON mm.user_id=u.user_id
					WHERE mm.mission_id=" . _sql($task->mission_id) . " AND u.user_name=" . _sql($performer_name));
				if ($err == ERR_OK) {
					$task->performer_id = $mission_member->user_id;
				}
			}

			$err = $task->save();

			/*
			if ($priority == 1 && $err == ERR_OK)
			{
				$task_user = new task_user;

				$task_user->task_id = $task->task_id;
				$task_user->user_id = $my_id;
				$task_user->priority = 1;

				$err = $task_user->save();
			}*/

			if (!_is_empty($comment) && $err == ERR_OK)
			{
				$task_comment = new task_comment;

				$task_comment->task_id = $task->task_id;
				$task_comment->user_id = $my_id;
				$task_comment->comment_type = 0;
				$task_comment->content = $comment;

				$err = $task_comment->save();
			}

			if (!_is_empty($skills) && $err == ERR_OK)
			{
				$skills = preg_replace("/;/", ",", $skills);
				$err = task::set_skills($task->task_id, $skills);
			}

			if ($err == ERR_OK)
				return $task;
			else 
				return null;
		}

		static public function getInboxTasks($user_id)
		{
			$sql = "SELECT COUNT(*) FROM t_task t
				WHERE t.performer_id=" . _sql($user_id) . 
				" AND t.complete_flag!=1 AND t.del_flag=0 AND t.mission_id IS NULL";

			$db = db::getDB();
			return $db->scalar($sql);
		}

		static public function set_skills($task_id, $skills)
		{
			$skill = new task_skill;
			if (_is_empty($task_id))
				return ERR_OK;

			$task = task::getModel($task_id);
			if ($task == null)
				return ERR_NOTFOUND_TASK;

			$mission = mission::getModel($task->mission_id);
			if ($mission == null)
				return ERR_NOTFOUND_MISSION;

			if (_is_empty($skills))
			{
				$err = $skill->remove_where("task_id=" . _sql($task_id), true);
			}
			else {
				if (!is_array($skills))
					$skills = preg_split("/,/", $skills);

				$where = "";
				foreach($skills as $skill_name)
				{
					$skill_name = trim($skill_name);
					if ($where != "")
						$where .= ",";
					$where .= _sql($skill_name);
				}
				if ($where != "")
					$where = " AND skill_name NOT IN (" . $where . ")";

				$err = $skill->remove_where("task_id=" . _sql($task_id) . $where, true);

				if ($err == ERR_OK) {
					foreach($skills as $skill_name)
					{
						$skill_name = trim($skill_name);
						$skill = new task_skill;
						$err = $skill->select("task_id=" . _sql($task_id) . " AND skill_name=" . _sql($skill_name));
						if ($err == ERR_NODATA) {
							$skill->task_id = $task_id;
							$skill->skill_name = $skill_name;

							$err = $skill->save();
							if ($err != ERR_OK)
								return $err;
						}

						skill::add_skill($mission->home_id, $skill_name);
					}
				}
			}

			return $err;
		}

	};
?>