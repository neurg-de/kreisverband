<?php
/**
 * Social Links — Unified helpers
 *
 * Central registry of social platforms, forgiving URL normalizer,
 * and a reusable social-links bar renderer that works identically
 * for persons, Kreisverbände, and Ortsverbände.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Canonical list of supported social platforms.
 *
 * Each entry:
 *   label    — Human-readable name
 *   icon     — FontAwesome 6 class
 *   base     — Base URL used to build a profile link from a bare username
 *   extract  — Regex to pull a username out of a pasted full URL (optional)
 *
 * @return array<string, array>
 */
function gk_social_platforms() {
    return array(
        'instagram' => array(
            'label'   => 'Instagram',
            'icon'    => 'fab fa-instagram',
            'color'   => '#E4405F',
            'base'    => 'https://www.instagram.com/%s',
            'extract' => '#instagram\.com/([^/?]+)#i',
        ),
        'facebook' => array(
            'label'   => 'Facebook',
            'icon'    => 'fab fa-facebook-f',
            'color'   => '#1877F2',
            'base'    => 'https://www.facebook.com/%s',
            'extract' => '#facebook\.com/([^/?]+)#i',
        ),
        'x' => array(
            'label'   => 'X',
            'icon'    => 'fab fa-x-twitter',
            'color'   => '#000000',
            'base'    => 'https://x.com/%s',
            'extract' => '#(?:twitter|x)\.com/([^/?]+)#i',
        ),
        'tiktok' => array(
            'label'   => 'TikTok',
            'icon'    => 'fab fa-tiktok',
            'color'   => '#000000',
            'base'    => 'https://www.tiktok.com/@%s',
            'extract' => '#tiktok\.com/@?([^/?]+)#i',
        ),
        'threads' => array(
            'label'   => 'Threads',
            'icon'    => 'fab fa-threads',
            'color'   => '#000000',
            'base'    => 'https://www.threads.net/@%s',
            'extract' => '#threads\.net/@?([^/?]+)#i',
        ),
        'bluesky' => array(
            'label'   => 'Bluesky',
            'icon'    => 'fab fa-bluesky',
            'color'   => '#0085FF',
            'base'    => 'https://bsky.app/profile/%s',
            'extract' => '#bsky\.app/profile/([^/?]+)#i',
        ),
        'mastodon' => array(
            'label'   => 'Mastodon',
            'icon'    => 'fab fa-mastodon',
            'color'   => '#6364FF',
            'base'    => '',   // Mastodon URLs are instance-specific, stored as full URL
            'extract' => '',
        ),
        'youtube' => array(
            'label'   => 'YouTube',
            'icon'    => 'fab fa-youtube',
            'color'   => '#FF0000',
            'base'    => 'https://www.youtube.com/%s',
            'extract' => '#youtube\.com/(?:@|channel/|c/)?([^/?]+)#i',
        ),
    );
}


/**
 * Normalize any user input into a proper profile URL.
 *
 * Accepts:
 *   - A full URL  → returned as-is (with minor cleanup)
 *   - A @handle   → @ stripped, turned into URL via base pattern
 *   - A bare name → turned into URL via base pattern
 *
 * @param string $platform Key from gk_social_platforms().
 * @param string $input    Raw user input (URL, handle, or username).
 * @return string Full URL or empty string.
 */
function gk_normalize_social_url( $platform, $input ) {
    $input = trim( $input );
    if ( $input === '' ) {
        return '';
    }

    $platforms = gk_social_platforms();
    $def       = $platforms[ $platform ] ?? null;

    // Already a URL — return cleaned.
    if ( preg_match( '#^https?://#i', $input ) ) {
        return $input;
    }

    // Mastodon: instance-specific, can't build from username alone.
    // Accept @user@instance format and build URL.
    if ( $platform === 'mastodon' ) {
        if ( preg_match( '#^@?([^@]+)@(.+)$#', $input, $m ) ) {
            return 'https://' . $m[2] . '/@' . $m[1];
        }
        return '';
    }

    if ( ! $def || empty( $def['base'] ) ) {
        return '';
    }

    // Strip leading @ (common copy-paste artifact).
    $username = ltrim( $input, '@' );

    return sprintf( $def['base'], rawurlencode( $username ) );
}


/**
 * Build a normalized links array from raw contact data.
 *
 * Maps internal storage keys (e.g. 'insta', 'twitter') to canonical
 * platform keys and normalizes each value into a full URL.
 *
 * @param array $contact Raw contact data (as stored in meta).
 * @return array<string, string> Platform key => full URL (only non-empty).
 */
function gk_build_social_links( $contact ) {
    // Map storage keys to canonical platform keys.
    $map = array(
        'instagram' => array( 'insta', 'instagram' ),
        'facebook'  => array( 'facebook' ),
        'x'         => array( 'twitter', 'x' ),
        'tiktok'    => array( 'tiktok' ),
        'threads'   => array( 'threads' ),
        'bluesky'   => array( 'bluesky' ),
        'mastodon'  => array( 'mastodon' ),
        'youtube'   => array( 'youtube' ),
    );

    $links = array();
    foreach ( $map as $platform => $keys ) {
        foreach ( $keys as $key ) {
            if ( ! empty( $contact[ $key ] ) ) {
                $url = gk_normalize_social_url( $platform, $contact[ $key ] );
                if ( $url ) {
                    $links[ $platform ] = $url;
                }
                break;
            }
        }
    }

    return $links;
}


/**
 * Render a social-links bar.
 *
 * Works identically for persons, KV, and OV — just pass the links.
 *
 * @param array $links   Platform key => URL (from gk_build_social_links).
 * @param array $args    Optional. 'class' => extra CSS class, 'label' => aria-label.
 */
function gk_social_links_bar( $links, $args = array() ) {
    if ( empty( $links ) ) {
        return;
    }

    $platforms = gk_social_platforms();
    $class     = 'gk-social-links' . ( ! empty( $args['class'] ) ? ' ' . esc_attr( $args['class'] ) : '' );
    $label     = $args['label'] ?? 'Soziale Netzwerke';
    ?>
    <nav class="<?php echo $class; ?>" aria-label="<?php echo esc_attr( $label ); ?>">
        <?php foreach ( $links as $platform => $url ) :
            $def = $platforms[ $platform ] ?? null;
            if ( ! $def ) continue;
        ?>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $def['label'] ); ?>" data-platform="<?php echo esc_attr( $platform ); ?>">
                <i class="<?php echo esc_attr( $def['icon'] ); ?>" aria-hidden="true"></i>
                <span class="gk-social-links__label"><?php echo esc_html( $def['label'] ); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php
}


/**
 * Get social links for the current context (KV or OV).
 *
 * Resolves to the OV's links if the current post belongs to one,
 * otherwise falls back to the Kreisverband's links from either
 * term meta or gk_kv_info option.
 *
 * @return array<string, string> Platform key => URL.
 */
function gk_get_context_social_links() {
    global $post;

    // Try OV/KV term meta first.
    $slug = isset( $post ) ? gk_get_post_zuordnung_slug( $post->ID ) : '';
    if ( empty( $slug ) ) {
        $slug = 'kreisverband';
    }

    $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
    if ( $term ) {
        $contact = gk_get_ov_contact( $term->term_id );
        $links   = gk_build_social_links( $contact );
        if ( ! empty( $links ) ) {
            return $links;
        }
    }

    // Fallback: KV-wide settings from setup wizard.
    $kv = get_option( 'gk_kv_info', array() );
    // Migrate old social_twitter key.
    if ( empty( $kv['social_x'] ) && ! empty( $kv['social_twitter'] ) ) {
        $kv['social_x'] = $kv['social_twitter'];
    }
    $kv_contact = array();
    $key_map = array(
        'social_instagram' => 'insta',
        'social_facebook'  => 'facebook',
        'social_x'         => 'x',
        'social_tiktok'    => 'tiktok',
        'social_threads'   => 'threads',
        'social_bluesky'   => 'bluesky',
        'social_mastodon'  => 'mastodon',
        'social_youtube'   => 'youtube',
    );
    foreach ( $key_map as $kv_key => $contact_key ) {
        if ( ! empty( $kv[ $kv_key ] ) ) {
            $kv_contact[ $contact_key ] = $kv[ $kv_key ];
        }
    }

    return gk_build_social_links( $kv_contact );
}


/**
 * Render the footer social bar.
 *
 * Big brand-colored icons, responsive. Uses context social links.
 */
function gk_footer_social_bar() {
    $links = gk_get_context_social_links();
    if ( empty( $links ) ) {
        return;
    }

    $platforms = gk_social_platforms();
    ?>
    <nav class="gk-social-links gk-social-links--footer" aria-label="Soziale Netzwerke">
        <?php foreach ( $links as $platform => $url ) :
            $def = $platforms[ $platform ] ?? null;
            if ( ! $def ) continue;
        ?>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $def['label'] ); ?>" data-platform="<?php echo esc_attr( $platform ); ?>" style="--brand-color: <?php echo esc_attr( $def['color'] ); ?>">
                <i class="<?php echo esc_attr( $def['icon'] ); ?>" aria-hidden="true"></i>
                <span class="gk-social-links__label"><?php echo esc_html( $def['label'] ); ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php
}
