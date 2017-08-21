<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class mission_attach extends model 
	{
		public function __construct()
		{
			parent::__construct("t_mission_attach",
				"mission_attach_id",
				array(
					"mission_id",
					"attach_name",
					"creator_id",
					"file_size"),
				array("auto_inc" => true));
		}

		public function get_url($renew=false)
		{
			$this->attach_url = _full_url($this->attach_name, $renew);
			$this->file_name = _basename($this->attach_name);
		}

		public function copy_attach_from_template($template_attach)
		{
			if ($template_attach == null)
				return ERR_OK;

			$file_name = _basename($template_attach->attach_name);
			$path = dirname(DATA_PATH . $template_attach->attach_name);

			$this->attach_name = ATTACH_URL . date('Y/m/') . "a_" . $this->mission_attach_id . "/" . $file_name;
			_mkdir(dirname(dirname(DATA_PATH . $this->attach_name)));
			copy($path, dirname(DATA_PATH . $this->attach_name));
			$this->file_size = $template_attach->file_size;

			return $this->save();
		}

	};
?>