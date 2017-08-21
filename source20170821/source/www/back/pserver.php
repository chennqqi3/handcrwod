<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2015/09/15
        Module Name:        Push notification server
    ---------------------------------------------------*/
    
    define('OB_DISABLE',        true);
    define('DEFAULT_PHP',       'pserver.php');

    require_once("include/utility.php");

    ini_set('display_errors', 1);
    error_reporting(E_ALL);

    $db = db::getDB();
    while(true) {
        $db->begin();
        push_msg::send_push();
        $db->commit();

        usleep(500);
    }
