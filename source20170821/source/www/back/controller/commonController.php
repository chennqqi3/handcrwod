<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class CommonController extends controller {
		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype, $priv_group = UTYPE_NONE, $priv = UTYPE_NONE)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function booth()
		{
			return "popup/common_booth";
		}

		public function booth_upload()
		{
			global $GLOBALS;
			if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
				$jpg = $GLOBALS["HTTP_RAW_POST_DATA"];
				$tmpfile = substr(md5(time()), 0, 10) . ".jpg";
				$tmppath = TMP_PATH . $tmpfile;
				file_put_contents($tmppath, $jpg);
				echo "tmp/" . $tmpfile;
			} else{
				echo "";
			}
			_erase_old(TMP_PATH);
			exit;
		}

		public function upload($upload_type = null)
		{
			$this->upload_type = $upload_type;
			return "popup/common_upload";
		}

		public function upload_ajax() {
			$this->start();

			$tmppath = _tmp_path();
			$tmpfile = _basename($tmppath);

			if (($filename = _upload("file", $tmppath)) == null) {
				$this->finish(null, ERR_FAIL_UPLOAD, 400);
			} 

			_erase_old(TMP_PATH);

			$this->finish(array("path" => "tmp/" . $tmpfile, "filename" => $filename), ERR_OK);
		}

		public function down_attach($param1, $param2 = null, $param3 = null)
		{
			if ($param1 == "tmp" && $param2 != null && $param3 != null) {
				$path = TMP_PATH . $param2;
				$filename = $param2;
				if ($param3 != null)
					$filename = $param3;
				$sz = filesize($path);
				$fp = fopen($path, "rb");
				if ($fp) {
					ob_end_clean();
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					header("Content-Length: " . $sz);
					fpassthru($fp);
				}
			}
			else if ($param1 != null && $param2 != null && $param3 == null) {
				$path = ATTACH_PATH . $param1;
				$filename = $param1;
				if ($param2 != null)
					$filename = $param2;
				$sz = filesize($path);
				$fp = fopen($path, "rb");
				if ($fp) {
					ob_end_clean();
					header('Content-Disposition: attachment; filename="' . $filename . '"');
					header("Content-Length: " . $sz);
					fpassthru($fp);
				}
			}
			
			exit;
		}

		public function upload_avartar_ajax()
		{
			$ext = _get_uploaded_ext("file");
			if ($ext != null) {
				$tmppath = _tmp_path("jpg");
				$tmpfile = _basename($tmppath);
				
				_upload("file", $tmppath); 

				_resize_userphoto($tmppath, $ext, 120, 120);

				_erase_old(TMP_PATH);

				$this->finish(array("tmp_path" => "tmp/" . $tmpfile), ERR_OK);
			}
			else {
				$this->checkError(ERR_INVALID_IMAGE);
			}
		}

		public function upload_image()
		{
			return "popup/common_upload_image";
		}

		public function upload_image_ajax()
		{
			$ext = _get_uploaded_ext("photo");
			if ($ext != null) {
				$tmppath = _tmp_path("jpg");
				$tmpfile = _basename($tmppath);
			
				_upload("photo", $tmppath); 

				_resize_photo($tmppath, $ext, 1000, 800);

				_erase_old(TMP_PATH);

				$this->finish(array("tmp_path" => "tmp/" . $tmpfile), ERR_OK);
			}
			else {
				$this->checkError(ERR_INVALID_IMAGE);
			}
		}

		public function now_ajax()
		{
			$this->finish(array("now" => time()), ERR_OK);
		}

		public function check_email_ajax($email, $user_id=null)
		{
			$ret = user::is_exist_by_email($email, $user_id);
			$this->finish(array("ret" => !$ret), ERR_OK);
		}

		public function select_subatccat($parent_id, $default=null, $path_mode=false)
		{
			$atccat = new atccat;
			if ($default != null && $default != "")
			{
				?><option value=""><?php p($default) ?></option><?php
			}	
			if ($parent_id == null)
				$where = "parent_id IS NULL";
			else {
				$where = "parent_id = " . _sql($parent_id);
			}
			$order = "sort ASC";
			
			$err = $atccat->select($where, array("order" => $order));
			while ($err == ERR_OK)
			{
				$key = $key . "";
				if ($path_mode) {
					?><option value="<?php p($atccat->atccat_path); ?>"><?php p($atccat->title) ?></option><?php
				}
				else {
					?><option value="<?php p($atccat->atccat_id); ?>"><?php p($atccat->title) ?></option><?php
				}
				$err = $atccat->fetch();
			}
			exit;
		}

		public function select_subfrmcat($parent_id, $default=null, $path_mode=false)
		{
			$frmcat = new frmcat;
			if ($default != null && $default != "")
			{
				?><option value=""><?php p($default) ?></option><?php
			}	
			if ($parent_id == null)
				$where = "parent_id IS NULL";
			else {
				$where = "parent_id = " . _sql($parent_id);
			}
			$order = "sort ASC";
			
			$err = $frmcat->select($where, array("order" => $order));
			while ($err == ERR_OK)
			{
				$key = $key . "";
				if ($path_mode) {
					?><option value="<?php p($frmcat->frmcat_path); ?>"><?php p($frmcat->title) ?></option><?php
				}
				else {
					?><option value="<?php p($frmcat->frmcat_id); ?>"><?php p($frmcat->title) ?></option><?php
				}
				$err = $frmcat->fetch();
			}
			exit;
		}
		
		public function alarmconfig_ajax($alarm_type, $detail_id, $alarm) {
			$user_id = _user_id();
			if ($user_id != null) {
				$alarmconfig = new alarmconfig;
				
				$err = $alarmconfig->select("alarm_type=" . _sql($alarm_type) . " AND detail_id=" . _sql($detail_id) . " AND user_id=" . _sql($user_id));
				
				$alarmconfig->user_id = $user_id;
				$alarmconfig->alarm_type = $alarm_type;
				$alarmconfig->detail_id = $detail_id;
				$alarmconfig->alarm = $alarm;
				
				$err = $alarmconfig->save();
			}
			else {
				$err = ERR_NODATA;
			}
			
			$this->finish(null, $err);
		}

		public function down_gravatar($user_id) {
			$gravatar_url = "http://www.gravatar.com/avatar/" . $user_id ."?d=identicon";
			$file_path = AVARTAR_PATH . $user_id. ".jpg";
			if(!file_exists($file_path)) {
				_mkdir(AVARTAR_PATH);
				$file = file_get_contents($gravatar_url);
				if($file !== false)
				{
					$ret = file_put_contents($file_path, $file);
					if($ret === false)
					{
						print "could not create file.";
						exit;						
					}
				}
				else
				{
					$file_path = SITE_ROOT . "resource/img/unknown.png";
				}

				if (file_exists($file_path))
				{
					$fp = fopen($file_path, 'rb');
					if($file !== false)
						header("Content-Type: image/jpeg");
					else
						header("Content-Type: image/png");
					header("Content-Length: " . filesize($file_path));
					fpassthru($fp);
				}
			}
			exit;
		}

		public function down_mattach($year_month, $org_file, $file_name)
		{
			$year = substr($year_month, 0, 4);
			$month = substr($year_month, 4);
			ob_clean();
			@ini_set('error_reporting', E_ALL & ~ E_NOTICE);
			@apache_setenv('no-gzip', 1);
			@ini_set('zlib.output_compression', 'Off');

			$file = DATA_PATH . ATTACH_URL . $year . "/" . $month . "/" . $org_file;

			if (!file_exists($file))
			{
				print "該当ファイルは存在しません。";
				exit;
			}
			/*
			$size = @filesize($file);
			$ext = _extname($file_name);
			$mime = _mime($ext);

			// Send the content type header
			header('Content-type: ' . $mime);

			if (ISIE || ISEDGE) {
				$file_name = mb_convert_encoding($file_name, "SJIS-win", "UTF-8");
			}

			if ($mime == "application/octet-stream")
				header('Content-Disposition: attachment; filename="' . $file_name . '"');
			else
				header('Content-Disposition: inline; filename="' . $file_name . '"');
			
			global $_SERVER;

			// Check if it's a HTTP range request
			if(isset($_SERVER['HTTP_RANGE'])){
			    // Parse the range header to get the byte offset
			    $ranges = array_map(
			        'intval', // Parse the parts into integer
			        explode(
			            '-', // The range separator
			            substr($_SERVER['HTTP_RANGE'], 6) // Skip the `bytes=` part of the header
			        )
			    );
			 
			    // If the last range param is empty, it means the EOF (End of File)
			    if(!$ranges[1]){
			        $ranges[1] = $size - 1;
			    }
			 
			    // Send the appropriate headers
			    header('HTTP/1.1 206 Partial Content');
			    header('Accept-Ranges: bytes');
			    header('Content-Length: ' . ($ranges[1] - $ranges[0])); // The size of the range
			 
			    // Send the ranges we offered
			    header(
			        sprintf(
			            'Content-Range: bytes %d-%d/%d', // The header format
			            $ranges[0], // The start range
			            $ranges[1], // The end range
			            $size // Total size of the file
			        )
			    );
			 
			    // It's time to output the file
			    $f = @fopen($file, 'rb'); // Open the file in binary mode
			    if ($f) {
				    $chunkSize = 8192; // The size of each chunk to output
				 
				    // Seek to the requested start range
				    @fseek($f, $ranges[0]);
				 
				    // Start outputting the data
				    while(true){
				        // Check if we have outputted all the data requested
				        if(@ftell($f) >= $ranges[1]){
				            break;
				        }
				 
				        // Output the data
				        echo @fread($f, $chunkSize);
				 
				        // Flush the buffer immediately
				        @ob_flush();
				        flush();
				    }
			    }
			}
			else {
			    // It's not a range request, output the file anyway
			    header('Content-Length: ' . $size);
			 
			    // Read the file
			    @readfile($file);
			 
			    // and flush the buffer
			    @ob_flush();
			    flush();
			}
			*/
			require_once 'include/HTTP/Download.php';

			$ext = _extname($file_name);
			$mime = _mime($ext);

			$params = array('file'=> $file, 'contenttype' => $mime);
			$down = new HTTP_Download($params);

			if (ISIE || ISEDGE)
				$file_name = mb_convert_encoding($file_name, "SJIS-win", "UTF-8");

			$cd = $mime == "application/octet-stream" ? HTTP_DOWNLOAD_ATTACHMENT : HTTP_DOWNLOAD_INLINE;
			$down->setContentDisposition($cd, $file_name);

			$down->send(true); 
			exit;
		}

		public function down_thumb_mattach($year, $month, $file) {
			preg_match('/(.+)_([0-9]+).jpg/', $file, $matches);
			if (count($matches) == 3)
			{
				$org = $matches[1];
				$max_width_height = $matches[2];

				$org_path = DATA_PATH . ATTACH_URL . $year . "/" . $month . "/" . $org;
				$thmb_dir = DATA_PATH . THUMB_URL . $year . "/" . $month . "/";
				$thmb_path = $thmb_dir . $file;

				if (!file_exists($thmb_dir))
					_mkdir($thmb_dir);

				if (!file_exists($thmb_path))
				{
					if (!copy($org_path, $thmb_path))
					{
						print "could not create file.";
						exit;
					}

					_resize_photo($thmb_path, "jpg", $max_width_height, $max_width_height);
				}

				if (file_exists($thmb_path))
				{
					require_once 'include/HTTP/Download.php';
					$params = array('file'=> $thmb_path, 'contenttype' => 'image/jpeg');
					$down = new HTTP_Download($params);
					$down->send(true);
				}
			}
			exit;
		}
	}
?>