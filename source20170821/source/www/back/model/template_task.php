<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class template_task extends model 
	{
		public function __construct()
		{
			parent::__construct("t_template_task",
				"template_task_id",
				array(
					"template_id",
					"task_name",
					"plan_budget",
					"plan_hours",
					"summary",
					"x",
					"y",
					"processed"),
				array("auto_inc" => true));
		}
	};
?>