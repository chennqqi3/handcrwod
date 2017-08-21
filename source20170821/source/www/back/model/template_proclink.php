<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class template_proclink extends model 
	{
		public function __construct()
		{
			parent::__construct("t_template_proclink",
				"template_proclink_id",
				array(
					"template_id",
					"from_task_id",
					"to_task_id"),
				array("auto_inc" => true));
		}
	};
?>