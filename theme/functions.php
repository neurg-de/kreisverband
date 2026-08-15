<?php
/**
 * Neurg Kreisverband Theme
 *
 * Inspired by "Joseph knows best" by Benjamin Jopen (kre8tiv.de)
 * and the work of Andreas Gregor (andreasgregor.de).
 * Full rewrite by Severin Kistner.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GK_VERSION', '0.6.1' );
define( 'GK_DIR', get_template_directory() );
define( 'GK_URI', get_template_directory_uri() );
define( 'GK_IMAGE_DIR', GK_URI . '/lib/images/' );
define( 'GK_SCRIPT_DIR', GK_URI . '/lib/js/' );

// For backwards compatibility with original theme templates
define( 'IMAGE_DIR', GK_IMAGE_DIR );
define( 'SCRIPT_DIR', GK_SCRIPT_DIR );

if ( ! isset( $content_width ) ) {
    $content_width = 783;
}

// Core theme setup: menus, theme support, scripts, sidebars
require_once GK_DIR . '/inc/theme-setup.php';

// Custom post types: person; taxonomies: abteilung, gk_zuordnung
require_once GK_DIR . '/inc/post-types.php';

// Meta boxes for person & page data
require_once GK_DIR . '/inc/meta-boxes.php';

// Per-abteilung meta: position, function, hidden per person×abteilung
require_once GK_DIR . '/inc/abteilung-meta.php';

// Shortcodes for displaying persons, OV lists, UI elements
require_once GK_DIR . '/inc/shortcodes.php';

// Ortsverband system: OV configuration, template routing, navigation
require_once GK_DIR . '/inc/ortsverband.php';

// Custom roles: Kreisadmin, OV-Admin, OV-Autor
require_once GK_DIR . '/inc/roles.php';

// Admin settings page for OV configuration
require_once GK_DIR . '/inc/settings.php';

// Events system: event CPT, scheduling, iCal, OV integration
require_once GK_DIR . '/inc/events.php';

// Setup wizard: first-run config, Impressum, Datenschutz, KV details
require_once GK_DIR . '/inc/setup-wizard.php';

// Built-in SEO: Open Graph, meta descriptions, JSON-LD, canonical
require_once GK_DIR . '/inc/seo.php';

// Donation system: Twingle integration, bank details, shortcodes
require_once GK_DIR . '/inc/donation.php';

// Contact form: built-in form with spam protection
require_once GK_DIR . '/inc/contact-form.php';

// Cookie consent banner (DSGVO/GDPR)
require_once GK_DIR . '/inc/cookie-consent.php';

// Social links: platform registry, URL normalizer, unified bar component
require_once GK_DIR . '/inc/social-links.php';

// Social share buttons
require_once GK_DIR . '/inc/social-share.php';

// Gutenberg blocks: Abteilung person grid
require_once GK_DIR . '/inc/blocks.php';

// Admin customizations: dashboard, editor styles, security
require_once GK_DIR . '/inc/admin.php';

// Widgets
require_once GK_DIR . '/inc/widgets.php';

// The Taurus integration: compliance badge, Customizer settings, sponsor credit
require_once GK_DIR . '/inc/taurus.php';

// Kreiskarte: interactive SVG map of the Landkreis
require_once GK_DIR . '/inc/kreiskarte.php';

// Kreiskarte Generator: admin tool to build the SVG map from OpenStreetMap
require_once GK_DIR . '/inc/kreiskarte-generator.php';

// Dev only: seed demo content for local testing
if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
    require_once GK_DIR . '/inc/dev-seed-data.php';
}
