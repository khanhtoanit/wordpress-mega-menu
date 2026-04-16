<?php
/**
 * Plugin Name: Kato Salient Mega Menu
 * Description: Custom 3-column mega menu for Salient based on the Kato SRS.
 * Version: 1.0.0
 * Author: OpenAI
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'KATO_SALIENT_MEGA_MENU_FILE' ) ) {
    define( 'KATO_SALIENT_MEGA_MENU_FILE', __FILE__ );
}

if ( ! defined( 'KATO_SALIENT_MEGA_MENU_PATH' ) ) {
    define( 'KATO_SALIENT_MEGA_MENU_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'KATO_SALIENT_MEGA_MENU_URL' ) ) {
    define( 'KATO_SALIENT_MEGA_MENU_URL', plugin_dir_url( __FILE__ ) );
}

require_once KATO_SALIENT_MEGA_MENU_PATH . 'includes/class-kato-salient-mega-menu.php';

add_action( 'plugins_loaded', array( 'Kato_Salient_Mega_Menu', 'instance' ) );
