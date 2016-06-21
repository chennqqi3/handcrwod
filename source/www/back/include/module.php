<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class module {
		public $_props;

		public function __construct(){
		}

		public function __get($prop) {
			if ($prop == "props") {
				return $this->_props;
			}
			else {
				return $this->_props[$prop];
			}
		}

		public function __set($prop, $val) {
			$this->_props[$prop] = $val;
		}

		public function __call($method, $params) {
			global $curController;

			if (method_exists($curController, $method)) {
				call_user_func_array(array($curController, $method), $params);
			}
		}

		public function existProp($prop)
		{
			$keys = array_keys($this->_props);
			foreach($keys as $key)
			{
				if ($key == $prop)
					return true;
			}
			return false;
		}

		static public function show()
		{
			$count = func_num_args();
			$params = array();

			if ($count == 0) {
				$action = "action";
			}
			else {
				for ($i = 0; $i < $count; $i ++) {
					if ($i == 0)
						$action = func_get_arg($i);
					else
						$params[] = func_get_arg($i);
				}
			}

			$module = new static;
			$module_name = str_replace("Module", "", get_class($module));
			$module->_view = call_user_func_array(array($module, $action), $params);
			if ($module->_view == null) {
				if ($action == "action")
					$module->_view = _template("module/" . $module_name . ".php");
				else 
					$module->_view = _template("module/" . $module_name . "_" . $action . ".php");
			}

			$module->include_view();
		}

		public function include_view()
		{
			if (file_exists($this->_view)) {
				include($this->_view);

				$this->addmodview($this->_view);
			}
		}
	}
?>