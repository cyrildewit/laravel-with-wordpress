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

if (!class_exists('If_So_Geo_Service')) {
class If_So_Geo_Service {

	private static $instance; 

	public function __construct() {
		$this->web_service_url = 
			'http://www.if-so.com/api/'.IFSO_API_VERSION.'/geolocation-service/geolocation-api.php';
	}

	public static function getInstance() {
		if ( NULL == self::$instance )
			self::$instance = new If_So_Geo_Service();

		return self::$instance;
	}

	private function cache_geo_data($geoData) {
		$encodedGeoData = json_encode($geoData, JSON_UNESCAPED_UNICODE);
		// setcookie('ifso_geo_data', $encodedGeoData, time() + (60 * 15), "/");
		$_SESSION['ifso_geo_data'] = $encodedGeoData;
	}

	private function get_cached_geo_data() {
		if ( isset($_SESSION['ifso_geo_data']) )
			return json_decode(stripslashes($_SESSION['ifso_geo_data']), true);

		return NULL;
	}

	private function get_geo_data($license, $user_ip, $action) {
		$url = $this->web_service_url . 
				"?license=" . $license . "&ip=" . $user_ip . "&action=" . $action;
		$response = wp_remote_get( $url ,array('timeout' => 10) );

		if( is_array($response) ) {
			return json_decode( $response['body'], true );
		} else {
			return json_encode(array('success' => false));
		}
	}

	public function get_location_by_ip($license, $user_ip) {
		// try get cached geo data
		$cachedGeoData = $this->get_cached_geo_data();

		if ($cachedGeoData !== NULL) {
			return $cachedGeoData;
		}

		$geoData = $this->get_geo_data($license, $user_ip, 'get_ip_info');

		// cache if success
		if ( isset($geoData['success']) && $geoData['success'] === true )
			$this->cache_geo_data($geoData);
		else
			$this->cache_geo_data(array());

		return $geoData;
	}

	public function get_status($license) {
		$url = $this->web_service_url . "?action=get_status&license=".$license;
		$response = wp_remote_get($url, array('timeout' => 20) );

		if( is_array($response) ) {
			$data = json_decode( $response['body'], true );

			return $data;
		} else {
			return json_encode(array('success' => false));
		}
	}
}

}