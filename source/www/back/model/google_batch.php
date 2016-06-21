<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/06/04
    ---------------------------------------------------*/

    class google_batch extends model 
    {
        public function __construct()
        {
            parent::__construct("t_google_batch",
                "google_batch_id",
                array("task_id",
                    "update_type"),
                array("auto_inc" => true));
        }

        public static function add_event($task_id)
        {
            $google_batch = new google_batch;

            $err = $google_batch->select("task_id=" . _sql($task_id) . " AND update_type=0");
            if ($err == ERR_NODATA) {
                $google_batch->google_batch_id = null;
                $google_batch->task_id = $task_id;
                $google_batch->update_type = 0;
                $google_batch->save();
            }

            return;
        }

        public static function remove_event($task_id)
        {
            $google_batch = new google_batch;

            $err = $google_batch->select("task_id=" . _sql($task_id) . " AND update_type=1");
            if ($err == ERR_NODATA) {
                $google_batch->google_batch_id = null;
                $google_batch->task_id = $task_id;
                $google_batch->update_type = 1;
                $google_batch->save();
            }

            return;
        }
    };
?>