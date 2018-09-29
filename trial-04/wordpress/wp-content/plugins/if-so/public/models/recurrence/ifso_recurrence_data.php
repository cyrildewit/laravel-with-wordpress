<?php

	class If_So_Recurrence_Data {
		
		private $trigger_id;
		private $rule;
		private $recurrence_version_index;
		private $data_rules;

		public function __construct($trigger_id,
									$rule,
									$recurrence_version_index,
									$data_rules) {
			$this->trigger_id = $trigger_id;
			$this->rule = $rule;
			$this->recurrence_version_index = $recurrence_version_index;
			$this->data_rules = $data_rules;
		}

		/* Getters */

		public function get_trigger_id() {
			return $this->trigger_id;
		}

		public function get_rule() {
			return $this->rule;
		}

		public function get_data_rules() {
			return $this->data_rules;
		}

		public function get_recurrence_version_index() {
			return $this->recurrence_version_index;
		}

		public function get_recurrence_trigger_type() {
			return $this->rule['trigger_type'];
		}

		public function get_recurrence_type() {
			return $this->rule['recurrence_option'];
		}
	}