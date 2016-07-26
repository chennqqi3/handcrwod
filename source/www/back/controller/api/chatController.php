<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2015/09/15
	---------------------------------------------------*/

	class ChatController extends APIController {
		public $err_login;

		public function __construct(){
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_LOGINUSER);
		}

		public function add_room()
		{
			$param_names = array("mission_id", "croom_name");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$my_id = _user_id();
			$plan = _user_plan();

			$db = db::getDB();

			$sql = "SELECT a.mission_id FROM t_mission a LEFT JOIN t_mission_member mm ON a.mission_id=mm.mission_id 
				WHERE a.mission_id=" . _sql($params->mission_id) . " AND mm.user_id=" . _sql($my_id) . " AND a.del_flag=0 ";

			$mission_id = $db->scalar($sql);
			if ($mission_id == null)
				$this->checkError(ERR_NOTFOUND_MISSION);

			$croom = croom::add_croom($params->mission_id, $params->croom_name);

			if ($croom != null)
				$this->finish(array("mission_id" => $croom->mission_id), ERR_OK);
			else 
				$this->checkError(ERR_SQL);
		}

		public function edit_room()
		{
			$param_names = array("mission_id", "croom_name", "summary");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id"));
			$params = $this->api_params;

			// start transaction
			$this->start();

			$croom = croom::getModel($params->mission_id);
			$croom->load($params);

			$err = $croom->save();

			$this->finish(array("mission_id" => $croom->mission_id), $err);
		}

		public function remove()
		{
			$param_names = array("mission_ids");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			// start transaction
			$this->start();

			$err = ERR_OK;

			$mission_ids = preg_split("/,/", $params->mission_ids);
			foreach($mission_ids as $mission_id) {
				$croom = croom::getModel($mission_id);
				if ($croom != null) {
					$err = $croom->remove();
					$this->checkError($err);
				}
			}

			$this->finish(null, $err);
		}

		public function search()
		{
			$param_names = array("search_string", "mission_id", "complete_flag", "sort_field", "sort_order");
			$this->setApiParams($param_names);
			$params = $this->api_params;
			
			$user_id = _user_id();

			$crooms = array();
			$croom = new croom;

			$sql = "SELECT cr.mission_id, cr.croom_name, m.mission_id, m.mission_name FROM t_croom cr 
			    INNER JOIN t_mission m ON cr.mission_id=m.mission_id AND m.del_flag=0
				LEFT JOIN t_cuser cu ON cu.mission_id=cr.mission_id
				WHERE cu.user_id=" . _sql($user_id) . " AND 
					m.del_flag=0 AND cr.del_flag=0";

			if ($params->mission_id != null)
				$sql .= " AND m.mission_id=" . _sql($params->mission_id);

			if ($params->complete_flag == 1)
				$sql .= " AND m.complete_flag=1";
			else
				$sql .= " AND m.complete_flag=0";

			if (!_is_empty($params->search_string)) {
				$ss = _sql("%" . $params->search_string . "%");
				$sql .= " AND croom_name LIKE " . $ss;
			}

			if ($params->sort_field != null)
				$order = $params->sort_field . " " . $params->sort_order;
			else 
				$order = "m.mission_name ASC, cr.croom_name DESC";

			$err = $croom->query($sql,
				array("order" => $order,
					"limit" => $params->limit,
					"offset" => $params->offset));
			if ($err != ERR_NODATA)
				$this->checkError($err);

			while ($err == ERR_OK)
			{
				array_push($crooms, array("mission_id" => $croom->mission_id,
					"croom_name" => $croom->croom_name,
					"mission_id" => $croom->mission_id,
					"mission_name" => $croom->mission_name));

				$err = $croom->fetch();
			}

			$this->finish(array("crooms" => $crooms), ERR_OK);
		}

		public function users()
		{
			$param_names = array("mission_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$users = croom::get_users($params->mission_id);

			$this->finish(array("users" => $users), ERR_OK);
		}

		public function add_user()
		{
			$param_names = array("mission_id", "user_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$user = user::getModel($params->user_id);
			if ($user == null)
				$this->checkError(ERR_NOTFOUND_USER);

			$croom = croom::getModel($params->mission_id);
			if ($croom == null)
				$this->checkError(ERR_NOTFOUND_CROOM);

			$err = croom::add_user($params->mission_id, $params->user_id);

			$this->finish(array(), $err);
		}

		public function remove_user()
		{
			$param_names = array("mission_id", "user_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$user = user::getModel($params->user_id);
			if ($user == null)
				$this->checkError(ERR_NOTFOUND_USER);

			$croom = croom::getModel($params->mission_id);
			if ($croom == null)
				$this->checkError(ERR_NOTFOUND_CROOM);

			$err = croom::remove_user($params->mission_id, $params->user_id);

			$this->finish(array(), $err);
		}

		public function send_message()
		{
			$param_names = array("cmsg_id", "mission_id", "content", "to_id");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id", "content"));
			$params = $this->api_params;

			$my_id = _user_id();

			$err = cmsg::message($params->cmsg_id, $params->mission_id,
				$my_id, $params->to_id, $params->content);

			$this->finish(array(), $err);
		}

		public function star_message()
		{
			$param_names = array("cmsg_id", "star");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$my_id = _user_id();

			$err = cmsg::star($params->cmsg_id, $my_id, $params->star);

			$this->finish(array(), $err);
		}

		public function read_messages()
		{
			$param_names = array("mission_id", "cmsg_ids");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id"));
			$params = $this->api_params;

			$my_id = _user_id();

			$err = ERR_OK;
			if ($params->cmsg_ids == null) {
				$err = cunread::read_mission($params->mission_id, $my_id);
			}
			else {
				foreach ($params->cmsg_ids as $cmsg_id) {
					$err = cunread::read($cmsg_id, $my_id);
					if($err != ERR_OK)
						break;
				}
			}

			$err = cunread::refresh_unreads($params->mission_id);

			$this->finish(null, $err);
		}

		public function messages()
		{
			$param_names = array("home_id", "mission_id", "prev_id", "next_id", "star", "limit");
			$this->setApiParams($param_names);
			$this->checkRequired(array("home_id"));
			$params = $this->api_params;

			if ($params->next_id < 0)
				$cmsgs = array();
			else {
				$cmsgs = cmsg::messages($params->home_id, $params->mission_id, _user_id(), $params->prev_id, $params->next_id, $params->star, $params->limit);
			}

			$this->finish(array("messages" => $cmsgs), ERR_OK);
		}

		public function search_messages()
		{
			$param_names = array("home_id", "mission_id", "search_string", "prev_id", "next_id");
			$this->setApiParams($param_names);
			$this->checkRequired(array("home_id", "search_string"));
			$params = $this->api_params;

			$cmsgs = cmsg::search_messages($params->home_id, $params->mission_id, $params->search_string, $params->prev_id, $params->next_id, _user_id());

			$this->finish(array("messages" => $cmsgs), ERR_OK);
		}

	}

?>