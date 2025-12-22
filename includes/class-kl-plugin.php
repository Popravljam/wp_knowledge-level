<?php
/**
 * Main Plugin Bootstrap Class
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Plugin {

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - loads all components
     */
    private function __construct() {
        $this->load_dependencies();
        $this->init_components();
    }

    /**
     * Load required files
     */
    private function load_dependencies() {
        $files = array(
            'includes/class-kl-badge-cpt.php',
            'includes/class-kl-post-meta.php',
            'includes/class-kl-render.php',
            'includes/class-kl-settings.php',
            'includes/class-kl-block.php',
        );

        foreach ( $files as $file ) {
            $path = KL_PLUGIN_DIR . $file;
            if ( file_exists( $path ) ) {
                require_once $path;
            } else {
                wp_die( 'Knowledge Level plugin error: Missing file ' . esc_html( $file ) );
            }
        }
    }

    /**
     * Initialize components
     */
    private function init_components() {
        KL_Badge_CPT::get_instance();
        KL_Post_Meta::get_instance();
        KL_Render::get_instance();
        KL_Settings::get_instance();
        KL_Block::get_instance();
    }

    /**
     * Activation callback
     */
    public static function activate() {
        // Check WordPress version
        if ( version_compare( get_bloginfo( 'version' ), '6.0', '<' ) ) {
            wp_die( 'Knowledge Level requires WordPress 6.0 or higher.' );
        }
        
        // Check PHP version
        if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
            wp_die( 'Knowledge Level requires PHP 7.4 or higher. You are running ' . PHP_VERSION );
        }
        
        // Load class if not loaded yet
        if ( ! class_exists( 'KL_Badge_CPT' ) ) {
            require_once KL_PLUGIN_DIR . 'includes/class-kl-badge-cpt.php';
        }
        
        // Trigger CPT registration
        KL_Badge_CPT::register_post_type();
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Deactivation callback
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
