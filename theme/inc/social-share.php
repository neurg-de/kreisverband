<?php
/**
 * Social Sharing
 *
 * Share buttons for posts and pages.
 * Template tag: gk_social_share()
 * Auto-display on single posts via filter.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Render social share buttons.
 *
 * @param bool $echo Whether to echo or return.
 */
function gk_social_share( $echo = true ) { // phpcs:ignore Universal.NamingConventions.NoReservedKeywordParameterNames.echoFound -- Preserve the public parameter name for PHP named-argument callers.
    if ( ! is_singular() ) {
		return '';
    }

    $raw_url     = get_permalink();
    $title       = get_the_title();
    $post        = get_post();
    $raw_excerpt = $post->post_excerpt ? $post->post_excerpt : wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 );

    ob_start();
    ?>
    <div class="gk-social-share">
        <span class="share-label">Teilen</span>

        <a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $raw_url ) ); ?>"
            target="_blank" rel="noopener noreferrer" class="share-facebook" title="Auf Facebook teilen">
            <span class="fa-brands fa-facebook-f" aria-hidden="true"></span>
            <span class="sr-only">Auf Facebook teilen</span>
        </a>

        <a href="<?php echo esc_url( 'https://x.com/intent/post?url=' . rawurlencode( $raw_url ) . '&text=' . rawurlencode( $title ) ); ?>"
            target="_blank" rel="noopener noreferrer" class="share-x" title="Auf X teilen">
            <span class="fa-brands fa-x-twitter" aria-hidden="true"></span>
            <span class="sr-only">Auf X teilen</span>
        </a>

        <a href="<?php echo esc_url( 'https://bsky.app/intent/compose?text=' . rawurlencode( $title . ' ' . $raw_url ) ); ?>"
            target="_blank" rel="noopener noreferrer" class="share-bluesky" title="Auf Bluesky teilen">
            <span class="fa-brands fa-bluesky" aria-hidden="true"></span>
            <span class="sr-only">Auf Bluesky teilen</span>
        </a>

        <a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $raw_url ) ); ?>"
            target="_blank" rel="noopener noreferrer" class="share-whatsapp" title="Per WhatsApp teilen">
            <span class="fa-brands fa-whatsapp" aria-hidden="true"></span>
            <span class="sr-only">Per WhatsApp teilen</span>
        </a>

        <a href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $raw_excerpt . "\n\n" . $raw_url ) ); ?>"
            class="share-email" title="Per E-Mail teilen">
            <span class="fa-solid fa-envelope" aria-hidden="true"></span>
            <span class="sr-only">Per E-Mail teilen</span>
        </a>
    </div>
    <?php
    $output = ob_get_clean();

    if ( $echo ) {
        echo wp_kses_post( $output );
    }
    return $output;
}

// Backwards compatibility.
/**
 * Socialshare.
 */
function kr8_socialshare() {
	gk_social_share(); }


/**
 * Optionally auto-append share buttons to single post content.
 *
 * @param string $content Content HTML.
 */
function gk_auto_social_share( $content ) {
    if ( ! is_singular( 'post' ) || ! is_main_query() ) {
		return $content;
    }

    $enabled = apply_filters( 'gk_auto_social_share', true );
    if ( ! $enabled ) {
		return $content;
    }

    return $content . gk_social_share( false );
}
add_filter( 'the_content', 'gk_auto_social_share', 20 );
