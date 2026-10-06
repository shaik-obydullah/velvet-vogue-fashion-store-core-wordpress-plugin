=== Obydullah Fashion Store Core ===
Contributors: obydullah
Tags: fashion store, ecommerce, custom post types, content management, slider
Text Domain: obydullah-fashion-store-core
Requires at least: 6.2
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Manage hero sliders, testimonials, and footer settings from the WordPress admin. Works with any theme.

== Description ==

Obydullah Fashion Store Core is a custom WordPress plugin that powers the backend content management for a fashion e-commerce storefront. It registers dedicated Custom Post Types (CPTs) and meta boxes, allowing store administrators to manage homepage hero slides, customer testimonials, and global footer settings from the WordPress admin dashboard without writing any code.

The plugin is theme-agnostic. It handles content storage and administration only; your theme renders the front-end output.

= Features =

* **Hero Slider Management** &mdash; Create and reorder hero slides with custom kicker text (e.g. "Autumn / Winter 2026") and subtitles. Supports featured images and menu-order sorting.
* **Testimonials** &mdash; Manage customer testimonials with quote text, star ratings (1&ndash;5), role/title, and avatar URL.
* **Footer Settings (Singleton)** &mdash; A single-instance screen for the site-wide footer: logo tagline, social media URLs (Instagram, Pinterest, YouTube, TikTok), quick links with an add/remove repeater, contact information (address, phone, email), and copyright text.
* **Admin Dashboard** &mdash; A centralized page under the **Fashion Store Core** menu linking to each content section.
* **WordPress REST API** &mdash; All three CPTs are REST-enabled for headless front ends or block-editor integration.

= Theme integration =

The plugin registers the following post types and meta keys for your templates.

**Post types**

* `ofsc_hero_slide` &mdash; public, REST-enabled, supports title / thumbnail / page-attributes
* `ofsc_testimonial` &mdash; public, REST-enabled, supports title
* `ofsc_footer` &mdash; admin-only UI, REST-enabled, supports title

**Meta keys**

* Hero slide: `ofsc_kicker`, `ofsc_subtitle`
* Testimonial: `ofsc_testimonial_quote`, `ofsc_testimonial_role`, `ofsc_testimonial_rating`, `ofsc_testimonial_avatar`
* Footer: `ofsc_footer_tagline`, `ofsc_footer_social` (associative array), `ofsc_footer_links` (array of `text` / `url` pairs), `ofsc_footer_address`, `ofsc_footer_phone`, `ofsc_footer_email`, `ofsc_footer_copyright`

Example &mdash; render a testimonial carousel:

```
$testimonials = get_posts( array(
    'post_type'      => 'ofsc_testimonial',
    'posts_per_page' => 10,
) );

foreach ( $testimonials as $testimonial ) {
    printf(
        '<blockquote>%s</blockquote>',
        esc_html( get_post_meta( $testimonial->ID, 'ofsc_testimonial_quote', true ) )
    );
}
```

Example &mdash; render the footer quick links:

```
$links = get_post_meta( $footer_id, 'ofsc_footer_links', true );

if ( is_array( $links ) ) {
    foreach ( $links as $link ) {
        printf(
            '<a href="%s">%s</a>',
            esc_url( $link['url'] ),
            esc_html( $link['text'] )
        );
    }
}
```

= Security =

Every save handler verifies a post-specific nonce, rejects autosaves and revisions, confirms the post type, and checks `current_user_can( 'edit_post', $post_id )` before writing. All input is unslashed and sanitized on first touch, and all output is escaped.

== Installation ==

1. Upload the `obydullah-fashion-store-core` folder to `/wp-content/plugins/`, or install the plugin through the **Plugins** screen in WordPress.
2. Activate the plugin through the **Plugins** screen. On activation, sample draft content is created so each section is ready to edit, and the rewrite rules for the hero slide permalink are written immediately.
3. Navigate to **Fashion Store Core** in the admin sidebar.

Requires PHP 8.0+ and WordPress 6.2+. Older environments are reported with an admin notice instead of a fatal error.

== Frequently Asked Questions ==

= Can I add more than one footer? =

No. The Footer Settings post type is enforced as a singleton. Attempting to create a second entry redirects you to the existing one, so there is always exactly one global footer configuration.

= Are the post types compatible with the Block Editor? =

Yes. All three CPTs have `show_in_rest` enabled, so they load and save correctly in the Gutenberg block editor.

= Do I need a specific theme? =

No. The CPTs and meta boxes work with any theme, though you will need template code of your own to display the front-end output. See the theme integration section above.

= Does the plugin delete my content when I deactivate or uninstall it? =

No. Both deactivating and uninstalling the plugin leave all hero slides, testimonials, and footer settings intact in the database. The plugin ships no cleanup routine, so the content deliberately outlives it. If you want the data gone, delete the three post types (`ofsc_hero_slide`, `ofsc_testimonial`, `ofsc_footer`) and their meta keys manually.

= How do I reorder the hero slides? =

Each hero slide supports page attributes, so set its **Order** field in the sidebar panel. Slides are queried in menu-order sequence.

== Screenshots ==

1. The Fashion Store Core admin dashboard with navigation cards for each section.
2. The Hero Slides list screen and the hero slide settings meta box.
3. The Testimonials editor with the quote meta box and the details sidebar box.
4. The Footer Settings editor with logo/tagline, social media, quick links repeater, contact, and copyright meta boxes.

== Changelog ==

= 1.0.1 =
* Fixed: activation now registers the post types before flushing rewrite rules, so the hero slide permalink works without a manual **Permalinks &rarr; Save Changes**.
* Fixed: activation no longer adds another sample row on every re-activation.
* Fixed: a failed sample insert is reported in the admin instead of passing silently.
* Added a runtime check for the minimum WordPress version, matching the header requirement.
* Hardened the save handlers against array input, and they now reject a forged nonce with a 403 instead of dropping the save silently.
* Ratings are stored as an integer within the 1&ndash;5 range.

= 1.0.0 =
* Initial release.
* Hero Slide CPT with kicker and subtitle meta fields.
* Testimonial CPT with quote, rating, role, and avatar meta fields.
* Footer Settings singleton CPT with logo/tagline, social links, quick links repeater, contact info, and copyright meta fields.
* Admin dashboard page with navigation cards.
* Nonce, capability, and sanitization guards on all save handlers.

== Upgrade Notice ==

= 1.0.1 =
Writes the hero slide rewrite rules during activation, so you no longer have to save the permalinks by hand. Save handlers now reject a forged or expired nonce with a 403 instead of silently ignoring it, so reload an editor tab that was open before the update once.

= 1.0.0 =
Initial release.
