<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/11/01
	---------------------------------------------------*/

	class BatchController extends controller {
		private $alerts;
		private $start_time;

		public function __construct(){
			parent::__construct();	

			$this->alerts = array();

			$this->where = "";

			$this->dialog_mode = false;
		}

		public function checkPriv($action, $utype)
		{
			parent::checkPriv($action, UTYPE_NONE);
		}

		public function index() {
			try {
				$this->start_time = time();
				//set_time_limit(BATCH_TIME_LIMIT);

				//$this->update_priority();

				$this->update_google_calendar();

				$this->mission_repeat();

				//$this->send_unread();

				/*
				$this->send_alarm();
				*/
			} catch (Exception $e) {
				_batch_log("バッチエラー" . $e->getMessage());
			}

			$this->finish(null, ERR_OK);
		}

		public function check_time() {
			if (time() - $this->start_time > BATCH_TIME_LIMIT)
			{
				_batch_log("バッチタイムアウト終了 開始時刻=" . _datetime($this->start_time) . " 終了時刻=" . _datetime());
				$this->finish(null, ERR_BATCH_TIMEOUT);
			}
		}

		public function update_google_calendar()
		{
			$db = db::getDB();

			$google_batch = new google_batch;

			$err = $google_batch->select("", array("limit" => 100));
			
			while($err == ERR_OK)
			{
				$task_id = $google_batch->task_id;
				_batch_log("Googleカレンダー更新 task_id=" . $task_id);

				$this->start();

				$db->query("DELETE FROM t_google_batch WHERE task_id=" . _sql($task_id));

				$this->commit();

				if ($google_batch->update_type == 0)
					google_event::set_event($task_id);
				else
					google_event::remove_event($task_id);

				$err = $google_batch->fetch();

				$this->check_time();
			}
		}

		private function update_priority()
		{
			$task = new task;
			$today = _date();
			$err = $task->query("SELECT t.*, tu.user_id task_user_id FROM t_task t LEFT JOIN t_task_user tu ON t.task_id=tu.task_id AND 
				(t.user_id=tu.user_id OR t.performer_id=tu.user_id) 
				WHERE t.complete_flag=0 AND tu.priority IS NULL AND
					(t.plan_start_date IS NOT NULL AND " . _sql_date() . " >= t.plan_start_date OR
					t.plan_end_date IS NOT NULL AND  " . _sql_date() . " >= t.plan_end_date)");

			while($err == ERR_OK) {
				if ($task->task_user_id == null) {
					$task->update_priority($task->user_id);
					$task->update_priority($task->performer_id);
				}
				else {
					$task->update_priority($task->task_user_id);
				}
				$err = $task->fetch();

				$this->check_time();
			}
		}

		public function send_unread()
		{
			$cunread = new cunread;

			$sql = "SELECT c.*, m.mission_name, u.user_name, u.email 
				FROM t_cunread c
				INNER JOIN t_mission m ON c.mission_id=m.mission_id
				INNER JOIN m_user u ON c.user_id=u.user_id
				WHERE c.mail_flag IS NULL AND TIME_TO_SEC(TIMEDIFF(NOW(), c.create_time)) > " . UNREAD_LIMIT . " AND
					m.del_flag=0 AND u.del_flag=0 
				ORDER BY u.user_id, m.mission_id, c.cunread_id";

			$err = $cunread->query($sql);

			if ($err == ERR_NODATA)
				return;

			$user_id = -1;
			$mission_id = -1;
			$mission_urls = "";
			while ($err == ERR_OK)
			{
				$this->start();

				if ($mission_id != $cunread->mission_id)
				{
					$mission_urls .= $cunread->mission_name . "\n";
					$mission_urls .= "URL：" . SITE_ORIGIN . "/app/#/chats/" . $cunread->mission_id . "\n";
					$mission_id = $cunread->mission_id;
				}

				if ($user_id != $cunread->user_id)
				{
					$this->send_unread_mail($cunread, $mission_urls);

					$user_id = $cunread->user_id;
					$mission_id = -1;
					$mission_urls = "";
				}

				$cunread->mail_flag = 1;
				$cunread->save();

				$this->commit();

				$err = $cunread->fetch();

				if ($err == ERR_NODATA) {
					$this->send_unread_mail($cunread, $mission_urls);
				}

				$this->check_time();
			}
		}

		private function send_unread_mail($cunread, $mission_urls)
		{
			$title = "【ハンドクラウド】未読メッセージがあります";

			$body = $cunread->user_name . "様\n\n";
			$body .= MAIL_HEADER;
			$body .= $cunread->user_name . "様の未読メッセージがあるチャットをお知らせします\n\n";
			$body .= $mission_urls;
			$body .= MAIL_FOOTER;

			if (!_is_empty($cunread->email)) {
				_send_mail($cunread->email, $cunread->user_name, $title, $body);

				_batch_log("メール送信 送信先=" . $cunread->email . " タイトル=" . $title);
			}
		}

		private function send_alarm()
		{
			$user = new user;
			$err = $user->select("alarm_mail_flag=1");

			$count = 0;

			while($err == ERR_OK)
			{
				$db = db::getDB();
				$db->set_time_zone(_time_zone());

				$now = $db->scalar("SELECT NOW()");
				$alarm_time = _date() . " " . str_pad($user->alarm_time, 2, "0", STR_PAD_LEFT) . ":00:00";

				if ($alarm_time < $now) {
					$alarm = new alarm;
					$err = $alarm->select("alarm_time=" . _sql($alarm_time) . " AND user_id=" . _sql($user->user_id));
					if ($err == ERR_NODATA || $alarm->alarm_flag == 0) {
						$tasks = "";
						$task = new task;
						$sql = "SELECT t.task_name, m.mission_name FROM t_task t LEFT JOIN t_mission m ON t.mission_id=m.mission_id 
							WHERE t.performer_id=" . _sql($user->user_id) . " AND t.complete_flag=0 AND t.del_flag=0 AND m.del_flag=0 AND m.complete_flag=0";
						$err = $task->query($sql, array("order" => "m.mission_id ASC, task_id ASC"));
						while($err == ERR_OK) {
							$tasks .= "[" . $task->mission_name . "]" . $task->task_name . "\n";
							$err = $task->fetch();
						}
						
						if ($tasks != "") {
							$title = "割り当てられているタスクの通知";
							$body = $user->user_name . "様 \n";
							$body .= MAIL_HEADER;
							$body .= "あなたに現在割り当てられているタスクは以下の通りです。\n";
							$body .= $tasks;
							$body .= "\nシステムにログインしてご確認お願いします。\n";
							$body .= "URL：" . SITE_ORIGIN . "/app\n";
							$body .= MAIL_FOOTER;

							if (!_is_empty($user->email)) {
								// スパムメール対策
								// _send_mail($user->email, $user->user_name, $title, $body);

								_batch_log("メール送信 送信先=" . $user->email . " タイトル=" . $title);
							}
						}

						$this->start();

						$alarm->user_id = $user->user_id;
						$alarm->alarm_time = $alarm_time;
						$alarm->alarm_flag = 1;

						$err = $alarm->save();

						$this->commit();

						$count ++;
						if ($count > 500)
							return;
					}
				}

				$err = $user->fetch();

				$this->check_time();
			}
		}

		public function mission_repeat() {
			$today = getdate();
			$weekday = $today["wday"];
			$mday = $today["mday"];
			$mon = $today["mon"];
			$date = $mon . "-" . $mday;

			$mission = new mission;
			$task = new task;

			$err = $mission->select("repeat_type > 0 AND complete_flag=0");

			while ($err == ERR_OK)
			{
				switch ($mission->repeat_type) {
					case REPEAT_EVERYDAY:
						$repeat = true;
						break;
					case REPEAT_WORKDAY:
						$repeat = ($weekday >= 1 && $weekday <= 6) ? true : false;
						break;
					case REPEAT_WEEK:
						$repeat = $weekday == $mission->repeat_day ? true : false;
						break;
					case REPEAT_MONTH:
						$repeat = $mday == $mission->repeat_day ? true : false;
						break;
					case REPEAT_YEAR:
						$repeat = $date == $mission->repeat_day ? true : false;
						break;
					default:
						break;
				}

				if ($repeat) {
					$err = $task->select("mission_id=" . _sql($mission->mission_id) . " 
						AND complete_flag=1 AND complete_time < " .  _sql_date());

					while ($err == ERR_OK) {
						$this->start();

						$task->complete_flag = 0;
						$task->save();

						$task_user = task_user::getByTaskAndUserId($task->task_id, $task->user_id);
						$task_user->priority = 1;
						$task_user->save();

						$task_user = task_user::getByTaskAndUserId($task->task_id, $task->performer_id);
						$task_user->priority = 1;
						$task_user->save();

						$this->commit();

						$err = $task->fetch();
					}

				}

				$err = $mission->fetch();

				$this->check_time();
			}
		}

		public function install() {
			_install_batch();
			exit;
		}

		public function uninstall() {
			_uninstall_batch();
			exit;
		}

		public function run() {
			_run_batch();
			exit;
		}
	}
?>