<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class task_comment extends model 
	{
		public function __construct()
		{
			parent::__construct("t_task_comment",
				"task_comment_id",
				array(
					"task_id",
					"user_id",
					"comment_type",
					"content",
					"attach",
					"check_result",
					"file_size"),
				array("auto_inc" => true));
		}

		public function get_url($renew=false)
		{
			if ($this->comment_type == 2) {
				$this->attach_url = _full_url($this->attach, $renew);
				$this->file_name = _basename($this->attach);
			}
		}
	};
?>