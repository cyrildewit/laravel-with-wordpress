<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://if-so.com
 * @since      1.0.0
 *
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Plugin_Name
 * @subpackage Plugin_Name/includes
 * @author     Your Name <info@if-so.com>
 */
class If_So {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Plugin_Name_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/* license plans */
	protected $plans;

	/* number of times to check if the license is 
		valid before deacitvation occur */
	protected $num_of_retries_to_check_license;

	/* interval between checking valid license (in seconds) */
	protected $interval_valid_license_check;

	/* interval between checking invalid license (in seconds) */
	protected $interval_invalid_license_check;

	/* Services */

	/* License Service. handles all license functionality */
	protected $license_service;

	/* Geolocation Service */
	protected $geo_service;

	/* Impressions Service */
	protected $impressions_service;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'if-so';
		$this->version = '2.0.4';

		/* License Configuration */

		// $this->ifso_primary_license_product_id = 2473; // Free Trial
		$this->plans = array(
			6530,
			2473,
			5965,
			8261,
			9129,
			9132,
			9134,
			9136,
			9029,
		);

		// "If>So Dynamic WordPress Content – Lifetime License",
		// "If>So Dynamic WordPress Content – Yearly Subscription",
		// "If>So Dynamic WordPress Content – Monthly Subscription"

		$this->num_of_retries_to_check_license = 8;
		$this->interval_valid_license_check = (60 * 60 * 12);
		$this->interval_invalid_license_check = (60 * 60 * 6);

		$this->define_global_constants();
		$this->load_dependencies();
		$this->load_services();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Plugin_Name_Loader. Orchestrates the hooks of the plugin.
	 * - Plugin_Name_i18n. Defines internationalization functionality.
	 * - Plugin_Name_Admin. Defines all hooks for the admin area.
	 * - Plugin_Name_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-if-so-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-if-so-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-if-so-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-if-so-public.php';

		if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			// load our custom updater
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'services/libs/EDD_SL_Plugin_Updater.php';
		}

		/**
		 * The class responsible for defining all code necessary to activate /
		 deactivate / etc of IfSo's License.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'services/class-if-so-license-service.php';

		/**
		 * The class responsible for defining all code related to Geolocation
		 	Service
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'services/class-if-so-geo-service.php';

		/**
		 * Imperssions service. Members impressions updater
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'services/class-if-so-impressions-service.php';

		$this->loader = new If_So_Loader();
	}

	private function load_services() {
		$this->load_license_service();
		$this->load_geo_service();
		$this->load_impressions_service();
	}

	private function load_license_service() {
		$this->license_service = 
			new If_So_License_Service(
				$this->plans,
			  	$this->num_of_retries_to_check_license,
			  	$this->interval_valid_license_check,
			  	$this->interval_invalid_license_check
			);
	}

	private function load_geo_service() {
		$this->geo_service = 
				If_So_Geo_Service::getInstance();
	}

	private function load_impressions_service() {
		$this->impressions_service = 
				new If_So_Impressions_Service();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Plugin_Name_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new If_So_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new If_So_Admin( $this->get_plugin_name(), $this->get_version() );
		$plugin_settings = new If_So_Admin_Settings( $this->get_plugin_name(), $this->get_version() );
		
		$license_service = $this->get_license_service();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'init', $plugin_admin, 'register_post_types', 1 );
		
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'add_plugin_menu_items' );
		$this->loader->add_filter( 'manage_ifso_triggers_posts_columns', $plugin_settings, 'ifso_add_custom_column_title', 100, 1 );
		$this->loader->add_action( 'manage_ifso_triggers_posts_custom_column', $plugin_settings, 'ifso_add_custom_column_data', 10, 2 );
		
		
		$this->loader->add_action( 'add_meta_boxes_ifso_triggers', $plugin_settings, 'ifso_add_meta_boxes', 1 );
		$this->loader->add_action( 'save_post_ifso_triggers', $plugin_settings, 'ifso_save_post_type' );
		$this->loader->add_filter( 'wpseo_metabox_prio', $plugin_settings, 'move_yoast_metabox_down', 10 );
		
		$this->loader->add_filter( 'template_include', $plugin_settings, 'include_ifso_custom_triggers_template', 1 );

		/* Ajax Actions */		
		$this->loader->add_action( 'wp_ajax_load_tinymce_repeater', $plugin_settings, 'load_tinymce' );

		$this->loader->add_action( 'wp_ajax_reset_analytics_count', $plugin_settings, 'reset_analytics_count_handler' );
		
		$this->loader->add_action( 'wp_ajax_reset_all_analytics_count', $plugin_settings, 'reset_all_analytics_count_handler' );

		$this->loader->add_action( 'wp_ajax_refresh_analytics_count', $plugin_settings, 'refresh_analytics_count_handler' );
		// $this->loader->add_action('admin_init', $plugin_settings,'edd_ifso_register_option');

		/* License Hooks */
		$this->loader->add_action('admin_init', $license_service,'edd_ifso_activate_license');
		$this->loader->add_action('admin_init', $license_service,'edd_ifso_deactivate_license');
		$this->loader->add_action('admin_init', $license_service,'edd_sl_ifso_plugin_updater',0);
		$this->loader->add_action('admin_init', $license_service,'edd_ifso_is_license_valid',0);

		$this->loader->add_action('admin_notices', $plugin_settings,'edd_ifso_admin_notices');
		
		// $this->loader->add_filter('the_content', $plugin_settings, 'custom_triggers_template', 99);

		// no need for the following action - used for if you want it to fire on the front-end for both visitors and logged-in users
		//add_action( 'wp_ajax_nopriv_my_action', 'my_action_callback' );
	}

	private function define_global_constants() {
		define( 'EDD_IFSO_STORE_URL', 'https://if-so.com' );
		define( 'EDD_IFSO_ITEM_NAME', 'If>So Dynamic WordPress Content - Monthly Subscription' );
		define( 'EDD_IFSO_PLUGIN_LICENSE_PAGE', 'wpcdd_admin_menu_settings' );
		define( 'GOOGLE_API_KEY', 'AIzaSyD_84lpDfAJL1Tee8nDHcATxb4QmSZjgTU' );
		define("IFSO_PLUGIN_MAIN_FILE_NAME", __FILE__);

		define("IFSO_WP_VERSION", '1.2.8');
		define("IFSO_API_VERSION", 'v2');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new If_So_Public( $this->get_plugin_name(), 
										   $this->get_version(),

										   $this->get_license_service(),
										   $this->get_geo_service(), 
										   $this->get_impressions_service() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_ajax_ifso_add_page_visit', $plugin_public, 'wp_ajax_ifso_add_page_visit_handler' );
		$this->loader->add_action( 'wp_ajax_nopriv_ifso_add_page_visit', $plugin_public, 'wp_ajax_ifso_add_page_visit_handler' );

		// create shortcode
		$this->loader->add_shortcode( 'ifso', $plugin_public, 'add_if_so_shortcode' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Plugin_Name_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	public function get_license_service() {
		return $this->license_service;
	}

	public function get_geo_service() {
		return $this->geo_service;
	}

	public function get_impressions_service() {
		return $this->impressions_service;
	}
}
