<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class user_skill extends model 
	{
		public function __construct()
		{
			parent::__construct("t_user_skill",
				"user_skill_id",
				array(
					"user_id",
					"skill_name"),
				array("auto_inc" => true));
		}
	};
?>