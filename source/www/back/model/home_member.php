<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2014/11/01
    ---------------------------------------------------*/

    class home_member extends model 
    {
        public function __construct()
        {
            parent::__construct("t_home_member",
                "home_member_id",
                array(
                    "home_id",
                    "user_id",
                    "priv",
                    "last_date",
                    "accepted"),
                array("auto_inc" => true));
        }

        public static function members($home_id)
        {
            $members = array();

            $my_id = _user_id();
            $mine = static::get_member($home_id, $my_id);
            if ($mine == null)
                return $members;

            $home_member = new model;
            $where = '';
            if ($mine->priv == HPRIV_GUEST) {
                // 参加しているルームのメンバーとのみ
                $where = "AND (hm.user_id IN (
                        SELECT DISTINCT mm.user_id FROM t_mission_member mm
                        WHERE mm.mission_id IN (
                            SELECT DISTINCT mm.mission_id FROM t_mission_member mm
                            LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
                            WHERE mm.del_flag=0 AND m.del_flag=0 AND m.complete_flag!=1 
                                AND m.home_id=" . _sql($home_id) . " 
                                AND mm.user_id=" . _sql($my_id) . " 
                                AND m.private_flag=" . CHAT_PRIVATE . "
                                )
                        ) OR hm.user_id=" . _sql($my_id) . ") ";
            }

            $sql = "SELECT hm.home_id, hm.user_id, u.user_name, u.email, u.login_id, hm.priv, hm.accepted
                FROM t_home_member hm 
                INNER JOIN m_user u ON hm.user_id=u.user_id 
                WHERE hm.home_id=" . _sql($home_id) . " AND hm.del_flag=0 " . $where . "
                ORDER BY hm.priv DESC, hm.create_time DESC";

            $err = $home_member->query($sql);
            while ($err == ERR_OK)
            {
                $home_member->avartar = _avartar_full_url($home_member->user_id);
                array_push($members, $home_member->props);

                $err = $home_member->fetch();
            }

            return $members;
        }

        public static function user_ids($home_id, $priv=-1)
        {
            $user_ids = array();

            $where = "hm.home_id=" . _sql($home_id);
            if ($priv != -1) {
                $where .= " AND hm.priv=" . $priv;
            }

            $home_member = new model;
            $err = $home_member->query("SELECT hm.user_id
                FROM t_home_member hm 
                INNER JOIN m_user u ON hm.user_id=u.user_id 
                WHERE " . $where . " AND hm.del_flag=0
                ORDER BY hm.priv DESC, hm.create_time DESC");

            while ($err == ERR_OK)
            {
                array_push($user_ids, $home_member->user_id);

                $err = $home_member->fetch();
            }

            return $user_ids;
        }

        public static function unaccepted_homes($user_id)
        {
            $homes = array();

            $home_member = new model;
            $err = $home_member->query("SELECT h.home_id, h.home_name, hm.create_time
                FROM t_home_member hm 
                INNER JOIN m_user u ON hm.user_id=u.user_id 
                INNER JOIN t_home h on hm.home_id=h.home_id
                WHERE hm.accepted=0 AND hm.del_flag=0 AND h.del_flag=0 AND hm.user_id=" . _sql($user_id) . "
                ORDER BY hm.create_time DESC");

            while ($err == ERR_OK)
            {
                array_push($homes, $home_member->props);

                $err = $home_member->fetch();
            }

            return $homes;
        }

        public static function get_member($home_id, $user_id)
        {
            $home_member = new home_member;

            $err = $home_member->select("home_id=" . _sql($home_id) . " AND user_id=" . _sql($user_id));
            if ($err == ERR_NODATA)
                return null;

            return $home_member;
        }

        public static function get_member_counts($home_id, $priv=null)
        {
            $home_member = new home_member;

            $sql = "SELECT COUNT(*) 
                    FROM t_home_member 
                    WHERE home_id=" . _sql($home_id) . " AND del_flag=0";
            if ($priv !== null) 
                $sql .= " AND priv=" . _sql($priv);

            $count = $home_member->scalar($sql);

            return $count;
        }

    };
?>