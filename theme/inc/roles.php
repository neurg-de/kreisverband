<?php
/**
 * Custom Roles & Capabilities
 *
 * Role hierarchy:
 *
 *   KV level (Kreisverband):
 *     administrator     — WP admin with all custom capabilities (gk_manage_ov, etc.)
 *     gk_kvautor        — KV author: posts, pages, persons at KV level only
 *     gk_kvautor_ov     — KV author with OV access: like kvautor but can also edit OV content
 *   OV level (Ortsverband):
 *     gk_ovadmin        — Admin for their OV: pages, posts, persons within their scope
 *     gk_ovautor        — Author for their OV: can create/edit own posts and persons
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Role Registration ───────────────────────────────────────────────────────

/**
 * Register custom roles on theme activation.
 */
function gk_register_roles() {

    // ── Administrator: add custom capabilities ────────────────────────────
    $admin_role = get_role( 'administrator' );
    if ( $admin_role ) {
        $admin_role->add_cap( 'edit_persons' );
        $admin_role->add_cap( 'edit_others_persons' );
        $admin_role->add_cap( 'publish_persons' );
        $admin_role->add_cap( 'delete_persons' );
        $admin_role->add_cap( 'delete_others_persons' );
        $admin_role->add_cap( 'gk_manage_ov' );
        $admin_role->add_cap( 'gk_manage_settings' );
    }

    // ── KV Level ────────────────────────────────────────────────────────────

    // KV-Autor: creates KV-level content (own posts, pages, persons) — no OV access
    add_role( 'gk_kvautor', 'KV-Autor', array(
        'read'                   => true,
        'upload_files'           => true,

        // Posts (own only)
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,

        // Pages (own only)
        'edit_pages'             => true,
        'edit_published_pages'   => true,
        'publish_pages'          => true,
        'delete_pages'           => true,
        'delete_published_pages' => true,

        // Person CPT (own only)
        'edit_persons'           => true,
        'publish_persons'        => true,
        'delete_persons'         => true,
    ) );

    // KV-Autor mit OV-Zugang: like KV-Autor but can also see/edit OV content
    add_role( 'gk_kvautor_ov', 'KV-Autor (mit OV-Zugang)', array(
        'read'                   => true,
        'upload_files'           => true,

        // Posts (incl. others = OV content)
        'edit_posts'             => true,
        'edit_others_posts'      => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,

        // Pages (incl. others = OV pages)
        'edit_pages'             => true,
        'edit_others_pages'      => true,
        'edit_published_pages'   => true,
        'publish_pages'          => true,
        'delete_pages'           => true,
        'delete_published_pages' => true,

        // Person CPT (incl. others = OV persons)
        'edit_persons'           => true,
        'edit_others_persons'    => true,
        'publish_persons'        => true,
        'delete_persons'         => true,

        // OV access
        'gk_manage_ov'           => true,
    ) );

    // ── OV Level ────────────────────────────────────────────────────────────

    // OV-Admin: can manage all content types but scoped to own authorship
    add_role( 'gk_ovadmin', 'OV-Admin', array(
        'read'                   => true,
        'upload_files'           => true,

        // Posts
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,
        'delete_published_posts' => true,

        // Pages
        'edit_pages'             => true,
        'edit_published_pages'   => true,
        'publish_pages'          => true,
        'delete_pages'           => true,
        'delete_published_pages' => true,

        // Person CPT
        'edit_persons'           => true,
        'publish_persons'        => true,
        'delete_persons'         => true,

        // Menus (requires edit_theme_options for nav-menus.php)
        'edit_theme_options'     => true,

        // OV-specific
        'gk_manage_ov'           => true,
    ) );

    // OV-Autor: can create and edit own content
    add_role( 'gk_ovautor', 'OV-Autor', array(
        'read'                   => true,
        'upload_files'           => true,

        // Posts (own only)
        'edit_posts'             => true,
        'edit_published_posts'   => true,
        'publish_posts'          => true,
        'delete_posts'           => true,

        // Person CPT (own only)
        'edit_persons'           => true,
        'publish_persons'        => true,
        'delete_persons'         => true,
    ) );
}
add_action( 'after_switch_theme', 'gk_register_roles' );


/**
 * Remove custom roles on theme deactivation (clean up).
 */
function gk_remove_roles() {
    remove_role( 'gk_kvautor' );
    remove_role( 'gk_kvautor_ov' );
    remove_role( 'gk_ovadmin' );
    remove_role( 'gk_ovautor' );
}
add_action( 'switch_theme', 'gk_remove_roles' );


// ── Grant KV-level roles access to all content ─────────────────────────────

/**
 * Ensure Kreisadmin and KV-Autor (mit OV) see all posts in admin lists,
 * not just their own.
 */
function gk_kv_sees_all_content( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }

    $user = wp_get_current_user();
    $full_access_roles = array( 'administrator', 'gk_kvautor_ov' );

    if ( ! array_intersect( $full_access_roles, (array) $user->roles ) ) {
        return;
    }

    // Remove accidental author restrictions so these roles see all content
    if ( $query->get( 'author' ) == $user->ID ) {
        $query->set( 'author', '' );
    }
}
add_action( 'pre_get_posts', 'gk_kv_sees_all_content' );


// ── Admin Menu Cleanup for Scoped Roles ─────────────────────────────────────

/**
 * Simplify the admin menu for scoped roles.
 *
 * OV roles: no Ortsverbände admin, no Tools
 * KV-Autor: no Tools
 */
function gk_simplify_admin_menu() {
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    // Verband settings page: admin + OV-Admin
    $verband_roles = array( 'administrator', 'gk_ovadmin' );
    if ( ! array_intersect( $verband_roles, $roles ) ) {
        remove_menu_page( 'gk-settings' );
    }

    // OV roles: simplified admin
    $ov_roles = array( 'gk_ovadmin', 'gk_ovautor' );
    if ( array_intersect( $ov_roles, $roles ) ) {
        remove_menu_page( 'tools.php' );
        remove_menu_page( 'themes.php' );
        return;
    }

    // KV-Autor (both variants): hide tools
    $kv_autor_roles = array( 'gk_kvautor', 'gk_kvautor_ov' );
    if ( array_intersect( $kv_autor_roles, $roles ) ) {
        remove_menu_page( 'tools.php' );
    }
}
add_action( 'admin_menu', 'gk_simplify_admin_menu', 999 );


// ── Admin Bar Cleanup ───────────────────────────────────────────────────────

/**
 * Remove admin bar items that scoped roles don't need.
 */
function gk_simplify_admin_bar() {
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    $scoped_roles = array( 'gk_ovadmin', 'gk_ovautor', 'gk_kvautor', 'gk_kvautor_ov' );
    if ( ! array_intersect( $scoped_roles, $roles ) ) {
        return;
    }

    global $wp_admin_bar;
}
add_action( 'wp_before_admin_bar_render', 'gk_simplify_admin_bar' );


// ── Dashboard Widget for OV Users ───────────────────────────────────────────

/**
 * Add a welcome widget for OV users showing their OV name.
 */
function gk_ov_dashboard_widget() {
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    $ov_roles = array( 'gk_ovadmin', 'gk_ovautor' );
    if ( array_intersect( $ov_roles, $roles ) ) {
        wp_add_dashboard_widget( 'gk_ov_welcome', 'Dein Ortsverband', 'gk_ov_welcome_widget_cb' );
    }

}
add_action( 'wp_dashboard_setup', 'gk_ov_dashboard_widget' );

function gk_ov_welcome_widget_cb() {
    $user = wp_get_current_user();
    // OV users have login matching their zuordnung slug (e.g. "ov-gauting").
    $ov_term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );

    if ( $ov_term ) {
        echo '<p>Du bist angemeldet als <strong>' . esc_html( $user->display_name ) . '</strong>';
        echo ' für den Ortsverband <strong>' . esc_html( $ov_term->name ) . '</strong>.</p>';
        echo '<p><a href="' . esc_url( admin_url( 'edit.php' ) ) . '" class="button">Beiträge verwalten</a> ';
        echo '<a href="' . esc_url( admin_url( 'edit.php?post_type=person' ) ) . '" class="button">Personen verwalten</a></p>';
    } else {
        echo '<p>Willkommen, ' . esc_html( $user->display_name ) . '!</p>';
    }
}
