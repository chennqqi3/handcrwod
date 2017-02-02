<?php
    /*---------------------------------------------------
        Project Name:       HandCrowd
        Developement:       
        Author:             Ken
        Date:               2017/01/11
        Module Name:        Chat server
    ---------------------------------------------------*/
    
    define('OB_DISABLE',        true);
    define('DEFAULT_PHP',       'eserver.php');

    require_once("include/utility.php");

	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	use Wrench\Exception\BadRequestException;

	class ChatServer extends Wrench\Application\Application
	{
        private $_clients = array();
        private $_users = array();
	
		protected function _encodeData($event, $data)
		{
			if(empty($event))
			{
				return false;
			}
			
			$payload = array(
				'event' => $event,
				'data' => $data
			);
			
			return _json_encode($payload);
		}
	
		protected function _decodeData($data)
		{
			$decodedData = json_decode($data, true);
			if($decodedData === null)
			{
				return false;
			}
			
			if(isset($decodedData['event'], $decodedData['data']) === false)
			{
				return false;
			}
			
			return $decodedData;
		}

		public function onConnecting($client)
		{
			$params = $client->getParams();
            $user_id = null;
            if (count($params) > 0)
            {
                $user_id = $params[0];
                $u = user::getModel($user_id);
                if ($u == null)
                {
                    $user_id = null;
                }
            }

            if ($user_id == null) {
                throw new BadRequestException('Invalid user');
            }

            if (count($params) > 1)
            {
                $client_key = $params[1];
                $client_ip = $client->getIp();

                // close other client (fix avast antivirus block websocket)
                foreach($this->_clients as $cl) 
                {
                    $ip = $cl->getIp();
                    $port = $cl->getPort();
                    $key = $cl->getClientKey();
                    if ($ip == $client_ip && 
                        $key == $client_key) { 
                        $client->log("close blocked socket ip:" . $ip . " port:" . $port . " client_key:" . $client_key);
                        $cl->close();
                    }
                }

                $client->setClientKey($client_key);
            }

            $client->session("user_id", $user_id);
            $client->session("user_name", $u->user_name);
            $client->setLastTime();
		}

        public function onConnect($client)
        {
            $id = $client->getId();
            $this->_clients[$id] = $client;

            $user_id = $client->session('user_id');

            if (!isset($this->_users[$user_id]))
                $this->_users[$user_id] = array();
            $this->_users[$user_id][$id] = $client;

            $client->log("User Info user_id:" . $user_id . " user_name:" . $client->session('user_name') . " client_key:" . $client->getClientKey());
        }

        public function onDisconnect($client)
        {
            $user_id = $client->session('user_id');

            $client->log("Disconnect user_id:" . $user_id);
            $id = $client->getId();
            unset($this->_clients[$id]);

            if (isset($this->_users[$user_id])) {
                unset($this->_users[$user_id][$id]);
            }
        }

	    /**
	     * @see Wrench\Application.Application::onData()
	     */
	    public function onData($payload, $client)
	    {
            $decodedData = $this->_decodeData($payload->getPayload());
            if($decodedData === false)
            {
                // @todo: invalid request trigger error...
            }

            $data = $decodedData['data'];
            $event = $decodedData['event'];
            if ($event == 'alive') // check alive
                return;

            if (isset($data["key"]) && $event != 'ok') {
                // send confirm message received
                $user_id_from = $client->session('user_id');
                $key = $data["key"];
                $msg = array(
                    "key" => $key,
                    "event" => $event
                );

                $this->send_ok($msg, $client); //send data to self 

                if ($this->checkAlreadyReceiveMessage($key, $client))
                {
                    $this->log("Already receive message");
                    // if already received message, skip the message
                    return;
                }
            }

            $actionName = 'on' . ucfirst($event);
            if(method_exists($this, $actionName))
            {
                call_user_func(array($this, $actionName), $client, $data);
            }
	    }

        public function checkAlreadyReceiveMessage($key, $client)
        {
            $keys = $client->session('in_keys');
            if ($keys != null) {
                foreach($keys as $k)
                {
                    if ($k == $key)
                        return true;
                }

                if (count($keys) > 10) {
                    // keep past 10 messages only
                    array_splice($keys, 0, 1);
                }
            }
            else {
                $keys = array();
            }
            array_push($keys, $key);

            $client->session('in_keys', $keys);
        }
        
        private function onEcho($client, $text)
        {   
            /*
            $encodedData = $this->_encodeData('echo', $text);
            foreach($this->_clients as $sendto)
            {
                $sendto->send($encodedData);
            }
            */
        }

        private function onBot_message($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $to_id = $user_id_from;
            $home_id = $data["home_id"];

            $bot_mission = mission::get_bot($home_id);     
            if ($bot_mission == null) 
                return;
            $mission_id = $bot_mission->mission_id;

            $this->start();
            $tasks = home::bot_tasks($home_id, $user_id_from);
            $this->commit();

            if (count($tasks) > 0) {
                foreach($tasks as $task) { 
                    if ($task["task_end_expire"]) {
                        $content = '&&<;;i class="icon-bubbles"&&>;;&&<;;/i&&>;; &&<;;a href="#/chats/' . $task['mission_id'] . '" class="text- primary"&&>;;' . $task["mission_name"] . '&&<;;/a&&>;; / &&<;;i class="fa fa-check-square-o"&&>;;&&<;;/i&&>;; &&<;;span class="text- primary"&&>;;' . $task["task_name"] . '&&<;;/span&&>;;の期限&&<;;span class="badge badge-danger"&&>;;' . _date(strtotime($task["plan_end_date"])) . '&&<;;/span&&>;;が切れました。'; 
                    } 
                    else if ($task["task_start_expire"]) {
                        $content = '&&<;;i class="icon-bubbles"&&>;;&&<;;/i&&>;; &&<;;a href="#/chats/' . $task['mission_id'] . '" class="text- primary"&&>;;' . $task["mission_name"] . '&&<;;/a&&>;; / &&<;;i class="fa fa-check-square-o"&&>;;&&<;;/i&&>;; &&<;;span class="text- primary"&&>;;' . $task["task_name"] . '&&<;;/span&&>;;の開始日&&<;;span class="badge badge-warning"&&>;;' . _date(strtotime($task["plan_start_date"])) . '&&<;;/span&&>;;が切れました。'; 
                    }
                    else 
                        continue;

                    $this->start();
                    $cmsg = cmsg::message(null, $mission_id, BOT_USER_ID, $to_id, $content);
                    $this->commit();

                    if ($cmsg != null) {
                        //prepare data to be sent to client
                        $msg = array(
                            'inserted' => true,
                            'cmsg_id'=> $cmsg->cmsg_id,
                            'user_id'=> BOT_USER_ID, 
                            'user_name'=> BOT_USER_NAME,
                            'mission_id'=> $mission_id, 
                            'home_id'=> $home_id,
                            'content'=> $content,
                            'date'=> _google_datetime()
                        );

                        $user_ids = array($to_id);
                        
                        $this->send_message('chat_message', $msg, BOT_USER_ID, $user_ids); //send data to self and to
                    }
                }
            }
        }

        private function onChat_message($client, $data)
        {
            $client->setLastTime();

            $user_id_from = $client->session('user_id');    //sender id
            $cmsg_id = $data["cmsg_id"];
            if ($cmsg_id < 0) { // insert
                $temp_cmsg_id = $cmsg_id;
                $cmsg_id = null;
            }
            else { // edit
                $temp_cmsg_id = null;
            }

            $mission_id = $data["mission_id"];
            $home_id = isset($data["home_id"]) ? $data["home_id"] : null; //message text
            $cache_id = $data["cache_id"];
            $content = _cache_get($data["cache_id"]); //message text
            $is_file = isset($data["is_file"]) ? $data["is_file"] : null; //message text
            $to_id = isset($data["to_id"]) ? $data["to_id"] : null;
            $user_name = $client->session("user_name");
            $home_name = isset($data["home_name"]) ? $data["home_name"] : null;

            $mission = mission::getModel($mission_id);
            if ($mission == null)
            {
                $client->log("[Error] Invalid mission_id:" . $mission_id);
                break;
            }
            if ($home_id == null)
                $home_id = $mission->home_id;

            $this->start();
            $cmsg = cmsg::message($cmsg_id, $mission_id, $user_id_from, $to_id, $content, $cache_id);
            $this->commit();

            if ($cmsg != null) {
                $client->log("[Chat message] cache_id=" . $cache_id . " cmsg_id=" . $cmsg->cmsg_id);
                //prepare data to be sent to client
                $msg = array(
                    'inserted'=> $cmsg_id == null,
                    'cmsg_id'=> $cmsg->cmsg_id,
                    'temp_cmsg_id' => $temp_cmsg_id,
                    'user_id'=> $user_id_from, 
                    'user_name'=> $user_name,
                    'mission_id'=> $mission_id, 
                    'mission_name'=> $mission->mission_name,
                    'home_id'=> $home_id,
                    'home_name'=> $home_name,
                    'cache_id'=> $cache_id,
                    'reacts'=> $cmsg->reacts,
                    'is_file'=> $is_file,
                    'date'=> _google_datetime()
                );

                if ($to_id != null)
                    $user_ids = array($to_id);
                else
                    $user_ids = mission_member::user_ids($mission_id);
                
                $this->send_message('chat_message', $msg, $user_id_from, $user_ids, null, null, $content); //send data to self and to
            }
            else {
                $client->log("Could not set cmsg");
            }
        }

        private function onReact_message($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $cmsg_id = $data["cmsg_id"];
            $emoticon_id = $data["emoticon_id"];
            $mission_id = $data["mission_id"];
            $user_name = $client->session("user_name");

            $mission = mission::getModel($mission_id);
            if ($mission == null)
            {
                $client->log("[Error] Invalid mission_id:" . $mission_id);
                break;
            }
            $home_id = $mission->home_id;

            $client->log("[React message] ");

            $this->start();
            $cmsg = cmsg::react($cmsg_id, $emoticon_id, $user_id_from);
            $this->commit();

            if ($cmsg != null) {
                //prepare data to be sent to client
                $msg = array(
                    'cmsg_id'=> $cmsg->cmsg_id,
                    'user_id'=> $user_id_from, 
                    'user_name'=> $user_name,
                    'mission_id'=> $mission_id, 
                    'mission_name'=> $mission->mission_name,
                    'reacts'=> $cmsg->reacts,
                    'date'=> _google_datetime()
                );

                $user_ids = mission_member::user_ids($mission_id);
                
                $this->send_message('react_message', $msg, $user_id_from, $user_ids); //send data to self and to
            }
        }

        /*
        private function onChat_messages($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $home_id = $data["home_id"];
            $mission_id = $data["mission_id"];
            $prev_id = isset($data["prev_id"]) ? $data["prev_id"] : null;
            $next_id = isset($data["next_id"]) ? $data["next_id"] : null;
            $star = isset($data["star"]) ? $data["star"] : null;
            $limit = isset($data["limit"]) ? $data["limit"] : null;

            $messages = cmsg::messages($home_id, $mission_id, $user_id_from, $prev_id, $next_id, $star, $limit);

            //prepare data to be sent to client
            $msg = array(
                'messages' => $messages,
                'home_id' => $home_id,
                'mission_id' => $mission_id,
                'prev_id' => $prev_id,
                'next_id' => $next_id
            );

            $user_ids = array($user_id_from);
            
            $this->send_message('chat_messages', $msg, $user_id_from, $user_ids, null, $client);
        }
        */

        private function onRemove_message($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $cmsg_id = $data["cmsg_id"];
            $mission_id = $data["mission_id"];

            $client->log("[Remove message] cmsg_id:".$cmsg_id);

            $this->start();
            $err = cmsg::remove_message($cmsg_id);
            $this->commit();

            if ($err == ERR_OK) {
                $msg = array(
                    'cmsg_id' => $cmsg_id,
                    'mission_id'=> $mission_id
                );

                $user_ids = mission_member::user_ids($mission_id);
                
                $this->send_message('remove_message', $msg, $user_id_from, $user_ids); //send data to self and to
            }
        }

        private function onAlert($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $alert_type = $data["alert_type"];
            $user_id = $data["user_id"];
            $info = $data["info"];

            $client->log("[Alert message] alert_type:" . $alert_type . " user_id=" . $user_id);

            $msg = array(
                'alert_type' => $alert_type,
                'data'=> $data
            );

            if ($user_id != null)
                $user_ids = array($user_id);
            else if (isset($info["mission_id"]) && $info["mission_id"] != null)
                $user_ids = mission_member::user_ids($info["mission_id"]);
            else
                return;
            
            $this->send_message('alert', $msg, $user_id_from, $user_ids); //send data to self and to
        }

        private function onDevice_token($client, $data)
        {
            $client->session('device_type', $data["device_type"]);
            $client->session('device_token', $data["device_token"]);

            $client->log("[Device token] token:" . $data["device_token"]);

            //$this->disconnect_other_by_token($client);
        }

        private function onStatus($client, $data)
        {
            $client->session('status', $data["status"]);

            $client->log("[Status] :" . $data["status"]);
        }

        private function onTask($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $type = $data["type"];
            $task_id = $data["task_id"];
            $mission_id = $data["mission_id"];

            $client->log("[Task info] type:" . $type . " task_id=" . $task_id);

            $msg = array(
                'type' => $type,
                'task_id'=> $task_id,
                'mission_id'=> $mission_id
            );

            $user_ids = mission_member::user_ids($mission_id);
            
            $this->send_message('task', $msg, $user_id_from, $user_ids, $client);
        }

        private function onMission($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $type = $data["type"];
            $mission_id = $data["mission_id"];
            $home_id = $data["home_id"];

            $client->log("[Mission info] type:" . $type . " mission_id=" . $mission_id);

            $msg = array(
                'type' => $type,
                'mission_id'=> $mission_id,
                'home_id'=> $home_id
            );

            $user_ids = home_member::user_ids($home_id);
            
            $this->send_message('mission', $msg, $user_id_from, $user_ids, $client);
        }

        private function onHome($client, $data)
        {
            $user_id_from = $client->session('user_id');    //sender id
            $type = $data["type"];
            $home_id = $data["home_id"];

            $client->log("[Home info] type:" . $type . " home_id=" . $home_id );

            $msg = array(
                'type' => $type,
                'home_id'=> $home_id
            );

            $user_ids = home_member::user_ids($home_id);

            if ($type == "remove_member") {
                if (isset($data["user_id"]))
                    array_push($user_ids, $data["user_id"]);
            }
            
            $this->send_message('home', $msg, $user_id_from, $user_ids, $client);
        }

        private function send_ok($data, $client)
        {
            $encodedData = $this->_encodeData('ok', $data);
            $ret = $client->send($encodedData);
        }

        private function send_message($event, $data, $from_id, $to_ids = null, $ignore_client = null, $to_client = null, $content=null)
        {
            if($to_ids == null)
            {
                // do nothing   
            }
            else
            {
                $encodedData = $this->_encodeData($event, $data);
                foreach($to_ids as $to_id)
                {
                    $must_push = $event == "chat_message";

                    if ($must_push) {
                        $must_push = mission_member::is_push($data["mission_id"], $to_id, $content);
                        $data["push_flag"] = $must_push;
                        $encodedData = $this->_encodeData($event, $data);
                    } 

                    $clients = $this->get_clients_by_user_id($to_id);
                    foreach($clients as $key => $client)
                    {
                        if ($ignore_client != null && $client == $ignore_client)
                            continue;
                        if ($to_client != null && $client != $to_client)
                            continue;
                        $ret = $client->send($encodedData);
                        $client->setLastTime(); // set last time

                        if ($ret) {
                            if ($must_push)
                                push_token::set_last($to_id, $client->session('device_type'), $client->session('device_token'));
                        }
                    }

                    if ($must_push) {
                        if ($from_id != $to_id) {
                            $push_message = $data["user_name"] . "さんから";
                            if (isset($data["home_name"]) || isset($data["mission_name"]))
                                $push_message .= "「";
                            if (isset($data["home_name"]))
                                $push_message .= $data["home_name"];
                            if (isset($data["mission_name"]))
                                $push_message .= ">" . $data["mission_name"] ;
                            if (isset($data["home_name"]) || isset($data["mission_name"]))
                                $push_message .= "」で";
                            $push_message .= "メッセージが届きました。";
                            $tokens = push_token::must_push($to_id);
                            foreach($tokens as $token)
                            {
                                $this->log("Sending push to :" . $token["device_token"]);
                                push_msg::add_push($token["device_type"], $token["device_token"], $push_message, $data["mission_id"], $data["cmsg_id"]);
                                push_token::set_last($to_id, $token["device_type"], $token["device_token"]);
                            }
                        }
                    }
                }
            }
            return true;
        }

        public function start()
        {
            $db = db::getDB();
            $db->begin();
        }

        public function commit()
        {
            $db = db::getDB();
            $db->commit();
        }

        public function rollback()
        {
            $db = db::getDB();
            $db->rollback();
        }

        private function get_clients_by_user_id($user_id) {
            if (isset($this->_users[$user_id]))
                return $this->_users[$user_id];
            else
                return array();
        }

        public function log($message, $type = 'info')
        {
            echo date('Y-m-d H:i:s') . ' [' . ($type ? $type : 'error') . '] ' . $message . PHP_EOL;
        }

        private function disconnect_other_by_token($client)
        {
            $client_id = $client->getClientId();
            $device_token = $client->session('device_token');
            $device_type = $client->session('device_type');
            if ($device_token == null || $device_type == null)
                return;

            foreach($this->_clients as $cl) 
            {
                if ($cl->getClientId() != $client_id && 
                    $cl->session('device_type') == $device_type &&
                    $cl->session('device_token') == $device_token) { 
                    $cl->close();
                }
            }
        }
	}

    $protocol = "ws";
    if (CSERVER_SSL)
        $protocol .= "s";
    $uri = sprintf("%s://0.0.0.0:%d/", $protocol, CSERVER_PORT);
    $server = new \Wrench\Server($uri, array(
        'allowed_origins'            => array(
            'mysite.localhost'
        ),
        'check_origin'               => false,
        'connection_manager_class'   => 'Wrench\ConnectionManager',
        'connection_manager_options' => array(
            'timeout_select'           => 0,
            'timeout_select_microsec'  => 200000,
            'socket_master_class'      => 'Wrench\Socket\ServerSocket',
            'socket_master_options'    => array(
                'backlog'                => 1000,
                'ssl_cert_file'          => CSERVER_CERT_PEM,
                'ssl_passphrase'         => CSERVER_CERT_PASSPHRASE,
                'ssl_allow_self_signed'  => true,
                'timeout_accept'         => 5,
                'timeout_socket'         => 5,
            ),
            'connection_class'         => 'Wrench\Connection',
            'connection_options'       => array(
                'socket_class'           => 'Wrench\Socket\ServerClientSocket',
                'socket_options'         => array(),
                'connection_id_secret'   => 'asu5gj656h64Da(0crt8pud%^WAYWW$u76dwb',
                'connection_id_algo'     => 'md5'
            )
        )
    ));

	$server->registerApplication('chat', new ChatServer());
	$server->run();
