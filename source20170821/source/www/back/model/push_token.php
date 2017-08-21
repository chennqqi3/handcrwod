<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/11/09
    ---------------------------------------------------*/

    class push_token extends model 
    {
        public function __construct()
        {
            parent::__construct("t_push_token",
                "push_token_id",
                array(
                    "device_type",
                    "device_token",
                    "user_id",
                    "last_message",
                    "push_flag"),
                array("auto_inc" => true));
        }

        public static function set_last($user_id, $device_type, $device_token)
        {
            if ($device_type && $device_token) {
                $push_token = new push_token;
                $err = $push_token->select("user_id=" . _sql($user_id) . " AND 
                    device_type=" . _sql($device_type) . " AND
                    device_token=" . _sql($device_token));

                if ($err == ERR_OK) {
                    $err = $push_token->save();
                }                
            }
        }

        public static function must_push($user_id)
        {
            $tokens = array();
            
            $push_token = new push_token;
            $err = $push_token->select("user_id=" . _sql($user_id) . 
                " AND TIME_TO_SEC(TIMEDIFF(NOW(), update_time)) > " . UNREAD_PUSH_LIMIT);

            while($err == ERR_OK) {
                array_push($tokens, 
                    array(
                        "user_id" => $user_id,
                        "device_type" => $push_token->device_type,
                        "device_token" => $push_token->device_token
                    )
                );
                $err = $push_token->fetch();
            }

            return $tokens;
        }
    }