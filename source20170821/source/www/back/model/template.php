<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/28
	---------------------------------------------------*/

	class template extends model 
	{
		public function __construct()
		{
			parent::__construct("t_template",
				"template_id",
				array(
					"user_id",
					"template_name",
					"summary",
					"job_back",
					"job_back_pos",
					"prc_back",
					"prc_back_pos"),
				array("auto_inc" => true));
		}

		public function copy_back_image_from_mission($mission)
		{
			if ($mission == null)
				return ERR_OK;

			$this->job_back = null;
			if (!_is_empty($mission->job_back)) {
				$job_back_file_name = _basename($mission->job_back);
				$job_back_path = dirname(DATA_PATH . $mission->job_back);
				if (file_exists($job_back_path)) {
					$job_back_ext = pathinfo($job_back_path, PATHINFO_EXTENSION); 
					$this->job_back = ATTACH_URL . date('Y/m/') . "t_" . $this->template_id . "_0." . $job_back_ext . "/" . $job_back_file_name;
					_mkdir(dirname(dirname(DATA_PATH . $this->job_back)));
					copy($job_back_path, dirname(DATA_PATH . $this->job_back));
				}
			}

			$this->prc_back = null;
			if (!_is_empty($mission->prc_back)) {
				$prc_back_file_name = _basename($mission->prc_back);
				$prc_back_path = dirname(DATA_PATH . $mission->prc_back);
				if (file_exists($prc_back_path)) {
					$prc_back_ext = pathinfo($prc_back_path, PATHINFO_EXTENSION); 

					$this->prc_back = ATTACH_URL . date('Y/m/') . "t_" . $this->template_id . "_1." . $prc_back_ext . "/" . $prc_back_file_name;
					_mkdir(dirname(dirname(DATA_PATH . $this->prc_back)));
					copy($prc_back_path, dirname(DATA_PATH . $this->prc_back));
				}
			}

			$this->job_back_pos = $mission->job_back_pos;
			$this->prc_back_pos = $mission->prc_back_pos;

			return $this->save();
		}

		public function delete_back_image($type)
		{
			if ($this->template_id == null)
				return ERR_OK;

			switch($type) {
				case 0:
					if ($this->job_back == null)
						return ERR_OK;

					// real file location
					@unlink(dirname(DATA_PATH . $this->job_back));

					$this->job_back = null;
					break;

				default: // case 1:
					if ($this->prc_back == null)
						return ERR_OK;

					// real file location
					@unlink(dirname(DATA_PATH . $this->prc_back));

					$this->prc_back = null;
					break;
			}
			
			return ERR_OK;
		}
	};
?>