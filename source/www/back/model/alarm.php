<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2015/1/12
	---------------------------------------------------*/

	class alarm extends model 
	{
		public function __construct()
		{
			parent::__construct("t_alarm",
				"alarm_id",
				array(
					"user_id",
					"alarm_time",
					"alarm_flag"),
				array("auto_inc" => true));
		}
	};
?>