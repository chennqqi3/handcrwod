<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/28
	---------------------------------------------------*/

	class TemplateController extends APIController {
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
			$param_names = array("mission_id", "template_name");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$task_id_map = array();

			// start transaction
			$this->start();

			$my_id = _user_id();
			$plan = _user_plan();

			$db = db::getDB();
  			$template_count = $db->scalar("SELECT COUNT(template_id) FROM t_template WHERE user_id=" . _sql($my_id) . " AND del_flag=0");
  			if ($plan->max_templates != -1 && $plan->max_templates <= $template_count)
  				$this->checkError(ERR_OVER_MAX_TEMPLATES, $plan->max_templates);

			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$template = new template;
			$template->template_name = $params->template_name;
			$template->user_id = $my_id;
			$template->summary = $mission->summary;

			$this->checkError($err = $template->save());

			$template->copy_back_image_from_mission($mission);

			// add tasks
			$task = new task;
			$err = $task->select("mission_id=" . _sql($params->mission_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$template_task = new template_task;
				$template_task->template_id = $template->template_id;
				$template_task->task_name = $task->task_name;
				$template_task->plan_budget = $task->plan_budget;
				$template_task->plan_hours = $task->plan_hours;
				$template_task->summary = $task->summary;
				$template_task->x = $task->x;
				$template_task->y = $task->y;
				$template_task->processed = $task->processed;

				$err = $template_task->save();
				$this->checkError($err);

				$task_id_map[$task->task_id] = $template_task->template_task_id;

				// add task_skill
				$task_skill = new task_skill;
				$err = $task_skill->select("task_id=" . _sql($task->task_id));
				if ($err != ERR_NODATA)
					$this->checkError($err);

				while ($err == ERR_OK)
				{
					$template_skill = new template_skill;
					$template_skill->template_id = $template->template_id;
					$template_skill->template_task_id = $template_task->template_task_id;
					$template_skill->skill_name = $task_skill->skill_name;

					$err = $template_skill->save();
					$this->checkError($err);

					$err = $task_skill->fetch();
				}

				$err = $task->fetch();
			}

			// add proclinks
			$proclink = new proclink;
			$err = $proclink->select("mission_id=" . _sql($params->mission_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$template_proclink = new template_proclink;
				$template_proclink->template_id = $template->template_id;
				if (isset($task_id_map[$proclink->from_task_id]))
					$template_proclink->from_task_id = $task_id_map[$proclink->from_task_id];
				else {
					$err = $proclink->fetch();
					continue;
				}
				if (isset($task_id_map[$proclink->to_task_id]))
					$template_proclink->to_task_id = $task_id_map[$proclink->to_task_id];
				else {
					$err = $proclink->fetch();
					continue;
				}

				$err = $template_proclink->save();
				$this->checkError($err);

				$err = $proclink->fetch();
			}

			// add attaches
			$mission_attach = new mission_attach;
			$err = $mission_attach->select("mission_id=" . _sql($params->mission_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$template_attach = new template_attach;
				$template_attach->template_id = $template->template_id;
				$template_attach->attach_name = "temp";
				$template_attach->file_size = $mission_attach->file_size;

				$err = $template_attach->save();
				$this->checkError($err);

				$err = $template_attach->copy_attach_from_mission_attach($mission_attach);
				$this->checkError($err);

				$err = $mission_attach->fetch();
			}

			$this->finish(array("template_id" => $template->template_id), ERR_OK);
		}

		public function edit()
		{
			$param_names = array("template_id", "template_name");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$template = template::getModel($params->template_id);
			if ($template == null)
				$this->checkError(ERR_NODATA);
			$template->template_name = $params->template_name;

			$err = $template->save();

			$this->finish(null, $err);
		}

		public function remove()
		{
			$param_names = array("template_ids");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$template_ids = preg_split("/,/", $params->template_ids);
			foreach($template_ids as $template_id) {
				$template = template::getModel($template_id);
				if ($template == null)
					continue;
				$err = $template->remove();
				$this->checkError($err);

				$template->delete_back_image(0);
				$template->delete_back_image(1);

				$template_task = new template_task;
				$err = $template_task->remove_where("template_id=" . _sql($template_id), true);
				$this->checkError($err);

				$template_proclink = new template_proclink;
				$err = $template_proclink->remove_where("template_id=" . _sql($template_id), true);
				$this->checkError($err);

				$template_attach = new template_attach;
				$err = $template_attach->select("template_id=" . _sql($template_id));
				if ($err != ERR_NODATA)
					$this->checkError($err);

				while ($err == ERR_OK) 
				{
					$template_attach->delete_attach();
					$template_attach->remove();

					$err = $template_attach->fetch();
				}
				$err = ERR_OK;
			}

			$this->finish(null, $err);
		}

		public function search()
		{
			$param_names = array("search_string");
			$this->setApiParams($param_names);
			$params = $this->api_params;
			
			$user_id = _user_id();

			$templates = array();
			$template = new template;

			$err = $template->select("user_id=" ._sql($user_id),
				array("order" => "template_id DESC"));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				array_push($templates, $template->props);

				$err = $template->fetch();
			}

			$this->finish(array("templates" => $templates), ERR_OK);
		}

		public function import()
		{
			$param_names = array("mission_id", "template_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$task_id_map = array();

			// start transaction
			$this->start();

			$my_id = _user_id();
			
			$mission = mission::getModel($params->mission_id);
			if ($mission == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$template = template::getModel($params->template_id);
			if ($template == null)
				$this->checkError(ERR_NODATA);

			// remove original tasks
			$task = new task;
			$err = $task->remove_where("mission_id=" . _sql($params->mission_id));
			$this->checkError($err);

			// remove original proclinks
			$proclink = new proclink;
			$err = $proclink->remove_where("mission_id=" . _sql($params->mission_id));
			$this->checkError($err);

			// save summary
			$mission->summary = $template->summary;
			$err = $mission->save();
			$this->checkError($err);

			// import back image
			$err = $mission->copy_back_image_from_template($template);
			$this->checkError($err);
			$mission->load_other_info();
			
			// add tasks
			$template_task = new template_task;
			$err = $template_task->select("template_id=" . _sql($params->template_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$task = new task;
				$task->mission_id = $params->mission_id;
				$task->user_id = $my_id;
				$task->performer_id = $my_id;
				$task->complete_flag = 0;
				$task->task_name = $template_task->task_name;
				$task->summary = $template_task->summary;
				$task->x = $template_task->x;
				$task->y = $template_task->y;
				$task->plan_budget = $template_task->plan_budget;
				$task->plan_hours = $template_task->plan_hours;
				$task->processed = $template_task->processed;

				$err = $task->save();
				$this->checkError($err);

				$task_id_map[$template_task->template_task_id] = $task->task_id;

				$err = $template_task->fetch();
			}

			// add task skills
			$template_skill = new template_skill;
			$err = $template_skill->select("template_id=" . _sql($params->template_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$task_skill = new task_skill;
				if (isset($task_id_map[$template_skill->template_task_id]))
					$task_skill->task_id = $task_id_map[$template_skill->template_task_id];
				else
					continue;
				$task_skill->skill_name = $template_skill->skill_name;

				$err = $task_skill->save();
				$this->checkError($err);

				$err = $template_skill->fetch();
			}

			// add proclinks
			$template_proclink = new template_proclink;
			$err = $template_proclink->select("template_id=" . _sql($params->template_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$proclink = new proclink;
				$proclink->mission_id = $params->mission_id;
				if (isset($task_id_map[$template_proclink->from_task_id]))
					$proclink->from_task_id = $task_id_map[$template_proclink->from_task_id];
				else
					continue;
				if (isset($task_id_map[$template_proclink->to_task_id]))
					$proclink->to_task_id = $task_id_map[$template_proclink->to_task_id];
				else
					continue;

				$err = $proclink->save();
				$this->checkError($err);

				$err = $template_proclink->fetch();
			}

			// add attaches
			$template_attach = new template_attach;
			$err = $template_attach->select("template_id=" . _sql($params->template_id));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK) 
			{
				$mission_attach = new mission_attach;
				$mission_attach->mission_id = $params->mission_id;
				$mission_attach->attach_name = "temp";
				$mission_attach->creator_id = _user_id();
				$mission_attach->file_size = $template_attach->file_size;

				$err = $mission_attach->save();
				$this->checkError($err);

				$err = $mission_attach->copy_attach_from_template($template_attach);
				$this->checkError($err);

				$err = $template_attach->fetch();
			}

			$this->finish(array(
				"summary" => $mission->summary,
				"job_back" => $mission->job_back, 
				"job_back_url" => $mission->job_back_url,
				"job_back_pos" => $mission->job_back_pos,
				"prc_back" => $mission->prc_back, 
				"prc_back_url" => $mission->prc_back_url,
				"prc_back_pos" => $mission->prc_back_pos), ERR_OK);
		}
	}
?>