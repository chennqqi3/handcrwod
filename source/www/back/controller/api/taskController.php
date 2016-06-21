<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class TaskController extends APIController {
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
			$param_names = array("mission_id", "task_name", "priority");
			$this->setApiParams($param_names);
			$this->checkRequired(array("task_name"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			$task = new task;
			$task->load($params);
			$task->user_id = $my_id;
			$task->performer_id = $my_id;
			$task->complete_flag = 0;
			$task->processed = 0;

			$err = $task->save();

			if ($params->priority == 1)
			{
				$task_user = new task_user;

				$task_user->task_id = $task->task_id;
				$task_user->user_id = $my_id;
				$task_user->priority = 1;

				$err = $task_user->save();
			}

			$this->finish(array("task_id" => $task->task_id, "mission_id" => $task->mission_id), $err);
		}

		public function add_tasks()
		{
			$param_names = array("mission_id", "task_names", "priority");
			$this->setApiParams($param_names);
			$this->checkRequired(array("task_names"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			$task_names = preg_split("/\n/", $params->task_names);

			$inserted = 0;

			foreach($task_names as $task_name) {			
				$task_name = trim($task_name);
				if ($task_name != "") {
					$task = new task;
					$task->load($params);
					$task->task_name = $task_name;
					$task->user_id = $my_id;
					$task->performer_id = $my_id;
					$task->complete_flag = 0;
					$task->processed = 0;

					$err = $task->save();

					if ($err == ERR_OK && $params->priority == 1)
					{
						$task_user = new task_user;

						$task_user->task_id = $task->task_id;
						$task_user->user_id = $my_id;
						$task_user->priority = 1;

						$err = $task_user->save();
					}

					if ($err == ERR_OK) {
						$inserted ++;
					}
				}	
			}

			$this->finish(array("inserted" => $inserted), $err);
		}

		public function edit()
		{
			$param_names = array("task_id", 
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
					"sort",
					"complete_flag",
					"progress",
					"summary",
					"x",
					"y",
					"processed",
					"proclevel",
					"priority",
					"skills");
			$this->setApiParams($param_names);
			$this->checkRequired(array("task_id"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$task = task::getModel($params->task_id);
			if ($task == null) 
				$this->checkError(ERR_NODATA);
			$old_performer_id = $task->performer_id;
			$old_budget = $task->plan_budget;

			if ($task->mission_id == null && $params->existProp('performer_id') && $params->performer_id != _user_id())
			{
				$this->checkError(ERR_NOTALLOW_PERFORMER);
			}
		
			$changed_data = $task->load_and_changed_data($params);

			if ($params->complete_flag) {
				$task->complete_time = "##NOW()";
				$task->progress = 100;
			}
			else if ($params->progress == 100) {
				$task->complete_time = "##NOW()";
				$task->complete_flag = 1;
			}
			else if ($params->progress < 100) {
				$task->complete_time = null;
				$task->complete_flag = 0;
			}

			$err = $task->save();

			if ($err == ERR_OK)
			{
				if ($params->skills != null) {
					task::set_skills($task->task_id, $params->skills);
				}

				if ($params->performer_id != null) 
				{
					$mission_member = new mission_member;
					$err = $mission_member->select("mission_id=" . _sql($task->mission_id) . " AND user_id=" . _sql($params->performer_id));
					if ($err == ERR_NODATA) {
						$mission_member->mission_id = $task->mission_id;
						$mission_member->user_id = $params->performer_id;

						$err = $mission_member->save();
						$this->checkError($err);
					}
				}

				if ($params->plan_start_date != null || 
					$params->plan_start_time != null || 
					$params->plan_end_date != null || 
					$params->plan_end_time != null)
				{
					// 期限が変更された場合、優先マークを初期化
					$task_user = task_user::getByTaskAndUserId($task->task_id, _user_id());
					if ($task_user->task_user_id != null) {
						$task_user->priority = null;

						$err = $task_user->save();
					}

					$task->update_priority(_user_id());
				}

				if ($params->sort !== null || $params->priority !== null)
				{
					$task_user = task_user::getByTaskAndUserId($task->task_id, _user_id());
					if ($params->sort !== null)
						$task_user->sort = $params->sort;
					if ($params->priority !== null)
						$task_user->priority = $params->priority;

					$err = $task_user->save();
				}

				if ($task->performer_id != null) {
					google_batch::add_event($task->task_id);
				}

				/*
				if ($old_performer_id != $task->performer_id || $old_budget != $task->plan_budget) {
					if ($old_performer_id != null) {
						team_member::refresh_deliver_amount($task->user_id, $old_performer_id);
					}
					if ($task->performer_id != null) {
						team_member::refresh_deliver_amount($task->user_id, $task->performer_id);
					}
				}
				*/
			}

			$this->finish(array("task_id" => $task->task_id, "mission_id" => $task->mission_id, "complete_flag" => $task->complete_flag), $err);
		}

		public function remove()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$task = task::getModel($params->task_id);
			if ($task != null) {
				if ($task->performer_id != null) {
					google_batch::remove_event($task->task_id);
				}

				$err = $task->remove();
				$this->checkError($err);

				$db = db::getDB();
				$err = $db->execute("UPDATE t_proclink SET del_flag=1 WHERE (from_task_id=" . _sql($task_id) . " OR to_task_id=" . _sql($task_id) . ") AND del_flag=0");
				$this->checkError($err);

				$this->finish(array("task_id" => $task->task_id, "mission_id" => $task->mission_id), $err);
			}
			else
				$this->checkError(ERR_NOTFOUND_TASK);
		}

		public function complete()
		{
			$param_names = array("task_ids");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$err = ERR_OK;

			$task_ids = preg_split("/,/", $params->task_ids);
			foreach($task_ids as $task_id) {
				$task = task::getModel($task_id);
				if ($task != null) {
					$task->complete_flag = 1;
					$task->complete_time = "##NOW()";
					$task->progress = 100;
					$err = $task->save();
					$this->checkError($err);

					if ($task->performer_id != null) {
						google_batch::add_event($task->task_id);
					}
				}
			}

			$this->finish(null, $err);
		}

		public function search()
		{
			$param_names = array("home_id", "mission_id", "search_string", "sort_field", "sort_order", "search_mode", "priority_only", "search_date", "search_this_week", "limit", "offset");
			$this->setApiParams($param_names);
			$params = $this->api_params;
			
			$user_id = _user_id();

			$tasks = array();
			$task = new task;

			$mission_ids = array();
			if ($params->mission_id != null) {
				array_push($mission_ids, $params->mission_id);
			}
			else if ($params->home_id != null) {
				$sql = "SELECT a.mission_id FROM t_mission a 
					LEFT JOIN t_mission_member mm ON a.mission_id=mm.mission_id 
					WHERE a.home_id=" . _sql($params->home_id) . " 
						AND mm.user_id=" . _sql($user_id) . " AND a.del_flag=0 AND a.complete_flag=0 ";

				$mission = new mission;
				$err = $mission->query($sql);
				while ($err == ERR_OK)
				{
					array_push($mission_ids, $mission->mission_id);

					$err = $mission->fetch();
				}
			}
			$mission_ids = join(",", $mission_ids);

			$sql = "SELECT DISTINCT t.*, m.mission_name, tu.sort, tu.priority, u.user_name, p.user_name performer_name FROM t_task t 
				LEFT JOIN t_mission m ON t.mission_id=m.mission_id 
				LEFT JOIN m_user u ON t.user_id=u.user_id 
				LEFT JOIN t_task_user tu ON t.task_id=tu.task_id AND tu.user_id=" . _sql(_user_id()) . " 
				LEFT JOIN m_user p ON t.performer_id=p.user_id ";

			$sql .= "WHERE t.del_flag=0";

			if ($mission_ids != '') {
				$sql .= " AND t.mission_id IN (" . $mission_ids . ")";
			}

			/*
			if ($params->mission_id == null) { // 自分のタスクだけ 
				$sql .= " AND t.performer_id=" . _sql($user_id);
			}
			*/

			if ($params->search_string != null)
				$sql .= " AND t.task_name LIKE " . _sql("%" . $params->search_string . "%");

			switch ($params->search_mode)
			{
				case 1: // all
					break;
				case 2: // completed
					$sql .= " AND t.complete_flag=1 ";
					break;
				default: // uncompleted and completed today
					$sql .= " AND (t.complete_flag=0 OR t.complete_flag=1 AND t.complete_time > CURDATE()) ";
			}

			if ($params->priority_only != null)
			{
				$sql .= " AND tu.priority=1";
			}

			if ($params->search_date != null)
			{
				$sql .= " AND (t.plan_start_date IS NOT NULL AND t.plan_start_date <= " . _sql($params->search_date) . " AND t.plan_end_date IS NULL OR
					t.plan_start_date IS NULL AND t.plan_end_date IS NOT NULL AND " . _sql($params->search_date) . " <= t.plan_end_date OR
					t.plan_start_date IS NOT NULL AND t.plan_start_date <= " . _sql($params->search_date) . " AND t.plan_end_date IS NOT NULL AND " . _sql($params->search_date) . " <= t.plan_end_date)";
			}

			if ($params->search_this_week == true)
			{
				$first = _first_weekday(_date());
				$last = _last_weekday(_date());
				$sql .= " AND (t.plan_start_date IS NOT NULL AND t.plan_start_date <= " . _sql($last) . " AND t.plan_end_date IS NULL OR
					t.plan_start_date IS NULL AND t.plan_end_date IS NOT NULL AND " . _sql($first) . " <= t.plan_end_date OR
					t.plan_start_date IS NOT NULL AND t.plan_start_date <= " . _sql($last) . " AND t.plan_end_date IS NOT NULL AND " . _sql($first) . " <= t.plan_end_date)";
			}

			if ($params->sort_field != null)
				$order = $params->sort_field . " " . $params->sort_order;
			else 
				$order = "tu.sort ASC, t.task_id DESC";

			$err = $task->query($sql,
				array("order" => $order,
					"limit" => $params->limit,
					"offset" => $params->offset));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				$task->update_priority($user_id);

				$task->creator_avartar = _avartar_full_url($task->user_id);
				$task->avartar = _avartar_full_url($task->performer_id);
				array_push($tasks, $task->props);

				$err = $task->fetch();
			}

			$this->finish(array("tasks" => $tasks), ERR_OK);
		}

		public function detail()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;
			
			$user_id = _user_id();

			$task = new task;
			
			$sql = "SELECT t.*, m.mission_name, u.user_name, p.user_name performer_name FROM t_task t 
				LEFT JOIN t_mission m ON t.mission_id=m.mission_id 
				LEFT JOIN m_user u ON t.user_id=u.user_id 
				LEFT JOIN m_user p ON t.performer_id=p.user_id 
				WHERE t.task_id=" . _sql($params->task_id) . " AND t.del_flag=0";

			$err = $task->query($sql);
			if ($err != ERR_NODATA)
				$this->checkError($err);

			if ($err == ERR_OK)
			{
				$task->creator_avartar = _avartar_full_url($task->user_id);
				$task->avartar = _avartar_full_url($task->performer_id);
				$this->finish(array("task" => $task->props), ERR_OK);
			}
			else
				$this->checkError($err);
		}

		public function update_sorts()
		{
			$param_names = array("sorts");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$sorts = $params->sorts;

			$user_id = _user_id();
			$sort = 0;

			foreach($sorts as $task_id)
			{
				$task_user = new task_user;
				$err = $task_user->select("task_id=" . _sql($task_id) . " AND user_id=" . _sql($user_id));
				if ($err != ERR_NODATA)
					$this->checkError($err);

				$task_user->task_id = $task_id;
				$task_user->user_id = $user_id;
				$task_user->sort = $sort;
				$err = $task_user->save();

				$sort ++;
			}

			$this->finish(null, ERR_OK);
		}

		public function get_skills()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$skills = array();
			$skill = new task_skill;

			$err = $skill->select("task_id=" . _sql($params->task_id),
				array("order" => "skill_name ASC"));

			while ($err == ERR_OK)
			{
				array_push($skills, $skill->skill_name);

				$err = $skill->fetch();
			}

			$this->finish(array("skills" => $skills), ERR_OK);
		}

		public function set_skills()
		{
			$param_names = array("task_id", "skills");
			$this->setApiParams($param_names);
			$this->checkRequired(array("task_id"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			task::set_skills($params->task_id, $params->skills);

			$this->finish(null, ERR_OK);
		}

		public function all_skills()
		{
			$this->setApiParams(array("home_id"));
			$params = $this->api_params;

			$skills = array();
			$skill = new skill;

			$where = "home_id IS NULL";
			if (!_is_empty($params->home_id))
				$where .= " OR home_id=" . _sql($params->home_id);

			$err = $skill->select($where,
				array("order" => "skill_name ASC"));

			while ($err == ERR_OK)
			{
				array_push($skills, trim($skill->skill_name));

				$err = $skill->fetch();
			}

			$this->finish(array("skills" => $skills), ERR_OK);
		}

		public function add_comment()
		{
			$param_names = array("task_id", "comment_type", "content");
			$this->setApiParams($param_names);
			$this->checkRequired(array("task_id", "comment_type"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();

			$task = task::getModel($params->task_id);
			if ($task == null)
				$this->checkError(ERR_NOTFOUND_TASK);

			$task_comment = new task_comment;
			$task_comment->load($params);
			$task_comment->user_id = $my_id;

			$err = $task_comment->save();

			if ($err == ERR_OK) {
				$task_comment = task_comment::getModel($task_comment->task_comment_id);
				$task_comment->avartar = _avartar_full_url($task_comment->user_id);
			}

			$this->finish(array("comment" => $task_comment->props), $err);
		}

		public function remove_comment()
		{
			$param_names = array("task_comment_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$task_comment = task_comment::getModel($params->task_comment_id);
			if ($task_comment == null)
				$this->finish(null, ERR_OK);

			if ($task_comment->comment_type == 2)
				@unlink(dirname(DATA_PATH . $task_comment->attach));

			$err = $task_comment->remove();

			$this->finish(null, $err);
		}

		public function get_comments()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$comments = array();
			$comment = new task_comment;

			$err = $comment->query("SELECT c.task_comment_id, c.task_id, c.comment_type, c.content, c.attach, c.file_size, c.user_id, u.user_name, c.create_time FROM t_task_comment c LEFT JOIN m_user u ON c.user_id=u.user_id WHERE c.del_flag=0 AND c.task_id=" . _sql($params->task_id),
				array("order" => "c.create_time DESC"));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				$comment->avartar = _avartar_full_url($comment->user_id);
				$comment->get_url();
				
				array_push($comments, $comment->props);

				$err = $comment->fetch();
			}

			$this->finish(array("comments" => $comments), ERR_OK);
		}

		public function get_candidates()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();
			$my_name = _user_name();

			$users = array();

			$task = task::getModel($params->task_id);
			if ($task == null)
				$this->checkError(ERR_NOTFOUND_TASK);

			$member = new model;
			$err = $member->query("SELECT mm.user_id, u.user_name, u.email, us.skill_name
				FROM t_mission_member mm
				INNER JOIN m_user u ON mm.user_id=u.user_id 
				LEFT JOIN t_user_skill us ON u.user_id=us.user_id
				WHERE mm.del_flag=0 AND mm.mission_id=" . _sql($task->mission_id) ."
				ORDER BY mm.create_time ASC, mm.user_id ASC");

			$user_id = -1;
			$old_user = null;
			while ($err == ERR_OK)
			{
				if ($member->user_id != $user_id)
				{
					if ($old_user != null)
						array_push($users, $old_user);

					$old_user = array("user_id" => $member->user_id, 
							"user_name" => $member->user_name,
							"email" => $member->email,
							"avartar" => _avartar_full_url($member->user_id),
							"skills" => $member->skill_name
						);
					$user_id = $member->user_id;
				}
				else {
					$old_user["skills"] .= "," . $member->skill_name;
				}

				$err = $member->fetch();
			}

			if ($old_user != null)
				array_push($users, $old_user);

			$task_skills = array();
			$task_skill = new task_skill;

			$err = $task_skill->select("task_id=" . _sql($params->task_id));
			while ($err == ERR_OK)
			{
				array_push($task_skills, $task_skill->skill_name);
				$err = $task_skill->fetch();
			}

			foreach ($users as &$user) {
				if ($user->user_id != $my_id) {
					$user["matched_skills"] = $this->matched_skills($task_skills, $user["skills"]);
					$user["ongoing_tasks"] = $this->ongoing_tasks($user["user_id"]);
				}
				else {
					$user["matched_skills"] = 0;
					$user["ongoing_tasks"] = 0;
				}
			}

			usort($users, "lsp_compare");

			array_push($users, array("user_id" => null,
				"type" => 1,
				"user_name" => "未定",
				"avartar" => _avartar_full_url(null)
			));

			/*
			array_push($users, array("user_id" => $my_id,
				"type" => 1,
				"user_name" => _user_name(),
				"avartar" => _avartar_full_url($my_id)
			));
			*/

			$this->finish(array("users" => $users), ERR_OK);
		}

		private function matched_skills($task_skills, $user_skills)
		{
			$matched = 0;
			$user_skills = preg_split("/,/", $user_skills);
			foreach ($task_skills as $tskill)
			{
				foreach ($user_skills as $uskill)
				{
					if ($tskill == $uskill)
						$matched ++;
				}
			}

			return $matched;
		}

		private function ongoing_tasks($user_id) {
			$db = db::getDB();
			return $db->scalar("SELECT COUNT(task_id) FROM t_task WHERE user_id!=" . _sql($user_id) . " AND performer_id=" . _sql($user_id) . " AND complete_flag=0");
		}

		public function add_proclink() 
		{
			$param_names = array("mission_id", "from_task_id", "to_task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			if ($params->from_task_id == $params->to_task_id)
				$this->checkError(ERR_SAME_FROMTO);

			// start transaction
			$this->start();

			$from_task = task::getModel($params->from_task_id);
			if ($from_task == null)
				$this->checkError(ERR_NODATA);
		
			$to_task = task::getModel($params->to_task_id);
			if ($to_task == null)
				$this->checkError(ERR_NODATA);

			$proclink = new proclink;

			$err = $proclink->select("from_task_id=" . _sql($params->from_task_id) . " AND to_task_id=" . _sql($params->to_task_id) . " OR 
				to_task_id=" . _sql($params->from_task_id) . " AND from_task_id=" . _sql($params->to_task_id));

			if ($err == ERR_OK)
				$this->checkError(ERR_ALREADY_LINKED);

			$proclink->load($params);
			$err = $proclink->save();

			if ($err == ERR_OK)
			{
				$from_task->processed = 1;
				$this->checkError($err = $from_task->save());

				$to_task->processed = 1;
				$this->checkError($err = $to_task->save());
			}

			$this->finish(null, ERR_OK);
		}

		public function get_proclinks()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$links = array();
			$proclink = new proclink;

			$err = $proclink->select("mission_id=" . _sql($params->mission_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				array_push($links, array( "from_task_id" => $proclink->from_task_id, "to_task_id" => $proclink->to_task_id, "critical" => $proclink->critical));

				$err = $proclink->fetch();
			}

			$this->finish(array("links" => $links), ERR_OK);
		}

		public function remove_proclink() 
		{
			$param_names = array("from_task_id", "to_task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$from_task = task::getModel($params->from_task_id);
			if ($from_task == null)
				$this->checkError(ERR_NODATA);
		
			$to_task = task::getModel($params->to_task_id);
			if ($to_task == null)
				$this->checkError(ERR_NODATA);

			$proclink = new proclink;

			$err = $proclink->select("from_task_id=" . _sql($params->from_task_id) . " AND to_task_id=" . _sql($params->to_task_id));

			if ($err != ERR_OK)
				$this->checkError(ERR_NODATA);

			$err = $proclink->remove();

			if ($err == ERR_OK) {
				$err = $proclink->select("from_task_id=" . _sql($params->from_task_id) . " OR to_task_id=" . _sql($params->from_task_id));
				if ($err == ERR_NODATA)
				{
					$from_task->processed = 0;
					$this->checkError($err = $from_task->save());
				}

				$err = $proclink->select("from_task_id=" . _sql($params->to_task_id) . " OR to_task_id=" . _sql($params->to_task_id));
				if ($err == ERR_NODATA)
				{
					$to_task->processed = 0;
					$this->checkError($err = $to_task->save());
				}
			}

			$this->finish(array("from_processed" => $from_task->processed, "to_processed" => $to_task->processed), ERR_OK);
		}

		/*
		public function refresh_deliver_amount()
		{
			$param_names = array("client_id", "performer_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$err = team_member::refresh_deliver_amount($params->client_id, $params->performer_id);

			$this->finish(null, $err);
		}
		*/

		public function print_task()
		{
			$param_names = array("task_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;			

			$task = new task;
			
			$sql = "SELECT t.*, m.mission_name, u.user_name, p.user_name performer_name FROM t_task t 
				LEFT JOIN t_mission m ON t.mission_id=m.mission_id 
				LEFT JOIN m_user u ON t.user_id=u.user_id 
				LEFT JOIN m_user p ON t.performer_id=p.user_id 
				WHERE t.task_id=" . _sql($params->task_id) . " AND t.del_flag=0";

			$err = $task->query($sql);
			if ($err == ERR_OK)
			{
				$task_skill = new task_skill;
				$skill_name = "";
				$err = $task_skill->select("task_id=" . _sql($params->task_id));
				while ($err == ERR_OK) {
					if ($skill_name == "")
						$skill_name = $task_skill->skill_name;
					else
						$skill_name .= "," . $task_skill->skill_name;
					$err = $task_skill->fetch();
				}
				$task->skill_name = $skill_name;
				$this->mTask = $task;
				?>
				<!doctype html>
				<html>
				    <head>
				        <meta charset="utf-8">
				        <meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'>
				        <title>タスクの印刷</title>
				        <meta name="description" content="">
						<style type="text/css">
							.task-detail {
								width: 100%;
							}

							@media print {
								.toolbar {
									display: none;
								}
							}
						</style>
				    </head>

					<body data-ng-app="print_task">		
						<h1><?php $this->mTask->detail("task_name"); ?></h1>
						<table class="task-detail" style="border-collapse: collapse;" border=1 cellpadding=3px>
							<tr>
								<th>チャットルーム名</th>
								<td colspan="3"><?php $this->mTask->detail("mission_name"); ?></td>
							</tr>
							<tr>
								<th>スキル</th>
								<td colspan="3"><?php $this->mTask->detail("skill_name"); ?></td>
							</tr>
							<tr>
								<th>担当者</th>
								<td colspan="3"><?php $this->mTask->detail("performer_name"); ?></td>
							</tr>
							<tr>
								<th>レベル</th>
								<td colspan="3"><?php for($l = 0; $l < $this->mTask->level; $l++) { p("★"); } ?></td>
							</tr>
							<tr>
								<th>開始予定日</th>
								<td><?php $this->mTask->date("plan_start_date"); ?></td>
								<th>完了予定日</th>
								<td><?php $this->mTask->date("plan_end_date"); ?></td>
							</tr>
							<tr>
								<th width="20%">工数</th>
								<td width="30%"><?php $this->mTask->number("plan_hours"); ?>人日</td>
								<th width="20%">予算</th>
								<td width="30%"><?php $this->mTask->currency("plan_budget"); ?>円</td>
							</tr>
							<tr>
								<th>作成者</th>
								<td colspan="3"><?php $this->mTask->detail("user_name"); ?></td>
							</tr>
							<tr>
								<th>作成日時</th>
								<td colspan="3"><?php $this->mTask->datetime("create_time"); ?></td>
							</tr>
						</table>
						<div class="toolbar">
							<a href="javascript:window.print()">印刷</a> <a href="javascript:window.history.back()">一覧へ</a>
						</div>
					</body>
				</html>
				<?php				
			}
			exit;
		}

		public function get_remainings()
		{
			$param_names = array();
			$this->setApiParams($param_names);

			$priority_tasks = task_user::getPriorityTasks(_user_id());
			$inbox_tasks = task::getInboxTasks(_user_id());

			$this->finish(array("priority_tasks" => $priority_tasks, "inbox_tasks" => $inbox_tasks), ERR_OK);
		}

		public function request_entrance()
		{
			$param_names = array("title", "content");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$params->content .= MAIL_FOOTER;
			_send_mail(ENTRANCE_EMAIL, ENTRANCE_EMAIL, $params->title, $params->content);

			$this->finish(array(), ERR_OK);
		}

		public function help_entrance()
		{
			$param_names = array("task_id", "title", "content");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$task = task::getModel($params->task_id);
			if ($task == null) {
				$this->finish(array(), ERR_NOTFOUND_TASK);
			}

			$sended = array();

			$params->content .= MAIL_FOOTER;

			$sql = "SELECT u.user_id, u.email, u.user_name FROM t_mission_member mm 
			INNER JOIN m_user u ON mm.user_id=u.user_id AND u.del_flag = 0
			WHERE mm.mission_id = " . _sql($task->mission_id);

			$mission_member = new mission_member;
			$err = $mission_member->query($sql);
			while ($err == ERR_OK) {
				if ($mission_member->user_id != _user_id()) {
					if ($task->performer_id == $mission_member->user_id) {// || $task->performer_id == null) {
						// 確認依頼メールは担当者へ、担当者が未定の場合は、送信しない
						_send_mail($mission_member->email, $mission_member->user_name, $params->title, $params->content);
						array_push($sended, $mission_member->user_id);
					}
				}
				$err = $mission_member->fetch();
			}

			$this->finish(array("sended" => $sended), ERR_OK);
		}

		public function upload_attach()
		{
			$param_names = array("task_id");
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

			$task = task::getModel($params->task_id);
			if ($task == null)
				$this->checkError(ERR_NOTFOUND_TASK);	

			$err = $task->upload_attach('file');

			if ($err == ERR_OK) {
				$this->finish(array("task_comment_id" => $task->new_attach->task_comment_id), ERR_OK);
			}
			else {
				$this->checkError($err);
			}
		}
	}

	function lsp_compare($u1, $u2)
	{
		if ($u1["matched_skills"] == $u2["matched_skills"])
		{
			if ($u1["level"] == $u2["level"])
			{
				if ($u1["ongoing_tasks"] == $u2["ongoing_tasks"])
				{
					return 0;
				}		
				else 
					return $u1["ongoing_tasks"] < $u2["ongoing_tasks"] ? -1 : 1;	
			}
			else 
				return $u1["level"] < $u2["level"] ? 1 : -1;
		}
		else {
			return $u1["matched_skills"] < $u2["matched_skills"] ? 1 : -1;
		}
	}
?>