# Obydullah Fitness Content Manager

![PHP](https://img.shields.io/badge/PHP-8.5-777BB4?logo=php&logoColor=white)
![WordPress](https://img.shields.io/badge/WordPress-7.0-21759B?logo=wordpress&logoColor=white)
![License](https://img.shields.io/badge/License-GPLv2-or-blue)
![Version](https://img.shields.io/badge/Version-1.0.0-informational)
![WordPress.org](https://img.shields.io/badge/WordPress.org-Plugin-21759B?logo=wordpress&logoColor=white)

[View on WordPress.org](https://wordpress.org/plugins/obydullah-fitness-content-manager/)

A single-file procedural WordPress plugin providing custom post types, meta boxes, an admin dashboard, AJAX booking handler, and site-wide settings for the Fitness Theme.

## Features

- 4 custom post types: Hero Slides, Services, Testimonials, Pricing Plans
- Meta boxes with nonce verification and field-level sanitization
- Custom admin dashboard with card grid navigation
- Bookings stored in a custom `{prefix}ofcm_bookings` table, with an admin list for search, status filters, and updates
- AJAX booking form handler with nonce validation
- Front-end booking form via the `[ofcm_booking_form]` shortcode, progressive-enhancement AJAX submission
- Site settings (Contact, Social Links, About, Stats & Rating) stored in `wp_options`
- Admin-only CPTs — content is not publicly queryable
- `show_in_rest` enabled on content CPTs for Gutenberg compatibility

## Requirements

- WordPress 6.2+ (for the `%i` identifier placeholder in `$wpdb->prepare()`)
- PHP 8.0+

## Installation

### From WordPress.org (Recommended)

1. Go to **Plugins → Add New** in your WordPress admin.
2. Search for `Obydullah Fitness Content Manager`.
3. Click **Install Now** and **Activate**.

[View on WordPress.org](https://wordpress.org/plugins/obydullah-fitness-content-manager/)

### Manual Installation

1. Download the plugin from [WordPress.org](https://wordpress.org/plugins/obydullah-fitness-content-manager/).
2. Upload the plugin folder to `/wp-content/plugins/`.
3. Activate the plugin through the **Plugins** menu in WordPress.
4. Configure settings under the **Obydullah Fitness Content Manager** menu.

## Usage

### Custom Post Types

| CPT Slug              | Label         | Purpose                | Meta Fields                                                   |
| --------------------- | ------------- | ---------------------- | ------------------------------------------------------------- |
| `ofcm_hero_slide`     | Hero Slides   | Homepage hero carousel | subtitle, description, btn_text, btn_url, btn2_text, btn2_url |
| `ofcm_service`        | Services      | Service offerings grid | icon (emoji)                                                  |
| `ofcm_testimonial`    | Testimonials  | Client reviews         | quote, role, rating (1-5), result                             |
| `ofcm_pricing`        | Pricing Plans | Pricing cards          | price, period, features, popular, btn_text                    |

All meta keys use the `ofcm_` prefix to avoid conflicts. CPTs register under the **Obydullah Fitness Content Manager** top-level menu for automatic sub-navigation.

Bookings are not a CPT — they live in a custom table:

| Table                                | Purpose            | Columns                                                          |
| ------------------------------------ | ------------------ | ---------------------------------------------------------------- |
| `{prefix}ofcm_bookings`           | Form submissions | id, name, email, mobile, goal, message, status, created_at   |

The table is created with `dbDelta()` on plugin activation.

### Booking Form

Drop the shortcode on any page or post:

```
[ofcm_booking_form title="Book Your Session"]
```

| Attribute | Default       | Purpose                    |
| --------- | ------------- | -------------------------- |
| `title`   | *(none)*      | Optional heading above the form |
| `button`  | `Book a Session` | Submit button label    |

The submission runs via `fetch()` to `admin-ajax.php`:

1. `assets/js/ofcm-booking-form.js` intercepts the submit of every `form.ofcm-booking-form` and posts a `FormData` of the form — the hidden `action` and `nonce` fields are part of it
2. `wp_ajax_ofcm_submit_booking` / `wp_ajax_nopriv_ofcm_submit_booking` hooks fire
3. The handler sanitizes the `nonce` field and calls `wp_verify_nonce()` against the `ofcm_booking_nonce` action
4. Validates required fields (name, email)
5. `ofcm_insert_booking()` writes a row via `$wpdb->insert()`
6. Returns a JSON success/error response as a plain string in `data`

The script writes that response into `.ofcm-booking-form__status` with `textContent` (never `innerHTML`), resets the form on success and keeps the entered values on failure, and disables the button while a request is in flight.

The nonce is generated via `wp_create_nonce( 'ofcm_booking_nonce' )` and passed to the script with `wp_localize_script()` as `window.ofcmBooking`, alongside the AJAX URL and the i18n strings the script needs.

The script is registered on `init` and enqueued by the shortcode, so a page without the form loads nothing. Themes that render their own markup need two things: the `ofcm-booking-form` class on the form, and either the shortcode's fields or `wp_enqueue_script( 'ofcm-booking-form' )` after the form is rendered.

### Front-end Styling Hooks

The shortcode emits no stylesheet — the theme styles it. The class names it relies on:

| Class                              | Element                          |
| ---------------------------------- | -------------------------------- |
| `ofcm-booking`                     | Shortcode wrapper                |
| `ofcm-booking-form`                | The `<form>` (what the JS binds to) |
| `ofcm-booking-form__title`         | Optional heading                 |
| `ofcm-booking-form__status`        | Result region, gains `is-success` or `is-error` |
| `ofcm-booking-form__noscript`      | `<noscript>` fallback            |

### Managing Bookings

Bookings are listed at **Obydullah Fitness Content Manager → Bookings** (`admin.php?page=ofcm-bookings`). The list supports search by name/email, per-status filters with counts, pagination, inline status updates, and deletion. Status changes post to `admin-post.php` with the `ofcm_booking_action` action and are protected by a `manage_options` capability check plus nonce verification.

### Data Access

Writes go through `$wpdb->insert()` and `$wpdb->update()`, which build their own SQL — no `prepare()` needed. Reads always use `prepare()`, with the table name passed through the `%i` identifier placeholder instead of being interpolated:

```php
// Write
$wpdb->insert( ofcm_bookings_table(), [ 'name' => $name, ... ], [ '%s', ... ] );
$wpdb->update( ofcm_bookings_table(), [ 'status' => $status ], [ 'id' => $id ], [ '%s' ], [ '%d' ] );

// Read
$wpdb->get_results( $wpdb->prepare( 'SELECT id, name, email FROM %i WHERE id = %d', $table, $id ) );
```

`ofcm_get_bookings()` keeps each filter combination as its own literal statement so every `prepare()` call receives a literal format string — dynamic `WHERE` fragments built in a variable are rejected by WPCS. An empty search becomes `'%%'`, which matches every row, so only the status filter changes the query shape.

`ofcm_update_booking()` is the only write path for edits. It only accepts columns listed in `ofcm_booking_editable_fields()` and sanitizes each one, so `id` and `created_at` can never be overwritten and `status` is rejected unless it matches a known value.

### Site Settings

Settings are stored in `wp_options` via `update_option()` / `get_option()`, so they persist across theme switches.

Available option keys:

- Contact: `ofcm_contact_email`, `ofcm_contact_phone`, `ofcm_contact_location`
- Social: `ofcm_social_instagram`, `ofcm_social_youtube`, `ofcm_social_tiktok`, `ofcm_social_linkedin`
- About: `ofcm_about_text1`, `ofcm_about_text2`, `ofcm_about_coach_name`, `ofcm_about_coach_title`, `ofcm_about_certs`, `ofcm_about_specialties`
- Stats: `ofcm_about_stat{1..3}_number`, `ofcm_about_stat{1..3}_label`, `ofcm_about_rating`, `ofcm_about_rating_source`

## Security

- All form submissions verified with WordPress nonces
- `current_user_can('edit_post', $post_id)` check on every meta save
- `DOING_AUTOSAVE` check prevents meta overwrite during autosave
- AJAX handler verifies the nonce manually and returns a JSON error on failure
- Booking admin actions require `manage_options` plus nonce verification
- All `$wpdb` reads use `$wpdb->prepare()` with `%d`/`%s` placeholders and `%i` for the table name — nothing is interpolated
- Writes use `$wpdb->insert()` / `update()` / `delete()` with explicit format lists — no raw SQL
- Booking edits go through an allowlist of editable columns, so `id` and `created_at` cannot be overwritten
- All output escaped with `esc_html()`, `esc_attr()`, `esc_url()`
- All input sanitized before database storage

## File Structure

```
obydullah-fitness-content-manager/
├── obydullah-fitness-content-manager.php # All plugin logic (single file)
├── assets/
│   ├── css/
│   │   └── ofcm-admin.css # Dashboard card grid + status badges
│   └── js/
│       └── ofcm-booking-form.js # AJAX submission for the front-end form
├── docs/
│   ├── case-study.html
│   └── README.md
└── readme.txt
```

## Changelog

### 1.0.0

- Initial release
- 4 CPTs: Hero Slides, Services, Testimonials, Pricing
- Bookings stored in a custom `{prefix}ofcm_bookings` table
- Admin dashboard with card grid
- Bookings admin list with search, status filters, and pagination
- AJAX booking form handler
- Front-end booking form via the `[ofcm_booking_form]` shortcode
- Site settings (Contact + About)
- Meta boxes with nonce verification and sanitization

## License

GPL v2 or later
