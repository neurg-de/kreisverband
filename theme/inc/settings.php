<?php
/**
 * Theme Settings: registration, sanitization, and option helpers
 *
 * Homepage variant discovery and configuration. The settings page UI
 * is rendered in admin.php (gk_settings_main_page).
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Homepage Variant Discovery ──────────────────────────────────────────────

/**
 * Discover available homepage variants from template-parts/home/*.php.
 *
 * Each variant file must have these headers in a docblock:
 *   Variant:       slug (e.g. andreas-gregor)
 *   Variant Name:  Human label
 *   Variant Desc:  Short description
 *   Variant Thumb: Relative path to screenshot (optional)
 *
 * @return array Keyed by variant slug => [ name, desc, thumb, file ].
 */
function gk_get_home_variants() {
    static $variants = null;
    if ( $variants !== null ) {
        return $variants;
    }

    $variants = array();
    $dir      = get_template_directory() . '/template-parts/home';

    if ( ! is_dir( $dir ) ) {
        return $variants;
    }

    foreach ( glob( $dir . '/*.php' ) as $file ) {
        // Skip companion settings files.
        if ( preg_match( '/-settings\.php$/', $file ) ) {
            continue;
        }

        $headers = get_file_data( $file, array(
            'variant'       => 'Variant',
            'variant_name'  => 'Variant Name',
            'variant_desc'  => 'Variant Desc',
            'variant_thumb' => 'Variant Thumb',
        ) );

        if ( empty( $headers['variant'] ) ) {
            continue;
        }

        $slug = sanitize_file_name( $headers['variant'] );

        $variants[ $slug ] = array(
            'name'  => $headers['variant_name'] ?: $slug,
            'desc'  => $headers['variant_desc'] ?: '',
            'thumb' => $headers['variant_thumb'] ?: '',
            'file'  => $file,
        );
    }

    uasort( $variants, function ( $a, $b ) {
        return strcasecmp( $a['name'], $b['name'] );
    } );

    return $variants;
}


/**
 * Load companion settings files for all discovered variants.
 *
 * Each variant can have a file template-parts/home/{slug}-settings.php
 * that defines gk_home_{slug_underscored}_render($s) and
 * gk_home_{slug_underscored}_sanitize($input).
 */
function gk_load_variant_settings_files() {
    static $loaded = false;
    if ( $loaded ) return;
    $loaded = true;

    $dir = get_template_directory() . '/template-parts/home';
    foreach ( gk_get_home_variants() as $slug => $v ) {
        $settings_file = $dir . '/' . $slug . '-settings.php';
        if ( file_exists( $settings_file ) ) {
            require_once $settings_file;
        }
    }
}


// ── Settings Registration ───────────────────────────────────────────────────

function gk_register_settings() {
    register_setting( 'gk_settings', 'gk_homepage', array(
        'type'              => 'array',
        'sanitize_callback' => 'gk_sanitize_homepage_settings',
        'default'           => array(),
    ) );

}
add_action( 'admin_init', 'gk_register_settings' );


// ── Homepage Settings: Sanitize ─────────────────────────────────────────────

function gk_sanitize_homepage_settings( $input ) {
    if ( ! is_array( $input ) ) return array();

    // Validate variant slug.
    $variant_slug = 'neue-energie';

    // Load companion settings files so sanitize callbacks exist.
    gk_load_variant_settings_files();

    // Preserve existing variant data, then sanitize submitted variants.
    $old_data = get_option( 'gk_homepage', array() );
    $old_variants = $old_data['variants'] ?? array();
    $new_variants = $old_variants; // keep data for non-submitted variants

    $submitted = $input['variants'] ?? array();
    foreach ( $submitted as $slug => $variant_input ) {
        $safe_slug = sanitize_file_name( $slug );
        $fn        = 'gk_home_' . str_replace( '-', '_', $safe_slug ) . '_sanitize';

        if ( function_exists( $fn ) ) {
            $new_variants[ $safe_slug ] = $fn( $variant_input );
        } else {
            // Generic sanitization: sanitize_text_field on strings, absint on numbers.
            $clean = array();
            foreach ( (array) $variant_input as $k => $v ) {
                $clean[ sanitize_key( $k ) ] = is_numeric( $v ) ? absint( $v ) : sanitize_text_field( $v );
            }
            $new_variants[ $safe_slug ] = $clean;
        }
    }

    return array(
        'home_variant' => $variant_slug,
        'variants'     => $new_variants,
    );
}


// ── Homepage Option Helpers ─────────────────────────────────────────────────

/**
 * Get a homepage setting from the active variant's store.
 *
 * Checks the active variant's settings first, then falls back to
 * the top-level gk_homepage option (backwards compat), then $default.
 *
 * @param string $key     Setting key.
 * @param mixed  $default Fallback value.
 * @return mixed
 */
function gk_homepage_option( $key, $default = '' ) {
    static $opts = null;
    static $variant_opts = null;

    if ( $opts === null ) {
        $opts         = get_option( 'gk_homepage', array() );
        $variant      = $opts['home_variant'] ?? 'neue-energie';
        $all_variants = $opts['variants'] ?? array();
        $variant_opts = $all_variants[ $variant ] ?? array();
    }

    // Top-level keys (home_variant itself)
    if ( $key === 'home_variant' ) {
        return $opts['home_variant'] ?? $default;
    }

    // Variant-specific settings
    if ( isset( $variant_opts[ $key ] ) && $variant_opts[ $key ] !== '' ) {
        return $variant_opts[ $key ];
    }

    // Backwards compat: check top-level (for migrated data)
    if ( isset( $opts[ $key ] ) && $opts[ $key ] !== '' ) {
        return $opts[ $key ];
    }

    return $default;
}


