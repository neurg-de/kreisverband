<?php
/**
 * Gutenberg Blocks
 *
 * Registers custom blocks for the theme.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Abteilung Block ────────────────────────────────────────────────────────

add_action( 'init', 'gk_register_abteilung_block' );
add_action( 'enqueue_block_editor_assets', 'gk_enqueue_block_editor_assets' );

function gk_register_abteilung_block() {
    register_block_type( 'gk/abteilung', array(
        'api_version'     => 3,
        'attributes'      => array(
            'slug' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'limit' => array(
                'type'    => 'number',
                'default' => 0,
            ),
        ),
        'render_callback' => 'gk_render_abteilung_block',
    ) );
}

function gk_render_abteilung_block( $attributes ) {
    if ( empty( $attributes['slug'] ) ) {
        return '';
    }

    // Auto-detect page zuordnung: OV pages filter to their members only
    $zuordnung = gk_get_post_zuordnung_slug( get_the_ID() );

    return gk_shortcode_abteilung( array(
        'slug'      => $attributes['slug'],
        'limit'     => $attributes['limit'] ?? 0,
        'zuordnung' => $zuordnung,
    ) );
}

function gk_enqueue_block_editor_assets() {
    global $post;

    // Determine the current page's zuordnung to filter available abteilungen
    $zuordnung = '';
    if ( $post ) {
        $zuordnung = gk_get_post_zuordnung_slug( $post->ID );
    }

    // Get abteilung terms, optionally filtered by zuordnung
    if ( $zuordnung && $zuordnung !== 'kreisverband' ) {
        // Only show abteilungen that have persons from this OV
        $person_ids = get_posts( array(
            'post_type'      => 'person',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'tax_query'      => array( array(
                'taxonomy' => 'gk_zuordnung',
                'field'    => 'slug',
                'terms'    => $zuordnung,
            ) ),
        ) );

        // Build a map: abteilung slug → count of persons from this OV
        $abt_counts = array();
        foreach ( $person_ids as $pid ) {
            $slugs = wp_get_post_terms( $pid, 'abteilung', array( 'fields' => 'slugs' ) );
            if ( is_wp_error( $slugs ) ) continue;
            foreach ( $slugs as $s ) {
                $abt_counts[ $s ] = ( $abt_counts[ $s ] ?? 0 ) + 1;
            }
        }

        $terms = array();
        foreach ( $abt_counts as $slug => $count ) {
            $term = get_term_by( 'slug', $slug, 'abteilung' );
            if ( $term ) {
                $term->count = $count;
                $terms[] = $term;
            }
        }
        usort( $terms, fn( $a, $b ) => strcasecmp( $a->name, $b->name ) );
    } else {
        $terms = get_terms( array(
            'taxonomy'   => 'abteilung',
            'hide_empty' => false,
            'orderby'    => 'name',
        ) );
        if ( is_wp_error( $terms ) ) {
            $terms = array();
        }
    }

    $options = array( array( 'value' => '', 'label' => '— Abteilung wählen —' ) );
    foreach ( $terms as $term ) {
        $options[] = array(
            'value' => $term->slug,
            'label' => "{$term->name} ({$term->count})",
        );
    }

    wp_enqueue_script(
        'gk-block-abteilung',
        GK_URI . '/lib/js/block-abteilung.js',
        array( 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render', 'wp-data' ),
        GK_VERSION,
        true
    );

    wp_localize_script( 'gk-block-abteilung', 'gkAbteilungBlock', array(
        'options'    => $options,
        'zuordnung'  => $zuordnung,
    ) );
}
