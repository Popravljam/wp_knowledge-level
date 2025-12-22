<?php
/**
 * Badge Rendering and Shortcodes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Render {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'kl_badges', array( $this, 'render_all_badges_shortcode' ) );
        add_shortcode( 'kl_badge', array( $this, 'render_single_badge_shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_filter( 'the_content', array( $this, 'display_at_top' ), 5 );
        add_filter( 'the_content', array( $this, 'auto_insert_badges' ), 20 );
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_styles() {
        if ( is_singular( 'post' ) || has_shortcode( get_post()->post_content, 'kl_badges' ) || has_shortcode( get_post()->post_content, 'kl_badge' ) ) {
            wp_enqueue_style( 'dashicons' );
            wp_enqueue_style( 'kl-frontend', KL_PLUGIN_URL . 'assets/css/frontend.css', array( 'dashicons' ), KL_VERSION );
            wp_enqueue_script( 'kl-frontend', KL_PLUGIN_URL . 'assets/js/frontend.js', array(), KL_VERSION, true );
        }
    }

    /**
     * Shortcode: [kl_badges]
     */
    public function render_all_badges_shortcode( $atts ) {
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return '';
        }
        
        return $this->render_badges( $post_id );
    }

    /**
     * Shortcode: [kl_badge id="123"]
     */
    public function render_single_badge_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => 0,
        ), $atts );
        
        $badge_id = intval( $atts['id'] );
        $post_id = get_the_ID();
        
        if ( ! $post_id || ! $badge_id ) {
            return '';
        }
        
        return $this->render_single_badge( $post_id, $badge_id );
    }

    /**
     * Render all badges for a post
     */
    public function render_badges( $post_id ) {
        $badge_values = get_post_meta( $post_id, '_kl_badges', true );
        
        if ( empty( $badge_values ) || ! is_array( $badge_values ) ) {
            return '';
        }
        
        // Get saved badge order
        $badge_order = get_post_meta( $post_id, '_kl_badge_order', true );
        $badge_order = is_array( $badge_order ) ? $badge_order : array();
        
        // Sort badge_values by saved order
        if ( ! empty( $badge_order ) ) {
            $sorted_badges = array();
            foreach ( $badge_order as $badge_id ) {
                if ( isset( $badge_values[ $badge_id ] ) ) {
                    $sorted_badges[ $badge_id ] = $badge_values[ $badge_id ];
                }
            }
            // Add any badges not in order (new badges)
            foreach ( $badge_values as $badge_id => $value ) {
                if ( ! isset( $sorted_badges[ $badge_id ] ) ) {
                    $sorted_badges[ $badge_id ] = $value;
                }
            }
            $badge_values = $sorted_badges;
        }
        
        // Get badge size setting
        $size = get_option( 'kl_badge_size', 'medium' );
        $size_class = 'kl-size-' . esc_attr( $size );
        
        $output = '<div class="kl-badges-container ' . $size_class . '">';
        
        foreach ( $badge_values as $badge_id => $value ) {
            $output .= $this->render_single_badge( $post_id, $badge_id, $value );
        }
        
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render a single badge
     */
    public function render_single_badge( $post_id, $badge_id, $value = null ) {
        $badge = get_post( $badge_id );
        
        if ( ! $badge || $badge->post_type !== 'kl_badge' || $badge->post_status !== 'publish' ) {
            return '';
        }
        
        // Get value if not provided
        if ( null === $value ) {
            $badge_values = get_post_meta( $post_id, '_kl_badges', true );
            $value = isset( $badge_values[ $badge_id ] ) ? intval( $badge_values[ $badge_id ] ) : 0;
        }
        
        if ( $value <= 0 ) {
            return '';
        }
        
        // Get badge settings
        $color = get_post_meta( $badge_id, '_kl_color', true );
        $mode = get_post_meta( $badge_id, '_kl_mode', true );
        $max = get_post_meta( $badge_id, '_kl_max', true );
        $token_style = get_post_meta( $badge_id, '_kl_token_style', true );
        $dashicon = get_post_meta( $badge_id, '_kl_dashicon', true );
        $custom_token = get_post_meta( $badge_id, '_kl_custom_token', true );
        $help = get_post_meta( $badge_id, '_kl_help', true );
        
        // Defaults
        $color = $color ? $color : '#2b6cb0';
        $mode = $mode ? $mode : 'tokens';
        $max = $max ? intval( $max ) : 5;
        $token_style = $token_style ? $token_style : 'circles';
        $dashicon = $dashicon ? $dashicon : 'dashicons-star-filled';
        $custom_token = $custom_token ? $custom_token : '★';
        
        $label = esc_html( $badge->post_title );
        $aria_label = sprintf( '%s: %d out of %d', $label, $value, $max );
        
        $output = '<div class="kl-badge kl-badge-' . esc_attr( $mode ) . '" style="--kl-color: ' . esc_attr( $color ) . ';" aria-label="' . esc_attr( $aria_label ) . '">';
        $output .= '<div class="kl-badge-label">';
        $output .= $label;
        if ( ! empty( $help ) ) {
            $output .= ' <span class="kl-badge-help dashicons dashicons-info" data-tooltip="' . esc_attr( $help ) . '" aria-label="' . esc_attr( $help ) . '"></span>';
        }
        $output .= '</div>';
        $output .= '<div class="kl-badge-visual">';
        
        if ( $mode === 'bar' ) {
            $output .= $this->render_bar( $value, $max );
        } else {
            $output .= $this->render_tokens( $value, $max, $token_style, $dashicon, $badge_id );
        }
        
        $output .= '</div>';
        $output .= '<div class="kl-badge-value">' . esc_html( $value ) . '/' . esc_html( $max ) . '</div>';
        $output .= '</div>';
        
        return $output;
    }

    /**
     * Render bar mode
     */
    private function render_bar( $value, $max ) {
        $percentage = ( $value / $max ) * 100;
        $output = '<div class="kl-bar-container">';
        $output .= '<div class="kl-bar-fill" style="width: ' . esc_attr( $percentage ) . '%;"></div>';
        $output .= '</div>';
        return $output;
    }

    /**
     * Render tokens mode
     */
    private function render_tokens( $value, $max, $token_style, $dashicon, $badge_id ) {
        $output = '<div class="kl-tokens kl-tokens-' . esc_attr( $token_style ) . '">';
        
        for ( $i = 1; $i <= $max; $i++ ) {
            $filled = $i <= $value;
            $class = $filled ? 'kl-token-filled' : 'kl-token-empty';
            
            switch ( $token_style ) {
                case 'dashes':
                    $output .= '<span class="kl-token ' . $class . '">—</span>';
                    break;
                case 'circles':
                    $symbol = $filled ? '●' : '○';
                    $output .= '<span class="kl-token ' . $class . '">' . $symbol . '</span>';
                    break;
                case 'filled_circles':
                    $output .= '<span class="kl-token ' . $class . '">●</span>';
                    break;
                case 'icons':
                    $output .= '<span class="kl-token kl-token-icon ' . $class . ' dashicons ' . esc_attr( $dashicon ) . '"></span>';
                    break;
                case 'custom':
                    $custom_token = get_post_meta( $badge_id, '_kl_custom_token', true );
                    $custom_token = $custom_token ? $custom_token : '★';
                    $output .= '<span class="kl-token ' . $class . '">' . esc_html( $custom_token ) . '</span>';
                    break;
            }
        }
        
        $output .= '</div>';
        return $output;
    }

    /**
     * Display badges at top of post (under author/date)
     */
    public function display_at_top( $content ) {
        if ( ! is_singular( 'post' ) || ! is_main_query() || ! in_the_loop() ) {
            return $content;
        }
        
        $position = get_option( 'kl_auto_insert', 'disabled' );
        
        // Only auto-insert at top if setting is 'top' or 'before'
        if ( $position !== 'before' && $position !== 'top' ) {
            return $content;
        }
        
        $badges = $this->render_badges( get_the_ID() );
        
        if ( empty( $badges ) ) {
            return $content;
        }
        
        return $badges . $content;
    }
    
    /**
     * Auto-insert badges into content
     */
    public function auto_insert_badges( $content ) {
        if ( ! is_singular( 'post' ) || ! is_main_query() ) {
            return $content;
        }
        
        $position = get_option( 'kl_auto_insert', 'disabled' );
        
        // Only insert at bottom if setting is 'after'
        if ( $position !== 'after' ) {
            return $content;
        }
        
        $badges = $this->render_badges( get_the_ID() );
        
        if ( empty( $badges ) ) {
            return $content;
        }
        
        return $content . $badges;
    }
}
