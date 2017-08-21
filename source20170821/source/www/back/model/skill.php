<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2015/01/05
	---------------------------------------------------*/

	class skill extends model 
	{
		public function __construct()
		{
			parent::__construct("m_skill",
				"skill_id",
				array(
					"skill_name",
					"home_id"),
				array("auto_inc" => true));
		}

		public static function getModel($pkvals, $ignore_del_flag=false)
		{
			$model = new static;
			$err = $model->get($pkvals, $ignore_del_flag);
			if ($err == ERR_OK)
				return $model;

			if (is_string($pkvals)) {
				$err = $model->select("skill_name = " . _sql($pkvals));
				if ($err == ERR_OK)
					return $model;
			}

			return null;
		}

		static public function is_exist($skill_name, $skill_id=null)
		{
			$skill = new skill;
			$where = "skill_name=" . _sql($skill_name);
			if ($skill_id != null)
			{
				$where .= " AND skill_id!=" . _sql($skill_id);
			}
			$err = $skill->select($where);
			return $err == ERR_OK;
		}

		static public function add_skill($home_id, $skill_name)
		{
			$skill = new skill;
			$where = "skill_name=" . _sql($skill_name) . " AND (home_id IS NULL";
			if ($home_id != null)
				$where .= " OR home_id=" . _sql($home_id);
			$where .= ")";
			$err = $skill->select($where);
			if ($err == ERR_OK)
				return true;
			else {
				$skill->skill_name = $skill_name;
				$skill->home_id = $home_id;
				$err = $skill->save();
				return $err == ERR_OK;
			}
		}		
	};
?>