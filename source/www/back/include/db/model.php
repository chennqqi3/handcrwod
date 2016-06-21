<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class model {
		private $_db;
		private $_table_name;
		private $_pkeys;
		private $_fields;

		private $_props;

		// options
		private $_auto_inc;

		private $sql_result;

		private $_viewHelper;

		function __construct($tname = null, $pkeys = null, $fields = null, $options=null) {
			$this->_table_name = $tname;
			$this->_pkeys = is_array($pkeys) ? $pkeys : array($pkeys);
			$this->_fields = is_array($fields) ? $fields : array($fields);

			if (is_array($options)) {
				$this->_auto_inc = $options["auto_inc"] == true ? true : false;
			}

			$this->_db = db::getDB();

			$this->_viewHelper = new viewHelper($this);

			$this->initProps();
		}

		function __clone() {
			$this->_db = db::getDB();
			$this->_viewHelper = new viewHelper($this);
		}

		private function initProps()
		{
			$this->_props = array();

			if ($this->_table_name != null) {
				// single table mode
				foreach($this->_pkeys as $f)
				{
					$this->_props[$f] = null;
				}

				foreach($this->_fields as $f)
				{
					$this->_props[$f] = null;
				}

				$this->_fields = array_merge($this->_fields, array("create_time", "update_time", "del_flag"));

				$this->_props["create_time"] = null;
				$this->_props["update_time"] = null;
				$this->_props["del_flag"] = null;
			}
			else {
				// table join mode
			}
		}

		public function __get($prop) {
			if ($prop == "table")
				return $this->_table_name;
			else if ($prop == "props")
				return $this->_props;
			else if ($prop == "db")
				return $this->_db;
			else
			{
				return isset($this->_props[$prop]) ? $this->_props[$prop] : null;
			}
		}

		public function __set($prop, $val) {
			if ($prop == "props") {
				if (is_array($val))
					$this->_props = $val;
			}
			else {
				$this->_props[$prop] = $val;
			}
		}

		public function __call($method, $params) {
			if (method_exists($this->_viewHelper, $method)) {
				call_user_func_array(array($this->_viewHelper, $method), $params);
			}
		}

		public function validatePkey()
		{
			foreach ($this->_pkeys as $field_name)
			{
				if ($this->_props[$field_name] == null)
					return false;
				else
					return true;
			}
		}

		public function insert()
		{
			// single table mode
			if (!$this->_auto_inc) {
				if (!$this->validatePkey())
					return ERR_INVALID_PKEY;
			}
			
			$this->_props["create_time"] = "##NOW()";
			$this->_props["update_time"] = "##NOW()";
			$this->_props["del_flag"] = 0;

			$sql = "INSERT INTO " . $this->table . "(";

			$fields = "";
			if (!$this->_auto_inc) {
				foreach ($this->_pkeys as $field_name)
				{
					if ($fields != "") $fields .= ",";
					$fields .= $field_name;
				}
			}
			foreach ($this->_fields as $field_name)
			{
				if ($fields != "") $fields .= ",";
				$fields .= $field_name;
			}

			$sql .= $fields . ") VALUES(";

			$vals = "";
			if (!$this->_auto_inc) {
				foreach ($this->_pkeys as $field_name)
				{
					if ($vals != "") $vals .= ",";
					$vals .= _sql($this->_props[$field_name]);
				}
			}
			foreach ($this->_fields as $field_name)
			{
				if ($vals != "") $vals .= ",";
				$vals .= _sql($this->_props[$field_name]);
			}

			$sql .= $vals . ");";

			$err = $this->_db->execute($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			if ($this->_auto_inc) {
				$pkey = $this->_pkeys[0];
				$this->$pkey = $this->last_id();
			}

			return $err;
		}

		public function update()
		{
			// single table mode
			if (!$this->validatePkey())
				return ERR_INVALID_PKEY;

			$this->_props["update_time"] = "##NOW()";

			$sql = "UPDATE " . $this->table . " SET ";

			$sub = "";
			foreach ($this->_fields as $field_name)
			{
				if ($sub != "")
					$sub .= ",";
				$sub .= $field_name . "=" . _sql($this->_props[$field_name]) . " ";
			}

			$sql .=  $sub . " WHERE ";

			$where = "";
			foreach ($this->_pkeys as $field_name)
			{
				if ($where != "")
					$where .= " AND ";
				$where .= $field_name . "=" . _sql($this->_props[$field_name]) . " ";
			}

			$sql .= $where;

			$err = $this->_db->execute($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			return $err;
		}

		public function save() 
		{
			if (!$this->validatePkey())
				return $this->insert();
			else 
				return $this->update();
		}

		public static function removeModel($pkvals, $permanent=false)
		{
			$model = static::getModel($pkvals);
			if ($model != null) {
				return $model->remove($permanent);
			}

			return ERR_OK;
		}

		public function remove($permanent=false)
		{
			// single table mode
			if (!$this->validatePkey())
				return ERR_INVALID_PKEY;

			if (!$permanent) {
				$sql = "UPDATE " . $this->table . " SET ";
				$sql .= "del_flag = 1, ";
				$sql .= "update_time=now() WHERE ";
			}
			else {
				$sql = "DELETE FROM " . $this->table . " WHERE ";
			}

			$where = "";
			foreach ($this->_pkeys as $field_name)
			{
				if ($where != "")
					$where .= " AND ";
				$where .= $field_name . "=" . _sql($this->_props[$field_name]) . " ";
			}

			$sql .= $where;

			$err = $this->_db->execute($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			return $err;
		}

		public function remove_where($where, $permanent=false)
		{
			// single table mode
			if (!$permanent) {
				$sql = "UPDATE " . $this->table . " SET del_flag = 1, update_time=now() WHERE " . $where;
			}
			else {
				$sql = "DELETE FROM " . $this->table . " WHERE " . $where;
			}

			$err = $this->_db->execute($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			return $err;
		}

		public static function getModel($pkvals, $ignore_del_flag=false)
		{
			$model = new static;
			$err = $model->get($pkvals, $ignore_del_flag);
			if ($err == ERR_OK)
				return $model;

			return null;
		}

		public function get($pkvals, $ignore_del_flag=false)
		{
			if (!is_array($pkvals))
				$pkvals = array($pkvals);

			if (count($pkvals) != count($this->_pkeys))
				return ERR_INVALID_PKEY;

			foreach($pkvals as $pkval)
			{
				if ($pkval == null)
					return ERR_INVALID_PKEY;
			}

			$where = "";
			if (!$ignore_del_flag)
				$where = "del_flag=0";

			$cnt = count($pkvals);
			for ($i = 0; $i < $cnt; $i ++)
			{
				if ($where != "")
					$where .= " AND ";
				$where .= $this->_pkeys[$i] . "=" . _sql($pkvals[$i]) . " ";
			}

			$sql = "SELECT * FROM " . $this->table . " WHERE " . $where;


			$err = $this->_db->query($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			if ($err != ERR_OK)
				return $err;

			$this->sql_result = $this->_db->sql_result;
			$row = $this->_db->fetch_array($this->sql_result);

			if (!$row)
				return ERR_NODATA;

			foreach ($this->_props as $field_name => $val)
			{
				if (is_string($field_name)) {
					$this->_props[$field_name] = $row[$field_name];
				}
			}

			return $err;
		}

		public static function countsModel($where="", $options=null, $ignore_del_flag=false)
		{
			$model = new static;
			return $model->counts($where, $options, $ignore_del_flag);
		}

		public function counts($where="", $options=null, $ignore_del_flag=false)
		{
			// single table mode
			if (!$ignore_del_flag) {
				if ($where != "")
					$where .= " AND ";
				$where .= "del_flag=0";
			}

			$sql = "SELECT COUNT(*) FROM " . $this->table . " WHERE " . $where;

			return $this->_db->scalar($sql);
		}

		public function select($where, $options=null, $ignore_del_flag=false)
		{
			// single table mode
			if (!$ignore_del_flag) {
				if ($where != "")
					$where = "(" . $where . ") AND ";
				$where .= "del_flag=0";
			}

			$sql = "SELECT * FROM " . $this->table;
			if ($where != "")
				$sql .= " WHERE " . $where;
			if ($options != null) {
				if (!_is_empty($options["order"]))
					$sql .= " ORDER BY " . $options["order"];
				if (!_is_empty($options["limit"]) && $options["limit"] >= 0) {
					$sql .= " LIMIT " . $options["limit"];

					if (!_is_empty($options["offset"]) && $options["offset"] >= 0)
						$sql .= " OFFSET " . $options["offset"];
				}
			}

			$err = $this->_db->query($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			if ($err != ERR_OK)
				return $err;

			$this->sql_result = $this->_db->sql_result;
			$row = $this->_db->fetch_array($this->sql_result);

			if (!$row)
				return ERR_NODATA;

			foreach ($this->_props as $field_name => $val)
			{
				if (is_string($field_name)) {
					$this->_props[$field_name] = $row[$field_name];
				}
			}

			return $err;
		}

		public function fetch()
		{
			// single table mode
			$err = ERR_OK;

			$row = $this->_db->fetch_array($this->sql_result);

			if (!$row)
				return ERR_NODATA;

			foreach ($this->_props as $field_name => $val)
			{
				if (is_string($field_name)) {
					$this->_props[$field_name] = $row[$field_name];
				}
			}

			return $err;
		}

		public function query($sql, $options=null)
		{
			// table join mode
			if ($options != null) {
				if (!_is_empty($options["where"]))
					$sql .= " WHERE " . $options["where"];
				if (!_is_empty($options["group"]))
					$sql .= " GROUP BY " . $options["group"];
				if (!_is_empty($options["order"]))
					$sql .= " ORDER BY " . $options["order"];
				if (!_is_empty($options["limit"]) && $options["limit"] >= 0) {
					$sql .= " LIMIT " . $options["limit"];

					if (!_is_empty($options["offset"]) && $options["offset"] >= 0)
						$sql .= " OFFSET " . $options["offset"];
				}
			}
			$err = $this->_db->query($sql);

			if (LOG_MODE && $err != ERR_OK)
				model::printSQLError($sql);

			if ($err != ERR_OK)
				return $err;

			$this->sql_result = $this->_db->sql_result;
			$row = $this->_db->fetch_array($this->sql_result);

			if (!$row)
				return ERR_NODATA;

			foreach ($row as $field_name => $val)
			{
				if (is_string($field_name)) {
					$this->_props[$field_name] = $row[$field_name];
				}
			}

			return $err;
		}

		public function scalar($sql, $options=null)
		{
			// table join mode
			if ($options != null) {
				if (!_is_empty($options["where"]))
					$sql .= " WHERE " . $options["where"];
				if (!_is_empty($options["group"]))
					$sql .= " GROUP BY " . $options["group"];
				if (!_is_empty($options["order"]))
					$sql .= " ORDER BY " . $options["order"];
				if (!_is_empty($options["limit"]) && $options["limit"] >= 0) {
					$sql .= " LIMIT " . $options["limit"];

					if (!_is_empty($options["offset"]) && $options["offset"] >= 0)
						$sql .= " OFFSET " . $options["offset"];
				}
			}
			return $this->_db->scalar($sql);
		}

		public function save_session($session_name)
		{
			_session($session_name, $this->_props);
		}

		public function load_session($session_name)
		{
			$this->_props = _session($session_name);
		}

		public function load($load_object, $ignores = array())
		{
			foreach ($this->_props as $field_name => $val)
			{
				if ($load_object->existProp($field_name) && !in_array($field_name, $ignores)) {
					if (is_array($load_object->$field_name)) {
						$this->$field_name = 0;
						foreach($load_object->$field_name as $v)
							$this->$field_name |= $v;
						// ',' join string
						$field_name_joined = $field_name . "_joined";
						$this->$field_name_joined = join(",", $load_object->$field_name);
					}
					else {
						$this->$field_name = $load_object->$field_name;
					}
				}
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

		public function is_exist_table()
		{
			return $this->_db->is_exist_table($this->_table_name);
		}

		public function last_id() 
		{
			return $this->_db->last_id();
		}

		static function printSQLError($sql)
		{
			global $g_err_msg;
			$g_err_msg = "SQL:$sql\nError Detail:"  . mysql_error();

			_err_log($g_err_msg);
		}
	};
?>