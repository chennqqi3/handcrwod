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

		public function send_message()
		{
			$param_names = array("cmsg_id", "mission_id", "content", "to_id", "cache_id");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id", "content"));
			$params = $this->api_params;

			$my_id = _user_id();

			$err = cmsg::message($params->cmsg_id, $params->mission_id,
				$my_id, $params->to_id, $params->content, $params->cache_id);

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

		public function unread_messages()
		{
			$param_names = array("mission_id", "cmsg_ids");
			$this->setApiParams($param_names);
			$this->checkRequired(array("mission_id"));
			$params = $this->api_params;

			$my_id = _user_id();

			$err = ERR_OK;
			if ($params->cmsg_ids != null) {
				foreach ($params->cmsg_ids as $cmsg_id) {
					$err = cunread::reset_unread($cmsg_id, $params->mission_id, $my_id);
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

            $home_id = $params->home_id;
            $mission_id = $params->mission_id;
            $prev_id = $params->prev_id;
            $next_id = $params->next_id;
            $star = $params->star;
            $limit = $params->limit;

			if ($params->next_id < 0)
				$cmsgs = array();
			else {
				$cmsgs = cmsg::messages($home_id, $mission_id, _user_id(), $prev_id, $next_id, $star, $limit);
			}

			$ret = array(
                'messages' => $cmsgs,
                'home_id' => $home_id,
                'mission_id' => $mission_id,
                'prev_id' => $prev_id,
                'next_id' => $next_id
            );

			$this->finish($ret, ERR_OK);
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

		public function react()
		{
			$param_names = array("cmsg_id", "emoticon_id");
			$this->setApiParams($param_names);
			$this->checkRequired($param_names);
			$params = $this->api_params;

			$my_id = _user_id();

			$cmsg = cmsg::react($params->cmsg_id, $params->emoticon_id, $my_id);
			if ($cmsg == null)
				$this->checkError(ERR_NOTFOUND_CMSG);
		
			$this->finish(array("cmsg", $cmsg->props), ERR_OK);
		}
	}

?>