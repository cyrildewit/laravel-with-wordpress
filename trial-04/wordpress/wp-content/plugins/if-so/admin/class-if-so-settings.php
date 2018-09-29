<?php

/**
 * The settings of the plugin.
 *
 * @link       http://devinvinson.com
 * @since      1.0.0
 *
 * @package    Wppb_Demo_Plugin
 * @subpackage Wppb_Demo_Plugin/admin
 */

/**
 * Class WordPress_Plugin_Template_Settings
 *
 */
class If_So_Admin_Settings {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;
	
	public $triggers_obj;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	
	public function plugin_settings_page(){
		return false; // main display will be overriden by a custom post type
	}
	
	public function ifso_trigger_settings_metabox( $post ){
		require_once('partials/ifso_trigger_settings_metabox.php');
		return false;
	}
	
	public function ifso_shortcode_display_metabox( $post ){
		require_once('partials/ifso_shortcode_display_metabox.php');
		return false;
	}

	public function ifso_helper_metabox( $post ) {
		require_once('partials/ifso_helper_metabox.php');
		return false;
	}

	public function ifso_statistics_metabox( $post ) {
		require_once('partials/ifso_statistics_metabox.php');
		return false;
	}

	public function display_admin_menu_settings_page( $post ){
		require_once('partials/ifso_settings_page_display.php');
		return false;
	}

	private function edd_ifso_is_in_activations_process() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {
			return true;
		} else {
			return false;
		}
	}

	private function edd_ifso_get_error_message() {
		if ( !$this->edd_ifso_is_in_activations_process() )
			return false;

		switch( $_GET['sl_activation'] ) {

				case 'false':

					$message = urldecode( $_GET['message'] );
					return $message;
					
					break;
				
				case 'true':
				default:
				
					break;

		}

		return true;
	}

	/**
	 * This is a means of catching errors from the activation method above and displaying it to the customer
	 */
	public function edd_ifso_admin_notices() {
		if ( isset( $_GET['sl_activation'] ) && ! empty( $_GET['message'] ) ) {

			switch( $_GET['sl_activation'] ) {

				case 'false':
					$message = urldecode( $_GET['message'] );
					?>
					<div class="error">
						<p><?php echo $message; ?></p>
					</div>
					<?php
					break;

				case 'true':
				default:
					// Developers can put a custom success message here for when activation is successful if they way.
					break;

			}
		}
	}

	/*
	 *	add plaugin menu items
	 */
	public function add_plugin_menu_items() {
		
		add_menu_page(
			__( 'If So', 'if-so' ), // The title to be displayed on this menu's corresponding page
			__( 'If-So', 'if-so' ), // The text to be displayed for this actual menu item
			'manage_options', // Which type of users can see this menu
			'if-so', // The unique ID - that is, the slug - for this menu item
			array($this, 'plugin_settings_page'), // The name of the function to call when rendering this menu's page
			plugin_dir_url( __FILE__ ) . 'images/logo-256x256.png', // icon url
			90 // position
		);
		
		global $submenu;
		$permalink = admin_url( 'post-new.php' ).'?post_type=ifso_triggers';


		$submenu['if-so'][] = array( __('Add New Trigger', 'if-so'), 'manage_options', $permalink );
		
		// $saveAsideAllTriggers = $submenu['if-so'][0];
		// $submenu['if-so'][0] = array( __('Add New Trigger', 'if-so'), 'manage_options', $permalink );
		// $submenu['if-so'][] = $saveAsideAllTriggers;

		add_submenu_page(
			'if-so',
			'License',
			'License',
			'manage_options',
			'wpcdd_admin_menu_settings', // wpcdd_admin_menu_license
			array( $this, 'display_admin_menu_settings_page' )
		);

		/*add_submenu_page(
			'if-so',
			'Add New',
			'Add New',
			'manage_options',
			'edit.php?post_type=ifso_triggers',
			array($this, 'plugin_settings_page')
		);*/
		
		/*add_submenu_page(
			'if-so',
			'Instructions',
			'Instructions',
			'manage_options',
			'wpcdd_admin_menu_instruction',
			array( $this, 'display_admin_menu_instruction' )
		);*/
	}
	
	// Create custom column to display shortcode
	public function ifso_add_custom_column_title( $prev_columns ){
		$columns = array(
			'cb'      => '<input type="checkbox" />',
			'title'    => __( 'Title', 'if-so' ),
			'trigger' => __( 'Triggers', 'if-so' ),
			'shortcode' => __( 'Shortcode', 'if-so' )
		);
		
		// add custom columns except yoast seo to the end of the table
		foreach($prev_columns as $col_index => $col_title) {
			if(strpos($col_index, 'wpseo') !== false) continue;
			if(array_key_exists($col_index, $columns)) continue;
			$columns[$col_index] = $col_title;
		}
		
		// set date column at the end of the table
		$columns['date'] = __( 'Date', 'if-so' );
		
		return $columns;
	}
	
	public function ifso_add_custom_column_data( $column, $post_id ){
		switch( $column ){
			case 'trigger' :
				$data = array();
				$triggers = '';
				
				$data_json = get_post_meta( $post_id, 'ifso_trigger_rules', true );
				if(!empty($data_json)) $data = json_decode($data_json, true);
				if(empty($data)) return false;
				$triggers_array = array();
				$query_strings_used = array();
				foreach($data as $rule) {
					if($rule['trigger_type'] == 'url' && !empty($rule['compare'])) $query_strings_used[] = "{$rule['compare']}";
					else
					{
						if ($rule['trigger_type'] == [])
						// incase no trigger got chosen
							$trigger_type = "Blank";
						else
							$trigger_type = $rule['trigger_type'];

						if (!in_array($trigger_type, $triggers_array))
							$triggers_array[] = $trigger_type;
					}
				}
				
				// add all query strings selected to the triggers array
				if(!empty($query_strings_used)) {
					$triggers_array[] = 'Custom URL (?ifso='.implode(', ', $query_strings_used).')';
				}
				
				if(!empty($triggers_array)) $triggers = implode('<br/>', $triggers_array);
				echo $triggers;
				break;
			case 'shortcode' :
				$shortcode = sprintf( '[ifso id="%1$d"]', $post_id);
				echo "<span class='shortcode'><input type='text' onfocus='this.select();' readonly='readonly' value='". $shortcode ."' class='large-text code'></span>";
				break;
		}
	}

	public function custom_triggers_template ($content) {
    	global $wp_query, $post;
    	return "HI";
	    /* Checks for single template by post type */
	    if ($post->post_type == "ifso_triggers"){
	    	die(PLUGIN_PATH);
	        if(file_exists(PLUGIN_PATH . '/Custom_File.php'))
	            return PLUGIN_PATH . '/Custom_File.php';
	    }

		return $single;
	}

	public function include_ifso_custom_triggers_template ($template_path) {
		if ( get_post_type() == 'ifso_triggers' ) {
	        if ( is_single() ) {
	            // checks if the file exists in the theme first,
	            // otherwise serve the file from the plugin
	            if ( $theme_file = locate_template( array ( 'single-ifso_triggers.php' ) ) ) {
	                $template_path = $theme_file;
	            } else {
	                $template_path = plugin_dir_path( __FILE__ ) . '/templates/single-ifso_triggers.php';
	            }
	    	}
	    }

	    return $template_path;
	}
	
	public function ifso_add_meta_boxes( $post_type ){
		
		add_filter( 'wpseo_metabox_prio', function() { return 'low'; } );
		
		add_meta_box(
			'ifso_triggers_metabox', 
			__('Trigger settings', 'if-so'), 
			array($this, 'ifso_trigger_settings_metabox'),
			'ifso_triggers',
			'normal',
			'high'
		);

		add_meta_box(
			'ifso_shortcode_display',
			__('Shortcode', 'if-so'),
			array( $this, 'ifso_shortcode_display_metabox' ),
			'ifso_triggers',
			'side',
			'default'
		);

		// Statistics Box		
		// add_meta_box(
		// 	'ifso_statistics_metabox',
		// 	(__('Analytics', 'if-so').'<a id="analytics-refresh-views" title="Refresh"><i class="fa fa-refresh refreshIcon" aria-hidden="true"><!--icon--></i></a>'),
		// 	array( $this, 'ifso_statistics_metabox' ),
		// 	'ifso_triggers',
		// 	'side',
		// 	'default'
		// );

		add_meta_box(
			'ifso_helper_metabox',
			__('Need Help?', 'if-so'),
			array( $this, 'ifso_helper_metabox' ),
			'ifso_triggers',
			'side',
			'default'
		);

		/*
		// in case that priority manipulation doesnt work
		function do_something_after_title() {
			$scr = get_current_screen();
			if ( ( $scr->base !== 'post' && $scr->base !== 'page' ) || $scr->action === 'add' )
				return;
			echo '<h2>After title only for post or page edit screen</h2>';
		}

		add_action( 'edit_form_after_title', 'do_something_after_title' );
		*/
	}
	
	public function move_yoast_metabox_down( $priority ){
		return 'low';
	}
	
	public function load_tinymce() {
		check_ajax_referer( 'my-nonce-string', 'nonce' );
		$editor_id = intval( $_POST['editor_id'] );
		
		wp_editor( '', 'repeatable_editor_content'.$editor_id, array(
			'wpautop'       => true,
			'textarea_name' => 'repeater['.$editor_id.'][repeatable_editor_content]',
			'textarea_class' => 'cloned-textarea',
			'textarea_rows' => 20,
		));
		wp_die();
	}


	// Helper method.
	// Loads given's $post_id default version metadata from DB.
	// TODO move it to dedicated service.
	private function load_default_version_metadata($post_id)
	{                           
		// first load default's metadata from the DB
		$data_default_metadata_json = 
		                get_post_meta( $post_id,
		                              'ifso_trigger_default_metadata',
		                               true );
		// second we check if it exists
		if ( !empty($data_default_metadata_json) ) {
			$default_version_metadata = json_decode($data_default_metadata_json, true);
		} else {
			$default_version_metadata = array(
				'statistics_count' => 0
			);
		}

		return $default_version_metadata;
	}


	// Helper method.
	// save to the DB in $post_id's default version metadata
	// the given $default_version_metadata obj.
	// TODO move this function to dedicated service.
	private function save_default_version_metadata($post_id, 
												   $default_version_metadata)
	{
		// save the new default's metadata to the DB
		// by first serializing it to JSON format
		$default_version_metadata_json = 
				json_encode($default_version_metadata, JSON_UNESCAPED_UNICODE );

		// save default's metadata to DB using WP's update_post_meta func
		update_post_meta( $post_id, 
						  'ifso_trigger_default_metadata', 
						   $default_version_metadata_json );
	}

	// Helper method.
	// Resets default's statistics_count in its metadata.
	// TODO move this function to a dedicated service.
	private function reset_default_version_statistics_count($post_id)
	{
		// Load $post_id's default version metadata
		$default_version_metadata = $this->load_default_version_metadata($post_id);

		// Update the statistics of the default version to 0
		$default_version_metadata['statistics_count'] = 0;

		$this->save_default_version_metadata($post_id, $default_version_metadata);
	}

	// gets called with ajax. TODO cleanup
	public function reset_analytics_count_handler() {
		check_ajax_referer( 'my-nonce-string', 'nonce' );
		
		$post_id = intval( $_POST['post_id'] );
		$version_index = intval( $_POST['version_index'] );

		// TODO: Create some kind of "DataRulesService"
		// 		 which will handle all the mutations of the rules
		//		 and any additional work related to the DataRules.

		if ($version_index == "default") {
			// Reset the default version
			$this->reset_default_version_statistics_count($post_id);
		} else {
			// Reset any other version which is not the default

			// Load data_rules
			$data_rules_json = get_post_meta( $post_id, 'ifso_trigger_rules', true );
			$data_rules = json_decode($data_rules_json, true);

			// Mutate the data_rules
			$data_rules[$version_index]['statistics_counter'] = 0;

			// Clean & Update
			$data_rules_cleaned = str_replace("\\", "\\\\\\", json_encode($data_rules));
			update_post_meta( $post_id, 'ifso_trigger_rules', $data_rules_cleaned);
		}




		wp_die(); // indicate end of stream
	}

	// gets called with ajax. TODO cleanup
	public function reset_all_analytics_count_handler() {
		check_ajax_referer( 'my-nonce-string', 'nonce' );

		$post_id = intval( $_POST['post_id'] );

		// TODO: Create some kind of "DataRulesService"
		// 		 which will handle all the mutations of the rules
		//		 and any additional work related to the DataRules.
		// 		 this service will also handle all work related to
		// 		 the deafult version as well because it's as well
		//		 a dataRule.

		// Load data_rules
		$data_rules_json = get_post_meta( $post_id, 'ifso_trigger_rules', true );
		$data_rules = json_decode($data_rules_json, true);

		// Mutate the data_rules
		if(!empty($data_rules)) {
			foreach($data_rules as $index => $rule) {
				$data_rules[$index]['statistics_counter'] = 0;
			}
		}

		// Clean & Update
		$data_rules_cleaned = str_replace("\\", "\\\\\\", json_encode($data_rules));
		update_post_meta( $post_id, 'ifso_trigger_rules', $data_rules_cleaned);

		/* Reset default version */
		$this->reset_default_version_statistics_count($post_id);		

		// by now, we resetted all the versions including the default
		// version

		wp_die(); // indicate end of stream
	}
	
	// gets called with ajax
	public function refresh_analytics_count_handler() {
		check_ajax_referer( 'my-nonce-string', 'nonce' );

		$post_id = intval( $_POST['post_id'] );

		// TODO: Create some kind of "DataRulesService"
		// 		 which will handle all the mutations of the rules
		//		 and any additional work related to the DataRules.

		// Load data_rules
		$data_rules_json = get_post_meta( $post_id, 'ifso_trigger_rules', true );
		$data_rules = json_decode($data_rules_json, true);

		$views = array();

		// Mutate the data_rules
		if(!empty($data_rules)) {
			foreach($data_rules as $index => $rule) {
				// TODO rename statistics_counter to statistics_count
				$views[intval($index)] = intval($rule['statistics_counter']);
			}
		}

		// As a final step, push into $views associative array
		// default's statistics_count in "default" key.
		$default_version_metadata = $this->load_default_version_metadata($post_id);
		$views["default"] = intval($default_version_metadata['statistics_count']);

		wp_die(json_encode($views)); // indicate end of stream
	}

	private function extract_autocomplete_selection_data($data) {
		if (!empty($data)) {
			$data = explode("^^", $data);
			$splitted_data = array();

			foreach ($data as $key => $value) {
				if (!empty($value) && $value != "1") {
					array_push($splitted_data, $value);
				}
			}

			$data = utf8_encode(implode('^^', $splitted_data));
			$data = str_replace('\\', '\\\\', $data);
		}

		return $data;
	}

	// TODO cleanup + refactor REQUIRED!
	public function ifso_save_post_type ( $post_id ){
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			die(__( 'You do not have sufficient previlliege to edit the post', 'if-so' ).'.');
		}
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}
		
		// Prevent quick edit from clearing custom fields
		if (defined('DOING_AJAX') && DOING_AJAX) {
			return $post_id;
		}
		
		$trigger_data = array();
		//if(empty($_POST['default'])) return $post_id;
		$trigger_data['default'] = (!empty($_POST['ifso_default'])) ? $_POST['ifso_default'] : '';
		//echo "<pre>".print_r($_POST['repeater'], true)."</pre>";
		if(empty($_POST['repeater'])) return $post_id;
		
		// Counting the number of repeaters!
		$repeaters_counter = 1;

		foreach($_POST['repeater'] as $index => $group_item) {
			if(empty($group_item['trigger_type'])) continue;

			$repeaters_counter += 1;
		}

		// die("<h1>".strval($repeaters_counter)."</h1>");

		$testing_mode = (is_numeric($_POST['testing-mode']) &&
						  intval($_POST['testing-mode']) <= $repeaters_counter) ? $_POST['testing-mode'] : "";

		// $default_version_statistics_count = 0;
		// if ( !empty($_POST['ifso_default_version_statistics_count']) )
		// 	$default_version_statistics_count = 
		// 		$_POST['ifso_default_version_statistics_count'];

		// Load default's version metadata
		$default_version_metadata = $this->load_default_version_metadata($post_id);

		foreach($_POST['repeater'] as $index => $group_item) {
			
			if($index === 'index_placeholder') continue;
			// if(empty($group_item['trigger_type']) || empty($group_item['repeatable_editor_content'])) continue; // was removed in order to allow saving of an empty content

			// Removed to omit validation
			// if(empty($group_item['trigger_type']))
			// 	continue;
			
			$compare = '';
			if(!empty($group_item['compare_referrer'])) $compare = $group_item['compare_referrer'];
			if(!empty($group_item['compare_url'])) $compare = $group_item['compare_url'];
			
			// Page Url Begin
			$page_url_compare = '';
			if(!empty($group_item['page-url-compare']))
				$page_url_compare = $group_item['page-url-compare'];
			$page_url_operator = '';
			if(!empty($group_item['page-url-operator']))
				$page_url_operator = $group_item['page-url-operator'];
						

			/* Trigger Type Begin */

			if (empty($group_item['trigger_type'])) {
				$trigger_type = array();
			} else {
				$trigger_type = $group_item['trigger_type'];
			}
			/* Trigger Type End */

			/* Begin Sessions */

			$ab_testing_no_sessions = '';
			if(!empty($group_item['ab-testing-sessions']))
				$ab_testing_no_sessions = $group_item['ab-testing-sessions'];

			$ab_testing_custom_sessions = '';
			if (!empty($group_item['ab-testing-custom-no-sessions'])) 
				$ab_testing_custom_sessions = $group_item['ab-testing-custom-no-sessions'];

			/* End Sessions */

			/* Begin User Behavior */

			$user_behavior_loggedinout = '';
			if (!empty($group_item['user-behavior-loggedinout'])) 
				$user_behavior_loggedinout = $group_item['user-behavior-loggedinout'];

			$user_behavior_returning = '';
			if (!empty($group_item['user-behavior-returning'])) 
				$user_behavior_returning = $group_item['user-behavior-returning'];

			$user_behavior_retn_custom = '';
			if (!empty($group_item['user-behavior-retn-custom'])) 
				$user_behavior_retn_custom = $group_item['user-behavior-retn-custom'];

			$user_behavior_browser_language = '';
			if (!empty($group_item['user-behavior-browser-language'])) 
				$user_behavior_browser_language = $group_item['user-behavior-browser-language'];

			$user_behavior_browser_language_primary_lang = '';
			if (!empty($group_item['user-behavior-browser-language-primary-lang'])) 
				$user_behavior_browser_language_primary_lang = $group_item['user-behavior-browser-language-primary-lang'];

			/* End User Behavior */

			$numberOfViews = 0;
			if (!empty($group_item['saved_number_of_views']))
				$numberOfViews = $group_item['saved_number_of_views'];

			$user_behavior_device_mobile = false;
			$user_behavior_device_tablet = false;
			$user_behavior_device_desktop = false;
			
			if ($group_item['user-behavior-device-mobile'] == "on")
				$user_behavior_device_mobile = true;

			if ($group_item['user-behavior-device-tablet'] == "on")
				$user_behavior_device_tablet = true;

			if ($group_item['user-behavior-device-desktop'] == "on")
				$user_behavior_device_desktop = true;

			// Geolocation Begin
			$geolocation_data = 
				$this->extract_autocomplete_selection_data($group_item['geolocation_data']);
			// Geolocation End

			// Pages visited Begin
			$page_visit_data = 
				$this->extract_autocomplete_selection_data($group_item['page_visit_data']);
			// Pages Visited End

			/* Recurrence Begin */

			$recurrenceOption = "";
			$recurrenceCustomUnits = "";
			$recurrenceCustomValue = "";

			/* Check if the selected trigger is one of the allowed triggers
			 * for the Recurrence feature */

			$allowed_triggers_for_recurrence = array("AB-Testing",
									 			     "advertising-platforms", 
									 			     "User-Behavior", // Has sub-option called "Logged"
									 			     "url",
									 			     "referrer",
									 			     "PageUrl",
									 			     "PageVisit");

			if (in_array($trigger_type, $allowed_triggers_for_recurrence)) {

				// Check also the sub-option called Logged (if the trigger type is User-Behavior)
				if ($trigger_type != "User-Behavior" ||
					($trigger_type == "User-Behavior" &&
					in_array($group_item['User-Behavior'], array('LoggedIn', 'LoggedOut', 'Logged')))) {

					$rawRecurrenceOption = $group_item['recurrence-option'];
					
					$recurrenceOption = trim($rawRecurrenceOption);

					// none is the default option
					$recurrenceOption = ($recurrenceOption) ? $recurrenceOption : "none";

					/* Custom Handling */
					if ($recurrenceOption == "custom") {
						$recurrenceCustomUnits = $group_item['recurrence-custom-units'];
						$recurrenceCustomValue = $group_item['recurrence-custom-value'];
					}
				}
			}
			
			/* Recurrenc End */

			/* Statistics Begin */

			$statistics_counter = 0;
			if ( !empty($group_item['statistics_counter']) )
				$statistics_counter = $group_item['statistics_counter'];

			/* Statistics End */

			// die($recurrenceOption);

			$trigger_data['rules'][] = array(
				'trigger_type' => $trigger_type,
				'AB-Testing' => $group_item['AB-Testing'],
				'User-Behavior' => $group_item['User-Behavior'],
				'user-behavior-loggedinout' => $user_behavior_loggedinout,
				'user-behavior-returning' => $user_behavior_returning,
				'user-behavior-retn-custom' => $user_behavior_retn_custom,
				'user-behavior-loggedinout' => $user_behavior_loggedinout,
				'user-behavior-browser-language' => $user_behavior_browser_language,
				'user-behavior-browser-language-primary-lang' => $user_behavior_browser_language_primary_lang,
				'user-behavior-device-mobile' => $user_behavior_device_mobile,
				'user-behavior-device-tablet' => $user_behavior_device_tablet,
				'user-behavior-device-desktop' => $user_behavior_device_desktop,
				'user-behavior-logged' => $group_item['user-behavior-logged'],
				'ab-testing-custom-no-sessions' => $ab_testing_custom_sessions,
				'time-date-start-date' => $group_item['time-date-start-date'],
				'time-date-end-date' => $group_item['time-date-end-date'],
				'Time-Date-Start' => $group_item['Time-Date-Start'],
				'Time-Date-End' => $group_item['Time-Date-End'],
				'Time-Date-Schedule-Selection' => $group_item['Time-Date-Schedule-Selection'],
				'Date-Time-Schedule' => $group_item['Date-Time-Schedule'],
				'testing-mode' => $testing_mode,
				'freeze-mode' => $group_item['freeze-mode'],
				'ab-testing-sessions' => $ab_testing_no_sessions,
				'number_of_views' => $numberOfViews,
				'trigger' => $group_item['trigger'],
				'chosen-common-referrers' => $group_item['chosen-common-referrers'],
				'custom' => $group_item['custom'],
				'page' => $group_item['page'],
				'operator' => $group_item['operator'],
				'compare' => strtolower($compare),
				'page-url-compare' => strtolower($page_url_compare),
				'page-url-operator' => $page_url_operator,
				'advertising_platforms' => strtolower($group_item['advertising_platforms']),
				'advertising_platforms_option' => $group_item['advertising_platforms_option'],
				'geolocation_data' => $geolocation_data,
				'recurrence_option' => $recurrenceOption,
				'recurrence_custom_units' => $recurrenceCustomUnits,
				'recurrence_custom_value' => $recurrenceCustomValue,
				'statistics_counter' => $statistics_counter,
				'page_visit_data' => $page_visit_data
			);

			// echo "<pre>".print_r($group_item, true)."</pre>";
			$trigger_data['vesrions'][] = $group_item['repeatable_editor_content'];
		}
		
		/*echo "<pre>".print_r($trigger_data, true)."</pre>";
		echo "<pre>".print_r($_POST['repeater'], true)."</pre>";
		die('died in save');*/
		
		/* DB Updates */

		// update default content
		update_post_meta( $post_id,
						  'ifso_trigger_default',
						  $trigger_data['default'] );

		// update default metadata
		// $default_version_metadata = array(
		// 	'statistics_count' => $default_version_statistics_count
		// );

		$this->save_default_version_metadata($post_id, $default_version_metadata);

		// print_r(json_encode($trigger_data['rules']));
		// die(json_encode($trigger_data['rules'], JSON_UNESCAPED_UNICODE));
		// update rules
		update_post_meta( $post_id, 'ifso_trigger_rules', json_encode($trigger_data['rules'], JSON_UNESCAPED_UNICODE ));

		// delete all previous versions
		delete_post_meta($post_id, 'ifso_trigger_version');
		
		if(!empty($trigger_data['vesrions'])) {
			foreach($trigger_data['vesrions'] as $version_content) {
				// add saved versions
				add_post_meta( $post_id, 'ifso_trigger_version', $version_content );
			}
		}


		// print_r(json_encode($trigger_data['rules']));
		// die();

		// update_post_meta( $post_id, 'ifso_trigger_rules', json_encode($trigger_data['rules']));

		// echo $post_id;
		// $data_rules_json = get_post_meta( $post_id, 'ifso_trigger_rules', true );
		// print_r($data_rules_json);
		// die();
		
		
		//die('died in save');
		//update_post_meta( $post_id, 'ifso_trigger_rules', htmlspecialchars(json_encode($trigger_data), ENT_QUOTES, 'UTF-8') );
	}
}