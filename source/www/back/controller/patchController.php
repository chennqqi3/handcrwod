<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class PatchController extends controller {
		public function __construct(){
			$this->_page_id = "patch";
			parent::__construct();	
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function index() {
			$patch = new patch;
			$this->must_patches = $patch->patch_info();
			return "none/patch_index";
		}

		public function patch_ajax() {
			$this->start();

			$patch = new patch;
			$err = $patch->patch();

			$this->finish(null, $err);
		}

		public function merge_member_mission() {
			$db = db::getDB();
			$sql = "SELECT mm.user_id, mm.opp_user_id
				FROM t_mission m
				LEFT JOIN t_mission_member mm ON m.mission_id=mm.mission_id
				WHERE m.private_flag=2 AND m.del_flag=0 AND mm.user_id < mm.opp_user_id
				GROUP BY mm.user_id, mm.opp_user_id
				HAVING COUNT(m.mission_id)>1";

			$member = new model;

			$err = $member->query($sql);
			while ($err == ERR_OK) {
				$user_id = $member->user_id;
				$opp_user_id = $member->opp_user_id;

				$mission = new mission;
				$sql = "SELECT m.mission_id FROM t_mission_member mm 
					LEFT JOIN t_mission m ON mm.mission_id=m.mission_id
					WHERE m.private_flag=2 AND m.del_flag=0 AND 
						mm.user_id=" . _sql($user_id) . " AND mm.opp_user_id=" . _sql($opp_user_id);

				$t_mission_id = null;
				$err = $mission->query($sql);
				while ($err == ERR_OK) {
					if ($t_mission_id == null) {
						$t_mission_id = $mission->mission_id;
					}
					else {
						$f_mission_id = $mission->mission_id;

						print "<br/>merge from:" . $f_mission_id . " to:" . $t_mission_id . "<br/>";

						$sql = "UPDATE t_cmsg SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_cunread SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_message SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_mission_attach SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_mission_category SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_mission_category SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_proclink SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "UPDATE t_task SET mission_id=" . _sql($t_mission_id) . " WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "DELETE FROM t_mission WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "DELETE FROM t_mission_member WHERE mission_id=" . _sql($f_mission_id) . ";";
						$sql .= "DELETE FROM t_mission_user WHERE mission_id=" . _sql($f_mission_id) . ";";

						$db->execute_batch($sql);

					}

					$err = $mission->fetch();
				}

				$err = $member->fetch();
			}

			exit;
		}
	}
?>