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
 * This class defines all code necessary to activate / deactivate / etc of IfSo's License.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class If_So_License {

	private static function _query_ifso_api($edd_action, $license, $item_id) {
			// data to send in our API request
			$api_payload = array(
				'edd_action' => $edd_action, //'activate_license',
				'license'    => $license,
				'item_id'  => $item_id, // the name of our product in EDD
				'url'        => home_url()
			);

			$message = false;
			$license_data = false;

			// Call the custom API.
			$response = wp_remote_post( EDD_IFSO_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_payload ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

				if ( is_wp_error( $response ) ) {
					$message = $response->get_error_message();
				} else {
					$message = __( 'An error occurred, please try again.' );
				}

			} else {
				$license_data = json_decode( wp_remote_retrieve_body( $response ) );
			}

			if (!$license_data) return $message;
			return $license_data;
	}

	public static function deactivate_license($license, $item_id) {
		return self::_query_ifso_api('deactivate_license', $license, $item_id);
	}

	public static function activate_license($license, $item_id) {
		return self::_query_ifso_api('activate_license', $license, $item_id);
	}

}
