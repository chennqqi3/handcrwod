<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class task_skill extends model 
	{
		public function __construct()
		{
			parent::__construct("t_task_skill",
				"task_skill_id",
				array(
					"task_id",
					"skill_name"),
				array("auto_inc" => true));
		}
	};
?>