<?php
/**
 * Custom Post Types and Taxonomies
 *
 * Registers the "person" post type and taxonomies (abteilung, gk_zuordnung).
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'init', 'gk_register_person_post_type' );
add_action( 'init', 'gk_register_person_taxonomy', 0 );
add_action( 'init', 'gk_register_zuordnung_taxonomy', 0 );
add_action( 'init', 'gk_ensure_zuordnung_defaults', 20 );


/**
 * Register "Person" post type
 */
function gk_register_person_post_type() {
    $labels = array(
        'name'               => _x( 'Personen', 'post type general name', 'neurg-kreisverband' ),
        'singular_name'      => _x( 'Person', 'post type singular name', 'neurg-kreisverband' ),
        'add_new'            => _x( 'Neue hinzufuegen', 'Personen', 'neurg-kreisverband' ),
        'add_new_item'       => __( 'Neue Person hinzufuegen', 'neurg-kreisverband' ),
        'edit_item'          => __( 'Person bearbeiten', 'neurg-kreisverband' ),
        'new_item'           => __( 'Neue Person', 'neurg-kreisverband' ),
        'view_item'          => __( 'Person anschauen', 'neurg-kreisverband' ),
        'search_items'       => __( 'Person suchen', 'neurg-kreisverband' ),
        'not_found'          => __( 'Keine Person gefunden', 'neurg-kreisverband' ),
        'not_found_in_trash' => __( 'Keine Person im Papierkorb gefunden', 'neurg-kreisverband' ),
        'parent_item_colon'  => '',
    );

    register_post_type( 'person', array(
        'labels'   => $labels,
        'public'   => true,
        'menu_icon' => 'dashicons-id-alt',
        'supports' => array( 'title', 'editor', 'revisions', 'thumbnail', 'author' ),
        'has_archive' => true,
        'rewrite'  => array( 'slug' => 'person' ),
    ) );
}


/**
 * Register "Abteilung" taxonomy for persons
 */
function gk_register_person_taxonomy() {
    $labels = array(
        'name'              => _x( 'Abteilungen', 'taxonomy general name', 'neurg-kreisverband' ),
        'singular_name'     => _x( 'Abteilung', 'taxonomy singular name', 'neurg-kreisverband' ),
        'search_items'      => __( 'Abteilungen suchen', 'neurg-kreisverband' ),
        'all_items'         => __( 'Alle Abteilungen', 'neurg-kreisverband' ),
        'parent_item'       => __( 'Übergeordnete Abteilung', 'neurg-kreisverband' ),
        'parent_item_colon' => __( 'Übergeordnete Abteilung:', 'neurg-kreisverband' ),
        'edit_item'         => __( 'Abteilung bearbeiten', 'neurg-kreisverband' ),
        'update_item'       => __( 'Abteilung aktualisieren', 'neurg-kreisverband' ),
        'add_new_item'      => __( 'Abteilung anlegen', 'neurg-kreisverband' ),
        'new_item_name'     => __( 'Name der Abteilung', 'neurg-kreisverband' ),
        'menu_name'         => __( 'Abteilungen', 'neurg-kreisverband' ),
    );

    register_taxonomy( 'abteilung', array( 'person' ), array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'abteilung' ),
    ) );
}


/**
 * Register "Zuordnung" taxonomy for posts and events.
 *
 * Allows editors to assign each post/event to either the Kreisverband
 * or a specific Ortsverband. Replaces the old author-based OV detection
 * for homepage/archive filtering.
 */
function gk_register_zuordnung_taxonomy() {
    $labels = array(
        'name'              => _x( 'Zuordnung', 'taxonomy general name', 'neurg-kreisverband' ),
        'singular_name'     => _x( 'Zuordnung', 'taxonomy singular name', 'neurg-kreisverband' ),
        'search_items'      => __( 'Zuordnung suchen', 'neurg-kreisverband' ),
        'all_items'         => __( 'Alle Zuordnungen', 'neurg-kreisverband' ),
        'parent_item'       => null,
        'parent_item_colon' => null,
        'edit_item'         => __( 'Zuordnung bearbeiten', 'neurg-kreisverband' ),
        'update_item'       => __( 'Zuordnung aktualisieren', 'neurg-kreisverband' ),
        'add_new_item'      => __( 'Zuordnung hinzufuegen', 'neurg-kreisverband' ),
        'new_item_name'     => __( 'Name der Zuordnung', 'neurg-kreisverband' ),
        'menu_name'         => __( 'Zuordnung (KV/OV)', 'neurg-kreisverband' ),
    );

    register_taxonomy( 'gk_zuordnung', array( 'post', 'page', 'person', 'gk_event', 'attachment' ), array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_quick_edit' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'zuordnung' ),
    ) );
}


/**
 * Ensure the "Kreisverband" default term exists.
 */
function gk_ensure_zuordnung_defaults() {
    if ( ! taxonomy_exists( 'gk_zuordnung' ) ) {
        return;
    }

    // Create the default "Kreisverband" term if it doesn't exist.
    if ( ! term_exists( 'kreisverband', 'gk_zuordnung' ) ) {
        wp_insert_term( 'Kreisverband', 'gk_zuordnung', array(
            'slug'        => 'kreisverband',
            'description' => 'Beiträge des Kreisverbands',
        ) );
    }
}


/**
 * Enforce single zuordnung on save: exactly one term (KV or one OV).
 * Defaults to "Kreisverband" if none is set.
 */
function gk_default_zuordnung_on_save( $post_id, $post, $update ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( wp_is_post_revision( $post_id ) ) return;
    if ( ! in_array( $post->post_type, array( 'post', 'page', 'person', 'gk_event', 'attachment' ), true ) ) return;

    $terms = wp_get_object_terms( $post_id, 'gk_zuordnung', array( 'fields' => 'ids' ) );

    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        // No zuordnung → default to Kreisverband.
        $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        if ( $kv_term ) {
            wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
        }
    } elseif ( count( $terms ) > 1 ) {
        // Multiple zuordnungen → keep only the first one.
        wp_set_object_terms( $post_id, array( (int) $terms[0] ), 'gk_zuordnung' );
    }
}
add_action( 'save_post', 'gk_default_zuordnung_on_save', 10, 3 );


/**
 * Replace the default checkbox meta box with a radio/dropdown for zuordnung.
 * Ensures only one value can be selected in the editor.
 */
function gk_zuordnung_meta_box() {
    $post_types = array( 'post', 'page', 'person', 'gk_event', 'attachment' );
    foreach ( $post_types as $pt ) {
        remove_meta_box( 'gk_zuordnungdiv', $pt, 'side' );
        add_meta_box(
            'gk_zuordnung_select',
            __( 'Zuordnung (KV/OV)', 'neurg-kreisverband' ),
            'gk_zuordnung_meta_box_cb',
            $pt,
            'side',
            'high'
        );
    }
}
add_action( 'add_meta_boxes', 'gk_zuordnung_meta_box' );

function gk_zuordnung_meta_box_cb( $post ) {
    $terms     = get_terms( array( 'taxonomy' => 'gk_zuordnung', 'hide_empty' => false ) );
    $current   = wp_get_object_terms( $post->ID, 'gk_zuordnung', array( 'fields' => 'ids' ) );
    $current_id = ! empty( $current ) ? (int) $current[0] : 0;

    wp_nonce_field( 'gk_zuordnung_save', 'gk_zuordnung_nonce' );

    echo '<select name="gk_zuordnung_select" style="width:100%;max-width:100%;box-sizing:border-box">';
    echo '<option value="">' . esc_html__( '— Bitte wählen —', 'neurg-kreisverband' ) . '</option>';
    foreach ( $terms as $term ) {
        printf(
            '<option value="%d"%s>%s</option>',
            $term->term_id,
            selected( $current_id, $term->term_id, false ),
            esc_html( $term->name )
        );
    }
    echo '</select>';
}

/**
 * Save the single zuordnung from the dropdown.
 */
function gk_zuordnung_meta_box_save( $post_id ) {
    if ( ! isset( $_POST['gk_zuordnung_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['gk_zuordnung_nonce'], 'gk_zuordnung_save' ) ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

    $post = get_post( $post_id );
    if ( ! in_array( $post->post_type, array( 'post', 'page', 'person', 'gk_event', 'attachment' ), true ) ) return;

    $term_id = ! empty( $_POST['gk_zuordnung_select'] ) ? (int) $_POST['gk_zuordnung_select'] : 0;

    if ( $term_id ) {
        wp_set_object_terms( $post_id, array( $term_id ), 'gk_zuordnung' );
    } else {
        // No selection → default to Kreisverband.
        $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        if ( $kv_term ) {
            wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
        }
    }
}
add_action( 'save_post', 'gk_zuordnung_meta_box_save', 5, 1 );
