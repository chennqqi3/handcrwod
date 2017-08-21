<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class template_attach extends model 
	{
		public function __construct()
		{
			parent::__construct("t_template_attach",
				"template_attach_id",
				array(
					"template_id",
					"attach_name",
					"file_size"),
				array("auto_inc" => true));
		}

		public function copy_attach_from_mission_attach($mission_attach)
		{
			if ($mission_attach == null)
				return ERR_OK;

			$file_name = _basename($mission_attach->attach_name);
			$path = dirname(DATA_PATH . $mission_attach->attach_name);

			$this->attach_name = ATTACH_URL . date('Y/m/') . "t_a_" . $this->template_attach_id . "/" . $file_name;
			_mkdir(dirname(dirname(DATA_PATH . $this->attach_name)));
			copy($path, dirname(DATA_PATH . $this->attach_name));

			return $this->save();
		}

		public function delete_attach()
		{
			if ($this->attach_name != null)
			{
				@unlink(dirname(DATA_PATH . $this->attach_name));
			}
			
			return ERR_OK;
		}
	};
?>