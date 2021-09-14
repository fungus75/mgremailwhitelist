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


		$table_name = $wpdb->prefix . 'mgremailwhitelist_companies';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
        		company_id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        		company_name varchar(50) NOT NULL) $charset_collate;";
		dbDelta( $sql );


		$table_name = $wpdb->prefix . 'mgremailwhitelist_companyadmins';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			company_id bigint(20) NOT NULL,
			wp_userid  bigint(20) NOT NULL,
			PRIMARY KEY (company_id,wp_userid) ) $charset_collate;";
		dbDelta( $sql );


		$table_name = $wpdb->prefix . 'mgremailwhitelist_companymailaccounts';
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			email_id varchar(50) NOT NULL PRIMARY KEY,
			company_id bigint(20) NOT NULL) $charset_collate;";
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

