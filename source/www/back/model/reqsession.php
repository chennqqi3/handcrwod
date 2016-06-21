<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class reqsession
	{
		private $_props;
		private $session_prefix;
		private $_viewHelper;

		public function __construct($session_prefix)
		{
			$this->session_prefix = $session_prefix;

			$this->_viewHelper = new viewHelper($this);
			$this->_props = array();
		}

		public function __get($prop) {
			global $_REQUEST;

			if ($prop == "props")
				return $this->_props;
			else {
				if (!array_key_exists($prop, $this->_props)) {
					$val = isset($_REQUEST[$prop]) ? $_REQUEST[$prop] : null;
					if (is_array($val)) {
						$a = 0;
						foreach($val as $v)
							$a |= $v;
						_session($this->session_prefix . $prop, $a . "");
					}
					else if ($val !== null) {
						_session($this->session_prefix . $prop, $val);
					}
					else if (array_key_exists($prop, $_REQUEST))
						_session($this->session_prefix . $prop, null);
					$this->_props[$prop] = _session($this->session_prefix . $prop);
				}

				return $this->_props[$prop];
			}
		}

		public function __set($prop, $val) {
			$this->_props[$prop] = $val;
		}

		public function __call($method, $params) {
			if (method_exists($this->_viewHelper, $method)) {
				call_user_func_array(array($this->_viewHelper, $method), $params);
			}
		}
	};
?>