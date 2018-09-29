<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

function ifso_delete_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-if-so-license.php';

	global $wpdb;

	delete_option( 'wpcf7' );

	$posts = get_posts( array(
		'numberposts' => -1,
		'post_type' => 'ifso_triggers',
		'post_status' => 'any' ) );

	foreach ( $posts as $post ) {
		wp_delete_post( $post->ID, true );
		delete_post_meta($post->ID, 'ifso_trigger_default');
		delete_post_meta($post->ID, 'ifso_trigger_rules');
		delete_post_meta($post->ID, 'ifso_trigger_version');
	}

	// retrieve our license key & item name from the DB
	$license = get_option('edd_ifso_license_key');
	$item_name = get_option('edd_ifso_license_item_name');
	$status = get_option('edd_ifso_license_status');

	if ($license !== false &&
		$item_name !== false &&
		$status == "valid") {
		$license = trim( $license );

		// Deactivate the license
		If_So_License::deactivate_license($license, $item_name);
	}

	// Remove all the options related to If-So
	delete_option('edd_ifso_license_key');
	delete_option('edd_ifso_license_item_name');
	delete_option('edd_ifso_license_status');

	// Remove all transients in use
	delete_transient('ifso_transient_license_validation');
}

ifso_delete_plugin();
