<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/admin
 * @author     Your Name <email@example.com>
 */
class If_So_Admin {

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
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->load_dependencies();
	}
	
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		 
		//require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/class-if-so-settings-list.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) .  'admin/class-if-so-settings.php';
	}
	
	public function register_post_types() {
		$labels = array(
			'name'               => _x( 'Triggers', 'if-so' ),
			'singular_name'      => _x( 'Trigger', 'if-so' ),
			'add_new'            => _x( 'Add New', 'if-so' ),
			'add_new_item'       => __( 'Add New Trigger', 'if-so' ),
			'edit_item'          => __( 'Edit Trigger', 'if-so' ),
			'new_item'           => __( 'New Trigger', 'if-so' ),
			'all_items'          => __( 'All Triggers', 'if-so' ),
			'view_item'          => __( 'View Trigger', 'if-so' ),
			'search_items'       => __( 'Search Triggers', 'if-so' ),
			'not_found'          => __( 'No Triggers found', 'if-so' ),
			'not_found_in_trash' => __( 'No Triggers found in the Trash', 'if-so' ), 
			'parent_item_colon'  => '',
			'menu_name'          => 'Triggers'
		);
		/*$args = array(
			'labels'        => $labels,
			'description'   => 'Holds all the customized content triggers',
			'public'        => true,
			//'menu_position' => 5,
			'show_ui'             => true,
			'show_in_menu'        => 'if-so',
			'show_in_nav_menus'   => false,
			'exclude_from_search' => true,
			//public 
			'supports'      => array( 'title' ), //, 'editor'
			'has_archive'   => false,
		);*/
		$args = array(
			'labels'             => $labels,
			'description'        => __( 'Holds all the customized content triggers', 'if-so' ),
			'public'             => true,
			// 'publicly_queryable' => false, // removed at 27/1/2018
			'exclude_from_search' => true,
			'show_ui'            => true,
			'show_in_menu'       => 'if-so',
			'menu_position'			=> 90,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array( 'title', 'revisions' )
		);
		register_post_type( 'ifso_triggers', $args ); 
	}

	/**
	 * Register the stylesheets for the admin area.
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

		//wp_enqueue_style( $this->plugin_name.'BootstrapGridOnly', plugin_dir_url( __FILE__ ) . 'css/bootstrap-grid-only.css', array(), $this->version, 'all' );
		
		// load botstrap only on current plugin - to prevent collision
		$current_post_type = get_post_type();
		if(!empty($current_post_type) && $current_post_type == 'ifso_triggers') {
			echo "<style>
				/* collusion fix with other plugins */
				#ifso_triggers_metabox.postbox, #ifso_shortcode_display.postbox {
					display: block !important;
				}

				#edit-slug-box {
					display:none;
				}
			</style>";
			wp_enqueue_style( $this->plugin_name.'BootstrapCustom', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', array(), $this->version, 'all' );
		}
		
		//wp_enqueue_style( $this->plugin_name.'BootstrapCss', 'https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name.'FontAwesome', plugin_dir_url( __FILE__ ) . 'css/font-awesome-4.7.0/css/font-awesome.min.css', array(), $this->version, 'all' );

		/* jquery modal - jquerymodal.com */
		wp_enqueue_style( $this->plugin_name.'IfSoJqueryModalCSS', plugin_dir_url( __FILE__ ) . 'css/jquery.modal.min.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name.'Style', plugin_dir_url( __FILE__ ) . 'css/if-so-admin.css', array(), $this->version, 'all' );

		wp_enqueue_style( $this->plugin_name.'JQueryUiMinCss', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.min.css', array(), $this->version, 'all' );
		
		wp_enqueue_style( $this->plugin_name.'DateTimePickerCss', plugin_dir_url( __FILE__ ) . 'css/jquery.ifsodatetimepicker.css', array(), $this->version, 'all' );
		
		wp_enqueue_style( $this->plugin_name.'EasyAutoCompleteCSS', plugin_dir_url( __FILE__ ) . 'css/easy-autocomplete.min.css', array(), $this->version, 'all' );
				

		if(!empty($current_post_type) && $current_post_type == 'ifso_triggers' && is_rtl()) {
			wp_enqueue_style( $this->plugin_name.'StyleRtl', plugin_dir_url( __FILE__ ) . 'css/if-so-admin-rtl.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
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
		

		global $plugin_page;
		if ( is_plugin_active("wp-all-import-pro/wp-all-import-pro.php") &&
		     	( $plugin_page === 'pmxi-admin-import' || 
		     	  $plugin_page === 'pmxi-admin-manage' ) ) {
			// Prevent JS error
			return;
		} else if ( is_plugin_active("wp-all-export/wp-all-export.php") &&
		     		  ( $plugin_page === 'pmxe-admin-export' || 
		     		    $plugin_page === 'pmxe-admin-manage' ) ) {
			// Prevent JS error
			return;
		}

		$ajax_nonce = wp_create_nonce( "my-nonce-string" );

		echo "<script>var base_url = '".home_url()."';</script>";
		echo "<script>var nonce = '".$ajax_nonce."';</script>";
		echo "<script>
				var jsTranslations = [];
				jsTranslations['Version'] = '".__('Version')."';
				jsTranslations['translatable_dupplicated_query_string_notification_trigger'] = '".__('This query string is already in use with the current trigger.')."';
				jsTranslations['translatable_dupplicated_query_string_notification_publish'] = '".__('It is not possible to create two query strings with the same name. If you publish now, the second version will be deleted.')."';
		</script>";
		// wp_enqueue_script( $this->plugin_name.'GoogleAPIService', 'https://maps.googleapis.com/maps/api/js?key='.GOOGLE_API_KEY.'&libraries=places&callback=initAutocomplete', array(  ), $this->version, true );
		wp_enqueue_script( $this->plugin_name.'IfSoHelpers', plugin_dir_url( __FILE__ ) . 'js/helpers.js', array(), $this->version, false );

		wp_enqueue_script( $this->plugin_name.'BootstrapJS', plugin_dir_url( __FILE__ ) . 'js/bootstrap.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'JQueryMinUI', plugin_dir_url( __FILE__ ) . 'js/jquery-ui.min.js', array( 'jquery' ), $this->version, false );


		wp_enqueue_script( $this->plugin_name.'BootstrapValidator', plugin_dir_url( __FILE__ ) . 'js/validator.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'DateTimePickerFullMinJs', plugin_dir_url( __FILE__ ) . 'js/jquery.ifsodatetimepicker.full.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'WeeklyScheduleMinJs', plugin_dir_url( __FILE__ ) . 'js/jquery.weekly-schedule-plugin.min.js', array( 'jquery' ), $this->version, false );
		//wp_enqueue_script( $this->plugin_name.'RepeaterJs', plugin_dir_url( __FILE__ ) . 'js/repeater.js', array( 'jquery' ), $this->version, false );

		wp_enqueue_script( $this->plugin_name.'GooglePlacesJS', plugin_dir_url( __FILE__ ) . 'js/if-so-google-places.js', array( 'jquery' ), $this->version, true );

		wp_enqueue_script( $this->plugin_name.'EasyAutocompleteJS', plugin_dir_url( __FILE__ ) . 'js/jquery.easy-autocomplete.min.js', array( 'jquery' ), $this->version, false );

		/* jquery modal - http://jquerymodal.com/ */
		wp_enqueue_script( $this->plugin_name.'IfSoJqueryModalJS', plugin_dir_url( __FILE__ ) . 'js/jquery.modal.min.js', array( 'jquery' ), $this->version, false );		

		wp_enqueue_script( $this->plugin_name.'CustomizedContentJs', plugin_dir_url( __FILE__ ) . 'js/if-so-admin.js', array( 'jquery' ), $this->version, false );
		

		wp_enqueue_script( $this->plugin_name.'GooglePlacesAPI', 'https://maps.googleapis.com/maps/api/js?key='.GOOGLE_API_KEY.'&language=en&libraries=places&callback=initAutocomplete', array(), $this->version, true );






		// die($GOOGLE_SERVICE_URL);


	}

}