<?php
/**
 * Plugin Name:       Manage eMail Whitelist 
 * Plugin URI:        https://fungus.at/plugins/mgremailwhitelist/
 * Description:       Manage eMail Whitelists for a given postgrey-installation
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Rene Pilz
 * Author URI:        https://pilz.cc/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       mgremailwhitelist
 * Domain Path:       /languages
 */

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
        exit;


/**
 * Manage eMail Whitelist main class
 *
 * @class ManageEMailWhitelist
 * @version 0.0.1
 */
class ManageEMailWhitelist {
	private static $_instance;


	/**
	 * Disable object cloning.
	 */
	public function __clone() {}

	/**
	 * Disable unserializing of the class.
	 */
	public function __wakeup() {}

	/**
	 * Main plugin instance.
	 *
	 * @return object
	 */
	public static function instance() {
		if ( self::$_instance === null ) {
			self::$_instance = new self();
			add_action( 'plugins_loaded', array( self::$_instance, 'load_textdomain' ) );
			self::$_instance->includes();
			
		}
		return self::$_instance;
	}


	/**
	 * Constructor.
	 */
	public function __construct() {
		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

	}

	/**
	 * Include required files.
	 *
	 * @return void
	 */
	private function includes() {
		// include_once( plugin_dir_path( __FILE__ ) . 'includes/bot-detect.php' );
	}


	/**
	 * Create Tables
	 *
	 * @return void
	 */
	private function createTables() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


		$table_companies = $wpdb->prefix . 'mgremailwhitelist_companies';
		$sql = "CREATE TABLE IF NOT EXISTS $table_companies (
        		company_id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        		company_name varchar(50) NOT NULL) $charset_collate;";
		dbDelta( $sql );


		$table_name = $wpdb->prefix . 'mgremailwhitelist_companyadmins';
		$table_wp_users = $wpdb->prefix . 'users';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			company_id bigint(20) NOT NULL,
			wp_userid  bigint(20) unsigned NOT NULL,
			FOREIGN KEY (company_id) REFERENCES $table_companies (company_id),
			PRIMARY KEY (company_id,wp_userid) ) $charset_collate;";
		dbDelta( $sql );

		$table_name = $wpdb->prefix . 'mgremailwhitelist_companymailaccounts';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			email_id varchar(50) NOT NULL PRIMARY KEY,
			company_id bigint(20) NOT NULL,
			FOREIGN KEY (company_id) REFERENCES $table_companies (company_id)
			) $charset_collate;";
		dbDelta( $sql );
	}


	/**
	 * Plugin Activation
	 *
	 * @return void
	 */
	public function activation() {
		$this->createTables();

	}

        /**
         * Add Settings link to plugins - code from GD Star Ratings
         *
         * @wp-filter  plugin_action_links
         * @param  array   $links
         * @param  string  $file
         * @return array
         */
        public function add_settings_link($links, $file) {
                static $this_plugin;
                if (!$this_plugin) {
                        $this_plugin = plugin_basename(__FILE__);
                }

                if ($file == $this_plugin) {
                        $settings_link = '<a href="'.esc_url(admin_url('options-general.php?page=mgremailwhitelist')).'">'.__('Settings', 'mgremailwhitelist').'</a>';
                        array_unshift($links, $settings_link);
                }
                return $links;
        }

	// Register the settings page
	function register_settings_page() {
		add_options_page( __( 'ManageEMailWhitelist Settings', 'mgremailwhitelist' ), __( 'ManageEMailWhitelist', 'mgremailwhitelist' ), 'manage_options', 'mgremailwhitelist', array( $this, 'settings_page' ) );
	}


	// Register the plugin's setting
	function register_setting() {
		register_setting( 'mgremailwhitelist_settings', 'mgremailwhitelist_settings', array( $this, 'validate_settings' ) );
	}


	// Settings page
	function settings_page() {
		echo "<div class='wrap'>
			<h2>".esc_html__( 'ManageEMailWhitelist Settings', 'syntaxhighlighter' )."</h2>
		     ";
	}


	// Dashboard
	public function wpew_init_dashboard_widget() {
		wp_add_dashboard_widget(
			'mgremail_dash',
			'<span contenteditable="true" class="wpdn-title">E-Mail Whitelist</span><div class="wpdn-edit-title"></div><span class="status"></span>',
			array( $this, 'wpew_render_dashboard_widget' ),
			''
		);
	}


	public function wpew_render_dashboard_widget( $post, $args ) {
		echo "Something fancy<br>";
		echo "Another fancy";
	}



}



/**
 * Initialize plugIn.
 */
function ManageEMailWhitelist() {
        static $instance;

        // first call to instance() initializes the plugin
        if ( $instance === null || ! ( $instance instanceof ManageEMailWhitelist ) )
                $instance = ManageEMailWhitelist::instance();

        return $instance;
}

$manage_email_whitelist = ManageEMailWhitelist();

// add settings
add_filter('plugin_action_links', array($manage_email_whitelist, 'add_settings_link'), 10, 2);
add_action('admin_init', array( $manage_email_whitelist, 'register_setting' ) );
add_action('admin_menu', array( $manage_email_whitelist, 'register_settings_page' ) );
add_action('wp_dashboard_setup', array( $manage_email_whitelist, 'wpew_init_dashboard_widget'));

