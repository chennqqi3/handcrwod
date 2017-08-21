<?php
	/*---------------------------------------------------
		Project Name:		HandCrowd
		Developement:       
		Author:				Ken
		Date:				2014/12/03
	---------------------------------------------------*/

	class google_event extends model 
	{
		public function __construct()
		{
			parent::__construct("t_google_event",
				"google_event_id",
				array(
					"user_id",
					"calendar_id",
					"event_id",
					"task_id"),
				array("auto_inc" => true));
		}

		public static function set_event($task_id, $fork = true)
		{
			$err = ERR_OK;
			if (!GOOGLE_ENABLE)
				return ERR_OK;
			
			$pid = $fork ? _fork() : -1;
			if ($pid == -1 || !$pid) { // -1: fork failure 0: child process
				$task = task::getModel($task_id);
				if ($task != null) {
					google_event::remove_event($task_id, false);

					$user_id = $task->performer_id;
					if ($user_id == null)
						return ERR_OK;

					$user = user::getModel($user_id);
					if ($user == null)
						return ERR_OK;

					$time_zone = $user->time_zone;

					$client = google_token::get_client($user_id);
					$calendar_id = google_token::get_calendar_id($user_id);

					if ($client == null) {
						_batch_log("Google Error : not connected google");
					}
					if ($calendar_id == null) {
						_batch_log("Google Error : not found calendar");	
					}

					if ($client != null && $calendar_id != null) {
						if ($task->plan_start_date != null || $task->plan_end_date != null) {
							try {
								$service = new Google_Service_Calendar($client);
								
								$mission = mission::getModel($task->mission_id);

								$event = new Google_Service_Calendar_Event();

								$summary = $task->task_name;
								if ($task->complete_flag == 1)
									$summary = "✔ " . $summary;
								$event->setSummary($summary);
								$start_time = null;
								$start_date = null;
								if ($task->plan_start_date != null) {
									if ($task->plan_start_time != null && $task->plan_end_time != null)
										$start_time = $task->plan_start_time;
									else
										$start_date = $task->plan_start_date;
								}
								else if ($task->plan_end_date != null) {
									if ($task->plan_end_time != null)
										$start_time = $task->plan_end_time;
									else
										$start_date = $task->plan_end_date;
								}

								$start = new Google_Service_Calendar_EventDateTime();
								if ($start_time != null)
									$start->setDateTime(_google_datetime(strtotime($start_time)));
								else
									$start->setDate(_google_date(strtotime($start_date)));
								$start->setTimeZone($time_zone);
								$event->setStart($start);

								$end_time = null;
								$end_date = null;
								if ($task->plan_end_date != null) {
									if ($task->plan_start_time != null && $task->plan_end_time != null)
										$end_time = _google_datetime(strtotime($task->plan_end_time));
									else
										$end_date = _google_date(strtotime($task->plan_end_date) + 3600 * 24);
								}
								else {
									if ($start_time != null)
										$end_time = _google_datetime(strtotime($start_time));
									else
										$end_date = _google_date(strtotime($start_date));
								}
								$end = new Google_Service_Calendar_EventDateTime();
								if ($end_time != null)
									$end->setDateTime($end_time);
								else
									$end->setDate($end_date);
								$end->setTimeZone($time_zone);
								$event->setEnd($end);

								if ($mission != null) {
									$event->setDescription('# ' . $mission->mission_name);
								}
								$createdEvent = $service->events->insert($calendar_id, $event);
							}
							catch (Google_Service_Exception $e)
							{
								_batch_log("Google Error : " . $e->getMessage());
								$createdEvent = null;
							}

							if ($createdEvent != null) {
								$google_event = new google_event;
								$google_event->user_id = _user_id();
								$google_event->calendar_id = $calendar_id;
								$google_event->event_id = $createdEvent->getId();
								$google_event->task_id = $task_id;
					
								$err = $google_event->save();
							}
						}
					}
				}

				if (!$pid) { // child process
					exit;
				}
			}

			return $err;
		}

		public static function get_event($task_id) {
			$google_event = new google_event;

			$err = $google_event->select("task_id=" . _sql($task_id));
			if ($err == ERR_OK)
				return $google_event;
			else
				return null;
		}

		public static function remove_event($task_id, $fork = true) {
			if (!GOOGLE_ENABLE)
				return ERR_OK;

			$pid = $fork ? _fork() : -1;
			if ($pid == -1 || !$pid) { // -1: fork failure 0: child process
				$google_event = new google_event;

				$err = $google_event->select("task_id=" . _sql($task_id));
				while($err == ERR_OK) {
					// delete event
					$client = google_token::get_client($google_event->user_id);
					if ($client != null) {
						try {
							$service = new Google_Service_Calendar($client);

							$service->events->delete($google_event->calendar_id, $google_event->event_id);

							$google_event->remove(true);
						}
						catch (Google_Service_Exception $e)
						{
							_batch_log("Google Error : " . $e->getMessage());
						}
					}

					$err = $google_event->fetch();
				}

				if (!$pid) { // child process
					exit;
				}
			}

			return ERR_OK;
		}

	};
?>