<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2014/11/01
    ---------------------------------------------------*/

    class home extends model 
    {
        public function __construct()
        {
            parent::__construct("t_home",
                "home_id",
                array(
                    "client_id",
                    "home_name",
                    "summary",
                    "logo"),
                array("auto_inc" => true));
        }

        public static function getModel($pkvals, $ignore_del_flag=false)
        {
            $model = parent::getModel($pkvals, $ignore_del_flag);
            if ($model == null)
                return null;

            $model->logo_url = $model->logo_url();
            return $model;
        }


        public static function add_home($home_name)
        {
            $my_id = _user_id();
            if ($my_id == null)
                return null;

            $home = new home;
            $home->home_name = $home_name;
            $home->client_id = $my_id;

            $err = $home->save();

            if ($err == ERR_OK) {
                $home_member = new home_member;
                $home_member->home_id = $home->home_id;
                $home_member->user_id = $my_id;
                $home_member->priv = HPRIV_HMANAGER;
                $home_member->accepted = 1; // 承認済み
                $err = $home_member->save(); 
            }

            if ($err == ERR_OK) {
                // デフォルト全メンバー用の作成
                $mission1 = mission::add_mission($home->home_id, "アナウンス", CHAT_PUBLIC);

                // Botルームの作成
                $bot = mission::add_bot($home->home_id);
            }

            return $err == ERR_OK ? $home : null;
        }

        public static function last_home($home_id = null)
        {
            $my_id = _user_id();
            $db = db::getDB();

            if ($home_id == null) {
                $home = new home;
                $sql = "SELECT h.*, hm.last_date, hm.priv
                    FROM t_home h 
                    LEFT JOIN t_home_member hm ON h.home_id=hm.home_id
                    WHERE hm.user_id=" . _sql($my_id) . " AND h.del_flag=0
                    ORDER BY hm.last_date DESC LIMIT 1";

                $err = $home->query($sql);

                if ($err == ERR_OK) {
                    $sql = "UPDATE t_home_member 
                        SET last_date=NOW() WHERE home_id=" . _sql($home->home_id) . "
                        AND user_id=" . _sql($my_id);
                    $db->execute($sql);
                    
                    return array(
                        "home_id" => $home->home_id,
                        "home_name" => $home->home_name,
                        "last_date" => $home->last_date,
                        "priv" => $home->priv
                    );   
                }
                else 
                    return null;
            }
            else {
                $home_member = new home_member;
                $err = $home_member->select("home_id=" . _sql($home_id) . " AND user_id=" . _sql($my_id));
                if ($err == ERR_OK) {
                    $sql = "UPDATE t_home_member 
                        SET last_date=NOW() WHERE home_id=" . _sql($home_id) . "
                        AND user_id=" . _sql($my_id);

                    $db->execute($sql);
                }
                
                return static::last_home();
            }
        }

        public function is_member($user_id)
        {
            $home_member = new home_member;
            $err = $home_member->select("home_id=" . _sql($this->home_id) . 
                " AND user_id=" . _sql($user_id));

            return $err == ERR_OK;
        }

        public function add_member($user_id, $accepted = 0)
        {
            if ($this->is_member($user_id))
                return ERR_OK;

            // グループメンバーの追加
            $home_member = new home_member;
            $home_member->home_id = $this->home_id;
            $home_member->user_id = $user_id;
            $home_member->priv = HPRIV_MEMBER; // 初期メンバーに登録
            $home_member->accepted = $accepted; // 承認待ち

            $err = $home_member->save();

            if ($err == ERR_OK)
            {
                // 公開チャットルームにメンバーを追加
                $p_mission = new mission;

                $err = $p_mission->select("home_id=" . _sql($this->home_id) . 
                    " AND (private_flag=" . CHAT_PUBLIC . " OR private_flag=" . CHAT_BOT . ")");
                while ($err == ERR_OK)
                {
                    // チャットルームメンバーの追加
                    $mission_member = new mission_member;
                    $mission_member->mission_id = $p_mission->mission_id;
                    $mission_member->user_id = $user_id;

                    $mission_member->save();

                    $err = $p_mission->fetch();
                }

                $err = ERR_OK;
            }

            return $err;
        }

        public function remove_member($user_id)
        {
            $home_member = home_member::get_member($this->home_id, $user_id);
            if ($home_member == null)
                return ERR_OK;

            $err = $home_member->remove(true);
            if ($err == ERR_OK)
            {
                $db = db::getDB();

                // 個別チャットを削除する。
                $sql = "DELETE t_mission
                    FROM t_mission 
                    LEFT JOIN t_mission_member ON t_mission.mission_id=t_mission_member.mission_id
                    WHERE t_mission.home_id=" . _sql($this->home_id) . " 
                        AND t_mission.private_flag=" . CHAT_MEMBER . " 
                        AND t_mission_member.user_id=" . _sql($user_id);
                $err = $db->execute($sql);
                
                // メンバー削除
                $sql = "DELETE mm FROM t_mission_member mm 
                    LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
                    WHERE m.home_id=" . _sql($this->home_id) . " AND mm.user_id=" . _sql($user_id);

                $err = $db->execute($sql);
            }

            return $err;
        }

        public function update_logo($uploaded_photo)
        {
            if ($uploaded_photo != "") {
                $photo = $this->home_id . ".png";
                if (substr($uploaded_photo, 0, 3) == "tmp") {
                    @unlink(HOMELOGO_PATH . $photo);
                    @rename(SITE_ROOT . "/" . $uploaded_photo, HOMELOGO_PATH . $photo);
                }
                else if (substr($uploaded_photo, 0, 1) == "r") {
                    @unlink(HOMELOGO_PATH . $photo);
                    @rename(HOMELOGO_PATH . $uploaded_photo, HOMELOGO_PATH . $photo);
                }
            }
        }

        public function remove_logo()
        {
            $photo = $this->home_id . ".png";
            @unlink(HOMELOGO_PATH . $photo);
        }        

        public function logo_url()
        {
            if ($this->logo == null)
                return null;
            else
                return SITE_BASEURL . HOMELOGO_URL . $this->home_id . ".png?" . $this->logo;
        }

        public static function bot_tasks($home_id, $user_id)
        {
            $sql = "SELECT 
                    t.*, 
                    u.user_name performer_name,
                    m.mission_id, m.mission_name, 
                    t.plan_end_date IS NOT NULL AND DATE(t.plan_end_date) < DATE(NOW()) task_end_expire,
                    t.plan_start_date IS NOT NULL AND DATE(t.plan_start_date) < DATE(NOW()) task_start_expire
                FROM t_task t 
                LEFT JOIN t_mission m ON t.mission_id=m.mission_id
                LEFT JOIN m_user u ON t.performer_id=u.user_id
                WHERE m.home_id=" . _sql($home_id) . " AND
                    t.performer_id=" . _sql($user_id) . " AND 
                    t.del_flag=0 AND m.del_flag=0 AND
                    t.complete_flag=0 AND
                    (t.plan_end_date IS NOT NULL AND DATE(t.plan_end_date) < DATE(NOW()) AND t.end_alarm IS NULL OR 
                    t.plan_start_date IS NOT NULL AND DATE(t.plan_start_date) < DATE(NOW()) AND t.start_alarm IS NULL)
                ORDER BY t.plan_end_date ASC, t.plan_start_date ASC";

            $tasks = array();
            $task = new task;
            $err = $task->query($sql);
            while ($err == ERR_OK)
            {
                if ($task->task_end_expire)
                    $task->end_alarm = 1;
                if ($task->task_start_expire)
                    $task->start_alarm = 1;
                $err = $task->save();

                array_push($tasks, array(
                    "task_id" => $task->task_id,
                    "task_name" => $task->task_name,
                    "performer_id" => $task->performer_id,
                    "performer_name" => $task->performer_name,
                    "mission_id" => $task->mission_id,
                    "mission_name" => $task->mission_name,
                    "plan_start_date" => $task->plan_start_date,
                    "plan_start_time" => $task->plan_start_time,
                    "plan_end_date" => $task->plan_end_date,
                    "plan_end_time" => $task->plan_end_time,
                    "task_end_expire" => $task->task_end_expire,
                    "task_start_expire" => $task->task_start_expire,
                    "avartar" => _avartar_full_url($task->performer_id)
                ));
                $err = $task->fetch();
            }

            return $tasks;
        }
    };
?>