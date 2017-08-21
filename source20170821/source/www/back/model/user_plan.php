<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/28
	---------------------------------------------------*/

	class user_plan extends model 
	{
		public function __construct()
		{
			parent::__construct("t_user_plan",
				"user_plan_id",
				array(
					"user_id",
					"plan_type",
					"start_date",
					"end_date",
					"max_missions",
					"max_templates",
					"repeat_flag",
					"max_upload",
					"back_image_flag",
					"job_csv_flag",
					"contact_flag",
					"superchat_flag",
					"skill_report",
					"outsourcing_service",
					"visit_service"),
				array("auto_inc" => true));
		}
	};
?>