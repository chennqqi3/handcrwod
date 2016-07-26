<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/09/18
    ---------------------------------------------------*/

    class cmsg extends model 
    {
        public function __construct()
        {
            parent::__construct("t_cmsg",
                "cmsg_id",
                array(
                    "mission_id",
                    "from_id",
                    "to_id",
                    "cmsg_type",
                    "content",
                    "attach",
                    "file_size"),
                array("auto_inc" => true));
        }

        public static function message($cmsg_id, $mission_id, $from_id, $to_id, $content) 
        {
            if (_is_empty($content))
                return null;

            $cmsg = null;
            if (!_is_empty($cmsg_id))
                $cmsg = static::getModel($cmsg_id);

            if ($cmsg == null)
                $cmsg = new static;

            $cmsg->mission_id = $mission_id;
            $cmsg->from_id = $from_id;
            $cmsg->to_id = $to_id;
            $cmsg->cmsg_type = CMSG_TEXT;
            $cmsg->content = $content;

            $err = $cmsg->save();

            // set last date of mission
            mission::set_last_date($mission_id, $cmsg_id);

            if ($to_id == null)
                cunread::unread($cmsg->cmsg_id, $mission_id, $from_id);
            else
                cunread::refresh_unreads($mission_id);

            if ($err == ERR_OK)
            {
                return $cmsg;
            }
            else {
                return null;
            }
        }

        public static function remove_message($cmsg_id)
        {
            if (!_is_empty($cmsg_id))
            {
                $cmsg = cmsg::getModel($cmsg_id);
                if ($cmsg != null)
                {
                    $err = $cmsg->remove();
                    if ($err == ERR_OK) {
                        cunread::read($cmsg_id);
                        $err = cunread::refresh_unreads($cmsg->mission_id);
                    }
                }

                return $err;
            }

            return ERR_OK;
        }

        public static function messages($home_id, $mission_id, $user_id, $prev_id, $next_id, $star=false, $limit=null) 
        {
            if ($limit <= 0)
                $limit = 60;
            
            $cmsgs = array();
            if ($home_id == null)
                return $cmsgs;

            $cmsg = new cmsg;

            if ($mission_id != null) {
                $sql = "SELECT m.*, f.user_name from_name, u.cunread_id, ms.cmsg_star_id
                    FROM t_cmsg m 
                    INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                    " . ($star ? "INNER" : "LEFT") . " JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                    LEFT JOIN m_user f ON m.from_id=f.user_id
                    LEFT JOIN t_cunread u ON m.cmsg_id=u.cmsg_id AND u.user_id=" . _sql($user_id) . "
                    WHERE m.mission_id=" . _sql($mission_id) . " AND m.del_flag=0
                        AND (mi.private_flag=" . CHAT_BOT . " AND m.to_id=" . _sql($user_id) ." OR mi.private_flag!=" . CHAT_BOT . ")";
            }
            else {
                $sql = "SELECT m.*, f.user_name from_name, u.cunread_id, ms.cmsg_star_id, mi.mission_id, mi.mission_name
                    FROM t_cmsg m 
                    INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                    INNER JOIN t_home h ON h.home_id=mi.home_id
                    " . ($star ? "INNER" : "LEFT") . " JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                    LEFT JOIN m_user f ON m.from_id=f.user_id
                    LEFT JOIN t_cunread u ON m.cmsg_id=u.cmsg_id AND u.user_id=" . _sql($user_id) . "
                    WHERE h.home_id=" . _sql($home_id) . " AND m.del_flag=0
                        AND (mi.private_flag=" . CHAT_BOT . " AND m.to_id=" . _sql($user_id) ." OR mi.private_flag!=" . CHAT_BOT . ")";    
            }

            $order = "DESC";
            if (!_is_empty($prev_id)) {
                // load prev
                $sql .= " AND m.cmsg_id < " . _sql($prev_id);
            }
            else if (!_is_empty($next_id)) {
                // load next
                $sql .= " AND m.cmsg_id > " . _sql($next_id);
                $order = "ASC";
            }

            $sql .= " ORDER BY m.cmsg_id " . $order . " LIMIT " . $limit;
                
            $err = $cmsg->query($sql);

            while ($err == ERR_OK)
            {
                $item = array(
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $cmsg->from_name,
                    "date" => $cmsg->create_time,
                    "unread" => $cmsg->cunread_id != null,
                    "star" => $cmsg->cmsg_star_id != null
                );

                if ($mission_id == null) {
                    $item["mission_id"] = $cmsg->mission_id;
                    $item["mission_name"] = $cmsg->mission_name;
                }

                if ($order == "DESC")
                    array_splice($cmsgs, 0, 0, array($item));
                else
                    array_push($cmsgs, $item);

                $err = $cmsg->fetch();
            }

            // set last date of mission
            mission_member::set_last_date($mission_id, $user_id);

            return $cmsgs;
        }

        public static function search_messages($home_id, $mission_id, $search_string, $prev_id, $next_id, $user_id) 
        {
            $limit = 50;

            $cmsgs = array();

            $cmsg = new cmsg;

            $sql = "SELECT m.*, f.user_name from_name, mi.mission_name  
                FROM t_cmsg m 
                INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                INNER JOIN t_mission_member mm ON mm.mission_id=m.mission_id AND user_id=" . _sql($user_id) . "
                LEFT JOIN m_user f ON m.from_id=f.user_id 
                WHERE m.del_flag=0 AND mi.del_flag=0 AND 
                    mi.home_id=" . _sql($home_id) . " AND
                    m.content LIKE " . _sql("%" . $search_string . "%") . "
                    AND (mi.private_flag=" . CHAT_BOT . " AND m.to_id=" . _sql($user_id) ." OR mi.private_flag!=" . CHAT_BOT . ")";

            if (!_is_empty($mission_id))
            {
                $sql .= " AND m.mission_id=" . _sql($mission_id);
            }

            $order = "DESC";
            if (!_is_empty($prev_id)) {
                // load prev
                $sql .= " AND m.cmsg_id > " . _sql($prev_id);
                $order = "ASC";
            }
            else if (!_is_empty($next_id)) {
                // load next
                $sql .= " AND m.cmsg_id < " . _sql($next_id);
            }

            $sql .= " ORDER BY m.cmsg_id " . $order . " LIMIT " . $limit;
                
            $err = $cmsg->query($sql);

            while ($err == ERR_OK)
            {
                $item = array(
                    "mission_id" => $cmsg->mission_id,  
                    "mission_name" => $cmsg->mission_name,                  
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $cmsg->from_name,
                    "date" => $cmsg->create_time,
                );

                if ($order == "ASC")
                    array_splice($cmsgs, 0, 0, array($item));
                else
                    array_push($cmsgs, $item);

                $err = $cmsg->fetch();
            }

            return $cmsgs;
        }

        public static function star($cmsg_id, $user_id, $star)
        {
            $cmsg_star = new cmsg_star;
            $err = $cmsg_star->select("cmsg_id=" . _sql($cmsg_id) . " AND user_id=" . _sql($user_id));

            if ($star)
            {
                $cmsg_star->cmsg_id = $cmsg_id;
                $cmsg_star->user_id = $user_id;

                $err = $cmsg_star->save();
            }
            else {
                if ($err == ERR_OK) {
                    $err = $cmsg_star->remove(true);
                }
                else {
                    $err = ERR_OK;
                }
            }

            return $err;
        }

    };
?>