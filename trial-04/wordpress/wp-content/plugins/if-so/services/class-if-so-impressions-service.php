<?php

/**
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * This class handles all Geolocation requests to IfSo's API Web Service
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class If_So_Impressions_Service {

	public function __construct() {
		$this->web_service_url = 
			'http://www.if-so.com/api/'.IFSO_API_VERSION.'/impressions-service/impressions-api.php';
		$this->impressions_update_interval = 60 * 60 * 4; // 4 hours (in seconds)
		$this->transient_name = 'ifso_transient_impressions_update';
	}

	private function check_transient() {
		return get_transient( $this->transient_name ); 
	}

	private function set_new_transient() {
		set_transient( 
				$this->transient_name, 
				true,
		   		$this->impressions_update_interval );
	}

	private function update_impressions_to_ifso($license, $impressions) {
		$response = wp_remote_post( $this->web_service_url, 
			array(	'method' => 'POST',
				  	'timeout' => 15,
				  	'body' => 
						array('license' => $license,
							  'impressions' => $impressions)
			));

		if( is_array($response) ) {
			$data = json_decode( $response['body'], true );

			return $data;
		} else {
			return json_encode(array('error' => true));
		}
	}

	private function get_ifso_data() {
		$ifsoData = get_option('ifso');

		if ( !$ifsoData ) {
			// create new one
			$ifsoData = array(
					'impressions' => 0
				);

			update_option('ifso', $ifsoData);
		} else if ( !isset($ifsoData['impressions']) &&
			  		 isset($ifsoData['monthly_sesssions_count']) ) {
			// handle deprecated key
			$ifsoData['impressions'] = $ifsoData['monthly_sesssions_count'];

			update_option('ifso', $ifsoData);
		}

		return $ifsoData;
	}

	private function update_impressions($license) {
		$ifsoData = $this->get_ifso_data();
		$impressions = $ifsoData['impressions'];
		$this->update_impressions_to_ifso($license, $impressions);
	}

	public function increment() {
		$ifsoData = $this->get_ifso_data();
		$ifsoData['impressions'] += 1;
		update_option('ifso', $ifsoData);
	}

	public function handle($license) {
		if ( !$this->check_transient() ) {
			$this->set_new_transient();
			$this->update_impressions($license);
		}
	}
}