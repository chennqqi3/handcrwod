<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/09/18
    ---------------------------------------------------*/

    class cunread extends model 
    {
        public function __construct()
        {
            parent::__construct("t_cunread",
                "cunread_id",
                array(
                    "mission_id",
                    "cmsg_id",
                    "user_id",
                    "mail_flag"),
                array("auto_inc" => true));
        }

        public static function unread($cmsg_id, $mission_id, $from_id) 
        {
            $db = db::getDB();
            $sql = "DELETE FROM t_cunread WHERE mission_id=" . _sql($mission_id) . " 
                AND cmsg_id=" . _sql($cmsg_id);
            $db->execute($sql);

            $sql = "INSERT INTO t_cunread(mission_id, cmsg_id, user_id)
                SELECT mission_id, " . _sql($cmsg_id). ", user_id
                FROM t_mission_member
                WHERE mission_id=" . _sql($mission_id) . "
                    AND user_id!=" . _sql($from_id) . "
                    AND del_flag=0";
            $db->execute($sql);

            static::refresh_unreads($mission_id);

            return;
        }

        public static function read($cmsg_id, $user_id=null) 
        {
            $db = db::getDB();
            $sql = "DELETE FROM t_cunread WHERE cmsg_id=" . _sql($cmsg_id);

            if ($user_id != null)
                $sql .= "AND user_id=" . _sql($user_id);

            $err = $db->execute($sql);

            return $err;
        }

        public static function read_mission($mission_id, $user_id=null) 
        {
            $db = db::getDB();
            $sql = "DELETE FROM t_cunread WHERE mission_id=" . _sql($mission_id);

            if ($user_id != null)
                $sql .= "AND user_id=" . _sql($user_id);

            $err = $db->execute($sql);

            return $err;
        }

        public static function refresh_unreads($mission_id)
        {
            $db = db::getDB();

            $sql = "UPDATE t_mission_member m
                LEFT JOIN (SELECT COUNT(cunread_id) unreads, user_id 
                    FROM t_cunread 
                    WHERE mission_id=" . _sql($mission_id) . "
                    GROUP BY user_id) u ON m.user_id=u.user_id
                SET m.unreads=u.unreads
                WHERE m.mission_id=" . _sql($mission_id);
            $err = $db->execute($sql);

            return $err;
        }

        public static function last_text($mission_id)
        {
            $my_id = _user_id();

            $db = db::getDB();

            $sql = "SELECT c.content FROM t_cunread u
                LEFT JOIN t_cmsg c ON u.cmsg_id=c.cmsg_id
                WHERE u.mission_id=" . _sql($mission_id) . " AND
                    u.user_id=" . _sql($my_id) . "
                ORDER BY u.create_time DESC
                LIMIT 1";
            return $db->scalar($sql);
        }
    };
?>