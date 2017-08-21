<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2015/01/05
	---------------------------------------------------*/

	class SkillsController extends controller {
		public function __construct(){
			parent::__construct();	

			$this->_navi_menu = "master";
			$this->_subnavi_menu = "skills";
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
			$this->_subnavi_menu = "skills";
			$skills = array();
			$skill = new skill;
			
			$this->where = "1=1";

			$this->loadsearch("skills_lists");

			$this->counts = $skill->counts($this->where);

			$this->pagebar = new pageHelper($this->counts, $page, $size);

			$err = $skill->select($this->where,
				array("order" => $this->order,
					"limit" => $size,
					"offset" => $this->pagebar->page * $size));

			while ($err == ERR_OK)
			{
				$new_skill = clone $skill;

				array_push($skills, $new_skill);

				$err = $skill->fetch();
			}

			$this->skills = $skills;
			$this->mSkill = new skill;
		}

		private function loadsearch($session_id) {
			$this->search = new reqsession($session_id);

			if ($this->search->search_string != null) {
				$ss = _sql("%" . $this->search->search_string . "%");
				$this->where .= " AND skill_name LIKE " . $ss;
			}

			if ($this->search->sort_field != null)
				$this->order = $this->search->sort_field . " " . $this->search->sort_order;
			else 
				$this->order = "skill_name ASC";
		}

		public function save_ajax() {
			$this->start();

			if ($this->skill_id == null) {
				$skill = new skill;
				$skill->load($this);

				if (skill::is_exist($this->skill_name, $this->skill_id))
					$this->checkError(ERR_ALREADY_EXIST_SKILL);

				$this->checkError($err = $skill->save());
			}
			else {
				$skill = skill::getModel($this->skill_id);
				if ($skill == null)
					$this->checkError(ERR_NODATA);

				$org_skill_name = $skill->skill_name;
				if ($this->skill_name == $org_skill_name)
					$this->finish(array("skill_id" => $skill->skill_id), ERR_OK);

				if (skill::is_exist($this->skill_name, $this->skill_id))
					$this->checkError(ERR_ALREADY_EXIST_SKILL);

				$skill->load($this);
				$this->checkError($err = $skill->save());

				$db = db::getDB();

				$db->execute("UPDATE t_task_skill SET skill_name=" . _sql($skill->skill_name) . " WHERE skill_name=" . _sql($org_skill_name));
				$db->execute("UPDATE t_user_skill SET skill_name=" . _sql($skill->skill_name) . " WHERE skill_name=" . _sql($org_skill_name));
			}
								
			$this->finish(array("skill_id" => $skill->skill_id), $err);
		}

		public function delete_ajax() {
			$this->start();

			$db = db::getDB();

			$count = func_num_args();

			for ($i = 0; $i < $count; $i ++) {
				$skill_id = func_get_arg($i);
				$skill = skill::getModel($skill_id);

				if ($skill != null) {
					$err = $skill->remove();
					if ($err == ERR_OK) {
						$db->execute("DELETE FROM t_task_skill WHERE skill_name=" . _sql($skill->skill_name));
						$db->execute("DELETE FROM t_user_skill WHERE skill_name=" . _sql($skill->skill_name));
					}
				}
			}

			$this->finish(null, $err);
		}
	}
?>