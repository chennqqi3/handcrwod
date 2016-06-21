<?php

namespace WebSocket\Application;

/**
 * WebSocket Server Application
 * 
 * @author Nico Kaiser <nico@kaiser.me>
 */
abstract class Application
{
    protected static $instances = array();
    
    /**
     * Singleton 
     */
    protected function __construct() { }

    final private function __clone() { }
    
    final public static function getInstance()
    {
        $calledClassName = get_called_class();
        if (!isset(self::$instances[$calledClassName])) {
            self::$instances[$calledClassName] = new $calledClassName();
        }

        return self::$instances[$calledClassName];
    }

    abstract public function onConnecting($connection, $params);

    abstract public function onConnect($connection);

	abstract public function onDisconnect($connection);

	abstract public function onData($data, $client);

	// Common methods:
	
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
}