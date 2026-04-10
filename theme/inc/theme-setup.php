<?php
/**
 * Theme Setup: menus, theme support, scripts/styles, sidebars
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'after_setup_theme', 'gk_theme_setup' );
add_action( 'widgets_init', 'gk_register_sidebars' );
add_action( 'wp_enqueue_scripts', 'gk_enqueue_scripts', 999 );
add_action( 'after_setup_theme', 'gk_editor_styles' );


// ── Theme Support & Menus ────────────────────────────────────────────────────

function gk_theme_setup() {
    // Thumbnails and image sizes
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'menus' );

    add_image_size( 'thumbnail', 500, 500, false );
    add_image_size( 'medium', 400, 600, false );
    add_image_size( 'large', 1200, 1800, false );
    add_image_size( 'titelbild', 930, 465, true );
    add_image_size( 'listenansicht', 350, 220, true );
    add_image_size( '350uncropped', 350 );

    // Register navigation menus — KV defaults
    register_nav_menus( array(
        'nav-main'   => __( 'Hauptmenü', 'neurg-kreisverband' ),
        'nav-footer' => __( 'Links in der Fußleiste', 'neurg-kreisverband' ),
        'nav-mobile' => __( 'Menü für Mobilgeräte', 'neurg-kreisverband' ),
        'nav-sozial' => __( 'Soziale Netzwerke', 'neurg-kreisverband' ),
    ) );

    // Stop scaling down big images
    add_filter( 'big_image_size_threshold', '__return_false' );

    // Clean up WP head
    remove_action( 'wp_head', 'rsd_link' );
    remove_action( 'wp_head', 'wlwmanifest_link' );
    remove_action( 'wp_head', 'wp_generator' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );

    // Disable comments site-wide
    add_filter( 'comments_open', '__return_false', 20, 2 );
    add_filter( 'pings_open', '__return_false', 20, 2 );
}


// ── Scripts & Styles ─────────────────────────────────────────────────────────

function gk_enqueue_scripts() {
    if ( is_admin() ) return;

    // Styles
    wp_enqueue_style( 'gk-fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', array(), '6.5.1' );
    wp_enqueue_style( 'gk-main', GK_URI . '/lib/css/main.css', array(), GK_VERSION );

    // Scripts
    wp_enqueue_script( 'jquery' );
    wp_enqueue_script( 'gk-scripts', GK_URI . '/lib/js/scripts.js', array( 'jquery' ), GK_VERSION, true );
    wp_enqueue_script( 'gk-responsive-tabs', GK_URI . '/lib/js/responsiveTabs.min.js', array( 'jquery' ), GK_VERSION, true );
}


// ── Editor Styles ────────────────────────────────────────────────────────────

function gk_editor_styles() {
    add_editor_style( 'lib/css/editor.css' );
}


// ── Body Class for Design Theme ─────────────────────────────────────────────

function gk_body_class_design_theme( $classes ) {
    $classes[] = 'design-grundlagen2024';
    return $classes;
}
add_filter( 'body_class', 'gk_body_class_design_theme' );


// ── Sidebars ─────────────────────────────────────────────────────────────────

function gk_register_sidebars() {
    register_sidebar( array(
        'name'          => 'Infospalte',
        'description'   => 'Infospalte für Widgets. Wird auf den meisten Seiten angezeigt.',
        'id'            => 'infospalte',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h3 class="widgettitle">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Startseite - Artikel',
        'description'   => 'Artikel auf der Startseite',
        'id'            => 'hometeaser',
        'before_widget' => '<article id="%1$s" class="clearfix %2$s">',
        'after_widget'  => '</article>',
        'before_title'  => '<h1>',
        'after_title'   => '</h1>',
    ) );

    register_sidebar( array(
        'name'          => 'Startseite - Actionbox',
        'description'   => 'Drei Widgets auf der Startseite',
        'id'            => 'homeone',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h2>',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => 'Startseite - Infoleiste',
        'description'   => 'Infospalte der Startseite',
        'id'            => 'hometwo',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3>',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Presse',
        'description'   => 'Widgets in der Kategorie Presse',
        'id'            => 'presse',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h3 class="widgettitle">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => 'Fußleiste',
        'description'   => 'Platz für Widgets in der Fußleiste.',
        'id'            => 'fussleiste',
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget'  => '</li>',
        'before_title'  => '<h3 class="widgettitle">',
        'after_title'   => '</h3>',
    ) );
}


// ── Navigation Helpers ───────────────────────────────────────────────────────

function gk_nav_main() {
    wp_nav_menu( array(
        'container'   => false,
        'menu_class'  => 'navigation clearfix',
        'theme_location' => 'nav-main',
        'depth'       => 3,
        'fallback_cb' => 'gk_nav_fallback',
    ) );
}

/**
 * Output the OV header menu for the given zuordnung slug.
 */
function gk_nav_ov( $ov_slug ) {
    $location = 'nav-' . $ov_slug;
    if ( ! has_nav_menu( $location ) ) {
        return;
    }
    wp_nav_menu( array(
        'container'      => false,
        'menu_class'     => 'navigation clearfix nav-ov',
        'theme_location' => $location,
        'depth'          => 3,
        'fallback_cb'    => false,
    ) );
}

function gk_nav_mobile() {
    wp_nav_menu( array(
        'container'   => false,
        'menu_class'  => 'navigation clearfix',
        'theme_location' => 'nav-mobile',
        'depth'       => 3,
    ) );
}

function gk_nav_footer( $zuordnung_slug = '' ) {
    // Try zuordnung-specific footer menu first, fall back to generic nav-footer.
    $location = 'nav-footer';
    if ( $zuordnung_slug && has_nav_menu( 'nav-' . $zuordnung_slug . '-footer' ) ) {
        $location = 'nav-' . $zuordnung_slug . '-footer';
    }
    wp_nav_menu( array(
        'container'      => false,
        'menu_class'     => 'navigation clearfix nav-footer',
        'theme_location' => $location,
        'depth'          => 1,
        'fallback_cb'    => false,
    ) );
}

function gk_nav_social() {
    wp_nav_menu( array(
        'container'   => false,
        'menu_class'  => 'navigation clearfix nav-sozial',
        'theme_location' => 'nav-sozial',
        'depth'       => 1,
        'fallback_cb' => false,
    ) );
}

function gk_nav_fallback() {
    wp_page_menu( array(
        'show_home'  => true,
        'menu_class' => 'navigation nav-fallback clearfix',
    ) );
}

// Backwards compatibility aliases
function kr8_nav_main()   { gk_nav_main(); }
function kr8_nav_mobile() { gk_nav_mobile(); }
function kr8_nav_footer() { gk_nav_footer(); }
function kr8_nav_social() { gk_nav_social(); }


// ── Search Form ──────────────────────────────────────────────────────────────

add_filter( 'get_search_form', 'gk_search_form' );
function gk_search_form() {
    return '<section class="suche"><form role="search" method="get" class="searchform" action="' . esc_url( home_url( '/' ) ) . '">'
        . '<label for="gk-search" class="screen-reader-text">Suche</label>'
        . '<input type="text" name="s" id="gk-search" class="seachphrase" value="' . get_search_query() . '" placeholder="Suchbegriff eingeben ..." />'
        . '<button type="submit" class="button-submit"><span class="fa fa-search"></span> <span class="text">Suchen</span></button>'
        . '</form></section>';
}


// ── Image Handling ───────────────────────────────────────────────────────────

// Remove wrapping paragraphs from images
add_filter( 'the_content', 'gk_filter_ptags_on_images' );
function gk_filter_ptags_on_images( $content ) {
    return preg_replace( '/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content );
}

// Make all image sizes available in editor
add_filter( 'image_size_names_choose', 'gk_image_sizes_choose' );
function gk_image_sizes_choose( $sizes ) {
    global $_wp_additional_image_sizes;
    $custom = array();
    foreach ( $_wp_additional_image_sizes as $key => $value ) {
        $custom[ $key ] = ucwords( str_replace( '-', ' ', $key ) );
    }
    return array_merge( $custom, $sizes );
}


// Social share is now in inc/social-share.php


// ── Excerpt ──────────────────────────────────────────────────────────────────

add_filter( 'excerpt_more', 'gk_excerpt_more' );
function gk_excerpt_more() {
    return '...';
}
