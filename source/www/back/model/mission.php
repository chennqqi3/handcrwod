<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class mission extends model 
	{
		public function __construct()
		{
			parent::__construct("t_mission",
				"mission_id",
				array(
					"client_id",
					"home_id",
					"mission_name",
					"complete_flag",
					"complete_time",
					"summary",
					"job_back",
					"job_back_pos",
					"prc_back",
					"prc_back_pos",
					"repeat_type",
					"repeat_day",
					"private_flag",
					"last_date",
					"last_cmsg_id",
					"invite_key"),
				array("auto_inc" => true));
		}

		public static function get_mission_name($mission_id)
		{
			if ($mission_id == null)
				return "";

			$mission = mission::getModel($mission_id);
			if ($mission == null)
				return "";
			else
				return $mission->mission_name;
		}

		public static function from_mission_name($mission_name)
		{
			$my_id = _user_id();
			if ($my_id == null)
				return null;

			$mission = new mission;
			$err = $mission->select("client_id=" . _sql($my_id) . " AND mission_name=" . _sql($mission_name));
			if ($err == ERR_OK)
				return $mission;

			return null;
		}

		public static function add_mission($home_id, $mission_name, $private_flag, $user_id1=null, $user_id2=null)
		{
			$my_id = _user_id();
			if ($my_id == null)
				return null;

            $db = db::getDB();
            
			$mission = new mission;
			$mission->home_id = $home_id;
			$mission->mission_name = $mission_name;
			$mission->client_id = $my_id;
			$mission->complete_flag = 0;
			$mission->repeat_type = REPEAT_NONE;
			$mission->private_flag = $private_flag;

			$err = $mission->save();

			if ($err == ERR_OK) {
                // generate invite key
                $sql = "UPDATE t_mission SET invite_key=md5(concat(mission_id, create_time)) 
                    WHERE mission_id=" . _sql($mission->mission_id) . ";";

                $db->execute($sql);
			}

			if ($err == ERR_OK) {
				if ($private_flag == CHAT_PUBLIC || $private_flag == CHAT_BOT) {
					// 全メンバー用
					$home_member = new home_member;
					$err = $home_member->select("home_id=" . _sql($home_id) . " AND priv>" . _sql(HPRIV_GUEST));

					while ($err == ERR_OK)
					{
						$mission_member = new mission_member;
						$mission_member->mission_id = $mission->mission_id;
						$mission_member->user_id = $home_member->user_id;
						$mission_member->pinned = 1;
						$mission_member->push_flag = PUSH_TO;
						if ($my_id == $mission_member->user_id)
							$mission_member->priv = RPRIV_MANAGER;
						else
							$mission_member->priv = RPRIV_MEMBER;

						$err = $mission_member->save();	
						if ($err != ERR_OK)
							break;

						$err = $home_member->fetch();
					}

					if ($err == ERR_NODATA)
						$err = ERR_OK;
				}
				else if ($private_flag == CHAT_PRIVATE) {
					// 特定メンバー用
					$mission_member = new mission_member;
					$mission_member->mission_id = $mission->mission_id;
					$mission_member->user_id = $my_id;
					$mission_member->push_flag = PUSH_TO;
					$mission_member->priv = RPRIV_MANAGER;
					$err = $mission_member->save();	
				}
				else if ($private_flag == CHAT_MEMBER) {
					// 個別チャット
					$mission_member = new mission_member;
					$mission_member->mission_id = $mission->mission_id;
					$mission_member->user_id = $user_id1;
					$mission_member->opp_user_id = $user_id2;
					$mission_member->push_flag = PUSH_ALL;
					$err = $mission_member->save();	

					if ($err == ERR_OK) {
						$mission_member = new mission_member;
						$mission_member->mission_id = $mission->mission_id;
						$mission_member->user_id = $user_id2;
						$mission_member->opp_user_id = $user_id1;
						$mission_member->push_flag = PUSH_ALL;
						$err = $mission_member->save();	
					}
				}
			}

			return $err == ERR_OK ? $mission : null;
		}

		public function refresh_mission_member()
		{
			$my_id = _user_id();
			switch ($this->private_flag) {
				case CHAT_PUBLIC:
					$home_member = new home_member;
					$err = $home_member->select("home_id=" . _sql($this->home_id));

					while ($err == ERR_OK)
					{
						$priv = $home_member->priv;
						$mission_member = new mission_member;

						// すでに登録された場合はスキップ
						$err = $mission_member->select("mission_id=" . _sql($this->mission_id) . " 
							AND user_id=" . _sql($home_member->user_id));
						if ($priv == HPRIV_GUEST) {
							// ゲストは削除
							$mission_member->remove(true);
						}
						else {
							if ($err == ERR_NODATA)	{
								$mission_member->mission_id = $this->mission_id;
								$mission_member->user_id = $home_member->user_id;
								$mission_member->push_flag = PUSH_TO;
								$mission_member->priv = RPRIV_MEMBER;
								$err = $mission_member->save();
								if ($err != ERR_OK)
									return $err;
							}
						}

						$err = $home_member->fetch();
					}

					break;

				case CHAT_PRIVATE:
					$mission_member = new mission_member;

					// すでに登録された場合はスキップ
					$err = $mission_member->select("mission_id=" . _sql($this->mission_id));
					while ($err == ERR_OK)
					{
						if ($mission_member->user_id != $my_id) {
							$mission_member->remove(true);
						}

						$err = $mission_member->fetch();
					}
					break;
			}
			return ERR_OK;
		}

		public function load_other_info($renew=false)
		{
			$this->job_back_url = $this->job_back != null ? _full_url($this->job_back, $renew) : null;
			$this->prc_back_url = $this->prc_back != null ? _full_url($this->prc_back, $renew) : null;

			$this->repeat_month = 1;
			$this->repeat_monthday = 1;
			if ($this->repeat_type == 4) {
				$this->repeat_month = 1;
				$this->repeat_monthday = $this->repeat_day;
			}
			else if ($this->repeat_type == 5) {
				$md = @preg_split("/-/", $this->repeat_day);
				if (count($md) == 2) {
					$this->repeat_month = $md[0];
					$this->repeat_monthday = $md[1];
				}
			}

			if ($this->repeat_type == 3) {
				$this->repeat_weekday = $this->repeat_day;
			}
			else {
				$this->repeat_weekday = 0;	
			}

			// other
			if ($this->unreads > 0) {
				$this->last_text = cunread::last_text($this->mission_id);
			}

			$this->visible = ($this->pinned == 1 || $this->unreads > 0 || $this->pass_date !== null && $this->pass_date < 1);

			$last_date = max($this->last_date, $this->mm_last_date);
			if ($last_date == null)
				$last_date = null;

			$this->last_date = $last_date;

			$this->mission_name = ($this->private_flag != CHAT_MEMBER ? $this->mission_name : $this->opp_user_name);

		}

		public function upload_back_image($field, $type)
		{
			// $type 0: job 1: process
			if ($this->mission_id == null)
				return ERR_NOTFOUND_MISSION;

			$file_name = _get_uploaded_filename($field);
			$ext = _get_uploaded_ext($field);
			if ($ext != null) {
				$this->delete_back_image($type);

				$url = ATTACH_URL . date('Y/m/') . $this->mission_id . "_" . $type . "." . $ext;

				// real file location
				$path = DATA_PATH . $url;

				// upload
				if (_upload($field, $path) == null)
					return ERR_FAIL_UPLOAD;

				// resize image
				_resize_photo($path, $ext, 1920, 1200);

				// download url
				switch ($type) {
					case 0:
						$this->job_back = $url . "/" . $file_name;
						$this->job_back_pos = 0; // 画面に合わせて伸縮
						break;
					default: // case 1:
						$this->prc_back = $url . "/" . $file_name;
						$this->prc_back_pos = 3; // そのまま表示
						break;
				}

				$err = $this->save();

				// refresh download full url
				$this->load_other_info(true);

				return $err;
			}
			else 
				return ERR_INVALID_IMAGE;
		}

		public function delete_back_image($type)
		{
			if ($this->mission_id == null)
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

		public function copy_back_image_from_template($template)
		{
			if ($template == null)
				return ERR_OK;

			$this->job_back = null;
			if (!_is_empty($template->job_back)) {
				$job_back_file_name = _basename($template->job_back);
				$job_back_path = dirname(DATA_PATH . $template->job_back);
				if (file_exists($job_back_path)) {
					$job_back_ext = pathinfo($job_back_path, PATHINFO_EXTENSION); 

					$this->job_back = ATTACH_URL . date('Y/m/') . $this->mission_id . "_0." . $job_back_ext . "/" . $job_back_file_name;
					_mkdir(dirname(dirname(DATA_PATH . $this->job_back)));
					copy($job_back_path, dirname(DATA_PATH . $this->job_back));				
				}
			}

			$this->prc_back = null;
			if (!_is_empty($template->prc_back)) {
				$prc_back_file_name = _basename($template->prc_back);
				$prc_back_path = dirname(DATA_PATH . $template->prc_back);
				if (file_exists($prc_back_path)) {
					$prc_back_ext = pathinfo($prc_back_path, PATHINFO_EXTENSION); 

					$this->prc_back = ATTACH_URL . date('Y/m/') . $this->mission_id . "_1." . $prc_back_ext . "/" . $prc_back_file_name;
					_mkdir(dirname(dirname(DATA_PATH . $this->prc_back)));
					copy($prc_back_path, dirname(DATA_PATH . $this->prc_back));
				}
			}

			$this->job_back_pos = $template->job_back_pos;
			$this->prc_back_pos = $template->prc_back_pos;

			return $this->save();
		}

		public function upload_attach($field)
		{
			if ($this->mission_id == null)
				return ERR_NOTFOUND_MISSION;

			$file_name = _get_uploaded_filename($field);
			$file_size = _get_uploaded_filesize($field);

			$mission_attach = new mission_attach;

			$mission_attach->mission_id = $this->mission_id;
			$mission_attach->attach_name = $file_name;
			$mission_attach->creator_id = _user_id();
			$mission_attach->file_size = $file_size;

			$err = $mission_attach->save();
			if ($err != ERR_OK)
				return $err;

			$sub_dir = date('Y/m/');
			$save_file_name =  "a_" . $mission_attach->mission_attach_id;
			$url = ATTACH_URL . $sub_dir . $save_file_name;

			// real file location
			$path = DATA_PATH . $url;

			// upload
			if (_upload($field, $path) == null) {
				return ERR_FAIL_UPLOAD;
			}

			$ext = _extname($file_name);
			if ($ext == 'mov') {
				// convert to mp4
				$cmd = 'ffmpeg -i ' . $path . ' -acodec copy -vcodec copy ' . $path . '.mp4';
				exec($cmd);
				if (file_exists($path)) {
					@unlink($path);
					@rename($path . '.mp4', $path);
				}
				$file_name = preg_replace('/mov$/i', 'mp4', $file_name);
			}
			else if ($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "bmp" || $ext == "gif") {
				// thumnail
				$this->create_thumb(150, $path, $save_file_name, $sub_dir);
				$this->create_thumb(300, $path, $save_file_name, $sub_dir);
				$this->create_thumb(1000, $path, $save_file_name, $sub_dir);
			}

			// download url
			$mission_attach->attach_name = $url . "/" . $file_name;

			$err = $mission_attach->save();

			$this->new_attach = $mission_attach;

			return $err;
		}

		public function create_thumb($thmb_size, $path, $save_file_name, $sub_dir)
		{
			$thmb_file_name = $save_file_name . "_" . $thmb_size . ".jpg";
			$thmb_dir = DATA_PATH . THUMB_URL . $sub_dir;
			$thmb_path = $thmb_dir . $thmb_file_name;

			if (!file_exists($thmb_dir))
				_mkdir($thmb_dir);

			if (!file_exists($thmb_path))
			{
				if (copy($path, $thmb_path))
				{
					$ret = _resize_photo($thmb_path, "jpg", $thmb_size, $thmb_size);

					$this->image_width = $ret["org_width"];
					$this->image_height = $ret["org_height"];
				}
			}
		}

		public function delete_attach($mission_attach_id)
		{
			$mission_attach = mission_attach::getModel($mission_attach_id);
			if ($mission_attach == null)
				return ERR_OK;

			if ($mission_attach->attach_name != null)
			{
				@unlink(dirname(DATA_PATH . $mission_attach->attach_name));
			}

			$mission_attach->remove();
			
			return ERR_OK;
		}

		public static function set_last_date($mission_id, $cmsg_id=null)
		{
			$db = db::getDB();
            
            $sql = "UPDATE t_mission SET last_date=NOW()";
            if ($cmsg_id != null)
            	$sql .= ", last_cmsg_id=" . _sql($cmsg_id);
            $sql .= " WHERE mission_id=" . _sql($mission_id);

            $db->execute($sql);
		}

		public function is_member($user_id)
		{
			$mission_member = new mission_member;
			$err = $mission_member->select("mission_id=" . _sql($this->mission_id) . 
				" AND user_id=" . _sql($user_id));

			return $err == ERR_OK;
		}

		public function add_member($user_id, $accepted = 0)
		{
            if ($this->is_member($user_id))
                return ERR_OK;

			$home = home::getModel($this->home_id);
			if ($home == null)
				return ERR_NOTFOUND_HOME;

			$err = $home->add_member($user_id, $accepted);
			if ($err != ERR_OK)
				return $err;

			$mission_member = new mission_member;
			$mission_member->mission_id = $this->mission_id;
			$mission_member->user_id = $user_id;
			$mission_member->push_flag = PUSH_TO;
			$mission_member->priv = RPRIV_MEMBER;

			$err = $mission_member->save();

			return $err;
		}

        public function remove_member($user_id)
        {
        	$mission_member = new mission_member;
			$err = $mission_member->select("mission_id=" . _sql($this->mission_id) . 
				" AND user_id=" . _sql($user_id));
			if ($err == ERR_NODATA)
				return ERR_OK;

			$err = $mission_member->remove(true);

            return $err;
        }

        public static function get_bot($home_id)
        {
            $mission = new static;
            $sql = "SELECT m.*
                FROM t_mission m 
                WHERE m.home_id=" . _sql($home_id) . " AND
                    m.del_flag=0 AND m.private_flag=" . CHAT_BOT;

            $err = $mission->query($sql);
            if ($err == ERR_OK)
            	return $mission;
            else if ($err == ERR_NODATA) {
            	return static::add_bot($home_id);
            }
        }

        public static function add_bot($home_id)
        {
        	return static::add_mission($home_id, "アシスタント", CHAT_BOT);
        }
	};
?>