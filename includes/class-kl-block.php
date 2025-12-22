<?php
/**
 * Gutenberg Block Registration
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Block {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_block' ) );
    }

    /**
     * Register Gutenberg block
     */
    public function register_block() {
        if ( ! function_exists( 'register_block_type' ) ) {
            return;
        }

        register_block_type( KL_PLUGIN_DIR . 'blocks/kl-badges', array(
            'render_callback' => array( $this, 'render_block' ),
        ) );
    }

    /**
     * Render block callback
     */
    public function render_block( $attributes, $content ) {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return '';
        }
        
        return KL_Render::get_instance()->render_badges( $post_id );
    }
}
