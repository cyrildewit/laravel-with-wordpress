<?php

	class If_So_Statistics {

		public function __construct() { }

		/* Helper Methods */

		private function clean_data_rules($data_rules) {
			$cleaned_data_rules = str_replace("\\", "\\\\\\", json_encode($data_rules));
			return $cleaned_data_rules;
		}

		private function increment_rule_statistics_counter($versions_data) {
			$trigger_id = $versions_data->get_trigger_id();
			$index = $versions_data->get_recurrence_version_index();
			$rule = $versions_data->get_rule();
			$data_rules = $versions_data->get_data_rules();

			$current_statistics_counter = 0;
			if (isset($rule['statistics_counter']))
				$current_statistics_counter = $rule['statistics_counter'];

			$data_rules[$index]['statistics_counter'] = $current_statistics_counter + 1;
			$cleaned_data_rules = $this->clean_data_rules($data_rules);

			update_post_meta( $trigger_id, 'ifso_trigger_rules', $cleaned_data_rules );
		}

		/* API Methods */

		public function handle($versions_data) {
			$this->increment_rule_statistics_counter($versions_data);
		}

		public function handle_default($trigger_id) {
			$data_default_metadata_json = 
			                get_post_meta( $trigger_id,
			                              'ifso_trigger_default_metadata',
			                               true );
			if ( !empty($data_default_metadata_json) ) {
				$data_default_metadata = json_decode($data_default_metadata_json, true);
			} else {
				$data_default_metadata = array(
					'statistics_count' => 0
				);
			}

			$new_default_version_statistics_counter = 
				intval($data_default_metadata['statistics_count'])+1;

			$data_default_metadata['statistics_count'] = $new_default_version_statistics_counter;

			$default_version_metadata_json = 
					json_encode($data_default_metadata, JSON_UNESCAPED_UNICODE );

			update_post_meta( $trigger_id, 
							  'ifso_trigger_default_metadata', 
							   $default_version_metadata_json );
		}
	}

?>