<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class sqlHelper {
		static public function joinAnd($sqls) {
			return join(" AND ", $sqls);
		}
		static public function joinOr($sqls) {
			return join(" OR ", $sqls);
		}
		static public function joinSQL($sqls, $op = "AND") {
			if ($sqls == null || count($sqls) == 0)
				return "";
			$sql = $sqls[0];
			for ($i = 1; $i < count($sqls); $i ++) {
				$sql .= " " . $op . " " . $sqls[$i];
			}
			return $sql;
		}
	}

?>