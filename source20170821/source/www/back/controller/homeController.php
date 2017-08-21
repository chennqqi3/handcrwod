<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class HomeController extends controller {
		public function __construct(){
			parent::__construct();	

			$this->_navi_menu = "home";
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_ADMIN);
		}

		public function index() {
			//breadcrumbHelper::set_home();

			$users = array();
			$user = new user;

			$err = $user->select('',
				array("order" => 'user_id DESC',
					"limit" => 10));

			while ($err == ERR_OK)
			{
				$new_user = clone $user;

				array_push($users, $new_user);

				$err = $user->fetch();
			}

			$this->users = $users;

			$missions = array();
			$mission = new mission;
			
			$from_where = "FROM t_mission m LEFT JOIN m_user c ON m.client_id=c.user_id WHERE m.del_flag = 0";

			$err = $mission->query("SELECT m.*, c.user_name client_name " . $from_where,
				array("order" => "m.mission_id DESC",
					"limit" => 10));

			while ($err == ERR_OK)
			{
				$new_mission = clone $mission;

				array_push($missions, $new_mission);

				$err = $mission->fetch();
			}

			$this->missions = $missions;
		}

		public function breadcrumb_ajax() {
			breadcrumbHelper::read_save();

			$this->finish(null, ERR_OK);
		}
	}
?>