<?php
/**
 * Plugin Name: Obydullah Fashion Store Core
 * Description: Core functionality for the Fashion theme
 * Version:     1.0.0
 * Author:      Shaik Obydullah
 * Author URI:  https://obydullah.com
 * Text Domain: obydullah-fashion-store-core
 * Requires at least: 6.2
 * Requires PHP: 8.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * ====================================================================================
 *                         INDEX
 * ====================================================================================
 * 1. Security, Constants & Requirements
 * 2. Activation Hook
 * 3. Admin Dashboard
 * 4. Hero Slider CPT + Meta Boxes
 * 5. Testimonials CPT + Meta Boxes
 * 6. Footer Settings (Single Instance) + Meta Boxes
 * =====================================================================================
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( version_compare( PHP_VERSION, '8.0', '<' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Obydullah Fashion Store Core requires PHP 8.0 or higher. Your server is running PHP ', 'obydullah-fashion-store-core' );
        echo esc_html( PHP_VERSION );
        echo '.</p></div>';
    } );
    return;
}

define( 'OFSC_CORE_VERSION', '1.0.0' );
define( 'OFSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'OFSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ======================================================
   2. Activation Hook
====================================================== */

function ofsc_activate() {
    $hero_post = get_posts( [
        'post_type'      => 'ofsc_hero_slide',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ] );
    if ( empty( $hero_post ) ) {
        wp_insert_post( [
            'post_title'   => __( 'Sample Hero Slide', 'obydullah-fashion-store-core' ),
            'post_type'    => 'ofsc_hero_slide',
            'post_status'  => 'draft',
            'menu_order'   => 1,
            'meta_input'   => [
                'ofsc_kicker'   => __( 'Spring / Summer 2026', 'obydullah-fashion-store-core' ),
                'ofsc_subtitle' => __( 'Discover the latest collection.', 'obydullah-fashion-store-core' ),
            ],
        ] );
    }

    $testimonial_post = get_posts( [
        'post_type'      => 'ofsc_testimonial',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ] );
    if ( empty( $testimonial_post ) ) {
        wp_insert_post( [
            'post_title'   => __( 'Jane Doe', 'obydullah-fashion-store-core' ),
            'post_type'    => 'ofsc_testimonial',
            'post_status'  => 'draft',
            'meta_input'   => [
                'ofsc_testimonial_quote'  => __( 'Absolutely love the quality and style!', 'obydullah-fashion-store-core' ),
                'ofsc_testimonial_role'   => __( 'Fashion Enthusiast', 'obydullah-fashion-store-core' ),
                'ofsc_testimonial_rating' => 5,
            ],
        ] );
    }

    $footer_post = get_posts( [
        'post_type'      => 'ofsc_footer',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ] );
    if ( empty( $footer_post ) ) {
        wp_insert_post( [
            'post_title'  => __( 'Footer Settings', 'obydullah-fashion-store-core' ),
            'post_type'   => 'ofsc_footer',
            'post_status' => 'publish',
            'meta_input'  => [
                'ofsc_footer_copyright' => '&copy; ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ),
            ],
        ] );
    }

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ofsc_activate' );

/* ======================================================
   3. Admin Dashboard
====================================================== */

function ofsc_add_admin_menu() {
    add_menu_page(
        __( 'Fashion Store Core', 'obydullah-fashion-store-core' ),
        __( 'Fashion Store Core', 'obydullah-fashion-store-core' ),
        'manage_options',
        'ofsc-fashion-core',
        'ofsc_fashion_core_page',
        'dashicons-heart',
        59
    );
}
add_action( 'admin_menu', 'ofsc_add_admin_menu' );

function ofsc_fashion_core_page() {
    $sections = [
        'hero_slides' => [
            'title' => __( 'Hero Slides', 'obydullah-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=ofsc_hero_slide' ),
            'icon'  => 'dashicons-images-alt2',
        ],
        'testimonials' => [
            'title' => __( 'Testimonials', 'obydullah-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=ofsc_testimonial' ),
            'icon'  => 'dashicons-format-quote',
        ],
        'footer_settings' => [
            'title' => __( 'Footer Settings', 'obydullah-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=ofsc_footer' ),
            'icon'  => 'dashicons-layout',
        ],
    ];
    ?>
<div class="wrap ofsc-dashboard">
    <h1><?php esc_html_e( 'Obydullah Fashion Store', 'obydullah-fashion-store-core' ); ?></h1>
    <p class="ofsc-dashboard-description">
        <?php esc_html_e( 'Manage your store content from the sections below.', 'obydullah-fashion-store-core' ); ?>
    </p>
    <div class="ofsc-dashboard-grid"
        style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;margin-top:1.5rem;">
        <?php foreach ( $sections as $section ) : ?>
        <div class="ofsc-dashboard-card"
            style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:1.5rem;text-align:center;">
            <div class="dashicons <?php echo esc_attr( $section['icon'] ); ?>"
                style="font-size:2rem;width:auto;height:auto;margin-bottom:0.5rem;color:#f43f5e;"></div>
            <h2 style="margin:0.5rem 0;font-size:1rem;"><?php echo esc_html( $section['title'] ); ?></h2>
            <a href="<?php echo esc_url( $section['url'] ); ?>"
                class="button button-primary"><?php esc_html_e( 'Manage', 'obydullah-fashion-store-core' ); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
}

/* ======================================================
   4. Hero Slider CPT + Meta Boxes
====================================================== */

function ofsc_register_hero_slide_cpt() {
    register_post_type( 'ofsc_hero_slide', [
        'labels' => [
            'name'          => __( 'Hero Slides', 'obydullah-fashion-store-core' ),
            'singular_name' => __( 'Hero Slide', 'obydullah-fashion-store-core' ),
            'add_new_item'  => __( 'Add New Hero Slide', 'obydullah-fashion-store-core' ),
            'edit_item'     => __( 'Edit Hero Slide', 'obydullah-fashion-store-core' ),
        ],
        'public'        => true,
        'show_in_menu'  => 'ofsc-fashion-core',
        'menu_icon'     => 'dashicons-images-alt2',
        'supports'      => [ 'title', 'thumbnail', 'page-attributes' ],
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => [ 'slug' => 'ofsc-hero-slide' ],
    ] );
}
add_action( 'init', 'ofsc_register_hero_slide_cpt' );

function ofsc_add_hero_slide_meta_box() {
    add_meta_box(
        'ofsc_hero_slide_meta',
        __( 'Hero Slide Settings', 'obydullah-fashion-store-core' ),
        'ofsc_render_hero_slide_meta_box',
        'ofsc_hero_slide',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'ofsc_add_hero_slide_meta_box' );

function ofsc_render_hero_slide_meta_box( $post ) {
    $subtitle = get_post_meta( $post->ID, 'ofsc_subtitle', true );
    $kicker   = get_post_meta( $post->ID, 'ofsc_kicker', true );
    wp_nonce_field( 'ofsc_save_hero_slide_meta', 'ofsc_hero_slide_nonce' );
    ?>
<p>
    <label
        for="ofsc_kicker"><strong><?php esc_html_e( 'Kicker (e.g., "Autumn / Winter 2026")', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="text" id="ofsc_kicker" name="ofsc_kicker" value="<?php echo esc_attr( $kicker ); ?>" class="widefat">
</p>
<p>
    <label
        for="ofsc_subtitle"><strong><?php esc_html_e( 'Subtitle / Description', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <textarea id="ofsc_subtitle" name="ofsc_subtitle" rows="3"
        class="large-text"><?php echo esc_textarea( $subtitle ); ?></textarea>
</p>
<?php
}

function ofsc_save_hero_slide_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofsc_hero_slide_nonce'] ?? '' ) ), 'ofsc_save_hero_slide_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofsc_hero_slide' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    if ( isset( $_POST['ofsc_kicker'] ) ) {
        update_post_meta( $post_id, 'ofsc_kicker', sanitize_text_field( wp_unslash( $_POST['ofsc_kicker'] ) ) );
    }
    if ( isset( $_POST['ofsc_subtitle'] ) ) {
        update_post_meta( $post_id, 'ofsc_subtitle', sanitize_textarea_field( wp_unslash( $_POST['ofsc_subtitle'] ) ) );
    }
}
add_action( 'save_post_ofsc_hero_slide', 'ofsc_save_hero_slide_meta' );

/* ======================================================
   5. Testimonials CPT + Meta Boxes
====================================================== */

function ofsc_register_testimonial_cpt() {
    register_post_type( 'ofsc_testimonial', [
        'labels' => [
            'name'          => __( 'Testimonials', 'obydullah-fashion-store-core' ),
            'singular_name' => __( 'Testimonial', 'obydullah-fashion-store-core' ),
            'add_new_item'  => __( 'Add New Testimonial', 'obydullah-fashion-store-core' ),
            'edit_item'     => __( 'Edit Testimonial', 'obydullah-fashion-store-core' ),
        ],
        'public'          => true,
        'show_in_menu'    => 'ofsc-fashion-core',
        'menu_icon'       => 'dashicons-format-quote',
        'supports'        => [ 'title' ],
        'show_in_rest'    => true,
        'has_archive'     => false,
        'publicly_queryable' => true,
    ] );
}
add_action( 'init', 'ofsc_register_testimonial_cpt' );

function ofsc_add_testimonial_meta_boxes() {
    add_meta_box(
        'ofsc_testimonial_quote',
        __( 'Quote', 'obydullah-fashion-store-core' ),
        'ofsc_testimonial_quote_callback',
        'ofsc_testimonial',
        'normal',
        'high'
    );
    add_meta_box(
        'ofsc_testimonial_details',
        __( 'Details', 'obydullah-fashion-store-core' ),
        'ofsc_testimonial_details_callback',
        'ofsc_testimonial',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'ofsc_add_testimonial_meta_boxes' );

function ofsc_testimonial_quote_callback( $post ) {
    wp_nonce_field( 'ofsc_testimonial_meta', 'ofsc_testimonial_nonce' );
    $quote = get_post_meta( $post->ID, 'ofsc_testimonial_quote', true );
    echo '<textarea name="ofsc_testimonial_quote" rows="4" class="large-text">' . esc_textarea( $quote ) . '</textarea>';
}

function ofsc_testimonial_details_callback( $post ) {
    $rating = get_post_meta( $post->ID, 'ofsc_testimonial_rating', true );
    $role   = get_post_meta( $post->ID, 'ofsc_testimonial_role', true );
    $avatar = get_post_meta( $post->ID, 'ofsc_testimonial_avatar', true );
    ?>
<p>
    <label
        for="ofsc_testimonial_role"><strong><?php esc_html_e( 'Role / Title', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="text" name="ofsc_testimonial_role" id="ofsc_testimonial_role" value="<?php echo esc_attr( $role ); ?>"
        class="widefat"
        placeholder="<?php esc_attr_e( 'e.g., Fashion Enthusiast', 'obydullah-fashion-store-core' ); ?>">
</p>
<p>
    <label
        for="ofsc_testimonial_rating"><strong><?php esc_html_e( 'Rating (1-5)', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <select name="ofsc_testimonial_rating" id="ofsc_testimonial_rating" class="widefat">
        <?php for ( $i = 5; $i >= 1; $i-- ) : ?>
        <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( $i ); ?>
            <?php esc_html_e( 'Star' , 'obydullah-fashion-store-core' ); ?><?php echo $i > 1 ? 's' : ''; ?></option>
        <?php endfor; ?>
    </select>
</p>
<p>
    <label
        for="ofsc_testimonial_avatar"><strong><?php esc_html_e( 'Avatar URL', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="ofsc_testimonial_avatar" id="ofsc_testimonial_avatar"
        value="<?php echo esc_url( $avatar ); ?>" class="widefat"
        placeholder="<?php esc_attr_e( 'https://...', 'obydullah-fashion-store-core' ); ?>">
</p>
<?php
}

function ofsc_save_testimonial_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofsc_testimonial_nonce'] ?? '' ) ), 'ofsc_testimonial_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofsc_testimonial' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    if ( isset( $_POST['ofsc_testimonial_quote'] ) ) {
        update_post_meta( $post_id, 'ofsc_testimonial_quote', sanitize_textarea_field( wp_unslash( $_POST['ofsc_testimonial_quote'] ) ) );
    }
    if ( isset( $_POST['ofsc_testimonial_role'] ) ) {
        update_post_meta( $post_id, 'ofsc_testimonial_role', sanitize_text_field( wp_unslash( $_POST['ofsc_testimonial_role'] ) ) );
    }
    if ( isset( $_POST['ofsc_testimonial_rating'] ) ) {
        update_post_meta( $post_id, 'ofsc_testimonial_rating', intval( $_POST['ofsc_testimonial_rating'] ) );
    }
    if ( isset( $_POST['ofsc_testimonial_avatar'] ) ) {
        update_post_meta( $post_id, 'ofsc_testimonial_avatar', esc_url_raw( wp_unslash( $_POST['ofsc_testimonial_avatar'] ) ) );
    }
}
add_action( 'save_post_ofsc_testimonial', 'ofsc_save_testimonial_meta' );

/* ======================================================
   6. Footer Settings (Single Instance) + Meta Boxes
====================================================== */

function ofsc_register_footer_settings() {
    register_post_type( 'ofsc_footer', [
        'labels' => [
            'name'          => __( 'Footer Settings', 'obydullah-fashion-store-core' ),
            'singular_name' => __( 'Footer Settings', 'obydullah-fashion-store-core' ),
            'add_new_item'  => __( 'Edit Footer Settings', 'obydullah-fashion-store-core' ),
            'edit_item'     => __( 'Edit Footer Settings', 'obydullah-fashion-store-core' ),
        ],
        'public'           => false,
        'show_ui'          => true,
        'show_in_menu'     => 'ofsc-fashion-core',
        'menu_icon'        => 'dashicons-layout',
        'supports'         => [ 'title' ],
        'show_in_rest'     => true,
        'capability_type'  => 'post',
        'map_meta_cap'     => true,
    ] );
}
add_action( 'init', 'ofsc_register_footer_settings' );

function ofsc_limit_footer_settings() {
    global $pagenow;

    if ( 'post-new.php' !== $pagenow ) {
        return;
    }

    // Screen routing only, never a mutation, so a nonce does not apply here.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    $post_type = isset( $_GET['post_type'] ) ? sanitize_key( wp_unslash( $_GET['post_type'] ) ) : '';

    if ( 'ofsc_footer' !== $post_type ) {
        return;
    }

    $existing = get_posts( [
        'post_type'      => 'ofsc_footer',
        'posts_per_page' => 1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ] );
    if ( ! empty( $existing ) ) {
        $post_id = $existing[0];
        if ( current_user_can( 'edit_post', $post_id ) ) {
            wp_safe_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
            exit;
        }
    }
}
add_action( 'admin_init', 'ofsc_limit_footer_settings' );

function ofsc_add_footer_meta_boxes() {
    add_meta_box( 'ofsc_footer_logo', __( 'Logo & Tagline', 'obydullah-fashion-store-core' ), 'ofsc_footer_logo_callback', 'ofsc_footer', 'normal', 'high' );
    add_meta_box( 'ofsc_footer_social', __( 'Social Media URLs', 'obydullah-fashion-store-core' ), 'ofsc_footer_social_callback', 'ofsc_footer', 'normal', 'high' );
    add_meta_box( 'ofsc_footer_quick_links', __( 'Quick Links', 'obydullah-fashion-store-core' ), 'ofsc_footer_links_callback', 'ofsc_footer', 'normal', 'high' );
    add_meta_box( 'ofsc_footer_contact', __( 'Contact Information', 'obydullah-fashion-store-core' ), 'ofsc_footer_contact_callback', 'ofsc_footer', 'normal', 'high' );
    add_meta_box( 'ofsc_footer_copyright', __( 'Copyright Text', 'obydullah-fashion-store-core' ), 'ofsc_footer_copyright_callback', 'ofsc_footer', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'ofsc_add_footer_meta_boxes' );

function ofsc_footer_logo_callback( $post ) {
    wp_nonce_field( 'ofsc_footer_meta', 'ofsc_footer_nonce' );
    $tagline = get_post_meta( $post->ID, 'ofsc_footer_tagline', true );
    ?>
<p>
    <label
        for="ofsc_footer_tagline"><strong><?php esc_html_e( 'Tagline / Description', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <textarea name="ofsc_footer_tagline" id="ofsc_footer_tagline" rows="3"
        class="large-text"><?php echo esc_textarea( $tagline ); ?></textarea>
</p>
<?php
}

function ofsc_footer_social_callback( $post ) {
    $social = get_post_meta( $post->ID, 'ofsc_footer_social', true );
    if ( ! is_array( $social ) ) {
        $social = [];
    }
    ?>
<p>
    <label
        for="ofsc_footer_social_instagram"><strong><?php esc_html_e( 'Instagram URL', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="ofsc_footer_social[instagram]" id="ofsc_footer_social_instagram"
        value="<?php echo esc_url( $social['instagram'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label
        for="ofsc_footer_social_pinterest"><strong><?php esc_html_e( 'Pinterest URL', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="ofsc_footer_social[pinterest]" id="ofsc_footer_social_pinterest"
        value="<?php echo esc_url( $social['pinterest'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label
        for="ofsc_footer_social_youtube"><strong><?php esc_html_e( 'YouTube URL', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="ofsc_footer_social[youtube]" id="ofsc_footer_social_youtube"
        value="<?php echo esc_url( $social['youtube'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label
        for="ofsc_footer_social_tiktok"><strong><?php esc_html_e( 'TikTok URL', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="ofsc_footer_social[tiktok]" id="ofsc_footer_social_tiktok"
        value="<?php echo esc_url( $social['tiktok'] ?? '' ); ?>" class="widefat">
</p>
<?php
}

function ofsc_footer_links_callback( $post ) {
    $links = get_post_meta( $post->ID, 'ofsc_footer_links', true );
    if ( ! is_array( $links ) ) {
        $links = [];
    }
    ?>
<div id="ofsc-footer-links-repeater">
    <?php foreach ( $links as $index => $link ) : ?>
    <div class="ofsc-footer-link-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
        <input type="text" name="ofsc_footer_links[<?php echo esc_attr( $index ); ?>][text]"
            value="<?php echo esc_attr( $link['text'] ?? '' ); ?>"
            placeholder="<?php esc_attr_e( 'Link text', 'obydullah-fashion-store-core' ); ?>" style="flex:1;">
        <input type="url" name="ofsc_footer_links[<?php echo esc_attr( $index ); ?>][url]"
            value="<?php echo esc_url( $link['url'] ?? '' ); ?>"
            placeholder="<?php esc_attr_e( 'URL', 'obydullah-fashion-store-core' ); ?>" style="flex:1;">
        <button type="button"
            class="button ofsc-remove-link"><?php esc_html_e( 'Remove', 'obydullah-fashion-store-core' ); ?></button>
    </div>
    <?php endforeach; ?>
</div>
<button type="button" id="ofsc-add-footer-link"
    class="button"><?php esc_html_e( 'Add Link', 'obydullah-fashion-store-core' ); ?></button>
<script>
jQuery(function($) {
    var idx = <?php echo count( $links ); ?>;
    $('#ofsc-add-footer-link').on('click', function() {
        var row =
            '<div class="ofsc-footer-link-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">' +
            '<input type="text" name="ofsc_footer_links[' + idx +
            '][text]" placeholder="Link text" style="flex:1;">' +
            '<input type="url" name="ofsc_footer_links[' + idx +
            '][url]" placeholder="URL" style="flex:1;">' +
            '<button type="button" class="button ofsc-remove-link">Remove</button></div>';
        $('#ofsc-footer-links-repeater').append(row);
        idx++;
    });
    $(document).on('click', '.ofsc-remove-link', function() {
        $(this).closest('.ofsc-footer-link-row').remove();
    });
});
</script>
<?php
}

function ofsc_footer_contact_callback( $post ) {
    $address = get_post_meta( $post->ID, 'ofsc_footer_address', true );
    $phone   = get_post_meta( $post->ID, 'ofsc_footer_phone', true );
    $email   = get_post_meta( $post->ID, 'ofsc_footer_email', true );
    ?>
<p>
    <label
        for="ofsc_footer_address"><strong><?php esc_html_e( 'Address', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <textarea name="ofsc_footer_address" id="ofsc_footer_address" rows="3"
        class="large-text"><?php echo esc_textarea( $address ); ?></textarea>
</p>
<p>
    <label
        for="ofsc_footer_phone"><strong><?php esc_html_e( 'Phone', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="tel" name="ofsc_footer_phone" id="ofsc_footer_phone" value="<?php echo esc_attr( $phone ); ?>"
        class="widefat">
</p>
<p>
    <label
        for="ofsc_footer_email"><strong><?php esc_html_e( 'Email', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="email" name="ofsc_footer_email" id="ofsc_footer_email" value="<?php echo esc_attr( $email ); ?>"
        class="widefat">
</p>
<?php
}

function ofsc_footer_copyright_callback( $post ) {
    $copyright = get_post_meta( $post->ID, 'ofsc_footer_copyright', true );
    ?>
<p>
    <label
        for="ofsc_footer_copyright"><strong><?php esc_html_e( 'Copyright text', 'obydullah-fashion-store-core' ); ?></strong></label><br>
    <input type="text" name="ofsc_footer_copyright" id="ofsc_footer_copyright"
        value="<?php echo esc_attr( $copyright ); ?>" class="widefat">
</p>
<?php
}

function ofsc_save_footer_meta( $post_id ) {
    // Security & Execution Guards
    if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ofsc_footer_nonce'] ?? '' ) ), 'ofsc_footer_meta' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( 'ofsc_footer' !== get_post_type( $post_id ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Sanitize & Save
    if ( isset( $_POST['ofsc_footer_tagline'] ) ) {
        update_post_meta( $post_id, 'ofsc_footer_tagline', sanitize_textarea_field( wp_unslash( $_POST['ofsc_footer_tagline'] ) ) );
    }

    if ( isset( $_POST['ofsc_footer_social'] ) && is_array( $_POST['ofsc_footer_social'] ) ) {
        $social = array_map( 'esc_url_raw', wp_unslash( $_POST['ofsc_footer_social'] ) );
        update_post_meta( $post_id, 'ofsc_footer_social', $social );
    }

    if ( isset( $_POST['ofsc_footer_links'] ) && is_array( $_POST['ofsc_footer_links'] ) ) {
        // Unslashed once here; every leaf is sanitized individually in the loop below.
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $submitted = wp_unslash( $_POST['ofsc_footer_links'] );
        $links = [];
        foreach ( $submitted as $link ) {
            if ( ! is_array( $link ) ) {
                continue;
            }
            $text = sanitize_text_field( $link['text'] ?? '' );
            $url  = esc_url_raw( $link['url'] ?? '' );
            if ( $text && $url ) {
                $links[] = [ 'text' => $text, 'url' => $url ];
            }
        }
        update_post_meta( $post_id, 'ofsc_footer_links', $links );
    }

    if ( isset( $_POST['ofsc_footer_address'] ) ) {
        update_post_meta( $post_id, 'ofsc_footer_address', sanitize_textarea_field( wp_unslash( $_POST['ofsc_footer_address'] ) ) );
    }
    if ( isset( $_POST['ofsc_footer_phone'] ) ) {
        update_post_meta( $post_id, 'ofsc_footer_phone', sanitize_text_field( wp_unslash( $_POST['ofsc_footer_phone'] ) ) );
    }
    if ( isset( $_POST['ofsc_footer_email'] ) ) {
        update_post_meta( $post_id, 'ofsc_footer_email', sanitize_email( wp_unslash( $_POST['ofsc_footer_email'] ) ) );
    }
    if ( isset( $_POST['ofsc_footer_copyright'] ) ) {
        update_post_meta( $post_id, 'ofsc_footer_copyright', sanitize_text_field( wp_unslash( $_POST['ofsc_footer_copyright'] ) ) );
    }
}
add_action( 'save_post_ofsc_footer', 'ofsc_save_footer_meta' );