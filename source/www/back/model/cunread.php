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
                    "mail_flag",
                    "to_flag"),
                array("auto_inc" => true));
        }

        public function all($user_id) 
        {
            $cunreads = array();
            $cunread = new model;
            $err = $cunread->query("SELECT m.home_id, cu.mission_id, cu.cmsg_id, cu.to_flag
                FROM t_cunread cu LEFT JOIN t_mission m ON cu.mission_id=m.mission_id
                WHERE cu.user_id=" . _sql($user_id) . " AND m.del_flag=0");

            while($err == ERR_OK) {
                if (!isset($cunreads[$cunread->mission_id])) {
                    $cunreads[$cunread->mission_id] = array();
                }
                array_push($cunreads[$cunread->mission_id], array(
                        "cmsg_id" => $cunread->cmsg_id,
                        "to_flag" => $cunread->to_flag
                    ));
                $err = $cunread->fetch();
            }

            return $cunreads;
        }

        public static function unread($cmsg_id, $mission_id, $from_id, $to_ids) 
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

            if (is_array($to_ids)) {
                foreach ($to_ids as $to_id) {
                    if ($to_id == 'all') {
                        $sql = "UPDATE t_cunread SET to_flag=1 
                            WHERE mission_id=" . _sql($mission_id) . "
                                AND cmsg_id=" . _sql($cmsg_id);

                        $db->execute($sql);
                        break;
                    }
                    else {
                        $sql = "UPDATE t_cunread SET to_flag=1 
                            WHERE mission_id=" . _sql($mission_id) . "
                                AND user_id=" . _sql($to_id) . "
                                AND cmsg_id=" . _sql($cmsg_id);

                        $db->execute($sql);
                    }
                }
            }

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

        public static function reset_unread($cmsg_id, $mission_id, $user_id) 
        {
            $cunread = new cunread;

            $err = $cunread->select("cmsg_id=" . _sql($cmsg_id) . " AND mission_id=" . _sql($mission_id) . " AND user_id=" . _sql($user_id));
            if ($err == ERR_OK) {
                return ERR_OK;
            }

            $cunread->cmsg_id = $cmsg_id;
            $cunread->mission_id = $mission_id;
            $cunread->user_id = $user_id;
            $cunread->save();

            static::refresh_unreads($mission_id, $user_id);

            return ERR_OK;
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

        public static function refresh_unreads($mission_id, $user_id=null)
        {
            $db = db::getDB();

            $where1 = "";
            $where2 = "";
            if ($user_id != null) {
                $where1 = " AND user_id=" . _sql($user_id);
                $where2 = " AND m.user_id=" . _sql($user_id);
            }
            $sql = "UPDATE t_mission_member m
                LEFT JOIN (SELECT COUNT(cunread_id) unreads, user_id 
                    FROM t_cunread 
                    WHERE mission_id=" . _sql($mission_id) . $where1 . "
                    GROUP BY user_id) u ON m.user_id=u.user_id
                SET m.unreads=u.unreads
                WHERE m.mission_id=" . _sql($mission_id) . $where2;
            $err = $db->execute($sql);

            $sql = "UPDATE t_mission_member m
                LEFT JOIN (SELECT COUNT(cunread_id) unreads, user_id 
                    FROM t_cunread 
                    WHERE mission_id=" . _sql($mission_id) . $where1 . " AND to_flag=1
                    GROUP BY user_id) u ON m.user_id=u.user_id
                SET m.to_unreads=u.unreads
                WHERE m.mission_id=" . _sql($mission_id) . $where2;
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