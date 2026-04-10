<?php
/**
 * Dev Seed Data
 *
 * Seeds zuordnung terms with example social media links for development.
 * Only active when WP_DEBUG is true.
 *
 * Usage: wp eval 'gk_seed_social_data();'
 * Or visit: /wp-admin/?gk_seed_social=1 (as admin)
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Seed example social media contact data for KV and a sample OV.
 */
function gk_seed_social_data() {
    if ( ! taxonomy_exists( 'gk_zuordnung' ) ) {
        return array( 'error' => 'Taxonomy gk_zuordnung not registered.' );
    }

    $seeded = array();

    // ── Kreisverband ────────────────────────────────────────────────────────
    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    if ( ! $kv_term ) {
        gk_ensure_zuordnung_defaults();
        $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    }

    if ( $kv_term ) {
        $kv_social = array(
            '_gk_contact_facebook' => 'https://www.facebook.com/MeinKreisverband',
            '_gk_contact_insta'    => 'mein.kreisverband',
            '_gk_contact_twitter'  => 'MeinKV',
            '_gk_contact_tiktok'   => '@mein.kreisverband',
            '_gk_contact_threads'  => 'mein.kreisverband',
            '_gk_contact_mastodon' => 'https://gruene.social/@meinkv',
            '_gk_contact_www'      => 'https://example.com',
            '_gk_contact_email'    => 'info@example.com',
        );
        foreach ( $kv_social as $key => $value ) {
            update_term_meta( $kv_term->term_id, $key, $value );
        }

        $kv_info = get_option( 'gk_kv_info', array() );
        $kv_info = array_merge( $kv_info, array(
            'social_instagram' => 'mein.kreisverband',
            'social_facebook'  => 'https://www.facebook.com/MeinKreisverband',
            'social_x'         => 'MeinKV',
            'social_tiktok'    => '@mein.kreisverband',
            'social_threads'   => 'mein.kreisverband',
            'social_bluesky'   => 'meinkv.bsky.social',
            'social_mastodon'  => 'https://gruene.social/@meinkv',
            'social_youtube'   => 'https://www.youtube.com/@MeinKreisverband',
        ) );
        update_option( 'gk_kv_info', $kv_info );

        $seeded[] = 'Kreisverband';
    }

    // ── Sample OV ───────────────────────────────────────────────────────────
    $ov_term = get_term_by( 'slug', 'ov-musterstadt', 'gk_zuordnung' );
    if ( ! $ov_term ) {
        $result = wp_insert_term( 'OV Musterstadt', 'gk_zuordnung', array(
            'slug'        => 'ov-musterstadt',
            'description' => 'Ortsverband Musterstadt',
        ) );
        if ( ! is_wp_error( $result ) ) {
            $ov_term = get_term( $result['term_id'], 'gk_zuordnung' );
        }
    }

    if ( $ov_term ) {
        update_term_meta( $ov_term->term_id, '_gk_ov_type', 'ov' );
        update_term_meta( $ov_term->term_id, '_gk_ov_header', 'Musterstadt' );

        $ov_social = array(
            '_gk_contact_facebook' => 'GrueneMusterstadt',
            '_gk_contact_insta'    => 'gruene.musterstadt',
            '_gk_contact_www'      => 'https://gruene-musterstadt.example.com',
            '_gk_contact_email'    => 'info@gruene-musterstadt.example.com',
        );
        foreach ( $ov_social as $key => $value ) {
            update_term_meta( $ov_term->term_id, $key, $value );
        }
        $seeded[] = 'OV Musterstadt';
    }

    // ── Seed person social data ─────────────────────────────────────────────
    $persons = get_posts( array(
        'post_type'      => 'person',
        'posts_per_page' => 3,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'ASC',
    ) );

    foreach ( $persons as $i => $person ) {
        $name_slug = sanitize_title( $person->post_title );
        $socials   = array(
            'kr8mb_pers_contact_insta'    => $name_slug,
            'kr8mb_pers_contact_facebook' => 'https://www.facebook.com/' . $name_slug,
            'kr8mb_pers_contact_twitter'  => $name_slug,
        );
        if ( $i === 0 ) {
            $socials['kr8mb_pers_contact_tiktok']   = '@' . $name_slug;
            $socials['kr8mb_pers_contact_threads']   = $name_slug;
            $socials['kr8mb_pers_contact_mastodon'] = 'https://gruene.social/@' . $name_slug;
        }
        if ( $i === 1 ) {
            $socials['kr8mb_pers_contact_threads'] = 'https://www.threads.net/@' . $name_slug;
            $socials['kr8mb_pers_contact_tiktok']  = 'https://www.tiktok.com/@' . $name_slug;
        }

        foreach ( $socials as $key => $value ) {
            update_post_meta( $person->ID, $key, $value );
        }
        $seeded[] = 'Person: ' . $person->post_title;
    }

    return array( 'seeded' => $seeded );
}


/**
 * Admin action to trigger seeding via browser.
 */
add_action( 'admin_init', function () {
    if ( ! isset( $_GET['gk_seed_social'] ) || ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $result = gk_seed_social_data();
    $names  = implode( ', ', $result['seeded'] ?? array() );
    add_action( 'admin_notices', function () use ( $names ) {
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo '<strong>Social-Daten geseedet:</strong> ' . esc_html( $names );
        echo '</p></div>';
    } );
} );
