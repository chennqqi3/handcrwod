<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/11/09
    ---------------------------------------------------*/

    class push_msg extends model 
    {
        public function __construct()
        {
            parent::__construct("t_push_msg",
                "push_msg_id",
                array(
                    "device_type",
                    "device_token",
                    "message",
                    "fail_count",
                    "mission_id",
                    "cmsg_id"),
                array("auto_inc" => true));
        }

        public static function add_push($device_type, $device_token, $message, $mission_id, $cmsg_id)
        {
            $push_msg = new push_msg;
            $push_msg->device_type = $device_type;
            $push_msg->device_token = $device_token;
            $push_msg->message = $message;
            $push_msg->mission_id = $mission_id;
            $push_msg->cmsg_id = $cmsg_id;
            return $push_msg->save();
        }

        public static function send_push()
        {
            $push_msg = new push_msg;
            $err = $push_msg->select("");

            while($err == ERR_OK) {
                $res = _send_push($push_msg->device_type, $push_msg->device_token, $push_msg->message, $push_msg->mission_id, $push_msg->cmsg_id);

                if ($res || $push_msg->fail_count > 3) {
                    $push_msg->remove(true);
                }
                else {
                    $push_msg->fail_count += 1;
                    $push_msg->save();
                }

                $err = $push_msg->fetch();
            }

            return;
        }
    }
