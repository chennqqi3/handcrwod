<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class proclink extends model 
	{
		public function __construct()
		{
			parent::__construct("t_proclink",
				"proclink_id",
				array(
					"mission_id",
					"from_task_id",
					"to_task_id",
					"critical"),
				array("auto_inc" => true));
		}
	};
?>