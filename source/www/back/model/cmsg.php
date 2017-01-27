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
                    "reacts",
                    "attach",
                    "file_size",
                    "cache_id"),
                array("auto_inc" => true));
        }

        public function save()
        {
            if (is_array($this->reacts)) {
                $this->reacts = json_encode($this->reacts);
            }

            $err = parent::save();

            $this->reacts = json_decode($this->reacts);

            return $err;
        }

        public static function message($cmsg_id, $mission_id, $from_id, $to_id, $content, $cache_id=null) 
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
            $cmsg->to_id = $to_id; // unuse
            $cmsg->cmsg_type = CMSG_TEXT;
            $cmsg->content = $content;
            $cmsg->cache_id = $cache_id;

            $err = $cmsg->save();

            $to_ids = null;
            if (preg_match_all("/\[to\:(all)\]/", $content, $matches) ||
                preg_match_all("/\[to\:([0-9]+)\]/", $content, $matches)) {
                $to_ids = $matches[1];
            }

            // set last date of mission
            mission::set_last_date($mission_id, $cmsg_id);

            if ($to_id == null)
                cunread::unread($cmsg->cmsg_id, $mission_id, $from_id, $to_ids);
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

        public static function react($cmsg_id, $emoticon_id, $user_id)
        {
            $cmsg = static::getModel($cmsg_id);

            if ($cmsg) {
                $amount = react_user::set_react($cmsg_id, $emoticon_id, $user_id);
                $users = react_user::get_users($cmsg_id, $emoticon_id);

                if (!_is_empty($cmsg->reacts)) {
                    $reacts = json_decode($cmsg->reacts, true);
                }
                else {
                    $reacts = array();
                }

                // [[emoticon_id, amount],[emoticon_id, amount]]
                $found = false;
                for ($i = 0; $i < count($reacts); $i ++) {
                    if (is_array($reacts[$i])) {
                        $i_emoticon_id = $reacts[$i][0];
                        if ($i_emoticon_id == $emoticon_id) {
                            if ($amount > 0) {
                                $found = true;
                                $reacts[$i][1] = $amount;
                                $reacts[$i][2] = $users;
                            }
                            else {
                                array_splice($reacts, $i, 1);
                                break;
                            }
                        }
                    }
                }

                if (!$found && $amount > 0) {
                    array_push($reacts, array($emoticon_id, $amount, $users));
                }

                $cmsg->reacts = $reacts;

                $err = $cmsg->save();
            }

            return $cmsg;
        }

        public static function remove_message($cmsg_id)
        {
            if (!_is_empty($cmsg_id))
            {
                $cmsg = cmsg::getModel($cmsg_id);
                if ($cmsg != null)
                {
                    $err = $cmsg->remove(true);
                    if ($err == ERR_OK) {
                        cunread::read($cmsg_id);
                        $err = cunread::refresh_unreads($cmsg->mission_id);
                        react_user::remove_all($cmsg_id);
                    }
                }

                return $err;
            }

            return ERR_OK;
        }

        /*
        private static function messages_in_mission($mission_id, $prev_id, $next_id, $limit)
        {
            if ($limit <= 0)
                $limit = 60;
            
            $cmsgs = array();
            if ($mission_id == null)
                return $cmsgs;

            $user_names = array();

            $cmsg = new cmsg;

            $sql = "SELECT cmsg_id, content, from_id, create_time, reacts
                FROM t_cmsg
                WHERE mission_id=" . _sql($mission_id);

            $order = "DESC";
            if (!_is_empty($prev_id)) {
                // load prev
                $sql .= " AND cmsg_id < " . _sql($prev_id);
            }
            else if (!_is_empty($next_id)) {
                // load next
                $sql .= " AND cmsg_id > " . _sql($next_id);
                $order = "ASC";
            }

            $sql .= " ORDER BY cmsg_id " . $order . " LIMIT " . $limit;
                
            $err = $cmsg->query($sql);

            while ($err == ERR_OK)
            {
                if (isset($user_names[$cmsg->from_id]))
                    $from_name = $user_names[$cmsg->from_id];
                else {
                    $from_name = user::get_user_name($cmsg->from_id);
                    $user_names[$cmsg->from_id] = $from_name;
                }
                $item = array(
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $from_name,
                    "date" => $cmsg->create_time,
                    "unread" => false,
                    "star" => false,
                    "reacts" => json_decode($cmsg->reacts)
                );

                if ($order == "DESC")
                    array_splice($cmsgs, 0, 0, array($item));
                else
                    array_push($cmsgs, $item);

                $err = $cmsg->fetch();
            }

            return $cmsgs;
        }
        */

        public static function messages($home_id, $mission_id, $user_id, $prev_id, $next_id, $star=false, $limit=null) 
        {
            if ($limit <= 0)
                $limit = 60;
            
            $cmsgs = array();
            if ($home_id == null)
                return $cmsgs;

            $user_names = array();

            $cmsg = new cmsg;

            if ($mission_id != null) {
                $sql = "SELECT m.cmsg_id, m.content, m.from_id, m.create_time, m.reacts, u.cunread_id, ms.cmsg_star_id
                    FROM t_cmsg m 
                    " . ($star ? "INNER" : "LEFT") . " JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                    LEFT JOIN t_cunread u ON m.cmsg_id=u.cmsg_id AND u.user_id=" . _sql($user_id) . "
                    WHERE m.mission_id=" . _sql($mission_id) . " AND m.del_flag=0";
                //$cmsgs = static::messages_in_mission($mission_id, $prev_id, $next_id, $limit);
            }
            else {
                $sql = "SELECT m.cmsg_id, m.content, m.from_id, m.create_time, m.reacts, u.cunread_id, ms.cmsg_star_id, mi.mission_id, mi.mission_name
                    FROM t_cmsg m 
                    INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                    INNER JOIN t_home h ON h.home_id=mi.home_id
                    " . ($star ? "INNER" : "LEFT") . " JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                    LEFT JOIN t_cunread u ON m.cmsg_id=u.cmsg_id AND u.user_id=" . _sql($user_id) . "
                    WHERE h.home_id=" . _sql($home_id) . " AND m.del_flag=0"; 
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
                if (isset($user_names[$cmsg->from_id]))
                    $from_name = $user_names[$cmsg->from_id];
                else {
                    $from_name = user::get_user_name($cmsg->from_id);
                    $user_names[$cmsg->from_id] = $from_name;
                }

                $item = array(
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $from_name,
                    "date" => $cmsg->create_time,
                    "unread" => $cmsg->cunread_id != null,
                    "star" => $cmsg->cmsg_star_id != null,
                    "reacts" => json_decode($cmsg->reacts)
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

            $sql = "SELECT m.*, f.user_name from_name, mi.mission_name, h.home_id, h.home_name, mi.private_flag, mm.opp_user_id  
                FROM t_cmsg m 
                INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                LEFT JOIN t_home h ON h.home_id=mi.home_id
                INNER JOIN t_mission_member mm ON mm.mission_id=m.mission_id AND mm.user_id=" . _sql($user_id) . "
                LEFT JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                LEFT JOIN m_user f ON m.from_id=f.user_id 
                WHERE m.del_flag=0 AND mi.del_flag=0 AND ms.hidden IS NULL AND
                    m.content LIKE " . _sql("%" . $search_string . "%") . "
                    AND (mi.private_flag=" . CHAT_BOT . " AND m.to_id=" . _sql($user_id) ." OR mi.private_flag!=" . CHAT_BOT . ")";

            if (!_is_empty($mission_id))
            {
                $sql .= " AND mi.mission_id=" . _sql($mission_id);
            }
            else if (!_is_empty($home_id)) {
                $sql .= " AND mi.home_id=" . _sql($home_id);
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
                if ($cmsg->private_flag == CHAT_MEMBER) {
                    $cmsg->mission_name = user::get_user_name($cmsg->opp_user_id);
                    $home_name = "";
                }
                else {
                    $home_name = $cmsg->home_name;
                }
                
                $item = array(
                    "mission_id" => $cmsg->mission_id,  
                    "mission_name" => $cmsg->mission_name,                  
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $cmsg->from_name,
                    "date" => $cmsg->create_time,
                    "home_id" => $cmsg->home_id,
                    "home_name" => $home_name
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

        public static function star_messages($user_id, $prev_id, $next_id, $limit=null) 
        {
            if ($limit <= 0)
                $limit = 60;
            
            $cmsgs = array();

            $user_names = array();

            $cmsg = new cmsg;

            $sql = "SELECT m.cmsg_id, m.content, m.from_id, m.create_time, m.reacts,mi.mission_id, mi.mission_name, h.home_id, h.home_name, mi.private_flag, mm.opp_user_id  
                FROM t_cmsg m 
                INNER JOIN t_mission mi ON m.mission_id=mi.mission_id
                LEFT JOIN t_home h ON h.home_id=mi.home_id
                INNER JOIN t_cmsg_star ms ON m.cmsg_id=ms.cmsg_id AND ms.user_id=" . _sql($user_id) . "
                INNER JOIN t_mission_member mm ON mm.mission_id=m.mission_id AND mm.user_id=" . _sql($user_id) . "
                WHERE m.del_flag=0 AND mi.del_flag=0"; 

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
                if (isset($user_names[$cmsg->from_id]))
                    $from_name = $user_names[$cmsg->from_id];
                else {
                    $from_name = user::get_user_name($cmsg->from_id);
                    $user_names[$cmsg->from_id] = $from_name;
                }

                if ($cmsg->private_flag == CHAT_MEMBER) {
                    $cmsg->mission_name = user::get_user_name($cmsg->opp_user_id);
                    $home_name = "";
                }
                else {
                    $home_name = $cmsg->home_name;
                }
                $home_name = ($cmsg->private_flag != CHAT_MEMBER) ? $cmsg->home_name : "";

                $item = array(
                    "cmsg_id" => $cmsg->cmsg_id,
                    "content" => $cmsg->content,
                    "user_id" => $cmsg->from_id,
                    "user_name" => $from_name,
                    "date" => $cmsg->create_time,
                    "reacts" => json_decode($cmsg->reacts),
                    "mission_id" => $cmsg->mission_id,
                    "mission_name" => $cmsg->mission_name,
                    "home_id" => $cmsg->home_id,
                    "home_name" => $home_name
                );

                if ($order == "ASC")
                    array_splice($cmsgs, 0, 0, array($item));
                else
                    array_push($cmsgs, $item);

                $err = $cmsg->fetch();
            }

            return $cmsgs;
        }

    };
?>