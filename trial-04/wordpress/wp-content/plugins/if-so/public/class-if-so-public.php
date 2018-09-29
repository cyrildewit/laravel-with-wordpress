<?php

session_start();

require_once(__DIR__."/helpers/mobile_detection.php");
require_once(__DIR__."/models/recurrence/ifso_recurrence.php");
require_once(__DIR__."/models/recurrence/ifso_recurrence_data.php");
require_once(__DIR__."/models/statistics/ifso_statistics.php");
require_once(__DIR__."/models/geolocation/data/timezones.php");
require_once(__DIR__."/models/page-visits/ifso_page_visits_service.php");

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/public
 * @author     Your Name <email@example.com>
 */
class If_So_Public {

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

	/**
	 *	holds all the trigger types that are available in recurrence 
	 */
	private $recurrence_trigger_types;

	private $recurrence_handler;

	private $trigger_types_not_requires_license;

	/* License Service. Holds all functionality related to 
		license activation, deactivation, check for validy and more */
	private $license_service;

	/* Geolocation Service. Holds functionality of requesting
		for users location based on their IP */
	private $geo_service;

	/* Impressions Service */
	private $impressions_service;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name,
								 $version,
								 $license_service,
								 $geo_service,
								 $impressions_service ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->license_service = $license_service;
		$this->geo_service = $geo_service;
		$this->impressions_service = $impressions_service;

		$this->recurrence_trigger_types = array("AB-Testing",
                                      			"advertising-platforms", 
                                      			"url",
                                      			"referrer",
                                      			"PageUrl",
                                      			"PageVisit");
		$this->trigger_types_not_requires_license = array("Device", "User-Behavior", "Geolocation");

		$this->recurrence_handler = new If_So_Recurrence($this->recurrence_trigger_types, $this->trigger_types_not_requires_license);

		// $this->statistics_handler = new If_So_Statistics(); // Recover when statistics come back
	}

	// convert microsoft word kind of apostrophe -encoded
	private function convert_smart_quotes($string) 
	{ 
	    $search = array(chr(8216),
	    				chr(145), 
	                    chr(146), 
	                    chr(147), 
	                    chr(148), 
	                    chr(151)); 

	    $replace = array("'",
	    				 "'", 
	                     "'", 
	                     '"', 
	                     '"', 
	                     '-'); 

	    return str_replace($search, $replace, $string); 
	} 
	
	// private function _is_trigger_in_recurrence($trigger) {
	// 	$trigger_type = $trigger['trigger_type'];

	// 	return ($trigger && 
	// 			in_array($trigger_type, $this->recurrence_trigger_types) ||
	// 			($trigger_type == 'User-Behavior' && in_array($trigger['User-Behavior'], array('LoggedIn', 'LoggedOut', 'Logged'))));
	// }

	// private function _is_trigger_requires_license($trigger) {
	// 	$trigger_type = $trigger['trigger_type'];

	// 	return (!in_array($trigger_type, $this->trigger_types_not_requires_license) ||
	// 			($trigger_type == 'User-Behavior' && !in_array($trigger['User-Behavior'], array('LoggedIn', 'LoggedOut', 'Logged'))));
	// }

	// private function _is_recurrence_valid($data_rules,
	// 									  $recurrence_version_index,
	// 									  $recurrence_version_type,
	// 									  $recurrence_type,
	// 									  $is_license_valid) {
	// 	if (count($data_rules) > $recurrence_version_index) {
	// 		$recurrence_trigger = $data_rules[$recurrence_version_index];

	// 		if (!$recurrence_trigger) return;


	// 		// Well there is index like the cookie says...
	// 		if($recurrence_type == $recurrence_trigger['recurrence_option'] && // Same recurrence type TODO maybe we don't have to drop everything if not match
	// 		   $recurrence_version_type == $recurrence_trigger['trigger_type'] && // Same trigger type
	// 		   $this->_is_trigger_in_recurrence($recurrence_trigger) && // in recurrence
	// 		   ($is_license_valid || (!$is_license_valid && !$this->_is_trigger_requires_license($recurrence_trigger)))) { // check if trigger type requires license
	// 			return true;
	// 		}
	// 	}

	// 	return false;
	// }

	// private function _recurrence_cookies_handle($trigger_id, $recurrence_type, $recurrence_version_index, $recurrence_trigger_type) {
	// 	if ($recurrence_type == 'always') {
	// 		$ifso_recurrence_data = array();

	// 		if (isset($_COOKIE['ifso_recurrence_data'])) {
	// 			$ifso_recurrence_data_json = stripslashes($_COOKIE['ifso_recurrence_data']);
	// 			$ifso_recurrence_data = json_decode($ifso_recurrence_data_json, true);
	// 		}

	// 		// Set the new trigger
	// 		$ifso_recurrence_data[$trigger_id] = array(
	// 												'expiration_date' => '', // Always doesn't have expiration date
	// 												'version_index' => $recurrence_version_index,
	// 												'trigger_type' => $recurrence_trigger_type
	// 											 );

	// 		// set the cookie
	// 		setcookie('ifso_recurrence_data', 
	// 				  json_encode($ifso_recurrence_data, JSON_UNESCAPED_UNICODE), 
	// 				  time() + (86400 * 356)); // 356 days. 86400 = 1 day in second.
	// 	} else if ($recurrence_type == 'all-session') {
	// 		$ifso_recurrence_session_data = array(
	// 											'version_index' => $recurrence_version_index,
	// 											'trigger_type' => $recurrence_trigger_type
	// 										);
	// 		$ifso_recurrence_session_data_json = json_encode($ifso_recurrence_session_data, JSON_UNESCAPED_UNICODE);

	// 		setcookie('ifso_recurrence_session_' . $trigger_id, $ifso_recurrence_session_data_json, 0);
	// 	} // TODO 'custom' recurrence type
	// }

	// private function _recurrence_handle($trigger_id, $rule, $recurrence_type, $recurrence_index) {
	// 	if ($this->_is_trigger_in_recurrence($rule)) {
	// 		$recurrence_type = $rule['recurrence_option'];

	// 		$this->_recurrence_cookies_handle($trigger_id, $recurrence_type, $recurrence_index, $rule['trigger_type']);
	// 	}
	// }

	private function custom_apply_filters($tag, $value) {
		if ( has_filter($tag, 'display_rich_snippet') ) {
			remove_filter($tag,'display_rich_snippet');
			$return =  apply_filters($tag, $value);
			add_filter($tag,'display_rich_snippet');
		} else if ( has_filter($tag, 'tve_clean_wp_editor_content') ) {
			$return = $value;
		} else {
			$return =  apply_filters($tag, $value);
		}

		return $return;
	}	

	// Gets called when the trigger succeeded
	private function apply_filters_and_hooks($tag, $value, $recurrence_data) {
		// $this->_recurrence_handle($trigger_id, $rule, $recurrence_type, $index);

		if ($this->recurrence_handler->is_rule_in_recurrence($recurrence_data->get_rule())) {
			$this->recurrence_handler->handle($recurrence_data); // Apply recurrence
		}

		// $this->statistics_handler->handle($recurrence_data);

		return $this->custom_apply_filters($tag, $value);
	}

	private function apply_filters_and_hooks_for_default( $tag,
														  $value,
														  $trigger_id ) {

		if ( empty($value) )
			return '';

		// $this->statistics_handler->handle_default($trigger_id);

		return $this->custom_apply_filters($tag, $value);
	}

	private function is_haystack_contains_needle($haystack, $needle) {
		if (!$haystack || 
			!$needle || 
			!is_array($haystack))
			return false;

		foreach ($haystack as $val) {
			if ((strpos($val, $needle) !== false) || 
				 strpos($needle, $val) !== false) {
				return true;
			}
		}

		return false;
	}

	private function get_current_page_id() {
		global $wp_query;
		$page_id = $wp_query->post->ID;
		return $page_id;
	}


	/* Handle logic regarded to `Pages Visited` trigger */
	private function handle_page_visit_feature() {
		// Save this page
		// $page_id = $this->get_current_page_id();
		// IfSo_Page_Visits_Service::getInstance()->save_page_id($page_id);
	}

	/*
	 *	Create shortcode
	 */
	public function add_if_so_shortcode( $atts ) {
		// Validates the license
		$this->license_service->edd_ifso_is_license_valid();

		// get post id from shortcode
		if(empty($atts['id'])) return '';
		$trigger_id = $atts['id'];
		
		/* first of all we check if thr trigger_id is either
		 * 'draft' or 'trash' which in case it is we
		 * just returning an empty string
		 */

		$post_status = get_post_status($trigger_id);

		if ( $post_status &&
			 ( ($post_status == 'draft') ||
			   ($post_status == 'trash') ))
			return '';

		$data = array();
		$data_default = get_post_meta( $trigger_id, 'ifso_trigger_default', true );
		$data_rules_json = get_post_meta( $trigger_id, 'ifso_trigger_rules', true );
		$data_rules = json_decode($data_rules_json, true);
		$data_versions = get_post_meta( $trigger_id, 'ifso_trigger_version', false );
		// echo "<pre>".print_r($_SERVER, true)."</pre>";
		$referrer = trim($_SERVER['HTTP_REFERER'], '/');
		$referrer = str_replace('https://', '', $referrer);
		$referrer = str_replace('http://', '', $referrer);
		$referrer = str_replace('www.', '', $referrer);
		$current_url = "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";//http://
		
		// echo "<script>console.log(document.referrer);</script>";

		// $this->handle_page_visit_feature();

		// Begin Testing Mode Check
		if (!empty($data_rules) && 
			isset($data_rules[0]['testing-mode']) &&
			$data_rules[0]['testing-mode'] != "") {

			if (!is_numeric($data_rules[0]['testing-mode'])) return 'NOT NUMERIC!';

			//declare testing mode var
			$testingModeIndex = intval($data_rules[0]['testing-mode']);

			if (0 == $testingModeIndex) {
				// the default content is the testing mode, so we return it
				// return ( !empty($data_default) ) ? 
				// 	    apply_filters('the_content', $data_default) : 
				// 	    '';
				return $this->apply_filters_and_hooks_for_default( 'the_content',
															$data_default,
															$trigger_id );
			}

			$testingModeIndex -= 2; // the default content takes one and it keeps counting from 2

			// otherwise just return the index's version content if it's not in the upper limit
			if (sizeof($data_rules) >= $testingModeIndex &&
				$testingModeIndex >= 0) {
				return $this->custom_apply_filters('the_content', $data_versions[$testingModeIndex]);
			}
		}
		// End Testing Mode Check

		if( empty($data_rules) ) {
			// return default content if rules are empty
			// return ( !empty($data_default) ) ? 
			// 		apply_filters('the_content', $data_default) :
			// 		'';
			return $this->apply_filters_and_hooks_for_default( 'the_content',
														$data_default,
														$trigger_id );
		}

		/* Begin Cookies Handle */

		$cookie_name = 'ifso_visit_counts';

		$isNewUser = $_COOKIE[$cookie_name] == '';
		$numOfVisits = 0;
		$isUserLoggedIn = is_user_logged_in();

		if (!$isNewUser)
			$numOfVisits = $_COOKIE[$cookie_name] + 1;
			
		setcookie($cookie_name, $numOfVisits, time() + (86400 * 30), "/"); // 86400 = 1 day

		/* End Cookies Handle */
		
		// echo "<pre>".print_r($data_rules, true)."</pre>";

		/* Begin Handle Closed Features */

		$status  = get_option( 'edd_ifso_license_status' );
		$isLicenseValid = ($status !== false && $status == 'valid') ? true : false;
		$license = get_option('edd_ifso_license_key');

		$free_features = $this->trigger_types_not_requires_license;

		/* End Handle Closed Features */

		/* Impressions */
		$this->impressions_service->increment();
		$this->impressions_service->handle($license);

		/* Recurrence Handle Begin */

		// check if there is a cookie for the current trigger (a session cookie)
		if (isset($_COOKIE["ifso_recurrence_session_" . $trigger_id])) {
			
			$session_cookie_for_trigger_id_json = 
				stripslashes($_COOKIE["ifso_recurrence_session_" . $trigger_id]);
			
			$session_cookie_for_trigger_id = 
				json_decode($session_cookie_for_trigger_id_json, true);

			// There is a session cookie!
			$recurrence_version_index = $session_cookie_for_trigger_id['version_index'];
			$recurrence_version_type = $session_cookie_for_trigger_id['trigger_type'];
			// $versions_count = count($data_rules);

			$recurrence_type = 'all-session';
			$recurrence_expiration_date = '';

			if ($this->recurrence_handler->is_recurrence_valid($data_rules, $recurrence_version_index, $recurrence_version_type, $recurrence_type, $isLicenseValid, $recurrence_expiration_date)) {
				// the recurrence is valid!

				// One last test is the recurrence type

				$rule = $data_rules[$recurrence_version_index];
				$index = $recurrence_version_index;



				$recurrence_data = 
					new If_So_Recurrence_Data($trigger_id,
										 	  $rule,
										 	  $index,
										 	  $data_rules);

				// $this->statistics_handler->handle($recurrence_data);

				// TODO maybe this causes the statistics not to update for
				// recurrences?

				if ( !isset( $rule['freeze-mode'] ) || $rule['freeze-mode'] != "true" )
					return $this->custom_apply_filters('the_content', $data_versions[$recurrence_version_index]);
			}


			// if ($versions_count > $version_index) {
			// 	// Well there is index like the cookie says...
			// 	$recurrence_trigger = $data_rules[$version_index];

			// 	if($version_type == $recurrence_trigger['trigger_type'] && // Same trigger type
			// 	   _is_trigger_in_recurrence($trigger) && // in recurrence
			// 	  (!$isLicenseValid && _is_trigger_requires_license($recurrence_trigger))) { // check if trigger type requires license
			// 		// Return this trigger! it's in recurrence cycle
			// 		// and we do have a session cookie for that one.

			// 		return apply_filters('the_content', $data_versions[$version_index]);

			// 	} else {
			// 		// Unset the cookie! it's either not in recurrence-cycle or the license is not valid!
			// 		// CONSIDER UNSETTING!
			// 	}
			// }

		}

		if (isset($_COOKIE['ifso_recurrence_data'])) {
			$recurrence_data_json = stripslashes($_COOKIE['ifso_recurrence_data']);
			$recurrence_data = json_decode($recurrence_data_json, true);

			// setcookie('ifso_recurrence_data', '', 1); // unset
			// echo $recurrence_data_json;
			// print_r($recurrence_data);
			// die;

			// pull out the data for the current trigger (if exists)
			if (array_key_exists($trigger_id, $recurrence_data)) {
				$trigger_recurrence_data = $recurrence_data[$trigger_id];

				/* recurrence structure:
				 * {
				 * 		expiration_date: <timestamp>,
			 	 * 		version_index: <version_index>,
				 * 		trigger_type: <trigger_type>,		
				 * }
				 */

				$recurrence_expiration_date = '';
				if (array_key_exists('expiration_date', $trigger_recurrence_data))
					$recurrence_expiration_date = $trigger_recurrence_data['expiration_date'];
				
				$recurrence_version_index = '';
				if (array_key_exists('version_index', $trigger_recurrence_data))
					$recurrence_version_index = $trigger_recurrence_data['version_index'];
				
				$recurrence_version_type = '';
				if (array_key_exists('trigger_type', $trigger_recurrence_data))
					$recurrence_version_type = $trigger_recurrence_data['trigger_type'];

				$recurrence_type = '';
				if (array_key_exists('recurrence_type', $trigger_recurrence_data))
					$recurrence_type = $trigger_recurrence_data['recurrence_type'];

				if ($this->recurrence_handler->is_recurrence_valid($data_rules, $recurrence_version_index, $recurrence_version_type, $recurrence_type, $isLicenseValid, $recurrence_expiration_date)) {
					// the recurrence is valid!

					// we need to check the expiration_date later (when we add the 'custom' option)

					$rule = $data_rules[$recurrence_version_index];
					$index = $recurrence_version_index;

					$recurrence_data = 
						new If_So_Recurrence_Data($trigger_id,
											 	  $rule,
											 	  $index,
											 	  $data_rules);

					// $this->statistics_handler->handle($recurrence_data);

					// maybe it causes the statistics not to update
					// for recurrences?

					if ( !isset( $rule['freeze-mode'] ) || $rule['freeze-mode'] != "true" )
						return $this->custom_apply_filters('the_content', $data_versions[$index]);
				}
			}
		}

		/* Recurrence Handle End */

		// May hold user's geolocation data in case there is `Geolocation` version
		$user_geolocation_data = "";

		foreach($data_rules as $index => $rule) {
			if(empty($rule['trigger_type'])) continue;
			if($rule['freeze-mode'] == "true") continue; // skip freezed version
			// License no valid
			if (!$isLicenseValid && !in_array($rule['trigger_type'], $free_features)) continue;
			// Sub child features
			if (!$isLicenseValid && 
				$rule['trigger_type'] == "User-Behavior" &&
				!in_array($rule['User-Behavior'], array("LoggedIn", "LoggedOut", "Logged"))) continue;

			// Create recurrence data object if this rule is in recurrence-cycle
			// if ($this->recurrence_handler->is_rule_in_recurrence($rule)) {
				// Create Recurrence Data object
			$recurrence_data = new If_So_Recurrence_Data($trigger_id,
														 $rule,
														 $index,
														 $data_rules);
			// }

			if($rule['trigger_type'] == 'referrer') {
				// handle referrer
				if ($rule['trigger'] == 'common-referrers') {
					$chose_common_referrers = $rule['chosen-common-referrers'];

					if($chose_common_referrers == 'facebook') {
						if(strpos($referrer, 'facebook.com') !== false)
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}
					else if($chose_common_referrers == 'google') {
						if(strpos($referrer, 'google.') !== false)
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}
					// TODO - check twitter referrer not working ($_SERVER['HTTP_REFERER'] is empty)
					else if($chose_common_referrers == 'twitter') {
						if(strpos($referrer, 'twitter.') !== false)
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}
					else if($chose_common_referrers == 'youtube') {
						if(strpos($referrer, 'youtube.') !== false)
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}
				}
				else if($rule['trigger'] == 'page-on-website' && $rule['page']) {
					
					$page_id = (int)$rule['page'];
					$page_link = get_permalink($page_id);
					$page_link = trim($page_link, '/');
					$page_link = str_replace('https://', '', $page_link);
					$page_link = str_replace('http://', '', $page_link);
					$page_link = str_replace('www.', '', $page_link);
					// echo "<pre>".print_r($page_link, true)."</pre>";
					// var_dump($referrer);
					// $page = get_page($page_id);
					if($referrer == $page_link)
						return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
				}
				else {
					// custom referrer
					// handle url custom referrer - currently the only one
					if($rule['custom'] == 'url') {
						
						if($rule['operator'] == 'is' || $rule['operator'] == 'is-not') {
							// remove trailing slashes and http from comparition when exact match is requested
							$rule['compare'] = trim($rule['compare'], '/');
							$rule['compare'] = str_replace('https://', '', $rule['compare']);
							$rule['compare'] = str_replace('http://', '', $rule['compare']);
							$rule['compare'] = str_replace('www.', '', $rule['compare']);
						}
						
						if($rule['operator'] == 'contains' && (strpos($referrer, $rule['compare']) !== false))
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // match wildcards
						else if($rule['operator'] == 'is' && $referrer == $rule['compare']) 
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // exact match
						else if($rule['operator'] == 'is-not' && $referrer != $rule['compare'])
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // not exact match
						else if($rule['operator'] == 'not-containes' && (strpos($referrer, $rule['compare']) === false))
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // does'nt match wildcards
					}
				}
			}
			else if ($rule['trigger_type'] == 'PageUrl') {
				$operator = $rule['page-url-operator'];
				$page_url = $rule['page-url-compare'];

				if($operator == 'is' || $operator == 'is-not') {
					// remove trailing slashes and http from comparition when exact match is requested
					$page_url = trim($page_url, '/');
					$page_url = str_replace('https://', '', $page_url);
					$page_url = str_replace('http://', '', $page_url);
					$page_url = str_replace('www.', '', $page_url);
				}
				
				if($operator == 'contains' && (strpos($current_url, $page_url) !== false))
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // match wildcards
				else if($operator == 'is' && $current_url == $page_url) 
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // exact match
				else if($operator == 'is-not' && $current_url != $page_url)
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // not exact match
				else if($operator == 'not-containes' && (strpos($current_url, $page_url) === false))
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data); // does'nt match wildcards

			}
			else if($rule['trigger_type'] == 'url' || $rule['trigger_type'] == 'advertising-platforms') {

				$compare = '';
				if ($rule['trigger_type'] == 'url') {
					$compare = $rule['compare'];
				} else if ($rule['trigger_type'] == 'advertising-platforms') {
					$compare = $rule['advertising_platforms'];
				}

				if(!empty($_GET['ifso']) && $_GET['ifso'] == $compare)
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
			}
			else if($rule['trigger_type'] == 'AB-Testing') {
				if (!empty($rule['AB-Testing'])) {
					$perc 			= $rule['AB-Testing'];
					// $gCounter 		= $rule['a-b-counter'];
					$numberOfViews 	= $rule['number_of_views'];
					$sessionsBound 	= $rule['ab-testing-sessions'];

					if ($sessionsBound == 'Custom') {
						if (empty($rule['ab-testing-custom-no-sessions'])) continue;

						$sessionsBound = $rule['ab-testing-custom-no-sessions'];
					}

					// Check if we passed the number of sessions
					// dedicated to that post
					if ($sessionsBound != 'Unlimited' &&
						$numberOfViews >= (int)$sessionsBound) continue;

					$factors = array("25%" => 4,
									 "33%" => 3,
									 "50%" => 2,
									 "75%" => 4);

					$factor = $factors[$perc];

					$factRemainder = $numberOfViews % $factor;

					// Sets new a-b-counter
					// $gCounter += 1;
					$numberOfViews += 1;

					// $data_rules[$index]['a-b-counter'] = $gCounter % $factor;
					$data_rules[$index]['number_of_views'] = $numberOfViews;

					$data_rules_cleaned = str_replace("\\", "\\\\\\", json_encode($data_rules));

					update_post_meta( $trigger_id, 'ifso_trigger_rules', $data_rules_cleaned);

					if ($perc == "25%" && $factRemainder == 0) {
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					} else if ($perc == "33%" && $factRemainder == 0) {
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					} else if ($perc == "50%" && $factRemainder == 0) {
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					} else if ($perc == "75%" &&
							   in_array($factRemainder, array(0, 1, 2))) {
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}
				} 
			} else if ($rule['trigger_type'] == 'User-Behavior') {
					/*
						Helpers:
							$isNewUser = TRUE/FALSE
							$numOfVisits = NUMBER
							$isUserLoggedIn = TRUE/FALSE
					*/

					$user_behavior = $rule['User-Behavior'];


					if ($user_behavior == "NewUser") {
						// Check if new user

						if ($isNewUser) {
							// Yes he is a new user!
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}

					} else if ($user_behavior == "Returning") {
						// Check if the user is returning based on 
						// 'user-behavior-returning' OR 'user-behavior-retn-custom'
						// incase 'user-behavior-returning' is CUSTOM.

						// Check if it's custom thus use 'user-behavior-retn-custom'

						$numOfReturns = 0;

						if ($rule['user-behavior-returning'] == "custom") {
							$numOfReturns = intval($rule['user-behavior-retn-custom']);
						} else {
								$returnsOptions = array("first-visit" => 1,
									 					"second-visit" => 2,
												 		"three-visit" => 3);

								$numOfReturns = $returnsOptions[$rule['user-behavior-returning']];
						}

						// In here, $numOfReturns hold the number of returns we desire

						if ($numOfVisits >= $numOfReturns) {
							// We have a match! :)
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}

					} else if ($user_behavior == "LoggedIn") {
						// Check if the user is Logged in
						if ($isUserLoggedIn) {
							// Yes, he is!
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}
					} else if ($user_behavior == "LoggedOut") {
							// Check if the user is Logged out
							if (!$isUserLoggedIn) {
								// Yes, he isn't!
								return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
							}
					} else if ($user_behavior == "Logged") {
						// New Version of Logged In Out.
						// Keeping the previous one for backward compatibility.
						$loggedInOut = $rule['user-behavior-logged'];
						// return $loggedInOut;

						if ($loggedInOut == "logged-in" && $isUserLoggedIn) {
							// Yes! he is logged in!
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						} else if ($loggedInOut == "logged-out" && !$isUserLoggedIn) {
							// Yes! he is logged off
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}
					} else if ($user_behavior == "BrowserLanguage") {
						// grab user's language
						$user_languages = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
						$languages = [];

						preg_match_all("/[a-zA-Z-]{2,10}/",
									   $user_languages,
									   $languages);

						if ($languages &&
							is_array($languages[0]))
							$languages = $languages[0];

						$isPrimary = false;

						if ( isset($rule['user-behavior-browser-language-primary-lang']) )
							$isPrimary = ($rule['user-behavior-browser-language-primary-lang'] == 'true');

						$selectedLanguage = $rule['user-behavior-browser-language'];

						// check if user's language is in match with the 
						// user behavior selected language

						$isPresent = false;

						if ( $isPrimary ) {
							// the checkbox is selected, thus check only for 1st language
							// in the last (aka `primary`)

							if (is_array($languages)) {
								$isPresent = ($selectedLanguage == $languages[0]);
							} else {
								$isPresent = ($selectedLanguage == $languages);
							}
						} else {
							$isPresent = $this->is_haystack_contains_needle($languages,
								$selectedLanguage);
						}

						if ( $isPresent ) {
							// Yes! User's language is the same as the ifso setting
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}
					}
			} else if ($rule['trigger_type'] == "Device") {
				if ($rule["user-behavior-device-mobile"] && my_wp_is_mobile()) {
					// User is on Mobile
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
				} else if ($rule["user-behavior-device-tablet"] && my_wp_is_tablet()) {
					// User is on Tablet
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
				} else if ($rule["user-behavior-device-desktop"] && (!my_wp_is_mobile() && !my_wp_is_tablet())) {
					// User is on Desktop
					return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
				}
			} else if ($rule['trigger_type'] == "Time-Date") {

				// Check if the selection is "Start/End Date"

				$format = "Y/m/d H:i";
				$currDate = DateTime::createFromFormat($format, current_time($format));

				if ($rule["Time-Date-Schedule-Selection"] == "Start-End-Date") {
					if ( ( isset($rule['Time-Date-Start']) &&
						   isset($rule['Time-Date-End']) && 
						   $rule['Time-Date-Start'] == "None" &&
						   $rule['Time-Date-End'] == "None" ) || 
						 ( empty($rule['time-date-end-date']) && 
						  	empty($rule['time-date-start-date']) ) ) {
						return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
					}

					if ( ( isset($rule['Time-Date-Start']) && 
						   $rule['Time-Date-Start'] == "None" ) ||
						  empty($rule['time-date-start-date']) ) {

						// No start date
						$endDate = DateTime::createFromFormat($format, $rule['time-date-end-date']);

						if ($currDate <= $endDate) {
							// Yes! we are in the right time frame
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}

					} else if ( ( isset($rule['Time-Date-End']) && 
						   		  $rule['Time-Date-End'] == "None" ) ||
						  		  empty($rule['time-date-end-date']) ) {

						// No end date
						$startDate = DateTime::createFromFormat($format, $rule['time-date-start-date']);

						if ($currDate >= $startDate) {
							// Yes! we are in the right time frame
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}
					} else {
						// Both have dates
						$startDate = DateTime::createFromFormat($format, $rule['time-date-start-date']);
						$endDate = DateTime::createFromFormat($format, $rule['time-date-end-date']);

						if ($currDate >= $startDate &&
							$currDate <= $endDate) {
							// Yes! we are in the right time frame
							return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
						}
					}
				} else {
					// Otherwise the selection is "Schedule-Date"
					$schedule = json_decode($rule['Date-Time-Schedule']);
					$currTime = current_time($format);
					$currDay = date('w');
					$selectedHours = $schedule->$currDay;
					$dayYearMonth = preg_split("/ /", $currTime)[0];

					if (!empty($selectedHours)) {
						foreach ($selectedHours as $hoursKey => $hoursPair) {
							$startHour = $dayYearMonth." ".$hoursPair[0];
							$endHour = $dayYearMonth." ".$hoursPair[1];
							
							$startDate = DateTime::createFromFormat($format, $startHour);
							$endDate = DateTime::createFromFormat($format, $endHour);

							// Check if in between
							// if so we display this version's content

							if ($currDate >= $startDate &&
								$currDate <= $endDate) {
								return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
							}
						}
					}
				}
			} else if ($rule['trigger_type'] == "Geolocation") {

				if (!empty($rule['geolocation_data'])) {
					$geolocation_data = utf8_decode($rule['geolocation_data']);
					$splitted_geolocation_data = explode("^^", $geolocation_data);

					// Get client's IP
					if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
					    $ip = $_SERVER['HTTP_CLIENT_IP'];
					} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
					    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
					} else {
					    $ip = $_SERVER['REMOTE_ADDR'];
					}

					if ( empty( $user_geolocation_data ) ) {
						$user_geolocation_data = $this->geo_service->get_location_by_ip($license,
																   $ip);
					}

					if ( $user_geolocation_data != NULL &&
						 isset($user_geolocation_data['success']) &&
						 isset($user_geolocation_data['countryCode']) &&
						 isset($user_geolocation_data['city']) &&
						 isset($user_geolocation_data['stateProv']) &&
						 $user_geolocation_data['success'] &&
						 $user_geolocation_data['countryCode'] &&
						 $user_geolocation_data['city'] ) {
						// No error
						$countryCode = $user_geolocation_data['countryCode'];
						$stateProv = $user_geolocation_data['stateProv'];
						$continentCode = $user_geolocation_data['continentCode'];
						
						$city = $this->convert_smart_quotes(trim($user_geolocation_data['city']));
						$city = str_replace("‘", "'", $city); // Replace that weird character

	                    foreach ($splitted_geolocation_data as $key => $value) {
	                        $explodedData = explode("!!", $value);

	                        $symbolType = strtolower($explodedData[0]);

	                        if ($symbolType == "country") {
	                        	// COUNTRY HANDLING

	                        	$selectedCountryCode = $explodedData[2];

	                        	if (!$selectedCountryCode)
	                        		continue;

		                        if ($this->areTheyEqualOrContains($countryCode, $selectedCountryCode)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
		                        }
	                        } else if ($symbolType == 'city') {
	                        	// CITY HANDLING
	                        	
	                        	$selectedCity = $explodedData[2];

		                        $cleanedSelectedCity = $this->convert_smart_quotes(trim(str_replace('\\', '', $selectedCity)));
								$cleanedSelectedCity = str_replace("‘", "'", $selectedCity);

								if (!$cleanedSelectedCity)
									continue;

								if ($this->areTheyEqualOrContains($city, $cleanedSelectedCity)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
								} else if ($this->areTheyEqualOrContains($stateProv, $cleanedSelectedCity)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
								}
	                        } else if ($symbolType == 'continent') {
	                        	// CONTINENT HANDLING

	                        	$selectedContinentCode = $explodedData[2];

	                        	if (!$selectedContinentCode)
	                        		continue;

		                        if ($this->areTheyEqualOrContains($continentCode, $selectedContinentCode)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
		                        }
	                        } else if ($symbolType == 'state') {
	                        	// STATE HANDLING

	                        	$selectedStateName = $explodedData[1];

	                        	if (!$selectedStateName)
	                        		continue;

		                        if ($this->areTheyEqualOrContains($stateProv, $selectedStateName)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
		                        }
	                        } else if ($symbolType == 'timezone') {
	                        	// TIMEZONE HANDLING

	                        	if ( !isset($userTimezone) ) {
	                        		$userTimezone = "";

	                        		if (isset($user_geolocation_data['timeZone'])) {
										$userTimezone = $user_geolocation_data['timeZone'];
										$userTimezone = str_replace("\/", "/", $userTimezone);
									}
	                        	}

	                        	$selectedTimezone = $explodedData[1];

	                        	if (!$selectedTimezone)
	                        		continue;

		                        if ($this->isUserTimezoneInSelectedTimeZone($userTimezone, $selectedTimezone)) {
		                        	// We got a match!
		                        	return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
		                        }
	                        }
	                    }
                	}
				}
			} else if ($rule['trigger_type'] == "PageVisit") {

				if (!empty($rule['page_visit_data'])) {
					$page_visit_data = utf8_decode($rule['page_visit_data']);
					$page_visit_data = explode("^^", $page_visit_data);

					foreach ($page_visit_data as $key => $value) {
						$data = explode("!!", $value);
						$symbolType = strtolower($data[0]);


						if ($symbolType == "page") {
							$page_id = $data[2];
							$is_visited = 
								IfSo_Page_Visits_Service::getInstance()->is_visited($page_id);

							if ($is_visited) {
		                        return $this->apply_filters_and_hooks('the_content', $data_versions[$index], $recurrence_data);
							}
						}
					}


				}


			}
		}
			//echo "<pre>".print_r($rule, true)."</pre>";
		
		// return default content if nothing match
		// return (!empty($data_default)) ? apply_filters('the_content', $data_default) : '';
		return $this->apply_filters_and_hooks_for_default( 'the_content',
													$data_default,
													$trigger_id );
	}

	private function isUserTimezoneInSelectedTimeZone($userTimezone, $timezoneName) {
		global $timezones;

		$utcs = array();

		if ( isset( $timezones[$timezoneName] ) )
			$utcs = $timezones[$timezoneName];

		if ( empty($utcs) )
			return false;

		foreach ($utcs as $timezone) {
			if ($this->areTheyEqualOrContains($userTimezone, $timezone))
				return true;
		}

		return false;
	}

	private function areTheyEqualOrContains($a, $b) {
		return (strpos($a, $b) !== false ||
				strpos($b, $a) !== false ||
			    $a == $b);
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/if-so-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugin_Name_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugin_Name_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		$ajax_nonce = wp_create_nonce( "ifso-nonce" );
		echo "<script>var nonce = '".$ajax_nonce."';</script>";
		$ajax_url = admin_url('admin-ajax.php');
		echo "<script>var ajaxurl = '".$ajax_url."';</script>";
		$page_id = $this->get_current_page_id();
		echo "<script>var ifso_page_id = '".$page_id."';</script>";

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/if-so-public.js', array( 'jquery' ), $this->version, false );
	}

	public function wp_ajax_ifso_add_page_visit_handler() {
		check_ajax_referer( 'ifso-nonce', 'nonce' );

		$page_id = intval( $_POST['page_id'] );
		IfSo_Page_Visits_Service::getInstance()->save_page_id($page_id);

		wp_die(); // indicate end of stream
	}

}
