<?php

	class If_So_Recurrence {
		
		private $trigger_id;
		private $rule;
		private $type;
		private $index;
		private $recurrence_trigger_types;
		private $trigger_types_not_requires_license;

		public function __construct($recurrence_trigger_types, $trigger_types_not_requires_license) {
			$this->recurrence_trigger_types = $recurrence_trigger_types;
			$this->trigger_types_not_requires_license = $trigger_types_not_requires_license;
		}

		private function _is_rule_requires_license($rule) {
			$trigger_type = $rule['trigger_type'];

			return (!in_array($trigger_type, $this->trigger_types_not_requires_license) ||
					($trigger_type == 'User-Behavior' && !in_array($rule['User-Behavior'], array('LoggedIn', 'LoggedOut', 'Logged'))));
		}

		private function _get_seconds_from_units($unit_type, $unit_value) {
			$MINUTE_IN_SECONDS = 60; // 60 sec
			$DAY_IN_SECONDS = (60*60*24);
			$WEEK_IN_SECONDS = $DAY_IN_SECONDS*7;
			$MONTH_IN_SECONDS = $WEEK_IN_SECONDS*4;

			if ($unit_type == 'day') {
				return $unit_value * $DAY_IN_SECONDS;
			} else if ($unit_type == 'week') {
				return $unit_value * $WEEK_IN_SECONDS;
			} else if ($unit_type == 'month') {
				return $unit_value * $MONTH_IN_SECONDS;
			} else if ($unit_type == 'minute') {
				return $unit_value * $MINUTE_IN_SECONDS;
			} else {
				return 0;
			}
		}

		// private function _craft_expiration_date($seconds_in_future) {
		// 	return date("m/d/Y h:i:s a", time() + $seconds_in_future);
		// }

		private function _craft_expiration_date($unit_type, $unit_value) {
			$date_time = new DateTime('NOW');

			$unit_type = $unit_type . 's';

			$interval_date_string = $unit_value . " " . $unit_type;

			date_add($date_time, date_interval_create_from_date_string($interval_date_string));
		
			return $date_time->format('m/d/Y h:i:s a');
		}

		private function _is_expiration_date_valid($expiration_date) {
			// we consider empty as infinity
			if ($expiration_date == '')
				return true;

			$current_date_time = new DateTime('NOW');
			$expiration_date_in_date_time = DateTime::createFromFormat('m/d/Y h:i:s a', $expiration_date);

			return ($current_date_time < $expiration_date_in_date_time);
		}

		private function _cookies_handle($recurrence_data) { //$trigger_id, $recurrence_type, $recurrence_version_index, $recurrence_trigger_type) {
			$recurrence_type = $recurrence_data->get_recurrence_type();
			$trigger_id = $recurrence_data->get_trigger_id();
			$recurrence_version_index = $recurrence_data->get_recurrence_version_index();
			$recurrence_trigger_type = $recurrence_data->get_recurrence_trigger_type();
			$actual_rule = $recurrence_data->get_rule();

			if ($recurrence_type == 'always' ||
				$recurrence_type == 'custom') {
				$ifso_recurrence_data = array();
				$expiration_date = '';

				/* Begin Handling Custom Situation */

				if ($recurrence_type == 'custom') {
					$unit_type = $actual_rule['recurrence_custom_units'];
					$unit_value = $actual_rule['recurrence_custom_value'];

					// $seconds = $this->_get_seconds_from_units($unit_type, $unit_value);
					$expiration_date = $this->_craft_expiration_date($unit_type, $unit_value);
				}

				/* End Handling Custom Situation */

				if (isset($_COOKIE['ifso_recurrence_data'])) {
					$ifso_recurrence_data_json = stripslashes($_COOKIE['ifso_recurrence_data']);
					$ifso_recurrence_data = json_decode($ifso_recurrence_data_json, true);
				}

				// Set the new trigger
				$ifso_recurrence_data[$trigger_id] = array(
														'expiration_date' => $expiration_date,
														'version_index' => $recurrence_version_index,
														'trigger_type' => $recurrence_trigger_type,
														'recurrence_type' => $recurrence_type
													 );

				// set the cookie
				setcookie('ifso_recurrence_data', 
						  json_encode($ifso_recurrence_data, JSON_UNESCAPED_UNICODE), 
						  time() + (86400 * 356 * 3),
						  '/'); // 3 years (356 * 3) days. 86400 = 1 day in second.
			} else if ($recurrence_type == 'all-session') {
				$ifso_recurrence_session_data = array(
													'version_index' => $recurrence_version_index,
													'trigger_type' => $recurrence_trigger_type,
													'recurrence_type' => $recurrence_type
												);
				$ifso_recurrence_session_data_json = json_encode($ifso_recurrence_session_data, JSON_UNESCAPED_UNICODE);

				setcookie('ifso_recurrence_session_' . $trigger_id, $ifso_recurrence_session_data_json, 0, '/');
			}
		}


		/* API methods */

		public function is_rule_in_recurrence($rule) {
			$trigger_type = $rule['trigger_type'];

			return ($rule && 
					in_array($trigger_type, $this->recurrence_trigger_types) ||
					($trigger_type == 'User-Behavior' && in_array($rule['User-Behavior'], array('LoggedIn', 'LoggedOut', 'Logged'))));
		}

		public function is_recurrence_valid($data_rules,
											$recurrence_version_index,
											$recurrence_version_type,
											$recurrence_type,
											$is_license_valid,
											$recurrence_expiration_date) {
			if (count($data_rules) > $recurrence_version_index) {
				$recurrence_trigger = $data_rules[$recurrence_version_index];

				if (!$recurrence_trigger) return;

				// Well there is an index like the cookie says...

				$is_so_far_so_good = false;

				if($recurrence_type == $recurrence_trigger['recurrence_option'] && // Same recurrence type TODO maybe we don't have to drop everything if not match
				   $recurrence_version_type == $recurrence_trigger['trigger_type'] && // Same trigger type
				   $this->is_rule_in_recurrence($recurrence_trigger) && // in recurrence
				   ($is_license_valid || (!$is_license_valid && !$this->_is_rule_requires_license($recurrence_trigger)))) { // check if trigger type requires license
					$is_so_far_so_good = true;
				}

				if (!$is_so_far_so_good)
					return false;

				/* check expiration date */
				if ($this->_is_expiration_date_valid($recurrence_expiration_date)) {
					return true;
				}
			}

			return false;
		}

		public function handle($recurrence_data) { //$trigger_id, $rule, $recurrence_type, $recurrence_index) {
			if ($this->is_rule_in_recurrence($recurrence_data->get_rule())) {
				// $recurrence_type = $rule['recurrence_option'];
				$this->_cookies_handle($recurrence_data); // $trigger_id, $recurrence_type, $recurrence_index, $rule['trigger_type']);
			}
		}
	}