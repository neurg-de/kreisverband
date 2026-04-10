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
function gk_social_share( $echo = true ) {
    if ( ! is_singular() ) return '';

    $raw_url = get_permalink();
    $title   = get_the_title();
    $post    = get_post();
    $raw_excerpt = $post->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 20 );

    ob_start();
    ?>
    <div class="gk-social-share">
        <span class="share-label">Teilen:</span>

        <a href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $raw_url ) ); ?>"
           target="_blank" rel="noopener noreferrer" class="share-facebook" title="Auf Facebook teilen">
            <span class="fa fa-facebook" aria-hidden="true"></span>
            <span class="sr-only">Facebook</span>
        </a>

        <a href="<?php echo esc_url( 'https://x.com/intent/post?url=' . rawurlencode( $raw_url ) . '&text=' . rawurlencode( $title ) ); ?>"
           target="_blank" rel="noopener noreferrer" class="share-x" title="Auf X teilen">
            <span class="fab fa-x-twitter" aria-hidden="true"></span>
            <span class="sr-only">X</span>
        </a>

        <a href="<?php echo esc_url( 'https://bsky.app/intent/compose?text=' . rawurlencode( $title . ' ' . $raw_url ) ); ?>"
           target="_blank" rel="noopener noreferrer" class="share-bluesky" title="Auf Bluesky teilen">
            <span aria-hidden="true">&#x1F58A;</span>
            <span class="sr-only">Bluesky</span>
        </a>

        <a href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $title . ' ' . $raw_url ) ); ?>"
           target="_blank" rel="noopener noreferrer" class="share-whatsapp" title="Per WhatsApp teilen">
            <span class="fa fa-whatsapp" aria-hidden="true"></span>
            <span class="sr-only">WhatsApp</span>
        </a>

        <a href="<?php echo esc_url( 'mailto:?subject=' . rawurlencode( $title ) . '&body=' . rawurlencode( $raw_excerpt . "\n\n" . $raw_url ) ); ?>"
           class="share-email" title="Per E-Mail teilen">
            <span class="fa fa-envelope" aria-hidden="true"></span>
            <span class="sr-only">E-Mail</span>
        </a>
    </div>
    <?php
    $output = ob_get_clean();

    if ( $echo ) {
        echo $output;
    }
    return $output;
}

// Backwards compatibility
function kr8_socialshare() { gk_social_share(); }


/**
 * Optionally auto-append share buttons to single post content.
 */
function gk_auto_social_share( $content ) {
    if ( ! is_singular( 'post' ) || ! is_main_query() ) return $content;

    $enabled = apply_filters( 'gk_auto_social_share', true );
    if ( ! $enabled ) return $content;

    return $content . gk_social_share( false );
}
add_filter( 'the_content', 'gk_auto_social_share', 20 );
