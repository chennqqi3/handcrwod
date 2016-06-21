<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class UsersController extends controller {
		public function __construct(){
			parent::__construct();	

			$this->_navi_menu = "users";
			$this->_subnavi_menu = "users";
		}

		public function checkPriv($action, $utype)
		{
			switch($action) {
				default:
					parent::checkPriv($action, UTYPE_ADMIN);
					break;
			}
		}

		public function index($page = 0, $size = 10) {
			$this->_subnavi_menu = "users";
			$users = array();
			$user = new user;
			
			$this->where = "1=1";

			$this->loadsearch("users_lists");

			$this->counts = $user->counts($this->where);

			$this->pagebar = new pageHelper($this->counts, $page, $size);

			$err = $user->select($this->where,
				array("order" => $this->order,
					"limit" => $size,
					"offset" => $this->pagebar->page * $size));

			while ($err == ERR_OK)
			{
				$new_user = clone $user;

				array_push($users, $new_user);

				$err = $user->fetch();
			}

			$this->users = $users;
		}

		private function loadsearch($session_id) {
			$this->search = new reqsession($session_id);

			if ($this->search->search_string != null) {
				$ss = _sql("%" . $this->search->search_string . "%");
				$this->where .= " AND (user_name LIKE " . $ss;
				$this->where .= " OR email LIKE " . $ss . ") ";
			}

			if ($this->search->search_user_type != null) {
				$this->where .= " AND user_type = " . _sql($this->search->search_user_type);
			}

			if ($this->search->search_plan_type != null) {
				$this->where .= " AND plan_type = " . _sql($this->search->search_plan_type);
			}

			if ($this->search->sort_field != null)
				$this->order = $this->search->sort_field . " " . $this->search->sort_order;
			else 
				$this->order = "user_id DESC";
		}

		public function select_user($page = 0, $size = 7) {
			$this->search = new reqsession("select_user");

			$me = _user();
			$users = array();
			$user = new user;

			$this->where = "";
			
			if ($this->search->search_string != null) {
				$ss = _sql("%" . $this->search->search_string . "%");
				$this->where .= "user_name LIKE " . $ss;
			}

			if ($this->search->sort_field != null)
				$this->order = $this->search->sort_field . " " . $this->search->sort_order;
			else 
				$this->order = "user_name ASC";

			$this->counts = $user->counts($this->where);

			$this->pagebar = new pageHelper($this->counts, $page, $size);

			$err = $user->select($this->where,
				array("order" => $this->order,
					"limit" => $size,
					"offset" => $this->pagebar->page * $size));

			while ($err == ERR_OK)
			{
				$new_user = clone $user;

				array_push($users, $new_user);

				$err = $user->fetch();
			}

			$this->users = $users;

			return "popup/users_select_user";
		}

		public function insert() {
			$user = new user;

			$this->mUser = $user;

			return "users_edit";
		}

		public function edit($user_id) {
			$user = user::getModel($user_id);
			if ($user == null)
				$this->showError(ERR_NODATA);
			$user->avartar = _avartar_url($user_id);
			$this->mUser = $user;
		}

		public function save_ajax($user_id = null) {
			$this->start();

			if ($user_id == null) {
				$user = new user;
			}
			else {
				$user = user::getModel($user_id);
			}
			$user->load($this);
			
			if (user::is_exist_by_email($this->email, $user_id))
				$this->checkError(ERR_ALREADY_USING_EMAIL);

			if ($user_id != null) {
				if ($this->old_password != null && md5($this->old_password) == $user->password && 
					$this->new_password != null) {
					$user->password = md5($this->new_password);
				}
			}
			else {
				$user->password = md5($this->password);
				$user->language = DEFAULT_LANGUAGE;
				$user->time_zone = TIME_ZONE;
				$user->activate_flag = 1;
			}
			$this->checkError($err = $user->save());

			if ($err == ERR_OK)
			{
				if ($user_id == null)
					_opr_log("ユーザー登録成功 メール:" . $user->email . "(" . $user->user_id . ")");
				else 
					_opr_log("ユーザー変更成功 メール:" . $user->email . "(" . $user->user_id . ")");
			}

			// update_avartar
			$user->update_avartar($this->photo);
								
			$this->finish(array("user_id" => $user->user_id), $err);
		}

		public function delete_ajax($user_id) {
			$this->start();

			$count = func_num_args();

			for ($i = 0; $i < $count; $i ++) {
				$user_id = func_get_arg($i);
				$user = user::getModel($user_id);

				if ($user != null) {
					if (!$user->check_delete())
						$this->checkError(ERR_DELUSER);

					$err = $user->remove();	
		
					if ($err == ERR_OK)
					{
						_opr_log("ユーザー削除成功 ユーザーID:" . $user->user_id . " メール:" . $user->email);
					}
				}
			}

			$this->finish(null, $err);
		}

		public function activate_ajax($user_id) {
			$this->start();

			$count = func_num_args();

			for ($i = 0; $i < $count; $i ++) {
				$user_id = func_get_arg($i);
				$user = user::getModel($user_id);

				if ($user != null) {
					$user->activate_flag = 1;
					$user->activate_key = "";
					$err = $user->save();
				}
			}

			$this->finish(null, $err);
		}

		public function detail($user_id) {
			$user = user::getModel($user_id);
			if ($user == null)
				$this->showError(ERR_NODATA);
			
			$this->mUser = $user;
		}
	}
?>