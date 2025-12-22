<?php
/**
 * Badge Types Custom Post Type
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Badge_CPT {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_post_type' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
        add_action( 'save_post_kl_badge', array( $this, 'save_meta' ), 10, 2 );
        add_filter( 'manage_kl_badge_posts_columns', array( $this, 'custom_columns' ) );
        add_action( 'manage_kl_badge_posts_custom_column', array( $this, 'custom_column_content' ), 10, 2 );
    }

    /**
     * Register custom post type
     */
    public static function register_post_type() {
        $labels = array(
            'name'               => __( 'Badge Types', 'knowledge-level' ),
            'singular_name'      => __( 'Badge Type', 'knowledge-level' ),
            'add_new'            => __( 'Add New', 'knowledge-level' ),
            'add_new_item'       => __( 'Add New Badge Type', 'knowledge-level' ),
            'edit_item'          => __( 'Edit Badge Type', 'knowledge-level' ),
            'new_item'           => __( 'New Badge Type', 'knowledge-level' ),
            'view_item'          => __( 'View Badge Type', 'knowledge-level' ),
            'search_items'       => __( 'Search Badge Types', 'knowledge-level' ),
            'not_found'          => __( 'No badge types found', 'knowledge-level' ),
            'not_found_in_trash' => __( 'No badge types found in trash', 'knowledge-level' ),
        );

        $args = array(
            'labels'              => $labels,
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-awards',
            'capability_type'     => 'post',
            'hierarchical'        => false,
            'supports'            => array( 'title' ),
            'has_archive'         => false,
            'rewrite'             => false,
            'query_var'           => false,
            'can_export'          => true,
            'show_in_rest'        => false,
        );

        register_post_type( 'kl_badge', $args );
    }

    /**
     * Add meta boxes
     */
    public function add_meta_boxes() {
        add_meta_box(
            'kl_badge_settings',
            __( 'Badge Settings', 'knowledge-level' ),
            array( $this, 'render_meta_box' ),
            'kl_badge',
            'normal',
            'high'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'kl_badge_meta_save', 'kl_badge_meta_nonce' );

        $color = get_post_meta( $post->ID, '_kl_color', true );
        $mode = get_post_meta( $post->ID, '_kl_mode', true );
        $max = get_post_meta( $post->ID, '_kl_max', true );
        $token_style = get_post_meta( $post->ID, '_kl_token_style', true );
        $dashicon = get_post_meta( $post->ID, '_kl_dashicon', true );
        $custom_token = get_post_meta( $post->ID, '_kl_custom_token', true );
        $help = get_post_meta( $post->ID, '_kl_help', true );

        // Defaults
        $color = $color ? $color : '#2b6cb0';
        $mode = $mode ? $mode : 'tokens';
        $max = $max ? intval( $max ) : 5;
        $token_style = $token_style ? $token_style : 'circles';
        $dashicon = $dashicon ? $dashicon : 'dashicons-star-filled';
        $custom_token = $custom_token ? $custom_token : '';

        ?>
        <table class="form-table">
            <tr>
                <th><label for="kl_color"><?php _e( 'Color', 'knowledge-level' ); ?></label></th>
                <td>
                    <input type="color" id="kl_color" name="kl_color" value="<?php echo esc_attr( $color ); ?>" />
                    <p class="description"><?php _e( 'Choose badge color', 'knowledge-level' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="kl_mode"><?php _e( 'Display Mode', 'knowledge-level' ); ?></label></th>
                <td>
                    <select id="kl_mode" name="kl_mode">
                        <option value="bar" <?php selected( $mode, 'bar' ); ?>><?php _e( 'Bar', 'knowledge-level' ); ?></option>
                        <option value="tokens" <?php selected( $mode, 'tokens' ); ?>><?php _e( 'Tokens', 'knowledge-level' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="kl_max"><?php _e( 'Max Value', 'knowledge-level' ); ?></label></th>
                <td>
                    <input type="number" id="kl_max" name="kl_max" value="<?php echo esc_attr( $max ); ?>" min="1" max="100" />
                    <p class="description"><?php _e( 'Maximum value for this badge', 'knowledge-level' ); ?></p>
                </td>
            </tr>
            <tr class="kl-tokens-only">
                <th><label for="kl_token_style"><?php _e( 'Token Style', 'knowledge-level' ); ?></label></th>
                <td>
                    <select id="kl_token_style" name="kl_token_style">
                        <option value="dashes" <?php selected( $token_style, 'dashes' ); ?>><?php _e( 'Dashes (—)', 'knowledge-level' ); ?></option>
                        <option value="circles" <?php selected( $token_style, 'circles' ); ?>><?php _e( 'Circles (○●)', 'knowledge-level' ); ?></option>
                        <option value="filled_circles" <?php selected( $token_style, 'filled_circles' ); ?>><?php _e( 'Filled Circles (●)', 'knowledge-level' ); ?></option>
                        <option value="icons" <?php selected( $token_style, 'icons' ); ?>><?php _e( 'Icons (Dashicons)', 'knowledge-level' ); ?></option>
                        <option value="custom" <?php selected( $token_style, 'custom' ); ?>><?php _e( 'Custom Character', 'knowledge-level' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr class="kl-custom-token-only">
                <th><label for="kl_custom_token"><?php _e( 'Custom Token', 'knowledge-level' ); ?></label></th>
                <td>
                    <input type="text" id="kl_custom_token" name="kl_custom_token" value="<?php echo esc_attr( $custom_token ); ?>" maxlength="3" style="width:100px;" />
                    <p class="description"><?php _e( 'Enter 1-3 characters (e.g., ★, ▶, •)', 'knowledge-level' ); ?></p>
                </td>
            </tr>
            <tr class="kl-icons-only">
                <th><label for="kl_dashicon"><?php _e( 'Dashicon', 'knowledge-level' ); ?></label></th>
                <td>
                    <input type="text" id="kl_dashicon" name="kl_dashicon" value="<?php echo esc_attr( $dashicon ); ?>" />
                    <p class="description"><?php _e( 'Dashicon class name (e.g., dashicons-star-filled)', 'knowledge-level' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="kl_help"><?php _e( 'Help Text', 'knowledge-level' ); ?></label></th>
                <td>
                    <textarea id="kl_help" name="kl_help" rows="3" class="large-text"><?php echo esc_textarea( $help ); ?></textarea>
                    <p class="description"><?php _e( 'Optional description for this badge', 'knowledge-level' ); ?></p>
                </td>
            </tr>
        </table>
        <script>
        (function() {
            var modeSelect = document.getElementById('kl_mode');
            var tokenStyleSelect = document.getElementById('kl_token_style');
            var tokenRows = document.querySelectorAll('.kl-tokens-only');
            var iconRows = document.querySelectorAll('.kl-icons-only');
            var customTokenRows = document.querySelectorAll('.kl-custom-token-only');
            
            function updateVisibility() {
                var isTokens = modeSelect.value === 'tokens';
                var isIcons = tokenStyleSelect.value === 'icons';
                var isCustom = tokenStyleSelect.value === 'custom';
                tokenRows.forEach(function(row) { row.style.display = isTokens ? '' : 'none'; });
                iconRows.forEach(function(row) { row.style.display = (isTokens && isIcons) ? '' : 'none'; });
                customTokenRows.forEach(function(row) { row.style.display = (isTokens && isCustom) ? '' : 'none'; });
            }
            
            modeSelect.addEventListener('change', updateVisibility);
            tokenStyleSelect.addEventListener('change', updateVisibility);
            updateVisibility();
        })();
        </script>
        <?php
    }

    /**
     * Save meta data
     */
    public function save_meta( $post_id, $post ) {
        // Security checks
        if ( ! isset( $_POST['kl_badge_meta_nonce'] ) || ! wp_verify_nonce( $_POST['kl_badge_meta_nonce'], 'kl_badge_meta_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Sanitize and save color
        if ( isset( $_POST['kl_color'] ) ) {
            $color = sanitize_hex_color( $_POST['kl_color'] );
            update_post_meta( $post_id, '_kl_color', $color ? $color : '#2b6cb0' );
        }

        // Sanitize and save mode
        if ( isset( $_POST['kl_mode'] ) ) {
            $mode = sanitize_text_field( $_POST['kl_mode'] );
            $mode = in_array( $mode, array( 'bar', 'tokens' ) ) ? $mode : 'tokens';
            update_post_meta( $post_id, '_kl_mode', $mode );
        }

        // Sanitize and save max
        if ( isset( $_POST['kl_max'] ) ) {
            $max = intval( $_POST['kl_max'] );
            $max = max( 1, min( 100, $max ) );
            update_post_meta( $post_id, '_kl_max', $max );
        }

        // Sanitize and save token style
        if ( isset( $_POST['kl_token_style'] ) ) {
            $token_style = sanitize_text_field( $_POST['kl_token_style'] );
            $allowed = array( 'dashes', 'circles', 'filled_circles', 'icons', 'custom' );
            $token_style = in_array( $token_style, $allowed ) ? $token_style : 'circles';
            update_post_meta( $post_id, '_kl_token_style', $token_style );
        }

        // Sanitize and save dashicon
        if ( isset( $_POST['kl_dashicon'] ) ) {
            $dashicon = sanitize_text_field( $_POST['kl_dashicon'] );
            update_post_meta( $post_id, '_kl_dashicon', $dashicon );
        }

        // Sanitize and save custom token
        if ( isset( $_POST['kl_custom_token'] ) ) {
            $custom_token = sanitize_text_field( $_POST['kl_custom_token'] );
            // Limit to 3 characters
            $custom_token = mb_substr( $custom_token, 0, 3 );
            update_post_meta( $post_id, '_kl_custom_token', $custom_token );
        }

        // Sanitize and save help text
        if ( isset( $_POST['kl_help'] ) ) {
            $help = sanitize_textarea_field( $_POST['kl_help'] );
            update_post_meta( $post_id, '_kl_help', $help );
        }
    }

    /**
     * Custom admin columns
     */
    public function custom_columns( $columns ) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['mode'] = __( 'Mode', 'knowledge-level' );
        $new_columns['max'] = __( 'Max', 'knowledge-level' );
        $new_columns['color'] = __( 'Color', 'knowledge-level' );
        $new_columns['date'] = $columns['date'];
        return $new_columns;
    }

    /**
     * Custom column content
     */
    public function custom_column_content( $column, $post_id ) {
        switch ( $column ) {
            case 'mode':
                $mode = get_post_meta( $post_id, '_kl_mode', true );
                echo esc_html( ucfirst( $mode ? $mode : 'tokens' ) );
                break;
            case 'max':
                $max = get_post_meta( $post_id, '_kl_max', true );
                echo esc_html( $max ? $max : '5' );
                break;
            case 'color':
                $color = get_post_meta( $post_id, '_kl_color', true );
                $color = $color ? $color : '#2b6cb0';
                echo '<span style="display:inline-block;width:30px;height:20px;background:' . esc_attr( $color ) . ';border:1px solid #ddd;"></span>';
                break;
        }
    }
}
