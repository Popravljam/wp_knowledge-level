<?php
/**
 * Plugin Name: Knowledge Level
 * Description: Add configurable badges to posts with customizable display modes (bars or tokens).
 * Version: 1.0.5
 * Author: Lazar Covs
 * Text Domain: knowledge-level
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
if ( ! defined( 'KL_VERSION' ) ) {
    define( 'KL_VERSION', '1.0.5' );
}
if ( ! defined( 'KL_PLUGIN_DIR' ) ) {
    define( 'KL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'KL_PLUGIN_URL' ) ) {
    define( 'KL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'KL_PLUGIN_BASENAME' ) ) {
    define( 'KL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}

// Load plugin classes
if ( file_exists( KL_PLUGIN_DIR . 'includes/class-kl-plugin.php' ) ) {
    require_once KL_PLUGIN_DIR . 'includes/class-kl-plugin.php';
} else {
    add_action( 'admin_notices', function() {
        echo '<div class="error"><p>Knowledge Level: Plugin files are missing. Please reinstall the plugin.</p></div>';
    } );
    return;
}

// Initialize plugin
function kl_init_plugin() {
    if ( class_exists( 'KL_Plugin' ) ) {
        KL_Plugin::get_instance();
    }
}
add_action( 'plugins_loaded', 'kl_init_plugin' );

// Activation hook
register_activation_hook( __FILE__, array( 'KL_Plugin', 'activate' ) );

// Deactivation hook
register_deactivation_hook( __FILE__, array( 'KL_Plugin', 'deactivate' ) );
