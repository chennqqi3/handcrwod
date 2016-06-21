<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/12/5
    ---------------------------------------------------*/

    class cmsg_star extends model 
    {
        public function __construct()
        {
            parent::__construct("t_cmsg_star",
                "cmsg_star_id",
                array(
                    "cmsg_id",
                    "user_id"),
                array("auto_inc" => true));
        }
    }
?>