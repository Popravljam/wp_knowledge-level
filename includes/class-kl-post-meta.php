<?php
/**
 * Post Meta Box for Badge Values
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class KL_Post_Meta {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', array( $this, 'register_post_meta' ) );
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
        add_action( 'save_post_post', array( $this, 'save_meta' ), 10, 2 );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Enqueue admin scripts for sortable
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }
        
        global $post;
        if ( ! $post || $post->post_type !== 'post' ) {
            return;
        }
        
        wp_enqueue_script( 'jquery-ui-sortable' );
        wp_enqueue_style( 'kl-admin', KL_PLUGIN_URL . 'assets/css/admin.css', array(), KL_VERSION );
    }
    
    /**
     * Register post meta for REST API (Gutenberg)
     */
    public function register_post_meta() {
        register_post_meta( 'post', '_kl_badges', array(
            'type'         => 'object',
            'single'       => true,
            'show_in_rest' => array(
                'schema' => array(
                    'type'       => 'object',
                    'properties' => array(),
                ),
            ),
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
        ) );
    }

    /**
     * Add meta box
     */
    public function add_meta_box() {
        add_meta_box(
            'kl_post_badges',
            __( 'Knowledge Level Badges', 'knowledge-level' ),
            array( $this, 'render_meta_box' ),
            'post',
            'side',
            'default'
        );
    }

    /**
     * Render meta box
     */
    public function render_meta_box( $post ) {
        wp_nonce_field( 'kl_post_badges_save', 'kl_post_badges_nonce' );

        $badge_values = get_post_meta( $post->ID, '_kl_badges', true );
        $badge_values = is_array( $badge_values ) ? $badge_values : array();
        
        $badge_order = get_post_meta( $post->ID, '_kl_badge_order', true );
        $badge_order = is_array( $badge_order ) ? $badge_order : array();

        // Get all badge types
        $badges = get_posts( array(
            'post_type'      => 'kl_badge',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC',
        ) );

        if ( empty( $badges ) ) {
            echo '<p>' . __( 'No badge types found. Please create badge types first.', 'knowledge-level' ) . '</p>';
            return;
        }
        
        // Sort badges by saved order
        if ( ! empty( $badge_order ) ) {
            usort( $badges, function( $a, $b ) use ( $badge_order ) {
                $pos_a = array_search( $a->ID, $badge_order );
                $pos_b = array_search( $b->ID, $badge_order );
                if ( $pos_a === false ) return 1;
                if ( $pos_b === false ) return -1;
                return $pos_a - $pos_b;
            } );
        }

        echo '<p class="description" style="margin-bottom:10px;">' . __( 'Drag to reorder badges (order affects display on frontend)', 'knowledge-level' ) . '</p>';
        echo '<div class="kl-badges-meta kl-sortable-badges">';
        
        foreach ( $badges as $badge ) {
            $badge_id = $badge->ID;
            $max = get_post_meta( $badge_id, '_kl_max', true );
            $max = $max ? intval( $max ) : 5;
            $value = isset( $badge_values[ $badge_id ] ) ? intval( $badge_values[ $badge_id ] ) : 0;
            $help = get_post_meta( $badge_id, '_kl_help', true );
            
            ?>
            <div class="kl-badge-field" data-badge-id="<?php echo esc_attr( $badge_id ); ?>" style="margin-bottom: 15px;padding:10px;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;cursor:move;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <span class="dashicons dashicons-menu" style="color:#999;"></span>
                    <label style="display:block;font-weight:600;margin:0;flex:1;">
                        <?php echo esc_html( $badge->post_title ); ?>
                        <?php if ( $help ) : ?>
                            <span class="dashicons dashicons-info" title="<?php echo esc_attr( $help ); ?>" style="font-size:16px;"></span>
                        <?php endif; ?>
                    </label>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="range" 
                           id="kl_badge_range_<?php echo esc_attr( $badge_id ); ?>" 
                           min="0" 
                           max="<?php echo esc_attr( $max ); ?>" 
                           value="<?php echo esc_attr( $value ); ?>"
                           style="flex:1;" />
                    <input type="number" 
                           name="kl_badges[<?php echo esc_attr( $badge_id ); ?>]" 
                           id="kl_badge_num_<?php echo esc_attr( $badge_id ); ?>"
                           min="0" 
                           max="<?php echo esc_attr( $max ); ?>" 
                           value="<?php echo esc_attr( $value ); ?>"
                           style="width:60px;" />
                    <span style="color:#666;">/ <?php echo esc_html( $max ); ?></span>
                </div>
            </div>
            <script>
            (function() {
                var range = document.getElementById('kl_badge_range_<?php echo esc_js( $badge_id ); ?>');
                var num = document.getElementById('kl_badge_num_<?php echo esc_js( $badge_id ); ?>');
                range.addEventListener('input', function() { num.value = range.value; });
                num.addEventListener('input', function() { range.value = num.value; });
            })();
            </script>
            <?php
        }
        
        echo '</div>';
        
        // Add sortable script
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('.kl-sortable-badges').sortable({
                handle: '.dashicons-menu',
                placeholder: 'kl-sortable-placeholder',
                tolerance: 'pointer',
                cursor: 'move',
                opacity: 0.7,
                update: function(event, ui) {
                    // Update order in hidden field
                    updateBadgeOrder();
                }
            });
            
            function updateBadgeOrder() {
                var order = [];
                $('.kl-badge-field').each(function() {
                    order.push($(this).data('badge-id'));
                });
                // Remove old hidden input if exists
                $('input[name="kl_badge_order"]').remove();
                // Add new hidden input with order
                $('.kl-badges-meta').append('<input type="hidden" name="kl_badge_order" value="' + order.join(',') + '">');
            }
            
            // Set initial order
            updateBadgeOrder();
        });
        </script>
        <?php
    }

    /**
     * Save meta
     */
    public function save_meta( $post_id, $post ) {
        // Security checks
        if ( ! isset( $_POST['kl_post_badges_nonce'] ) || ! wp_verify_nonce( $_POST['kl_post_badges_nonce'], 'kl_post_badges_save' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // Save badge order
        if ( isset( $_POST['kl_badge_order'] ) ) {
            $order_string = sanitize_text_field( $_POST['kl_badge_order'] );
            $order = array_map( 'intval', explode( ',', $order_string ) );
            update_post_meta( $post_id, '_kl_badge_order', $order );
        }
        
        // Process badge values
        if ( isset( $_POST['kl_badges'] ) && is_array( $_POST['kl_badges'] ) ) {
            $badge_values = array();
            
            foreach ( $_POST['kl_badges'] as $badge_id => $value ) {
                $badge_id = intval( $badge_id );
                $value = intval( $value );
                
                // Only store non-zero values
                if ( $value > 0 ) {
                    // Validate max
                    $max = get_post_meta( $badge_id, '_kl_max', true );
                    $max = $max ? intval( $max ) : 5;
                    $value = min( $value, $max );
                    
                    $badge_values[ $badge_id ] = $value;
                }
            }
            
            if ( ! empty( $badge_values ) ) {
                update_post_meta( $post_id, '_kl_badges', $badge_values );
            } else {
                delete_post_meta( $post_id, '_kl_badges' );
            }
        } else {
            delete_post_meta( $post_id, '_kl_badges' );
        }
    }
}
