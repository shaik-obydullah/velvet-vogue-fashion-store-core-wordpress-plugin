<?php
/**
 * Plugin Name: Velvet Vogue Fashion Store Core
 * Plugin URI: https://obydullah.com/project/velvet-vogue-fashion-store-core
 * Description: Manage hero sliders, testimonials, and footer settings from the WordPress admin. Works with any theme.
 * Version:     1.0.0
 * Author:      Shaik Obydullah
 * Author URI:  https://obydullah.com
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: velvet-vogue-fashion-store-core
 * Domain Path: /languages
 * Requires at least: 5.0
 * Requires PHP: 7.4
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

if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
    add_action( 'admin_notices', function () {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__( 'Velvet Vogue Fashion Store Core requires PHP 7.4 or higher. Your server is running PHP ', 'velvet-vogue-fashion-store-core' );
        echo esc_html( PHP_VERSION );
        echo '.</p></div>';
    } );
    return;
}

define( 'VVFS_CORE_VERSION', '1.0.0' );
define( 'VVFS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VVFS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/* ======================================================
   2. Activation Hook
====================================================== */

function vvfs_activate() {
    $hero_post = get_posts( array(
        'post_type'      => 'vvfs_hero_slide',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( empty( $hero_post ) ) {
        wp_insert_post( array(
            'post_title'   => __( 'Sample Hero Slide', 'velvet-vogue-fashion-store-core' ),
            'post_type'    => 'vvfs_hero_slide',
            'post_status'  => 'draft',
            'menu_order'   => 1,
            'meta_input'   => array(
                'vvfs_kicker'   => __( 'Spring / Summer 2026', 'velvet-vogue-fashion-store-core' ),
                'vvfs_subtitle' => __( 'Discover the latest collection.', 'velvet-vogue-fashion-store-core' ),
            ),
        ) );
    }

    $testimonial_post = get_posts( array(
        'post_type'      => 'vvfs_testimonial',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( empty( $testimonial_post ) ) {
        wp_insert_post( array(
            'post_title'   => __( 'Jane Doe', 'velvet-vogue-fashion-store-core' ),
            'post_type'    => 'vvfs_testimonial',
            'post_status'  => 'draft',
            'meta_input'   => array(
                'vvfs_testimonial_quote'  => __( 'Absolutely love the quality and style!', 'velvet-vogue-fashion-store-core' ),
                'vvfs_testimonial_role'   => __( 'Fashion Enthusiast', 'velvet-vogue-fashion-store-core' ),
                'vvfs_testimonial_rating' => 5,
            ),
        ) );
    }

    $footer_post = get_posts( array(
        'post_type'      => 'vvfs_footer',
        'posts_per_page' => 1,
        'fields'         => 'ids',
    ) );
    if ( empty( $footer_post ) ) {
        wp_insert_post( array(
            'post_title'  => __( 'Footer Settings', 'velvet-vogue-fashion-store-core' ),
            'post_type'   => 'vvfs_footer',
            'post_status' => 'publish',
            'meta_input'  => array(
                'vvfs_footer_copyright' => '&copy; ' . gmdate( 'Y' ) . ' ' . get_bloginfo( 'name' ),
            ),
        ) );
    }

    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'vvfs_activate' );

/* ======================================================
   3. Admin Dashboard
====================================================== */

function vvfs_add_admin_menu() {
    add_menu_page(
        __( 'Velvet Vogue Fashion Store', 'velvet-vogue-fashion-store-core' ),
        __( 'VVFS Core', 'velvet-vogue-fashion-store-core' ),
        'manage_options',
        'vvfs-fashion-core',
        'vvfs_fashion_core_page',
        'dashicons-heart',
        59
    );
}
add_action( 'admin_menu', 'vvfs_add_admin_menu' );

function vvfs_fashion_core_page() {
    $sections = array(
        'hero_slides' => array(
            'title' => __( 'Hero Slides', 'velvet-vogue-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=vvfs_hero_slide' ),
            'icon'  => 'dashicons-images-alt2',
        ),
        'testimonials' => array(
            'title' => __( 'Testimonials', 'velvet-vogue-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=vvfs_testimonial' ),
            'icon'  => 'dashicons-format-quote',
        ),
        'footer_settings' => array(
            'title' => __( 'Footer Settings', 'velvet-vogue-fashion-store-core' ),
            'url'   => admin_url( 'edit.php?post_type=vvfs_footer' ),
            'icon'  => 'dashicons-layout',
        ),
    );
    ?>
<div class="wrap vvfs-dashboard">
    <h1><?php esc_html_e( 'Velvet Vogue Fashion Store', 'velvet-vogue-fashion-store-core' ); ?></h1>
    <p class="vvfs-dashboard-description">
        <?php esc_html_e( 'Manage your store content from the sections below.', 'velvet-vogue-fashion-store-core' ); ?>
    </p>
    <div class="vvfs-dashboard-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.5rem;margin-top:1.5rem;">
        <?php foreach ( $sections as $section ) : ?>
        <div class="vvfs-dashboard-card" style="background:#fff;border:1px solid #ccc;border-radius:8px;padding:1.5rem;text-align:center;">
            <div class="dashicons <?php echo esc_attr( $section['icon'] ); ?>" style="font-size:2rem;width:auto;height:auto;margin-bottom:0.5rem;color:#f43f5e;"></div>
            <h2 style="margin:0.5rem 0;font-size:1rem;"><?php echo esc_html( $section['title'] ); ?></h2>
            <a href="<?php echo esc_url( $section['url'] ); ?>"
                class="button button-primary"><?php esc_html_e( 'Manage', 'velvet-vogue-fashion-store-core' ); ?></a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php
}

/* ======================================================
   4. Hero Slider CPT + Meta Boxes
====================================================== */

function vvfs_register_hero_slide_cpt() {
    register_post_type( 'vvfs_hero_slide', array(
        'labels' => array(
            'name'          => __( 'Hero Slides', 'velvet-vogue-fashion-store-core' ),
            'singular_name' => __( 'Hero Slide', 'velvet-vogue-fashion-store-core' ),
            'add_new_item'  => __( 'Add New Hero Slide', 'velvet-vogue-fashion-store-core' ),
            'edit_item'     => __( 'Edit Hero Slide', 'velvet-vogue-fashion-store-core' ),
        ),
        'public'        => true,
        'show_in_menu'  => 'vvfs-fashion-core',
        'menu_icon'     => 'dashicons-images-alt2',
        'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
        'show_in_rest'  => true,
        'has_archive'   => false,
        'rewrite'       => array( 'slug' => 'vvfs-hero-slide' ),
    ) );
}
add_action( 'init', 'vvfs_register_hero_slide_cpt' );

function vvfs_add_hero_slide_meta_box() {
    add_meta_box(
        'vvfs_hero_slide_meta',
        __( 'Hero Slide Settings', 'velvet-vogue-fashion-store-core' ),
        'vvfs_render_hero_slide_meta_box',
        'vvfs_hero_slide',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'vvfs_add_hero_slide_meta_box' );

function vvfs_render_hero_slide_meta_box( $post ) {
    $subtitle = get_post_meta( $post->ID, 'vvfs_subtitle', true );
    $kicker   = get_post_meta( $post->ID, 'vvfs_kicker', true );
    wp_nonce_field( 'vvfs_save_hero_slide_meta', 'vvfs_hero_slide_nonce' );
    ?>
<p>
    <label for="vvfs_kicker"><strong><?php esc_html_e( 'Kicker (e.g., "Autumn / Winter 2026")', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="text" id="vvfs_kicker" name="vvfs_kicker" value="<?php echo esc_attr( $kicker ); ?>" class="widefat">
</p>
<p>
    <label for="vvfs_subtitle"><strong><?php esc_html_e( 'Subtitle / Description', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <textarea id="vvfs_subtitle" name="vvfs_subtitle" rows="3" class="large-text"><?php echo esc_textarea( $subtitle ); ?></textarea>
</p>
<?php
}

function vvfs_save_hero_slide_meta( $post_id ) {
    if ( ! isset( $_POST['vvfs_hero_slide_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vvfs_hero_slide_nonce'] ) ), 'vvfs_save_hero_slide_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( 'vvfs_hero_slide' !== get_post_type( $post_id ) ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( isset( $_POST['vvfs_kicker'] ) ) {
        update_post_meta( $post_id, 'vvfs_kicker', sanitize_text_field( wp_unslash( $_POST['vvfs_kicker'] ) ) );
    }
    if ( isset( $_POST['vvfs_subtitle'] ) ) {
        update_post_meta( $post_id, 'vvfs_subtitle', sanitize_textarea_field( wp_unslash( $_POST['vvfs_subtitle'] ) ) );
    }
}
add_action( 'save_post_vvfs_hero_slide', 'vvfs_save_hero_slide_meta' );

/* ======================================================
   5. Testimonials CPT + Meta Boxes
====================================================== */

function vvfs_register_testimonial_cpt() {
    register_post_type( 'vvfs_testimonial', array(
        'labels' => array(
            'name'          => __( 'Testimonials', 'velvet-vogue-fashion-store-core' ),
            'singular_name' => __( 'Testimonial', 'velvet-vogue-fashion-store-core' ),
            'add_new_item'  => __( 'Add New Testimonial', 'velvet-vogue-fashion-store-core' ),
            'edit_item'     => __( 'Edit Testimonial', 'velvet-vogue-fashion-store-core' ),
        ),
        'public'          => true,
        'show_in_menu'    => 'vvfs-fashion-core',
        'menu_icon'       => 'dashicons-format-quote',
        'supports'        => array( 'title' ),
        'show_in_rest'    => true,
        'has_archive'     => false,
        'publicly_queryable' => true,
    ) );
}
add_action( 'init', 'vvfs_register_testimonial_cpt' );

function vvfs_add_testimonial_meta_boxes() {
    add_meta_box(
        'vvfs_testimonial_quote',
        __( 'Quote', 'velvet-vogue-fashion-store-core' ),
        'vvfs_testimonial_quote_callback',
        'vvfs_testimonial',
        'normal',
        'high'
    );
    add_meta_box(
        'vvfs_testimonial_details',
        __( 'Details', 'velvet-vogue-fashion-store-core' ),
        'vvfs_testimonial_details_callback',
        'vvfs_testimonial',
        'side',
        'default'
    );
}
add_action( 'add_meta_boxes', 'vvfs_add_testimonial_meta_boxes' );

function vvfs_testimonial_quote_callback( $post ) {
    wp_nonce_field( 'vvfs_testimonial_meta', 'vvfs_testimonial_nonce' );
    $quote = get_post_meta( $post->ID, 'vvfs_testimonial_quote', true );
    echo '<textarea name="vvfs_testimonial_quote" rows="4" class="large-text">' . esc_textarea( $quote ) . '</textarea>';
}

function vvfs_testimonial_details_callback( $post ) {
    $rating = get_post_meta( $post->ID, 'vvfs_testimonial_rating', true );
    $role   = get_post_meta( $post->ID, 'vvfs_testimonial_role', true );
    $avatar = get_post_meta( $post->ID, 'vvfs_testimonial_avatar', true );
    ?>
<p>
    <label for="vvfs_testimonial_role"><strong><?php esc_html_e( 'Role / Title', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="text" name="vvfs_testimonial_role" id="vvfs_testimonial_role" value="<?php echo esc_attr( $role ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'e.g., Fashion Enthusiast', 'velvet-vogue-fashion-store-core' ); ?>">
</p>
<p>
    <label for="vvfs_testimonial_rating"><strong><?php esc_html_e( 'Rating (1-5)', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <select name="vvfs_testimonial_rating" id="vvfs_testimonial_rating" class="widefat">
        <?php for ( $i = 5; $i >= 1; $i-- ) : ?>
        <option value="<?php echo esc_attr( $i ); ?>" <?php selected( $rating, $i ); ?>><?php echo esc_html( $i ); ?> <?php esc_html_e( 'Star' , 'velvet-vogue-fashion-store-core' ); ?><?php echo $i > 1 ? 's' : ''; ?></option>
        <?php endfor; ?>
    </select>
</p>
<p>
    <label for="vvfs_testimonial_avatar"><strong><?php esc_html_e( 'Avatar URL', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="vvfs_testimonial_avatar" id="vvfs_testimonial_avatar" value="<?php echo esc_url( $avatar ); ?>" class="widefat" placeholder="<?php esc_attr_e( 'https://...', 'velvet-vogue-fashion-store-core' ); ?>">
</p>
<?php
}

function vvfs_save_testimonial_meta( $post_id ) {
    if ( ! isset( $_POST['vvfs_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vvfs_testimonial_nonce'] ) ), 'vvfs_testimonial_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'vvfs_testimonial' ) {
        return;
    }
    if ( isset( $_POST['vvfs_testimonial_quote'] ) ) {
        update_post_meta( $post_id, 'vvfs_testimonial_quote', sanitize_textarea_field( wp_unslash( $_POST['vvfs_testimonial_quote'] ) ) );
    }
    if ( isset( $_POST['vvfs_testimonial_role'] ) ) {
        update_post_meta( $post_id, 'vvfs_testimonial_role', sanitize_text_field( wp_unslash( $_POST['vvfs_testimonial_role'] ) ) );
    }
    if ( isset( $_POST['vvfs_testimonial_rating'] ) ) {
        update_post_meta( $post_id, 'vvfs_testimonial_rating', intval( $_POST['vvfs_testimonial_rating'] ) );
    }
    if ( isset( $_POST['vvfs_testimonial_avatar'] ) ) {
        update_post_meta( $post_id, 'vvfs_testimonial_avatar', esc_url_raw( wp_unslash( $_POST['vvfs_testimonial_avatar'] ) ) );
    }
}
add_action( 'save_post_vvfs_testimonial', 'vvfs_save_testimonial_meta' );

/* ======================================================
   6. Footer Settings (Single Instance) + Meta Boxes
====================================================== */

function vvfs_register_footer_settings() {
    register_post_type( 'vvfs_footer', array(
        'labels' => array(
            'name'          => __( 'Footer Settings', 'velvet-vogue-fashion-store-core' ),
            'singular_name' => __( 'Footer Settings', 'velvet-vogue-fashion-store-core' ),
            'add_new_item'  => __( 'Edit Footer Settings', 'velvet-vogue-fashion-store-core' ),
            'edit_item'     => __( 'Edit Footer Settings', 'velvet-vogue-fashion-store-core' ),
        ),
        'public'           => false,
        'show_ui'          => true,
        'show_in_menu'     => 'vvfs-fashion-core',
        'menu_icon'        => 'dashicons-layout',
        'supports'         => array( 'title' ),
        'show_in_rest'     => true,
        'capability_type'  => 'post',
        'map_meta_cap'     => true,
    ) );
}
add_action( 'init', 'vvfs_register_footer_settings' );

function vvfs_limit_footer_settings() {
    global $pagenow;
    if ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'vvfs_footer' ) {
        $existing = get_posts( array(
            'post_type'      => 'vvfs_footer',
            'posts_per_page' => 1,
            'post_status'    => 'publish',
            'fields'         => 'ids',
        ) );
        if ( ! empty( $existing ) ) {
            $post_id = $existing[0];
            if ( current_user_can( 'edit_post', $post_id ) ) {
                wp_redirect( admin_url( 'post.php?post=' . $post_id . '&action=edit' ) );
                exit;
            }
        }
    }
}
add_action( 'admin_init', 'vvfs_limit_footer_settings' );

function vvfs_add_footer_meta_boxes() {
    add_meta_box( 'vvfs_footer_logo', __( 'Logo & Tagline', 'velvet-vogue-fashion-store-core' ), 'vvfs_footer_logo_callback', 'vvfs_footer', 'normal', 'high' );
    add_meta_box( 'vvfs_footer_social', __( 'Social Media URLs', 'velvet-vogue-fashion-store-core' ), 'vvfs_footer_social_callback', 'vvfs_footer', 'normal', 'high' );
    add_meta_box( 'vvfs_footer_quick_links', __( 'Quick Links', 'velvet-vogue-fashion-store-core' ), 'vvfs_footer_links_callback', 'vvfs_footer', 'normal', 'high' );
    add_meta_box( 'vvfs_footer_contact', __( 'Contact Information', 'velvet-vogue-fashion-store-core' ), 'vvfs_footer_contact_callback', 'vvfs_footer', 'normal', 'high' );
    add_meta_box( 'vvfs_footer_copyright', __( 'Copyright Text', 'velvet-vogue-fashion-store-core' ), 'vvfs_footer_copyright_callback', 'vvfs_footer', 'side', 'default' );
}
add_action( 'add_meta_boxes', 'vvfs_add_footer_meta_boxes' );

function vvfs_footer_logo_callback( $post ) {
    wp_nonce_field( 'vvfs_footer_meta', 'vvfs_footer_nonce' );
    $tagline = get_post_meta( $post->ID, 'vvfs_footer_tagline', true );
    ?>
<p>
    <label for="vvfs_footer_tagline"><strong><?php esc_html_e( 'Tagline / Description', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <textarea name="vvfs_footer_tagline" id="vvfs_footer_tagline" rows="3" class="large-text"><?php echo esc_textarea( $tagline ); ?></textarea>
</p>
<?php
}

function vvfs_footer_social_callback( $post ) {
    $social = get_post_meta( $post->ID, 'vvfs_footer_social', true );
    if ( ! is_array( $social ) ) {
        $social = array();
    }
    ?>
<p>
    <label for="vvfs_footer_social_instagram"><strong><?php esc_html_e( 'Instagram URL', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="vvfs_footer_social[instagram]" id="vvfs_footer_social_instagram" value="<?php echo esc_url( $social['instagram'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label for="vvfs_footer_social_pinterest"><strong><?php esc_html_e( 'Pinterest URL', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="vvfs_footer_social[pinterest]" id="vvfs_footer_social_pinterest" value="<?php echo esc_url( $social['pinterest'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label for="vvfs_footer_social_youtube"><strong><?php esc_html_e( 'YouTube URL', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="vvfs_footer_social[youtube]" id="vvfs_footer_social_youtube" value="<?php echo esc_url( $social['youtube'] ?? '' ); ?>" class="widefat">
</p>
<p>
    <label for="vvfs_footer_social_tiktok"><strong><?php esc_html_e( 'TikTok URL', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="url" name="vvfs_footer_social[tiktok]" id="vvfs_footer_social_tiktok" value="<?php echo esc_url( $social['tiktok'] ?? '' ); ?>" class="widefat">
</p>
<?php
}

function vvfs_footer_links_callback( $post ) {
    $links = get_post_meta( $post->ID, 'vvfs_footer_links', true );
    if ( ! is_array( $links ) ) {
        $links = array();
    }
    ?>
<div id="vvfs-footer-links-repeater">
    <?php foreach ( $links as $index => $link ) : ?>
    <div class="vvfs-footer-link-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">
        <input type="text" name="vvfs_footer_links[<?php echo esc_attr( $index ); ?>][text]" value="<?php echo esc_attr( $link['text'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'Link text', 'velvet-vogue-fashion-store-core' ); ?>" style="flex:1;">
        <input type="url" name="vvfs_footer_links[<?php echo esc_attr( $index ); ?>][url]" value="<?php echo esc_url( $link['url'] ?? '' ); ?>" placeholder="<?php esc_attr_e( 'URL', 'velvet-vogue-fashion-store-core' ); ?>" style="flex:1;">
        <button type="button" class="button vvfs-remove-link"><?php esc_html_e( 'Remove', 'velvet-vogue-fashion-store-core' ); ?></button>
    </div>
    <?php endforeach; ?>
</div>
<button type="button" id="vvfs-add-footer-link" class="button"><?php esc_html_e( 'Add Link', 'velvet-vogue-fashion-store-core' ); ?></button>
<script>
jQuery(function($){
    var idx = <?php echo count( $links ); ?>;
    $('#vvfs-add-footer-link').on('click', function(){
        var row = '<div class="vvfs-footer-link-row" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;align-items:center;">' +
            '<input type="text" name="vvfs_footer_links['+idx+'][text]" placeholder="Link text" style="flex:1;">' +
            '<input type="url" name="vvfs_footer_links['+idx+'][url]" placeholder="URL" style="flex:1;">' +
            '<button type="button" class="button vvfs-remove-link">Remove</button></div>';
        $('#vvfs-footer-links-repeater').append(row);
        idx++;
    });
    $(document).on('click', '.vvfs-remove-link', function(){
        $(this).closest('.vvfs-footer-link-row').remove();
    });
});
</script>
<?php
}

function vvfs_footer_contact_callback( $post ) {
    $address = get_post_meta( $post->ID, 'vvfs_footer_address', true );
    $phone   = get_post_meta( $post->ID, 'vvfs_footer_phone', true );
    $email   = get_post_meta( $post->ID, 'vvfs_footer_email', true );
    ?>
<p>
    <label for="vvfs_footer_address"><strong><?php esc_html_e( 'Address', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <textarea name="vvfs_footer_address" id="vvfs_footer_address" rows="3" class="large-text"><?php echo esc_textarea( $address ); ?></textarea>
</p>
<p>
    <label for="vvfs_footer_phone"><strong><?php esc_html_e( 'Phone', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="tel" name="vvfs_footer_phone" id="vvfs_footer_phone" value="<?php echo esc_attr( $phone ); ?>" class="widefat">
</p>
<p>
    <label for="vvfs_footer_email"><strong><?php esc_html_e( 'Email', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="email" name="vvfs_footer_email" id="vvfs_footer_email" value="<?php echo esc_attr( $email ); ?>" class="widefat">
</p>
<?php
}

function vvfs_footer_copyright_callback( $post ) {
    $copyright = get_post_meta( $post->ID, 'vvfs_footer_copyright', true );
    ?>
<p>
    <label for="vvfs_footer_copyright"><strong><?php esc_html_e( 'Copyright text', 'velvet-vogue-fashion-store-core' ); ?></strong></label><br>
    <input type="text" name="vvfs_footer_copyright" id="vvfs_footer_copyright" value="<?php echo esc_attr( $copyright ); ?>" class="widefat">
</p>
<?php
}

function vvfs_save_footer_meta( $post_id ) {
    if ( ! isset( $_POST['vvfs_footer_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['vvfs_footer_nonce'] ) ), 'vvfs_footer_meta' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( get_post_type( $post_id ) !== 'vvfs_footer' ) {
        return;
    }

    if ( isset( $_POST['vvfs_footer_tagline'] ) ) {
        update_post_meta( $post_id, 'vvfs_footer_tagline', sanitize_textarea_field( wp_unslash( $_POST['vvfs_footer_tagline'] ) ) );
    }

    if ( isset( $_POST['vvfs_footer_social'] ) && is_array( $_POST['vvfs_footer_social'] ) ) {
        $social = array_map( 'esc_url_raw', wp_unslash( $_POST['vvfs_footer_social'] ) );
        update_post_meta( $post_id, 'vvfs_footer_social', $social );
    }

    if ( isset( $_POST['vvfs_footer_links'] ) && is_array( $_POST['vvfs_footer_links'] ) ) {
        $links = array();
        foreach ( $_POST['vvfs_footer_links'] as $link ) {
            $text = sanitize_text_field( wp_unslash( $link['text'] ?? '' ) );
            $url  = esc_url_raw( wp_unslash( $link['url'] ?? '' ) );
            if ( $text && $url ) {
                $links[] = array( 'text' => $text, 'url' => $url );
            }
        }
        update_post_meta( $post_id, 'vvfs_footer_links', $links );
    }

    if ( isset( $_POST['vvfs_footer_address'] ) ) {
        update_post_meta( $post_id, 'vvfs_footer_address', sanitize_textarea_field( wp_unslash( $_POST['vvfs_footer_address'] ) ) );
    }
    if ( isset( $_POST['vvfs_footer_phone'] ) ) {
        update_post_meta( $post_id, 'vvfs_footer_phone', sanitize_text_field( wp_unslash( $_POST['vvfs_footer_phone'] ) ) );
    }
    if ( isset( $_POST['vvfs_footer_email'] ) ) {
        update_post_meta( $post_id, 'vvfs_footer_email', sanitize_email( wp_unslash( $_POST['vvfs_footer_email'] ) ) );
    }
    if ( isset( $_POST['vvfs_footer_copyright'] ) ) {
        update_post_meta( $post_id, 'vvfs_footer_copyright', sanitize_text_field( wp_unslash( $_POST['vvfs_footer_copyright'] ) ) );
    }
}
add_action( 'save_post_vvfs_footer', 'vvfs_save_footer_meta' );
