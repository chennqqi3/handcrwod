<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:				Ken
        Date:               2015/03/22
    ---------------------------------------------------*/

    class template_skill extends model 
    {
        public function __construct()
        {
            parent::__construct("t_template_skill",
                "template_skill_id",
                array(
                    "template_id",
                    "template_task_id",
                    "skill_name"),
                array("auto_inc" => true));
        }
    };
?>