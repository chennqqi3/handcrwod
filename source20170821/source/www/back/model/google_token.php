<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/12/03
	---------------------------------------------------*/

	class google_token extends model 
	{
		public function __construct()
		{
			parent::__construct("t_google_token",
				"user_id",
				array(
					"token",
					"calendar_id"),
				array("auto_inc" => false));
		}

		public static function getModel($pkvals, $ignore_del_flag=false)
		{
			$model = new static;
			$err = $model->get($pkvals, $ignore_del_flag);
			if ($err == ERR_OK)
				return $model;

			if (is_string($pkvals)) {
				$err = $model->select("user_id = " . _sql($pkvals));
				if ($err == ERR_OK)
					return $model;
			}

			return null;
		}

		public static function get_token($user_id)
		{
			$model = new static;

			$err = $model->select("user_id = " . _sql($user_id));
			if ($err == ERR_OK) {
				$token = $model->token;
				return $token;
			}
			else {
				return null;
			}
		}

		public static function set_token($user_id, $token)
		{
			$model = new static;

			$err = $model->select("user_id = " . _sql($user_id));
			if ($err == ERR_OK) {
				$model->token = $token;
				$err = $model->update();
			}
			else {
				$model->user_id = $user_id;
				$model->token = $token;
				$err = $model->insert();
			}

			return $err;
		}

		public static function get_calendar_id($user_id)
		{
			$model = new static;

			$err = $model->select("user_id = " . _sql($user_id));
			if ($err == ERR_OK) {
				return $model->calendar_id;
			}
			else {
				return null;
			}
		}

		public static function set_calendar_id($user_id, $calendar_id)
		{
			$model = new static;

			$err = $model->select("user_id = " . _sql($user_id));
			if ($err == ERR_OK) {
				$model->calendar_id = $calendar_id;
				$err = $model->update();
			}

			return $err;
		}

		public static function remove_token($user_id)
		{
			$model = new static;
			$err = $model->remove_where("user_id = " . _sql($user_id), true);
			return $err;
		}

		public static function get_client($user_id = null)
		{
			if (!GOOGLE_ENABLE)
				return null;

			try {
				$client = new Google_Client();
				$client->setClientId(GOOGLE_CLIENT_ID);
				$client->setClientSecret(GOOGLE_CLIENT_SECRET);
				$client->setRedirectUri(_url("google/connect_finish"));
				$client->setAccessType('offline');
				$client->setApprovalPrompt('force');
				$client->addScope("https://www.googleapis.com/auth/calendar");

				if ($user_id != null) {
					$token = self::get_token($user_id);

					if ($token != null) {
						$client->setAccessToken($token);
						if ($client->isAccessTokenExpired()) {
							$refreshToken= json_decode($token)->refresh_token;
							$client->refreshToken($refreshToken);
							$token=$client->getAccessToken();
							$client->setAccessToken($token);

							self::set_token($user_id, $token);
						}
					}
					else {
						return null;
					}
				}
			}
			catch (Exception $e)
			{
				_batch_log("Google Error : " . $e->getMessage());
				if ($e->getCode() == 401) {
					// refreshing the OAuth2 token unauthorized_client
					if ($user_id != null) {
						self::remove_token($user_id);
					}
				}
				return null;
			}
				
			return $client;
		}

		public static function create_handcrowd_calendar($user_id)
		{
			if (!GOOGLE_ENABLE)
				return;

			$client = self::get_client($user_id);
			$user = user::getModel($user_id);
			$time_zone = $user != null ? $user->time_zone : TIME_ZONE;

			if ($client != null) {
				$service = new Google_Service_Calendar($client);

				$calendar_id = google_token::get_calendar_id();
				if ($calendar_id == null) {
					try {
						$calendar = new Google_Service_Calendar_Calendar();
						$calendar->setSummary('Handcrowd');
						$calendar->setTimeZone($time_zone);

						$createdCalendar = $service->calendars->insert($calendar);

						google_token::set_calendar_id($user_id, $createdCalendar->getId());

						self::export_events($user_id);
					}
					catch (Google_Service_Exception $e)
					{
						_debug_log("Google Error : Create failed calendar " . $e->getMessage());
					}
				}
			}
		}

		public static function export_events($user_id)
		{
			if (_is_empty($user_id))
				return;

			$task = new task;

			$err = $task->select("performer_id=" . _sql($user_id) . " AND (plan_start_date>NOW() OR plan_end_date>NOW())");

			while ($err == ERR_OK) {
				google_batch::add_event($task->task_id, false);
				$err = $task->fetch();
			}
		}

		public static function delete_handcrowd_calendar($user_id)
		{
			if (!GOOGLE_ENABLE)
				return;

			$client = self::get_client($user_id);

			if ($client != null) {
				$service = new Google_Service_Calendar($client);

				$calendar_id = google_token::get_calendar_id($user_id);
				if ($calendar_id != null) {
					try {
						$service->calendars->delete($calendar_id);

						google_token::set_calendar_id($user_id, null);
					}
					catch (Google_Service_Exception $e)
					{
					}
				}
			}
		}
	};
?>