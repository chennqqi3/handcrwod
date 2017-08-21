<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2014/11/01
    ---------------------------------------------------*/

    class HomeController extends APIController {
        public $err_login;

        public function __construct(){
            parent::__construct();  
        }

        public function checkPriv($action, $utype)
        {
            parent::checkPriv($action, UTYPE_LOGINUSER);
        }

        public function add()
        {
            $param_names = array("home_name");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            // start transaction
            $this->start();

            $my_id = _user_id();
            $plan = _user_plan();

            $db = db::getDB();
            $home_count = $db->scalar("SELECT COUNT(home_id) FROM t_home 
                WHERE client_id=" . _sql($my_id) . " AND del_flag=0");
            if ($plan->max_homes != -1 && $plan->max_homes <= $home_count)
                $this->checkError(ERR_OVER_MAX_HOMES, $plan->max_homes);

            $home = home::add_home($params->home_name);

            if ($home != null)
                $this->finish(array("home" => 
                        array(
                            "home_id" => $home->home_id,
                            "home_name" => $home->home_name,
                            "last_date" => $home->last_date,
                            "priv" => HPRIV_HMANAGER
                        )
                    ), ERR_OK);
            else 
                $this->checkError(ERR_SQL);

        }

        public function edit()
        {
            $param_names = array("home_id", "home_name", "summary");
            $this->setApiParams($param_names);
            $this->checkRequired(array("home_id"));
            $params = $this->api_params;

            // start transaction
            $this->start();

            $home = home::getModel($params->home_id);
            $home->load($params);

            $my_id = _user_id();
            $home_member = home_member::get_member($params->home_id, $my_id);
            if ($home_member == null || $home_member->priv != HPRIV_HMANAGER)
                $this->checkError(ERR_NOPRIV);

            $err = $home->save();

            $this->finish(array("home_id" => $home->home_id), $err);
        }

        public function remove()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            // start transaction
            $this->start();

            $my_id = _user_id();

            $err = ERR_OK;

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $home_member = home_member::get_member($params->home_id, $my_id);
            if ($home_member == null || $home_member->priv != HPRIV_HMANAGER)
                $this->checkError(ERR_NOPRIV);
            
            $err = $home->remove();
            if ($err == ERR_OK)
            {
                $db = db::getDB();

                $sql = "DELETE mm FROM t_mission_member mm 
                    LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . " AND mm.user_id=" . _sql($params->user_id) . ";
                    DELETE ma FROM t_mission_attach ma 
                    LEFT JOIN t_mission m ON ma.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . ";
                    DELETE t FROM t_task t 
                    LEFT JOIN t_mission m ON t.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . ";
                    DELETE pl FROM t_proclink pl 
                    LEFT JOIN t_mission m ON pl.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . ";
                    DELETE c FROM t_cmsg c 
                    LEFT JOIN t_mission m ON c.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . ";
                    DELETE c FROM t_cunread c 
                    LEFT JOIN t_mission m ON c.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($params->home_id) . ";
                    DELETE FROM t_mission WHERE home_id=" . _sql($params->home_id) . ";";
                $err = $db->execute_batch($sql);
            }

            $this->finish(null, $err);
        }

        public function select()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $this->start();

            home::last_home($params->home_id);

            $this->finish(null, ERR_OK);
        }

        public function priv()
        {
            $param_names = array("home_id", "user_id", "priv");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $this->start();

            $my_id = _user_id();
            $home_id = $params->home_id;

            $home = home::getModel($home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $user_ids = home_member::user_ids($home_id, HPRIV_HMANAGER);
            if (!in_array($my_id, $user_ids))
                $this->checkError(ERR_NOPRIV);

            if (count($user_ids) < 2 && $params->user_id == $my_id)
                $params->priv = HPRIV_HMANAGER;

            $home_member = home_member::get_member($home_id, $params->user_id);
            if ($home_member == null)
                $this->checkError(ERR_NOTFOUND_USER);

            $home_member->priv = $params->priv;
            $err = $home_member->save();
            $this->checkError($err);

            $mission = new mission;
            $err = $mission->select("home_id=" . _sql($home_id) . " AND private_flag=" . _sql(CHAT_PUBLIC));
            while ($err == ERR_OK) {
                $mission->refresh_mission_member();
                $err = $mission->fetch();
            }

            $this->finish(array("priv" => $home_member->priv), ERR_OK);
        }

        public function search()
        {
            $param_names = array("search_string", "sort_field", "sort_order");
            $this->setApiParams($param_names);
            $params = $this->api_params;
            
            $my_id = _user_id();

            $homes = array();
            $home = new home;

            $sql = "SELECT h.home_id, h.home_name, h.summary, h.client_id, hm.last_date, hm.priv, h.logo
                FROM t_home h 
                LEFT JOIN t_home_member hm ON h.home_id=hm.home_id
                WHERE hm.user_id=" . _sql($my_id) . " AND h.del_flag=0 AND hm.accepted=1";

            if (!_is_empty($params->search_string)) {
                $ss = _sql("%" . $params->search_string . "%");
                $sql .= " AND h.home_name LIKE " . $ss;
            }

            /*
            if ($params->sort_field != null)
                $order = $params->sort_field . " " . $params->sort_order;
            else 
            */
            $order = "hm.last_date DESC";

            $err = $home->query($sql,
                array("order" => $order));
            if ($err != ERR_NODATA)
                $this->checkError($err);

            while ($err == ERR_OK)
            {
                $u = home::get_unreads($my_id, $home->priv, $home->home_id);

                $m = array(
                    "home_id" => $home->home_id,
                    "home_name" => $home->home_name,
                    "summary" => $home->summary,
                    "logo_url" => $home->logo_url(),
                    "last_date" => $home->last_date,
                    "priv" => $home->priv,
                    "unreads" => $u["unreads"],
                    "to_unreads" => $u["to_unreads"]
                ); 

                array_push($homes, $m);

                $err = $home->fetch();
            }

            $this->finish(array("homes" => $homes), ERR_OK);
        }

        public function get_name() 
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $home_name = "";
            $home = home::getModel($params->home_id);
            if ($home)
                $home_name = $home->home_name;

            $this->finish(array("home_name" => $home_name), ERR_OK);
        }

        public function get()
        {
            $param_names = array("home_id", "public_complete_flag", "private_complete_flag");
            $this->setApiParams($param_names);
            $this->checkRequired(array("home_id"));
            $params = $this->api_params;

            // start transaction
            $this->start();

            $my_id = _user_id();
            $my_name = _user_name();

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $home->members = home_member::members($params->home_id);

            $found = false;
            foreach ($home->members as $member) {
                if ($member["user_id"] == $my_id) {
                    $home->priv = $member["priv"];
                    $found = true;
                }
            }

            if ($found == false)
                $this->checkError(ERR_NOPRIV);

            $u = home::get_unreads($my_id, $home->priv, $params->home_id);
            $home->unreads = $u["unreads"];
            $home->to_unreads = $u["to_unreads"];

            // set last home
            home::last_home($home->home_id);

            $this->finish(array("home" => $home->props), ERR_OK);
        }

        public function invite()
        {   
            $param_names = array("email", "content", "home_id", "signup_url", "signin_url");
            $this->setApiParams($param_names);
            $this->checkRequired(array("email", "home_id"));
            $params = $this->api_params;

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            if (_is_empty($params->signup_url))
                $params->signup_url = DEFAULT_APP_URL . "#/signup";

            if (_is_empty($params->signin_url))
                $params->signin_url = DEFAULT_APP_URL . "#/signin";

            if (_is_empty($params->content)) 
                $params->content = MAIL_HEADER . "\n" . _user_name() . "様より、グループ「" . $home->home_name . "」へ招待されました。";
            else 
                $params->content = MAIL_HEADER . "\n" . $params->content;
            
            // start transaction
            $this->start();

            $user = user::getModel($params->email);

            if ($user == null) {
                $domain = strstr($params->email, '@', true);
                if ($domain == null)
                    $this->finish(null, ERR_INVALID_PARAMS);
                    
                $title = "～" . _user_name() . "より～　「ハンドクラウド」のグループへのご招待";
                $params->content .= "\n下記のURLにアクセスして、会員登録を行ってください。
" . $params->signup_url . "?invite_home_id=" . $params->home_id . "&email=" . $params->email . "&key=" . _key($params->home_id . $params->email) . "
" . MAIL_FOOTER;
                _send_mail($params->email, $params->email, $title, $params->content);

                $this->finish(array("user_id" => null), ERR_OK);
            }
            else {
                if ($home->is_member($user->user_id)) {
                    $this->finish(null, ERR_OK);
                }
                else {
                    $err = $home->add_member($user->user_id);
                    $this->checkError($err);

                    $title = "～" . _user_name() . "より～　「ハンドクラウド」のグループへのご招待";
                    $params->content .= "\n下記のURLにアクセスして、招待を承認してください。
" . $params->signin_url . "
" . MAIL_FOOTER;

                    _send_mail($user->email, $user->user_name, $title, $params->content);

                    $this->finish(array("user_id" => $user->user_id), ERR_OK);
                }
            }
        }

        public function accept_invite()
        {
            $param_names = array("home_id", "accept");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $home_member = home_member::get_member($params->home_id, _user_id());
            if ($home_member == null)
                $this->checkError(ERR_NOTFOUND_HOME_MEMBER);

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            if ($params->accept == 1) {
                $home_member->accepted = 1;
                $err = $home_member->save();
            }
            else {
                $err = $home->remove_member(_user_id());
            }

            $this->finish(null, $err);
        }

        public function self_invite()
        {   
            $param_names = array("home_id", "invite_key");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $user_id = _user_id();
            $user = _user();
            if ($user == null)
                $this->checkError(ERR_NOTFOUND_USER);

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            if ($home->invite_key != $home->invite_key)
                $this->checkError(ERR_INVALID_INVITE_KEY); 
            
            // start transaction
            $this->start();

            if (!$home->is_member($user_id)) {
                $err = $home->add_member($user_id, 1);
                $this->checkError($err);
            }
            
            $this->finish(array("home_id" => $params->home_id), ERR_OK);
        }

        public function members()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $members = home_member::members($params->home_id);

            $this->finish(array("members" => $members), ERR_OK);
        }

        public function remove_member()
        {
            $param_names = array("home_id", "user_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $this->start();

            $my_id = _user_id();

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $home_member = home_member::get_member($params->home_id, $my_id);
            if ($home_member == null || $home_member->priv != HPRIV_HMANAGER)
                $this->checkError(ERR_NOPRIV);

            if ($my_id == $params->user_id)
                $this->checkError(ERR_CANT_REMOVE_SELF);

            $err = $home->remove_member($params->user_id);

            $this->finish(null, $err);
        }

        public function break_home()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $this->start();

            $my_id = _user_id();

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $home_member = home_member::get_member($params->home_id, $my_id);

            if ($home_member == null)
                $this->checkError(ERR_NOPRIV);

            if  ($home_member->priv == HPRIV_HMANAGER) {
                if (1 == home_member::get_member_counts($params->home_id, HPRIV_HMANAGER))
                    $this->checkError(ERR_HMANAGER_CANT_BREAK);
            }

            $err = $home->remove_member($my_id);

            $this->finish(null, $err);
        }

        public function break_handcrowd()
        {
            $this->start();

            $my_id = _user_id();

            $home = new home;
            $sql = "SELECT h.*, hm.priv
                    FROM t_home_member hm LEFT JOIN t_home h ON hm.home_id=h.home_id
                    WHERE hm.user_id=" . _sql($my_id) . " AND h.del_flag=0";
            $err = $home->query($sql);
            while ($err == ERR_OK) {
                $member_count = home_member::get_member_counts($home->home_id);

                if ($home->priv == HPRIV_HMANAGER && 
                    home_member::get_member_counts($home->home_id, HPRIV_HMANAGER) == 1 &&
                    $member_count > 1)
                    $this->checkError(ERR_EXIST_HMANAGER);

                if ($member_count == 1) {
                    $err = $home->remove();
                    if ($err == ERR_OK)
                    {
                        $db = db::getDB();

                        $sql = "DELETE mm FROM t_mission_member mm 
                            LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . " AND mm.user_id=" . _sql($my_id) . ";

                            DELETE ma FROM t_mission_attach ma 
                            LEFT JOIN t_mission m ON ma.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . ";

                            DELETE t FROM t_task t 
                            LEFT JOIN t_mission m ON t.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . ";

                            DELETE pl FROM t_proclink pl 
                            LEFT JOIN t_mission m ON pl.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . ";

                            DELETE c FROM t_cmsg c 
                            LEFT JOIN t_mission m ON c.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . ";

                            DELETE c FROM t_cunread c 
                            LEFT JOIN t_mission m ON c.mission_id=m.mission_id
                            WHERE m.home_id=" . _sql($home_id) . ";

                            DELETE FROM t_mission WHERE home_id=" . _sql($home_id) . ";";
                        $err = $db->execute_batch($sql);
                        if ($err != ERR_OK)
                            $this->checkError($err);
                    }                    
                }
                else {
                    $err = $home->remove_member($my_id);
                    if ($err != ERR_OK)
                        $this->checkError($err);
                }

                $err = $home->fetch();
            }

            $user = _user();
            if ($user)
                $err = $user->remove();

            $this->finish(null, $err);
        }

        public function bot_messages()
        {
            $param_names = array("home_id", "self_only");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;
            
            $my_id = _user_id();

            $sql = "SELECT 
                    t.task_id, t.task_name, 
                    t.performer_id, u.user_name performer_name,
                    m.mission_id, m.mission_name, 
                    t.plan_start_date, t.plan_start_time,
                    t.plan_end_date, t.plan_end_time,
                    t.plan_end_date IS NOT NULL AND DATE(t.plan_end_date) < DATE(NOW()) task_end_expire,
                    t.plan_start_date IS NOT NULL AND DATE(t.plan_start_date) < DATE(NOW()) task_start_expire
                FROM t_task t 
                LEFT JOIN t_mission m ON t.mission_id=m.mission_id
                LEFT JOIN m_user u ON t.performer_id=u.user_id
                WHERE m.home_id=" . _sql($params->home_id) . " AND
                    t.performer_id=" . _sql($my_id) . " AND 
                    t.del_flag=0 AND m.del_flag=0 AND
                    t.complete_flag=0 AND
                    (t.plan_end_date IS NOT NULL AND DATE(t.plan_end_date) < DATE(NOW()) AND t.end_alarm != 1 OR 
                    t.plan_start_date IS NOT NULL AND DATE(t.plan_start_date) < DATE(NOW()) AND t.start_alarm != 1)
                ORDER BY t.plan_end_date ASC, t.plan_start_date ASC";

            $tasks = array();
            $task = new model;
            $err = $task->query($sql);
            while ($err == ERR_OK)
            {
                $task->avartar = _avartar_full_url($task->performer_id);
                $t = $task->props;
                array_push($tasks, $t);
                $err = $task->fetch();
            }

            $this->finish(array(
                "tasks" => $tasks
            ), ERR_OK);
        }

        public function logo_url()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);

            $this->finish(array("logo_url" => $home->logo_url()), ERR_OK);
        }

        public function upload_logo()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            // start transaction
            $this->start();

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);  
            
            $ext = _get_uploaded_ext("file");
            if ($ext != null) {
                $tmppath = _tmp_path("png");
                $tmpfile = _basename($tmppath);
                
                _upload("file", $tmppath); 

                _resize_userphoto($tmppath, $ext, 240, 240);

                _erase_old(TMP_PATH);

                $home->update_logo("tmp/" . $tmpfile);

                $home->logo = _newId();
                $home->save();

                $this->finish(array("logo_url" => $home->logo_url()), ERR_OK);
            }
            else {
                $this->checkError(ERR_INVALID_IMAGE);
            }
        }

        public function remove_logo()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;

            // start transaction
            $this->start();

            $home = home::getModel($params->home_id);
            if ($home == null)
                $this->checkError(ERR_NOTFOUND_HOME);  
            
            $home->remove_logo();

            $home->logo = null;
            $home->save();

            $this->finish(array("logo_url" => $home->logo_url()), ERR_OK);
        }

        public function emoticons()
        {
            $param_names = array("home_id");
            $this->setApiParams($param_names);
            $this->checkRequired($param_names);
            $params = $this->api_params;
            
            $this->finish(array("emoticons" => emoticon::all($mission->home_id)), ERR_OK);
        }
    }
?>