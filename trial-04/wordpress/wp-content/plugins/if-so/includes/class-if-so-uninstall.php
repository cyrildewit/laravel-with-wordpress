<?php

/**
 * Fired during plugin uninstall
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's uninstall.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <email@example.com>
 */
class If_So_Uninstall {

	/**
	 * Cleanup upon uninstall of the plugin
	 *
	 * @since    1.0.0
	 */
	public static function uninstall() {
		require_once plugin_dir_path( __FILE__ ) . '/class-if-so-license.php';

		// retrieve our license key & item id from the DB
		$license = get_option('edd_ifso_license_key');
		$item_id = get_option( 'edd_ifso_license_item_id' );

		if ($license !== false &&
			$item_id !== false) {
			$license = trim( $license );

			// Deactivate the license
			If_So_License::deactivate_license($license, $item_id);
		}

		// Remove all the options related to If-So
		delete_option('edd_ifso_license_key');
		delete_option('edd_ifso_license_item_name');
		delete_option('edd_ifso_license_item_id');
		delete_option('edd_ifso_license_status');
		delete_option('edd_ifso_had_license');

		// Remove all transients in use
		delete_transient('ifso_transient_license_validation');
	}

}
