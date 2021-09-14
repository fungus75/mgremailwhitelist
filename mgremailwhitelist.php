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
	 * Include required files.
	 *
	 * @return void
	 */
	private function includes() {
		// include_once( plugin_dir_path( __FILE__ ) . 'includes/bot-detect.php' );
	}

