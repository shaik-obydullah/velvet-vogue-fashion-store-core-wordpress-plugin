# Obydullah Fashion Store Core

[![WordPress](https://img.shields.io/badge/WordPress-6.2%2B-blue?logo=wordpress&logoColor=white)](https://wordpress.org/) [![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/) [![License](https://img.shields.io/badge/License-GPLv2%2F3-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html) [![Version](https://img.shields.io/badge/version-1.0.1-orange.svg)](https://wordpress.org/plugins/obydullah-fashion-store-core/) [![WordPress Plugin](https://img.shields.io/badge/WordPress%20Plugin-Download-brightgreen?logo=wordpress&logoColor=white)](https://wordpress.org/plugins/obydullah-fashion-store-core/)

A WordPress plugin that provides core content management functionality for the Obydullah Fashion Store theme, including hero sliders, testimonials, and footer settings.

## Description

Obydullah Fashion Store Core is a custom WordPress plugin built to power the backend content management for a fashion e-commerce storefront. It registers dedicated Custom Post Types (CPTs) and meta boxes, allowing store administrators to manage homepage hero slides, customer testimonials, and global footer settings from the WordPress admin dashboard without writing any code.

### Features

- **Hero Slider Management** -- Create and reorder hero slides with custom kicker text (e.g., "Autumn / Winter 2026") and subtitles. Supports featured images and page attribute ordering.
- **Testimonials** -- Manage customer testimonials with quote text, star ratings (1--5), role/title, and avatar URL fields.
- **Footer Settings (Singleton)** -- A single-instance CPT for managing the site-wide footer: logo tagline, social media links (Instagram, Pinterest, YouTube, TikTok), quick links (dynamic repeater), contact information (address, phone, email), and copyright text.
- **Admin Dashboard** -- A centralized dashboard page under the "Fashion Store Core" menu for quick navigation to each content section.
- **WordPress REST API** -- All CPTs are REST-enabled for headless or block-editor integration.

## Installation

1. Upload the `obydullah-fashion-store-core` folder to `/wp-content/plugins/`.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Navigate to **Fashion Store Core** in the admin sidebar to manage content.

## Usage

### Hero Slides

1. Go to **Fashion Store Core > Hero Slides**.
2. Click **Add New Hero Slide**.
3. Set the **Kicker** (season/collection label), **Subtitle** (description text), featured image, and menu order.
4. Save and repeat for additional slides.

### Testimonials

1. Go to **Fashion Store Core > Testimonials**.
2. Click **Add New Testimonial**.
3. Enter the customer's name as the title, write the **Quote**, and set the **Role**, **Rating**, and **Avatar URL** in the sidebar meta boxes.
4. Save.

### Footer Settings

1. Go to **Fashion Store Core > Footer Settings**.
2. There is only one footer entry. If it already exists, you will be redirected to edit it.
3. Fill in the tagline, social media URLs, quick links (use the "Add Link" button for repeater rows), contact details, and copyright text.
4. Save.

## Frequently Asked Questions

### Can I add more than one footer?

No. The Footer Settings CPT is enforced as a singleton. Attempting to create a new one redirects to the existing entry.

### Are the CPTs compatible with the Block Editor?

Yes. All three CPTs have `show_in_rest` enabled, so they work with the Gutenberg block editor.

### Do I need a specific theme?

This plugin is designed to work with the Obydullah Fashion Store theme, but the CPTs and meta boxes are functional with any theme. Theme template files are required to display the front-end output.

## Changelog

### 1.0.1

- Activation registers the post types before flushing rewrite rules, so hero slide permalinks work without a manual permalink save.
- Activation seeds sample content only when the site has none in any status, so re-activating no longer duplicates the sample rows.
- A failed sample insert is reported in the admin instead of passing silently.
- Added a runtime check for the minimum WordPress version (6.2) next to the PHP 8.0 gate.
- Save handlers reject non-string input, guard revisions, use post-specific nonce actions, and clamp the testimonial rating to 1-5.

### 1.0.0

- Initial release.
- Hero Slide CPT with kicker and subtitle meta fields.
- Testimonial CPT with quote, rating, role, and avatar meta fields.
- Footer Settings singleton CPT with logo/tagline, social links, quick links repeater, contact info, and copyright meta fields.
- Admin dashboard page with navigation cards.

## Author

**Shaik Obydullah**
[https://obydullah.com](https://obydullah.com)

## License

Licensed under the [GNU General Public License v2.0](https://www.gnu.org/licenses/gpl-2.0.html).
