<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class MissionsController extends controller {
		public function __construct(){
			parent::__construct();	

			$this->_navi_menu = "missions";
			$this->_subnavi_menu = "missions";
		}

		public function checkPriv($action, $utype)
		{
			switch($action) {
				default:
					parent::checkPriv($action, UTYPE_ADMIN);
					break;
			}
		}

		public function index($page = 0, $size = 10) {
			$this->_subnavi_menu = "missions";

			$missions = array();
			$mission = new mission;
			
			$this->where = "m.del_flag = 0 ";

			$this->loadsearch("missions_list");

			$from_where = "FROM t_mission m LEFT JOIN m_user c ON m.client_id=c.user_id WHERE " . $this->where;

			$this->counts = $mission->scalar("SELECT COUNT(*) " . $from_where);

			$this->pagebar = new pageHelper($this->counts, $page, $size);

			$err = $mission->query("SELECT m.*, c.user_name client_name " . $from_where,
				array("order" => $this->order,
					"limit" => $size,
					"offset" => $this->pagebar->page * $size));

			while ($err == ERR_OK)
			{
				$last_task = task::last_task($mission->mission_id);
				if ($last_task != null) {
					$mission->last_task_id = $last_task->task_id;
					$mission->last_task_name = $last_task->task_name;
				}

				$new_mission = clone $mission;

				array_push($missions, $new_mission);

				$err = $mission->fetch();
			}

			$this->missions = $missions;
		}

		private function loadsearch($session_id) {
			$this->search = new reqsession($session_id);

			if ($this->search->search_string != null) {
				$ss = _sql("%" . $this->search->search_string . "%");
				$this->where .= " AND (mission_name LIKE " . $ss . " OR a.user_name LIKE " . $ss . ")";
			}

			if ($this->search->sort_field != null)
				$this->order = $this->search->sort_field . " " . $this->search->sort_order;
			else 
				$this->order = "mission_id DESC";
		}

		public function edit($mission_id) {
			$user = new user;

			$this->mMission = $user;
			$this->mission_id = $mission_id;
		}

		public function detail($mission_id) {
			$mission = mission::getModel($mission_id);
			if ($mission == null)
				$this->showError(ERR_NODATA);

			$client = user::getModel($mission->client_id);
			if ($client != null) {
				$mission->client_name = $client->user_name;
			}

			$this->mMission = $mission;

			$tasks = array();
			$task = new task;
			$err = $task->query("SELECT t.*, c.user_name creator_name, p.user_name performer_name FROM t_task t LEFT JOIN m_user c ON t.user_id=c.user_id LEFT JOIN m_user p ON t.performer_id=p.user_id " . 
				"WHERE t.del_flag=0 AND t.mission_id=" . _sql($mission_id), array("order" => "task_id DESC"));

			while ($err == ERR_OK)
			{
				$new_task = clone $task;

				array_push($tasks, $new_task);

				$err = $task->fetch();
			}
			$this->tasks = $tasks;

			$members = array();
			$member = new mission_member;
			$err = $member->query("SELECT u.* FROM t_mission_member mm INNER JOIN m_user u ON mm.user_id=u.user_id " . 
				"WHERE mm.mission_id=" . _sql($mission_id) . " AND mm.del_flag=0", array("order" => "mm.mission_member_id DESC"));
			
			while ($err == ERR_OK)
			{
				$new_member = clone $member;

				array_push($members, $new_member);

				$err = $member->fetch();
			}
			$this->members = $members;
		}

		public function save_ajax($mission_id = null) {
			$this->start();

			if ($mission_id == null) {
				$mission = new mission;
				$mission->load($this);

				$mission->complete_flag = 0;

				$err = $mission->save();

				if ($err == ERR_OK) {
					$mission_member = new mission_member;
					$mission_member->mission_id = $mission->mission_id;
					$mission_member->user_id = $mission->client_id;
					$err = $mission_member->save();
				}
			}

			$this->finish(array("mission_id" => $mission->mission_id), $err);
		}

		public function delete_ajax() {
			$this->start();

			$count = func_num_args();

			for ($i = 0; $i < $count; $i ++) {
				$mission_id = func_get_arg($i);
				$mission = mission::getModel($mission_id);
				if ($mission != null) {
					$err = $mission->remove();
					$this->checkError($err);
					
					$mission->delete_back_image(0);
					$mission->delete_back_image(1);
				}
			}

			$this->finish(null, $err);
		}

		public function insert() {
			$mission = new mission;

			$this->mMission = $mission;

			return "missions_edit";
		}
	}
?>