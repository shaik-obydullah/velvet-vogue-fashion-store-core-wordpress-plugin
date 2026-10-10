<?php
/**
 * Plugin Name: Obydullah Fitness Content Manager
 * Plugin URI: https://obydullah.com/project/obydullah-fitness-content-manager
 * Description: Core functionality for fitness and personal training websites
 * Version: 1.0.0
 * Author: Shaik Obydullah
 * Author URI: https://obydullah.com
 * Text Domain: obydullah-fitness-content-manager
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * ================================================================
 *                         INDEX
 * ================================================================
 * 1. Dashboard Menu
 * 2. Hero Slider CPT + Meta Boxes
 * 3. Services CPT + Meta Boxes
 * 4. Testimonials CPT + Meta Boxes
 * 5. Pricing Plans CPT + Meta Boxes
 * 6. Bookings Custom Table + Admin List + AJAX Handler
 * 7. Site Settings (Contact + About)
 * ================================================================
 */

/* ======================================================
   1. Security & Constants
====================================================== */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * The plugin uses short array syntax, null coalescing, and first-class
 * callable invocation, all of which require PHP 8.0 or later.
 */
if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
    add_action( 'admin_notices', 'ofcm_php_version_notice' );
    return;
}

function ofcm_php_version_notice() {
    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html__(
            'Obydullah Fitness Content Manager requires PHP 8.0 or later. Please update your PHP version or contact your host.',
            'obydullah-fitness-content-manager'
        )
    );
}

define( 'OFCM_VERSION', '1.0.0' );
define( 'OFCM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OFCM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ======================================================
   1a. Bookings Table Schema
   ====================================================== */

function ofcm_bookings_table() {
    global $wpdb;
    return $wpdb->prefix . 'ofcm_bookings';
}

function ofcm_create_tables() {
    global $wpdb;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $table   = ofcm_bookings_table();
    $collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table} (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        name varchar(191) NOT NULL DEFAULT '',
        email varchar(191) NOT NULL DEFAULT '',
        mobile varchar(50) NOT NULL DEFAULT '',
        goal varchar(50) NOT NULL DEFAULT '',
        message text NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'new',
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY status (status),
        KEY created_at (created_at)
    ) {$collate};";

    dbDelta( $sql );
}
register_activation_hook( __FILE__, 'ofcm_create_tables' );

function ofcm_add_admin_menu() {
    add_menu_page(
        'Obydullah Fitness Content Manager',
        'Obydullah Fitness Content Manager',
        'manage_options',
        'obydullah-fitness-content-manager',
        'ofcm_core_page',
        'dashicons-heart',
        65
    );
}
add_action( 'admin_menu', 'ofcm_add_admin_menu', 9 );

function ofcm_enqueue_dashboard_assets( $hook ) {
    if ( 'toplevel_page_obydullah-fitness-content-manager' === $hook ) {
        wp_enqueue_style( 'ofcm-dashboard-css', OFCM_PLUGIN_URL . 'assets/css/ofcm-admin.css', [], OFCM_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'ofcm_enqueue_dashboard_assets' );

function ofcm_core_page() {
    $sections = [
        'hero_slides' => [
            'title' => __( 'Hero Slides', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'edit.php?post_type=ofcm_hero_slide' ),
            'icon'  => 'dashicons-slides',
        ],
        'services' => [
            'title' => __( 'Services', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'edit.php?post_type=ofcm_service' ),
            'icon'  => 'dashicons-admin-site',
        ],
        'testimonials' => [
            'title' => __( 'Testimonials', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'edit.php?post_type=ofcm_testimonial' ),
            'icon'  => 'dashicons-star-filled',
        ],
        'pricing' => [
            'title' => __( 'Pricing Plans', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'edit.php?post_type=ofcm_pricing' ),
            'icon'  => 'dashicons-money-alt',
        ],
        'bookings' => [
            'title' => __( 'Bookings', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'admin.php?page=ofcm-bookings' ),
            'icon'  => 'dashicons-calendar-alt',
        ],
        'settings' => [
            'title' => __( 'Site Settings', 'obydullah-fitness-content-manager' ),
            'url'   => admin_url( 'admin.php?page=ofcm-settings' ),
            'icon'  => 'dashicons-admin-generic',
        ],
    ];
    ?>
<div class="wrap ofcm-dashboard">
    <h1><?php esc_html_e( 'Obydullah Fitness Content Manager', 'obydullah-fitness-content-manager' ); ?></h1>
    <p class="ofcm-dashboard-description">
        <?php esc_html_e( 'Welcome to the Obydullah Fitness Content Manager plugin. Use the links below to manage your fitness content.', 'obydullah-fitness-content-manager' ); ?>
    </p>

    <div class="ofcm-dashboard-grid">
        <?php foreach ( $sections as $section ) : ?>
        <div class="ofcm-dashboard-card">
            <div class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"></div>
            <h2><?php echo esc_html( $section['title'] ); ?></h2>
            <a href="<?php echo esc_url( $section['url'] ); ?>"
                class="button button-primary"><?php esc_html_e( 'Manage', 'obydullah-fitness-content-manager' ); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
}

/* ======================================================
   2. Hero Slider CPT + Meta Boxes
====================================================== */

function ofcm_register_hero_slide_cpt() {
    register_post_type( 'ofcm_hero_slide', [
        'labels' => [
            'name'          => __( 'Hero Slides', 'obydullah-fitness-content-manager' ),
            'singular_name' => __( 'Hero Slide', 'obydullah-fitness-content-manager' ),
            'add_new_item'  => __( 'Add New Hero Slide', 'obydullah-fitness-content-manager' ),
            'edit_item'     => __( 'Edit Hero Slide', 'obydullah-fitness-content-manager' ),
            'all_items'     => __( 'Hero Slides', 'obydullah-fitness-content-manager' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-fitness-content-manager',
        'menu_icon'     => 'dashicons-slides',
        'supports'      => [ 'title', 'thumbnail', 'page-attributes' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'ofcm_register_hero_slide_cpt' );

function ofcm_add_hero_slide_meta_box() {
    add_meta_box(
        'ofcm_hero_slide_meta',
        __( 'Hero Slide Settings', 'obydullah-fitness-content-manager' ),
        'ofcm_render_hero_slide_meta_box',
        'ofcm_hero_slide',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ofcm_add_hero_slide_meta_box' );

function ofcm_render_hero_slide_meta_box( $post ) {
    $subtitle    = get_post_meta( $post->ID, 'ofcm_slide_subtitle', true );
    $description = get_post_meta( $post->ID, 'ofcm_slide_description', true );
    $btn_text    = get_post_meta( $post->ID, 'ofcm_slide_btn_text', true );
    $btn_url     = get_post_meta( $post->ID, 'ofcm_slide_btn_url', true );
    $btn2_text   = get_post_meta( $post->ID, 'ofcm_slide_btn2_text', true );
    $btn2_url    = get_post_meta( $post->ID, 'ofcm_slide_btn2_url', true );
    wp_nonce_field( 'ofcm_save_hero_slide_meta', 'ofcm_hero_slide_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label
                for="ofcm_slide_subtitle"><?php esc_html_e( 'Subtitle / Badge', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="text" id="ofcm_slide_subtitle" name="ofcm_slide_subtitle"
                value="<?php echo esc_attr( $subtitle ); ?>" class="widefat"
                placeholder="<?php esc_attr_e( 'e.g., TRANSFORM YOUR BODY', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ofcm_slide_description"><?php esc_html_e( 'Description', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><textarea id="ofcm_slide_description" name="ofcm_slide_description" rows="3" class="large-text"
                placeholder="<?php esc_attr_e( 'Short description for this slide...', 'obydullah-fitness-content-manager' ); ?>"><?php echo esc_textarea( $description ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label
                for="ofcm_slide_btn_text"><?php esc_html_e( 'Primary Button Text', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="text" id="ofcm_slide_btn_text" name="ofcm_slide_btn_text"
                value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Start Today', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="ofcm_slide_btn_url"><?php esc_html_e( 'Primary Button URL', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="url" id="ofcm_slide_btn_url" name="ofcm_slide_btn_url"
                value="<?php echo esc_url( $btn_url ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '#contact', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="ofcm_slide_btn2_text"><?php esc_html_e( 'Secondary Button Text', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="text" id="ofcm_slide_btn2_text" name="ofcm_slide_btn2_text"
                value="<?php echo esc_attr( $btn2_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Learn More', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="ofcm_slide_btn2_url"><?php esc_html_e( 'Secondary Button URL', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="url" id="ofcm_slide_btn2_url" name="ofcm_slide_btn2_url"
                value="<?php echo esc_url( $btn2_url ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '#about', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
</table>
<p class="description">
    <?php esc_html_e( 'Upload a featured image to use as the slide background.', 'obydullah-fitness-content-manager' ); ?></p>
<?php
}

function ofcm_save_hero_slide_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofcm_hero_slide_nonce'] ?? '' ) ), 'ofcm_save_hero_slide_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofcm_hero_slide' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'ofcm_slide_subtitle'    => 'sanitize_text_field',
        'ofcm_slide_description' => 'sanitize_textarea_field',
        'ofcm_slide_btn_text'    => 'sanitize_text_field',
        'ofcm_slide_btn_url'     => 'esc_url_raw',
        'ofcm_slide_btn2_text'   => 'sanitize_text_field',
        'ofcm_slide_btn2_url'    => 'esc_url_raw',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_ofcm_hero_slide', 'ofcm_save_hero_slide_meta' );

/* ======================================================
   3. Services CPT + Meta Boxes
====================================================== */

function ofcm_register_service_cpt() {
    register_post_type( 'ofcm_service', [
        'labels' => [
            'name'          => __( 'Services', 'obydullah-fitness-content-manager' ),
            'singular_name' => __( 'Service', 'obydullah-fitness-content-manager' ),
            'add_new_item'  => __( 'Add New Service', 'obydullah-fitness-content-manager' ),
            'edit_item'     => __( 'Edit Service', 'obydullah-fitness-content-manager' ),
            'all_items'     => __( 'Services', 'obydullah-fitness-content-manager' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-fitness-content-manager',
        'menu_icon'     => 'dashicons-admin-site',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'ofcm_register_service_cpt' );

function ofcm_add_service_meta_box() {
    add_meta_box(
        'ofcm_service_meta',
        __( 'Service Details', 'obydullah-fitness-content-manager' ),
        'ofcm_render_service_meta_box',
        'ofcm_service',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ofcm_add_service_meta_box' );

function ofcm_render_service_meta_box( $post ) {
    $icon = get_post_meta( $post->ID, 'ofcm_service_icon', true );
    wp_nonce_field( 'ofcm_save_service_meta', 'ofcm_service_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label
                for="ofcm_service_icon"><?php esc_html_e( 'Icon (emoji or Font Awesome class)', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="text" id="ofcm_service_icon" name="ofcm_service_icon" value="<?php echo esc_attr( $icon ); ?>"
                class="regular-text"
                placeholder="<?php esc_attr_e( 'e.g., 🏋️ or fa-dumbbell', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
</table>
<p class="description">
    <?php esc_html_e( 'Use an emoji or a Font Awesome class. The service title and description come from the post title and content.', 'obydullah-fitness-content-manager' ); ?>
</p>
<?php
}

function ofcm_save_service_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofcm_service_nonce'] ?? '' ) ), 'ofcm_save_service_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofcm_service' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    if ( isset( $_POST['ofcm_service_icon'] ) ) {
        update_post_meta( $post_id, 'ofcm_service_icon', sanitize_text_field( wp_unslash( $_POST['ofcm_service_icon'] ) ) );
    }
}
add_action( 'save_post_ofcm_service', 'ofcm_save_service_meta' );

/* ======================================================
   4. Testimonials CPT + Meta Boxes
====================================================== */

function ofcm_register_testimonial_cpt() {
    register_post_type( 'ofcm_testimonial', [
        'labels' => [
            'name'          => __( 'Testimonials', 'obydullah-fitness-content-manager' ),
            'singular_name' => __( 'Testimonial', 'obydullah-fitness-content-manager' ),
            'add_new_item'  => __( 'Add New Testimonial', 'obydullah-fitness-content-manager' ),
            'edit_item'     => __( 'Edit Testimonial', 'obydullah-fitness-content-manager' ),
            'all_items'     => __( 'Testimonials', 'obydullah-fitness-content-manager' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-fitness-content-manager',
        'menu_icon'     => 'dashicons-star-filled',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'ofcm_register_testimonial_cpt' );

function ofcm_add_testimonial_meta_boxes() {
    add_meta_box( 'ofcm_testimonial_quote', __( 'Quote', 'obydullah-fitness-content-manager' ), 'ofcm_testimonial_quote_callback', 'ofcm_testimonial', 'normal', 'high' );
    add_meta_box( 'ofcm_testimonial_details', __( 'Details', 'obydullah-fitness-content-manager' ), 'ofcm_testimonial_details_callback', 'ofcm_testimonial', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'ofcm_add_testimonial_meta_boxes' );

function ofcm_testimonial_quote_callback( $post ) {
    wp_nonce_field( 'ofcm_save_testimonial_meta', 'ofcm_testimonial_nonce' );
    $quote = get_post_meta( $post->ID, 'ofcm_testimonial_quote', true );
    echo '<textarea name="ofcm_testimonial_quote" rows="4" class="large-text">' . esc_textarea( $quote ) . '</textarea>';
}

function ofcm_testimonial_details_callback( $post ) {
    $role   = get_post_meta( $post->ID, 'ofcm_testimonial_role', true );
    $rating = get_post_meta( $post->ID, 'ofcm_testimonial_rating', true );
    $result = get_post_meta( $post->ID, 'ofcm_testimonial_result', true );
    ?>
<p>
    <label><strong><?php esc_html_e( 'Role / Title', 'obydullah-fitness-content-manager' ); ?></strong></label><br>
    <input type="text" name="ofcm_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="widefat"
        placeholder="<?php esc_attr_e( 'e.g., Lost 18 lbs', 'obydullah-fitness-content-manager' ); ?>">
</p>
<p>
    <label><strong><?php esc_html_e( 'Rating (1-5)', 'obydullah-fitness-content-manager' ); ?></strong></label><br>
    <input type="number" name="ofcm_testimonial_rating" value="<?php echo esc_attr( $rating ?: 5 ); ?>" min="1" max="5"
        class="small-text">
</p>
<p>
    <label><strong><?php esc_html_e( 'Result Label', 'obydullah-fitness-content-manager' ); ?></strong></label><br>
    <input type="text" name="ofcm_testimonial_result" value="<?php echo esc_attr( $result ); ?>" class="widefat"
        placeholder="<?php esc_attr_e( 'e.g., 4 months', 'obydullah-fitness-content-manager' ); ?>">
</p>
<?php
}

function ofcm_save_testimonial_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofcm_testimonial_nonce'] ?? '' ) ), 'ofcm_save_testimonial_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofcm_testimonial' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'ofcm_testimonial_role'   => 'sanitize_text_field',
        'ofcm_testimonial_quote'  => 'sanitize_textarea_field',
        'ofcm_testimonial_rating' => 'intval',
        'ofcm_testimonial_result' => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }
}
add_action( 'save_post_ofcm_testimonial', 'ofcm_save_testimonial_meta' );

/* ======================================================
   5. Pricing Plans CPT + Meta Boxes
====================================================== */

function ofcm_register_pricing_cpt() {
    register_post_type( 'ofcm_pricing', [
        'labels' => [
            'name'          => __( 'Pricing Plans', 'obydullah-fitness-content-manager' ),
            'singular_name' => __( 'Pricing Plan', 'obydullah-fitness-content-manager' ),
            'add_new_item'  => __( 'Add New Pricing Plan', 'obydullah-fitness-content-manager' ),
            'edit_item'     => __( 'Edit Pricing Plan', 'obydullah-fitness-content-manager' ),
            'all_items'     => __( 'Pricing Plans', 'obydullah-fitness-content-manager' ),
        ],
        'public'        => false,
        'show_ui'       => true,
        'show_in_menu'  => 'obydullah-fitness-content-manager',
        'menu_icon'     => 'dashicons-money-alt',
        'supports'      => [ 'title', 'editor', 'thumbnail' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => false,
    ] );
}
add_action( 'init', 'ofcm_register_pricing_cpt' );

function ofcm_add_pricing_meta_box() {
    add_meta_box( 'ofcm_pricing_meta', __( 'Pricing Details', 'obydullah-fitness-content-manager' ), 'ofcm_render_pricing_meta_box', 'ofcm_pricing', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'ofcm_add_pricing_meta_box' );

function ofcm_render_pricing_meta_box( $post ) {
    $price    = get_post_meta( $post->ID, 'ofcm_pricing_price', true );
    $period   = get_post_meta( $post->ID, 'ofcm_pricing_period', true );
    $features = get_post_meta( $post->ID, 'ofcm_pricing_features', true );
    $popular  = get_post_meta( $post->ID, 'ofcm_pricing_popular', true );
    $btn_text = get_post_meta( $post->ID, 'ofcm_pricing_btn_text', true );
    wp_nonce_field( 'ofcm_save_pricing_meta', 'ofcm_pricing_nonce' );
    ?>
<table class="form-table">
    <tr>
        <th><label for="ofcm_pricing_price"><?php esc_html_e( 'Price', 'obydullah-fitness-content-manager' ); ?></label></th>
        <td><input type="text" id="ofcm_pricing_price" name="ofcm_pricing_price"
                value="<?php echo esc_attr( $price ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '$79', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label for="ofcm_pricing_period"><?php esc_html_e( 'Period', 'obydullah-fitness-content-manager' ); ?></label></th>
        <td><input type="text" id="ofcm_pricing_period" name="ofcm_pricing_period"
                value="<?php echo esc_attr( $period ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( '/mo', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
    <tr>
        <th><label
                for="ofcm_pricing_features"><?php esc_html_e( 'Features (one per line)', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><textarea id="ofcm_pricing_features" name="ofcm_pricing_features" rows="6" class="large-text"
                placeholder="<?php esc_attr_e( "2x 1-on-1 sessions / week\nBasic nutrition guide\nProgress tracking", 'obydullah-fitness-content-manager' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><?php esc_html_e( 'Mark as Popular', 'obydullah-fitness-content-manager' ); ?></th>
        <td><label><input type="checkbox" name="ofcm_pricing_popular" value="1" <?php checked( $popular, 1 ); ?>>
                <?php esc_html_e( 'Highlight this plan', 'obydullah-fitness-content-manager' ); ?></label></td>
    </tr>
    <tr>
        <th><label for="ofcm_pricing_btn_text"><?php esc_html_e( 'Button Text', 'obydullah-fitness-content-manager' ); ?></label>
        </th>
        <td><input type="text" id="ofcm_pricing_btn_text" name="ofcm_pricing_btn_text"
                value="<?php echo esc_attr( $btn_text ); ?>" class="regular-text"
                placeholder="<?php esc_attr_e( 'Get Started', 'obydullah-fitness-content-manager' ); ?>"></td>
    </tr>
</table>
<?php
}

function ofcm_save_pricing_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofcm_pricing_nonce'] ?? '' ) ), 'ofcm_save_pricing_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofcm_pricing' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    $fields = [
        'ofcm_pricing_price'    => 'sanitize_text_field',
        'ofcm_pricing_period'   => 'sanitize_text_field',
        'ofcm_pricing_features' => 'sanitize_textarea_field',
        'ofcm_pricing_btn_text' => 'sanitize_text_field',
    ];
    foreach ( $fields as $key => $sanitize ) {
        if ( isset( $_POST[ $key ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- $key is limited to the fixed $fields map above, and its value goes straight into that entry's sanitizer callback.
            update_post_meta( $post_id, $key, $sanitize( wp_unslash( $_POST[ $key ] ) ) );
        }
    }

    $popular = isset( $_POST['ofcm_pricing_popular'] ) ? 1 : 0;
    update_post_meta( $post_id, 'ofcm_pricing_popular', $popular );
}
add_action( 'save_post_ofcm_pricing', 'ofcm_save_pricing_meta' );

/* ======================================================
   6. Bookings Table + Admin List + AJAX Handler
   ====================================================== */

/*
 * The bookings data layer talks to its own table, so $wpdb cannot be avoided
 * here. Every read is wrapped in $wpdb->prepare() with %i/%s/%d placeholders
 * and every write goes through insert()/update()/delete() with a format list.
 * The results are admin-only and change on every submission, so there is
 * nothing worth caching between requests.
 */
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
function ofcm_get_bookings( $args = [] ) {
    global $wpdb;

    $table = ofcm_bookings_table();

    $per_page = absint( $args['per_page'] ?? 20 );
    $offset   = absint( $args['offset'] ?? 0 );
    $status   = sanitize_key( $args['status'] ?? '' );
    $search   = sanitize_text_field( $args['search'] ?? '' );

    /*
     * An empty search turns into '%%', which matches every row because name
     * and email are NOT NULL, so only the status filter changes the shape of
     * the query. Keeping both statements as literals means the table name
     * travels through the %i identifier placeholder and every value through
     * a %s or %d placeholder.
     */
    $like = '%' . $wpdb->esc_like( $search ) . '%';

    if ( $status ) {
        return $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, name, email, mobile, goal, message, status, created_at
                 FROM %i
                 WHERE status = %s AND ( name LIKE %s OR email LIKE %s )
                 ORDER BY created_at DESC
                 LIMIT %d OFFSET %d',
                $table,
                $status,
                $like,
                $like,
                $per_page,
                $offset
            )
        );
    }

    return $wpdb->get_results(
        $wpdb->prepare(
            'SELECT id, name, email, mobile, goal, message, status, created_at
             FROM %i
             WHERE ( name LIKE %s OR email LIKE %s )
             ORDER BY created_at DESC
             LIMIT %d OFFSET %d',
            $table,
            $like,
            $like,
            $per_page,
            $offset
        )
    );
}

function ofcm_get_booking( $booking_id ) {
    global $wpdb;

    $table = ofcm_bookings_table();

    return $wpdb->get_row(
        $wpdb->prepare(
            'SELECT id, name, email, mobile, goal, message, status, created_at
             FROM %i
             WHERE id = %d',
            $table,
            absint( $booking_id )
        )
    );
}

function ofcm_count_bookings( $status = '' ) {
    global $wpdb;

    $table  = ofcm_bookings_table();
    $status = sanitize_key( $status );

    if ( $status ) {
        return (int) $wpdb->get_var(
            $wpdb->prepare( 'SELECT COUNT(*) FROM %i WHERE status = %s', $table, $status )
        );
    }

    return (int) $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %i', $table ) );
}

function ofcm_insert_booking( $name, $email, $mobile, $goal, $message ) {
    global $wpdb;

    $result = $wpdb->insert(
        ofcm_bookings_table(),
        [
            'name'       => $name,
            'email'      => $email,
            'mobile'     => $mobile,
            'goal'       => $goal,
            'message'    => $message,
            'status'     => 'new',
            'created_at' => current_time('mysql'),
        ],
        [ '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
    );

    return $result ? (int) $wpdb->insert_id : 0;
}

/**
 * Editable booking columns and their sanitizers.
 * Anything not in this map is ignored, so a caller can never
 * write to `id` or `created_at` by passing them through.
 */
function ofcm_booking_editable_fields() {
    return [
        'name'    => 'sanitize_text_field',
        'email'   => 'sanitize_email',
        'mobile'  => 'sanitize_text_field',
        'goal'    => 'sanitize_key',
        'message' => 'sanitize_textarea_field',
        'status'  => 'sanitize_key',
    ];
}

function ofcm_update_booking( $booking_id, $fields ) {
    global $wpdb;

    $booking_id = absint( $booking_id );
    if ( ! $booking_id ) {
        return false;
    }

    $schema  = ofcm_booking_editable_fields();
    $data    = [];
    $formats = [];

    foreach ( $fields as $column => $value ) {
        if ( ! isset( $schema[ $column ] ) ) {
            continue;
        }

        $data[ $column ] = $schema[ $column ]( $value );
        $formats[]       = '%s';
    }

    if ( ! $data ) {
        return false;
    }

    if ( isset( $data['status'] ) && ! array_key_exists( $data['status'], ofcm_booking_statuses() ) ) {
        return false;
    }

    $result = $wpdb->update(
        ofcm_bookings_table(),
        $data,
        [ 'id' => $booking_id ],
        $formats,
        [ '%d' ]
    );

    return false !== $result;
}

function ofcm_delete_booking( $booking_id ) {
    global $wpdb;

    $booking_id = absint( $booking_id );
    if ( ! $booking_id ) {
        return false;
    }

    $result = $wpdb->delete(
        ofcm_bookings_table(),
        [ 'id' => $booking_id ],
        [ '%d' ]
    );

    return false !== $result;
}
// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching

function ofcm_booking_goals() {
    return [
        ''             => __( '— Select —', 'obydullah-fitness-content-manager' ),
        'weight-loss'  => __( 'Weight Loss', 'obydullah-fitness-content-manager' ),
        'muscle-gain'  => __( 'Muscle Gain', 'obydullah-fitness-content-manager' ),
        'strength'     => __( 'Strength & Performance', 'obydullah-fitness-content-manager' ),
        'mobility'     => __( 'Mobility & Recovery', 'obydullah-fitness-content-manager' ),
        'general'      => __( 'General Fitness', 'obydullah-fitness-content-manager' ),
    ];
}

function ofcm_booking_statuses() {
    return [
        'new'       => __( 'New', 'obydullah-fitness-content-manager' ),
        'contacted' => __( 'Contacted', 'obydullah-fitness-content-manager' ),
        'booked'    => __( 'Booked', 'obydullah-fitness-content-manager' ),
        'closed'    => __( 'Closed', 'obydullah-fitness-content-manager' ),
    ];
}

function ofcm_booking_status_labels() {
    return [
        'new'       => 'ofcm-status-new',
        'contacted' => 'ofcm-status-contacted',
        'booked'    => 'ofcm-status-booked',
        'closed'    => 'ofcm-status-closed',
    ];
}

/* --- Admin List Page --- */

function ofcm_bookings_menu() {
    add_submenu_page(
        'obydullah-fitness-content-manager',
        __( 'Bookings', 'obydullah-fitness-content-manager' ),
        __( 'Bookings', 'obydullah-fitness-content-manager' ),
        'manage_options',
        'ofcm-bookings',
        'ofcm_render_bookings_page'
    );
}
add_action( 'admin_menu', 'ofcm_bookings_menu' );

function ofcm_handle_booking_admin_action() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have permission to manage bookings.', 'obydullah-fitness-content-manager' ) );
    }

    // Verify nonce - sanitize the input first
    $nonce = sanitize_text_field(wp_unslash($_POST['ofcm_bookings_nonce'] ?? ''));
    if ( ! wp_verify_nonce( $nonce, 'ofcm_save_booking_admin' ) ) {
        wp_die( esc_html__( 'Security check failed.', 'obydullah-fitness-content-manager' ) );
    }

    $action = sanitize_key( wp_unslash( $_POST['booking_action'] ?? '' ) );
    $id     = absint( $_POST['booking_id'] ?? 0 );

    if ( ! $id ) {
        wp_die( esc_html__( 'Invalid booking.', 'obydullah-fitness-content-manager' ) );
    }

    if ( 'update_status' === $action ) {
        $new_status = sanitize_key( wp_unslash( $_POST['booking_status'] ?? '' ) );
        ofcm_update_booking( $id, [ 'status' => $new_status ] );
    } elseif ( 'delete' === $action ) {
        ofcm_delete_booking( $id );
    }

    wp_safe_redirect( admin_url( 'admin.php?page=ofcm-bookings' ) );
    exit;
}
add_action( 'admin_post_ofcm_booking_action', 'ofcm_handle_booking_admin_action' );

function ofcm_render_bookings_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    /*
     * These are read-only list filters coming from GET links and the search
     * box. Nothing is written, so no nonce is involved; the capability check
     * above is what guards this screen.
     */
    // phpcs:disable WordPress.Security.NonceVerification.Recommended
    $status   = sanitize_key( wp_unslash( $_GET['status'] ?? '' ) );
    $search   = sanitize_text_field( wp_unslash( $_GET['s'] ?? '' ) );
    $paged    = max( 1, absint( $_GET['paged'] ?? 1 ) );
    // phpcs:enable WordPress.Security.NonceVerification.Recommended
    $per_page = 20;

    $total   = ofcm_count_bookings( $status );
    $offset  = ( $paged - 1 ) * $per_page;
    $total_pages = (int) ceil( $total / $per_page );
    $bookings = ofcm_get_bookings( [
        'per_page' => $per_page,
        'offset'   => $offset,
        'status'   => $status,
        'search'   => $search,
    ] );

    $statuses = ofcm_booking_statuses();
    $goals    = ofcm_booking_goals();
    $labels   = ofcm_booking_status_labels();
    ?>
<div class="wrap ofcm-dashboard">
    <h1><?php esc_html_e( 'Bookings', 'obydullah-fitness-content-manager' ); ?></h1>

    <form method="get">
        <input type="hidden" name="page" value="ofcm-bookings">
        <p class="search-box">
            <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>"
                placeholder="<?php esc_attr_e( 'Search name or email', 'obydullah-fitness-content-manager' ); ?>">
            <?php submit_button( __( 'Search Bookings', 'obydullah-fitness-content-manager' ), '', 'submit', false ); ?>
        </p>
    </form>

    <ul class="subsubsub">
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ofcm-bookings' ) ); ?>"
                class="<?php echo $status ? '' : 'current'; ?>">
                <?php
                /* translators: %s: total number of bookings. */
                echo esc_html( sprintf( __( 'All (%s)', 'obydullah-fitness-content-manager' ), number_format_i18n( $total ) ) );
                ?>
            </a>
        </li>
        <?php foreach ( $statuses as $key => $label ) : ?>
        <li>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=ofcm-bookings&status=' . $key ) ); ?>"
                class="<?php echo ( $status === $key ) ? 'current' : ''; ?>">
                <?php
                    /* translators: 1: booking status label, 2: number of bookings with that status. */
                    echo esc_html( sprintf( __( '%1$s (%2$s)', 'obydullah-fitness-content-manager' ), $label, number_format_i18n( ofcm_count_bookings( $key ) ) ) );
                    ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Email', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Phone', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Goal', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Message', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Status', 'obydullah-fitness-content-manager' ); ?></th>
                <th><?php esc_html_e( 'Submitted', 'obydullah-fitness-content-manager' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( ! $bookings ) : ?>
            <tr>
                <td colspan="7"><?php esc_html_e( 'No bookings found.', 'obydullah-fitness-content-manager' ); ?></td>
            </tr>
            <?php endif; ?>

            <?php foreach ( $bookings as $booking ) : ?>
            <?php $class = $labels[ $booking->status ] ?? ''; ?>
            <tr>
                <td><strong><?php echo esc_html( $booking->name ); ?></strong></td>
                <td><a
                        href="mailto:<?php echo esc_attr( $booking->email ); ?>"><?php echo esc_html( $booking->email ); ?></a>
                </td>
                <td><?php echo esc_html( $booking->mobile ); ?></td>
                <td><?php echo esc_html( $goals[ $booking->goal ] ?? $booking->goal ); ?></td>
                <td><?php echo esc_html( wp_trim_words( $booking->message, 12 ) ); ?></td>
                <td><span
                        class="ofcm-status <?php echo esc_attr( $class ); ?>"><?php echo esc_html( $statuses[ $booking->status ] ?? $booking->status ); ?></span>
                </td>
                <td><?php echo esc_html( mysql2date( 'M j, Y g:i A', $booking->created_at ) ); ?></td>
            </tr>
            <tr>
                <td colspan="7">
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                        class="ofcm-booking-row-actions">
                        <?php wp_nonce_field( 'ofcm_save_booking_admin', 'ofcm_bookings_nonce' ); ?>
                        <input type="hidden" name="action" value="ofcm_booking_action">
                        <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
                        <input type="hidden" name="booking_action" value="update_status">
                        <label class="screen-reader-text" for="status-<?php echo esc_attr( $booking->id ); ?>">
                            <?php esc_html_e( 'Status', 'obydullah-fitness-content-manager' ); ?>
                        </label>
                        <select name="booking_status" id="status-<?php echo esc_attr( $booking->id ); ?>">
                            <?php foreach ( $statuses as $key => $label ) : ?>
                            <option value="<?php echo esc_attr( $key ); ?>"
                                <?php selected( $booking->status, $key ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <?php submit_button( __( 'Update', 'obydullah-fitness-content-manager' ), 'primary', 'submit', false ); ?>
                    </form>

                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>"
                        class="ofcm-booking-row-actions">
                        <?php wp_nonce_field( 'ofcm_save_booking_admin', 'ofcm_bookings_nonce' ); ?>
                        <input type="hidden" name="action" value="ofcm_booking_action">
                        <input type="hidden" name="booking_id" value="<?php echo esc_attr( $booking->id ); ?>">
                        <input type="hidden" name="booking_action" value="delete">
                        <?php submit_button( __( 'Delete', 'obydullah-fitness-content-manager' ), 'delete', 'submit', false ); ?>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php if ( $total_pages > 1 ) : ?>
    <div class="tablenav">
        <div class="tablenav-pages">
            <span class="button disabled"><?php echo esc_html( sprintf( '%d / %d', $paged, $total_pages ) ); ?></span>
            <?php if ( $paged > 1 ) : ?>
            <a class="prev-page button"
                href="<?php echo esc_url( admin_url( 'admin.php?page=ofcm-bookings&paged=' . ( $paged - 1 ) . '&status=' . rawurlencode( $status ) ) ); ?>">&laquo;</a>
            <?php endif; ?>
            <?php if ( $paged < $total_pages ) : ?>
            <a class="next-page button"
                href="<?php echo esc_url( admin_url( 'admin.php?page=ofcm-bookings&paged=' . ( $paged + 1 ) . '&status=' . rawurlencode( $status ) ) ); ?>">&raquo;</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php
}

/* --- AJAX Handler --- */

function ofcm_handle_booking_form() {
    // Verify nonce - sanitize the input first
    $nonce = sanitize_text_field(wp_unslash($_POST['nonce'] ?? ''));
    if ( ! wp_verify_nonce( $nonce, 'ofcm_booking_nonce' ) ) {
        wp_send_json_error( __( 'Security verification failed', 'obydullah-fitness-content-manager' ) );
    }

    $name    = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $mobile  = sanitize_text_field(wp_unslash($_POST['phone'] ?? ''));
    $goal    = sanitize_text_field(wp_unslash($_POST['goal'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));

    if ( empty( $name ) || empty( $email ) ) {
        wp_send_json_error( __( 'Name and email are required.', 'obydullah-fitness-content-manager' ) );
    }

    $booking_id = ofcm_insert_booking( $name, $email, $mobile, $goal, $message );

    if ( ! $booking_id ) {
        wp_send_json_error( __( 'Failed to submit. Please try again.', 'obydullah-fitness-content-manager' ) );
    }

    wp_send_json_success( __( 'Thank you! We\'ll get back to you within 24 hours.', 'obydullah-fitness-content-manager' ) );
}
add_action( 'wp_ajax_ofcm_submit_booking', 'ofcm_handle_booking_form' );
add_action( 'wp_ajax_nopriv_ofcm_submit_booking', 'ofcm_handle_booking_form' );

/* ======================================================
   6a. Front-End Booking Form (assets + shortcode)
======================================================= */

/**
 * Registers the booking form script and hands it the AJAX config.
 *
 * Hooked to init rather than wp_enqueue_scripts so the handle exists before
 * any plugin, theme, widget or block can call the shortcode — the shortcode
 * then only has to enqueue. The script is registered, never enqueued here, so
 * a page without a booking form pays nothing. Themes that print their own
 * markup instead of using the shortcode can call
 * wp_enqueue_script( 'ofcm-booking-form' ) to get the same behaviour.
 */
function ofcm_register_booking_form_assets() {
    wp_register_script(
        'ofcm-booking-form',
        OFCM_PLUGIN_URL . 'assets/js/ofcm-booking-form.js',
        [],
        OFCM_VERSION,
        true
    );

    wp_localize_script(
        'ofcm-booking-form',
        'ofcmBooking',
        [
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'ofcm_booking_nonce' ),
            'i18n'    => [
                'submitting'   => __( 'Submitting…', 'obydullah-fitness-content-manager' ),
                'genericError' => __( 'Something went wrong. Please try again.', 'obydullah-fitness-content-manager' ),
                'networkError' => __( 'Could not reach the server. Please try again.', 'obydullah-fitness-content-manager' ),
            ],
        ]
    );
}
add_action( 'init', 'ofcm_register_booking_form_assets' );

/**
 * Renders the booking form.
 *
 * The field names match what ofcm_handle_booking_form() reads, so the markup
 * doubles as the contract for anyone writing their own form. Output is
 * deliberately unstyled beyond what a theme provides; the class names below
 * are the intended styling hooks.
 *
 * @param array $atts Shortcode attributes.
 */
function ofcm_booking_form_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'title'  => '',
            'button' => __( 'Book a Session', 'obydullah-fitness-content-manager' ),
        ],
        $atts,
        'ofcm_booking_form'
    );

    // Registering happens on init, so the handle is always ready here no matter
    // where the shortcode is rendered. The script prints in the footer.
    wp_enqueue_script( 'ofcm-booking-form' );

    ob_start();
    ?>
    <div class="ofcm-booking">
        <?php if ( $atts['title'] ) : ?>
            <h2 class="ofcm-booking-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
        <?php endif; ?>

        <?php // The ofcm-booking-form class is what ofcm-booking-form.js binds to. ?>
        <form class="ofcm-booking-form" method="post"
            action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
            <input type="hidden" name="action" value="ofcm_submit_booking">
            <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ofcm_booking_nonce' ) ); ?>">

            <p>
                <label for="ofcm-booking-name"><?php esc_html_e( 'Name', 'obydullah-fitness-content-manager' ); ?> <span aria-hidden="true">*</span></label>
                <input type="text" id="ofcm-booking-name" name="name" required>
            </p>
            <p>
                <label for="ofcm-booking-email"><?php esc_html_e( 'Email', 'obydullah-fitness-content-manager' ); ?> <span aria-hidden="true">*</span></label>
                <input type="email" id="ofcm-booking-email" name="email" required>
            </p>
            <p>
                <label for="ofcm-booking-phone"><?php esc_html_e( 'Phone', 'obydullah-fitness-content-manager' ); ?></label>
                <input type="tel" id="ofcm-booking-phone" name="phone">
            </p>
            <p>
                <label for="ofcm-booking-goal"><?php esc_html_e( 'Goal', 'obydullah-fitness-content-manager' ); ?></label>
                <select id="ofcm-booking-goal" name="goal">
                    <?php foreach ( ofcm_booking_goals() as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <label for="ofcm-booking-message"><?php esc_html_e( 'Message', 'obydullah-fitness-content-manager' ); ?></label>
                <textarea id="ofcm-booking-message" name="message" rows="5"></textarea>
            </p>
            <p>
                <button type="submit"><?php echo esc_html( $atts['button'] ); ?></button>
            </p>
            <p class="ofcm-booking-form__status" role="status" aria-live="polite"></p>
        </form>

        <noscript>
            <p class="ofcm-booking-form__noscript">
                <?php
                esc_html_e( 'This form needs JavaScript enabled.', 'obydullah-fitness-content-manager' );

                $contact_email = get_option( 'ofcm_contact_email', '' );

                if ( $contact_email ) {
                    ?>
                    <a href="<?php echo esc_url( 'mailto:' . $contact_email ); ?>"><?php echo esc_html( $contact_email ); ?></a>
                    <?php
                } else {
                    esc_html_e( 'Please contact us directly and we will get back to you.', 'obydullah-fitness-content-manager' );
                }
                ?>
            </p>
        </noscript>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ofcm_booking_form', 'ofcm_booking_form_shortcode' );

/* ======================================================
   7. Site Settings (Contact + About)
====================================================== */

function ofcm_settings_page() {
    add_submenu_page(
        'obydullah-fitness-content-manager',
        __( 'Site Settings', 'obydullah-fitness-content-manager' ),
        __( 'Site Settings', 'obydullah-fitness-content-manager' ),
        'manage_options',
        'ofcm-settings',
        'ofcm_render_settings_page'
    );
}
add_action( 'admin_menu', 'ofcm_settings_page' );

function ofcm_render_settings_page() {
    if ( isset( $_POST['ofcm_settings_submit'] ) && check_admin_referer( 'ofcm_save_settings' ) ) {
        $text_fields = [
            'ofcm_contact_email', 'ofcm_contact_phone', 'ofcm_contact_location',
            'ofcm_social_instagram', 'ofcm_social_youtube', 'ofcm_social_tiktok', 'ofcm_social_linkedin',
            'ofcm_about_text1', 'ofcm_about_text2', 'ofcm_about_coach_name', 'ofcm_about_coach_title',
            'ofcm_about_stat1_number', 'ofcm_about_stat1_label',
            'ofcm_about_stat2_number', 'ofcm_about_stat2_label',
            'ofcm_about_stat3_number', 'ofcm_about_stat3_label',
            'ofcm_about_rating', 'ofcm_about_rating_source',
        ];
        foreach ( $text_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        $textarea_fields = [ 'ofcm_about_certs', 'ofcm_about_specialties' ];
        foreach ( $textarea_fields as $key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_option( $key, sanitize_textarea_field( wp_unslash( $_POST[ $key ] ) ) );
            }
        }
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'obydullah-fitness-content-manager' ) . '</p></div>';
    }

    /*
     * Returns the raw stored value; every call site escapes it for its own
     * context with esc_attr() or esc_textarea().
     */
    $setting = function ( $key, $default = '' ) {
        return (string) get_option( $key, $default );
    };
    ?>
<div class="wrap">
    <h1><?php esc_html_e( 'Site Settings', 'obydullah-fitness-content-manager' ); ?></h1>
    <form method="post">
        <?php wp_nonce_field( 'ofcm_save_settings' ); ?>

        <h2 class="title"><?php esc_html_e( 'Contact Information', 'obydullah-fitness-content-manager' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="ofcm_contact_email"><?php esc_html_e( 'Email Address', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="email" id="ofcm_contact_email" name="ofcm_contact_email"
                        value="<?php echo esc_attr( $setting( 'ofcm_contact_email', 'alex@example.com' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_contact_phone"><?php esc_html_e( 'Phone Number', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_contact_phone" name="ofcm_contact_phone"
                        value="<?php echo esc_attr( $setting( 'ofcm_contact_phone', '+1 (555) 123-4567' ) ); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_contact_location"><?php esc_html_e( 'Location', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_contact_location" name="ofcm_contact_location"
                        value="<?php echo esc_attr( $setting( 'ofcm_contact_location', 'Online & In-Person (NYC)' ) ); ?>"
                        class="regular-text"></td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Social Links', 'obydullah-fitness-content-manager' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="ofcm_social_instagram"><?php esc_html_e( 'Instagram URL', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="url" id="ofcm_social_instagram" name="ofcm_social_instagram"
                        value="<?php echo esc_attr( $setting( 'ofcm_social_instagram', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_social_youtube"><?php esc_html_e( 'YouTube URL', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="url" id="ofcm_social_youtube" name="ofcm_social_youtube"
                        value="<?php echo esc_attr( $setting( 'ofcm_social_youtube', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_social_tiktok"><?php esc_html_e( 'TikTok URL', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="url" id="ofcm_social_tiktok" name="ofcm_social_tiktok"
                        value="<?php echo esc_attr( $setting( 'ofcm_social_tiktok', '#' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_social_linkedin"><?php esc_html_e( 'LinkedIn URL', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="url" id="ofcm_social_linkedin" name="ofcm_social_linkedin"
                        value="<?php echo esc_attr( $setting( 'ofcm_social_linkedin', '#' ) ); ?>" class="regular-text"></td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'About Section', 'obydullah-fitness-content-manager' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="ofcm_about_text1"><?php esc_html_e( 'About Paragraph 1', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><textarea id="ofcm_about_text1" name="ofcm_about_text1" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'ofcm_about_text1', "I'm Alex — a certified personal trainer with 8+ years of experience helping everyday people achieve extraordinary transformations. My approach blends science-backed programming with real-world accountability." ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_text2"><?php esc_html_e( 'About Paragraph 2', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><textarea id="ofcm_about_text2" name="ofcm_about_text2" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'ofcm_about_text2', "Whether you're a beginner or a seasoned athlete, I meet you where you are and take you where you want to go. No judgment. Just progress." ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_coach_name"><?php esc_html_e( 'Coach Name', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_coach_name" name="ofcm_about_coach_name"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_coach_name', 'Alex Rivera' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_coach_title"><?php esc_html_e( 'Coach Title', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_coach_title" name="ofcm_about_coach_title"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_coach_title', 'Certified Personal Trainer' ) ); ?>"
                        class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_certs"><?php esc_html_e( 'Certifications (one per line)', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><textarea id="ofcm_about_certs" name="ofcm_about_certs" rows="4"
                        class="large-text"><?php echo esc_textarea( $setting( 'ofcm_about_certs', "Certified Personal Trainer\nNSCA — CPT\nNutrition Specialist\nPrecision Nutrition Level 1\nBehavioral Coach\nMindset & habit formation\n200+ Transformations\nReal people, real results" ) ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_specialties"><?php esc_html_e( 'Specialties (one per line)', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><textarea id="ofcm_about_specialties" name="ofcm_about_specialties" rows="3"
                        class="large-text"><?php echo esc_textarea( $setting( 'ofcm_about_specialties', "Goal-Oriented\nEvery session has purpose\nData-Driven\nTrack & optimize your progress\nHolistic\nMindset + nutrition + movement\nAccountability\nI show up. You show up." ) ); ?></textarea>
                </td>
            </tr>
        </table>

        <h2 class="title"><?php esc_html_e( 'Stats & Rating', 'obydullah-fitness-content-manager' ); ?></h2>
        <table class="form-table">
            <tr>
                <th><label
                        for="ofcm_about_stat1_number"><?php esc_html_e( 'Stat 1 Number', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat1_number" name="ofcm_about_stat1_number"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat1_number', '500+' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_stat1_label"><?php esc_html_e( 'Stat 1 Label', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat1_label" name="ofcm_about_stat1_label"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat1_label', 'Clients' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_stat2_number"><?php esc_html_e( 'Stat 2 Number', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat2_number" name="ofcm_about_stat2_number"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat2_number', '97%' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_stat2_label"><?php esc_html_e( 'Stat 2 Label', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat2_label" name="ofcm_about_stat2_label"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat2_label', 'Success Rate' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_stat3_number"><?php esc_html_e( 'Stat 3 Number', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat3_number" name="ofcm_about_stat3_number"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat3_number', '8yr' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_stat3_label"><?php esc_html_e( 'Stat 3 Label', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_stat3_label" name="ofcm_about_stat3_label"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_stat3_label', 'Experience' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label for="ofcm_about_rating"><?php esc_html_e( 'Rating', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_rating" name="ofcm_about_rating"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_rating', '4.9 / 5.0' ) ); ?>" class="regular-text"></td>
            </tr>
            <tr>
                <th><label
                        for="ofcm_about_rating_source"><?php esc_html_e( 'Rating Source', 'obydullah-fitness-content-manager' ); ?></label>
                </th>
                <td><input type="text" id="ofcm_about_rating_source" name="ofcm_about_rating_source"
                        value="<?php echo esc_attr( $setting( 'ofcm_about_rating_source', 'from 200+ reviews' ) ); ?>"
                        class="regular-text"></td>
            </tr>
        </table>

        <?php submit_button( __( 'Save Settings', 'obydullah-fitness-content-manager' ), 'primary', 'ofcm_settings_submit' ); ?>
    </form>
</div>
<?php
}