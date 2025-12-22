<?php
/**
 * Plugin Settings
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Settings {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
    }

    /**
     * Register settings
     */
    public function register_settings() {
        register_setting( 'kl_settings', 'kl_auto_insert', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_auto_insert' ),
            'default'           => 'disabled',
        ) );
        
        register_setting( 'kl_settings', 'kl_badge_size', array(
            'type'              => 'string',
            'sanitize_callback' => array( $this, 'sanitize_badge_size' ),
            'default'           => 'medium',
        ) );

        add_settings_section(
            'kl_settings_section',
            __( 'Display Settings', 'knowledge-level' ),
            array( $this, 'settings_section_callback' ),
            'kl_settings'
        );

        add_settings_field(
            'kl_auto_insert',
            __( 'Auto-insert Badges', 'knowledge-level' ),
            array( $this, 'auto_insert_field_callback' ),
            'kl_settings',
            'kl_settings_section'
        );
        
        add_settings_field(
            'kl_badge_size',
            __( 'Badge Size', 'knowledge-level' ),
            array( $this, 'badge_size_field_callback' ),
            'kl_settings',
            'kl_settings_section'
        );
    }

    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'edit.php?post_type=kl_badge',
            __( 'Settings', 'knowledge-level' ),
            __( 'Settings', 'knowledge-level' ),
            'manage_options',
            'kl-settings',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Render settings page
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'kl_messages', 'kl_message', __( 'Settings saved', 'knowledge-level' ), 'updated' );
        }

        settings_errors( 'kl_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'kl_settings' );
                do_settings_sections( 'kl_settings' );
                submit_button( __( 'Save Settings', 'knowledge-level' ) );
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Settings section callback
     */
    public function settings_section_callback() {
        echo '<p>' . __( 'Configure how badges are displayed on your posts.', 'knowledge-level' ) . '</p>';
    }

    /**
     * Auto-insert field callback
     */
    public function auto_insert_field_callback() {
        $value = get_option( 'kl_auto_insert', 'disabled' );
        ?>
        <select name="kl_auto_insert">
            <option value="disabled" <?php selected( $value, 'disabled' ); ?>><?php _e( 'Disabled (use shortcode)', 'knowledge-level' ); ?></option>
            <option value="before" <?php selected( $value, 'before' ); ?>><?php _e( 'Before content', 'knowledge-level' ); ?></option>
            <option value="after" <?php selected( $value, 'after' ); ?>><?php _e( 'After content', 'knowledge-level' ); ?></option>
        </select>
        <p class="description">
            <?php _e( 'Automatically insert badges into post content, or use the [kl_badges] shortcode for manual placement.', 'knowledge-level' ); ?>
        </p>
        <?php
    }

    /**
     * Badge size field callback
     */
    public function badge_size_field_callback() {
        $value = get_option( 'kl_badge_size', 'medium' );
        ?>
        <select name="kl_badge_size">
            <option value="small" <?php selected( $value, 'small' ); ?>><?php _e( 'Small', 'knowledge-level' ); ?></option>
            <option value="medium" <?php selected( $value, 'medium' ); ?>><?php _e( 'Medium', 'knowledge-level' ); ?></option>
            <option value="large" <?php selected( $value, 'large' ); ?>><?php _e( 'Large', 'knowledge-level' ); ?></option>
        </select>
        <p class="description">
            <?php _e( 'Choose the display size for badges on the frontend.', 'knowledge-level' ); ?>
        </p>
        <?php
    }
    
    /**
     * Sanitize auto-insert option
     */
    public function sanitize_auto_insert( $value ) {
        $allowed = array( 'disabled', 'before', 'after' );
        return in_array( $value, $allowed ) ? $value : 'disabled';
    }
    
    /**
     * Sanitize badge size option
     */
    public function sanitize_badge_size( $value ) {
        $allowed = array( 'small', 'medium', 'large' );
        return in_array( $value, $allowed ) ? $value : 'medium';
    }
}
