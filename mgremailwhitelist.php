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

// Many of the code was inspired by
//     - https://github.com/user141080/admindashboardwidget
//     - https://github.com/Automattic/syntaxhighlighter

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) )
        exit;


/**
 * Add javascript
 */
function wpew_add_script($hook){
    
    // add JS-File only on the dashboard page
    if ('index.php' !== $hook) {
        return;
    }
    
    wp_enqueue_script( 'wpew_widget_script', plugin_dir_url(__FILE__) ."/js/widget-script.js", array(), NULL, true );
}

/**
 * hook to add js
 */
add_action( 'admin_enqueue_scripts', 'wpew_add_script' );





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
			<h2>".esc_html__( 'ManageEMailWhitelist Settings', 'mgremailwhitelist' )."</h2>
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
		global $wpdb;
		$current_user_id = get_current_user_id();
		$cmpMailAcc=$wpdb->prefix . 'mgremailwhitelist_companymailaccounts';
		$cmpAdmins =$wpdb->prefix . 'mgremailwhitelist_companyadmins';

		$emails=$wpdb->get_results( 
    			"
				SELECT email_id
				FROM  $cmpMailAcc, $cmpAdmins
				WHERE $cmpMailAcc.company_id = $cmpAdmins.company_id
				AND   $cmpAdmins.wp_userid   = $current_user_id
				ORDER BY email_id
    			"
		);
		echo esc_html__('Select email for E-Mail Whitelist','mgremailwhitelist')."<br/>";
		echo "<form id='wpew_form' action='".esc_url( admin_url( 'admin-ajax.php' ) )."' method='post'>
			<input type='hidden' id='wpew_action' name='wpew_action' value='wpew_user_data'>
			<select name='wpew_email' id='wpew_email'>";
	
		foreach ( $emails as $oneEmail ) {
    			echo "<option value='".esc_html($oneEmail->email_id)."'>".esc_html($oneEmail->email_id)."</option>";
		}
		echo "</select>";
		wp_nonce_field( 'wpew_nonce', 'wpew_nonce_field');
		echo "	<br class='clear'>
			<input name='save-data' class='button button-primary' value='Whitelist' type='submit'>
			</form>";

	}


	public function wpew_save_user_data() {
    		$msg = '';
    		if(array_key_exists('nonce', $_POST) AND  wp_verify_nonce( $_POST['nonce'], 'wpew_nonce' ) ) 
    		{   
        		$email=esc_html($_POST['email']);
        
			$folder='/home/fungusat/tmp/emailWhitelist';
			$fname=tempnam($folder,'emailWhitelist');
			file_put_contents($fname,$email);

        		// success message
        		$msg = 'In one minute: EMail '.$email.' whitelisted!';
    		}
    		else
    		{   
        		// error message
        		$msg = 'EMail could not be whitelisted!';
    		}
   
    		wp_send_json( $msg );
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
add_action('wp_ajax_wpew_user_data', array( $manage_email_whitelist,'wpew_save_user_data') );
