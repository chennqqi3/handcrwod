<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2016/08/01
	---------------------------------------------------*/

	class emoticon extends model 
	{
		public function __construct()
		{
			parent::__construct("t_emoticon",
				"emoticon_id",
				array(
					"home_id",
					"title",
					"alt",
					"image"),
				array("auto_inc" => true));
		}

		public static function all($home_id)
		{
			$emoticons = array();
			$emoticon = new model;

			$err = $emoticon->query("SELECT emoticon_id, title, alt, image, home_id
				FROM t_emoticon WHERE del_flag=0 AND (home_id IS NULL OR home_id=" . _sql($home_id) . ")");
			while ($err == ERR_OK) {
				$emoticon->image = EMOTICON_URL . $emoticon->image;
				array_push($emoticons, $emoticon->props);
				$err = $emoticon->fetch();
			}

			return $emoticons;
		}

		public static function is_exist_by_alt($home_id, $alt, $emoticon_id=null)
		{
			$emoticon = new static;
			$where = "alt=" . _sql($alt);
			$where .= " AND del_flag=0 ";
			$where .= " AND (home_id IS NULL OR home_id=" . _sql($home_id) . ")";
			if ($emoticon_id != null)
			{
				$where .= " AND emoticon_id!=" . _sql($emoticon_id);
			}
			$err = $emoticon->select($where);
			return $err == ERR_OK;
		}

		public static function upload($field)
		{
			$file_name = _get_uploaded_filename($field);
			$ext = _get_uploaded_ext($field);

			if ($ext != null) {
				$path = "tmp/" . _newId() . "." . $ext;
				$full_path = SITE_ROOT . $path;

				if (_upload($field, $full_path) == null)
					return null;

				if ($ext == 'jpg' || $ext == 'png') {
					_resize_userphoto($full_path, $ext, 22, 20);
				}

				return $path;
			}

			return null;
		}
	};
?>