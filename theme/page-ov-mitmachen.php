<?php
/**
 * OV Mitmachen: ways to get involved, next events and contact of one area.
 * Route: /{ov-homepage}/mitmachen/ (see inc/ov-subpages.php).
 *
 * Reuses the OV homepage sections, so contact data and social links always
 * come from the area's own contact fields.
 *
 * @package Neurg_Kreisverband
 */

$context = gk_ov_subpage_context();
$ov_term = $context['term'];
$term_id = $ov_term->term_id;
$ov_slug = $ov_term->slug;
$config  = gk_ov_subpage_config( $term_id );
$contact = gk_get_ov_contact( $term_id );

$donation     = function_exists( 'gk_get_donation_config' ) ? gk_get_donation_config() : array();
$has_donation = ! empty( $donation['enabled'] );
$donate_url   = function_exists( 'gk_get_donate_page_url' ) ? gk_get_donate_page_url( $donation ) : '';
$donate_cta   = $donation['cta_text'] ?? 'Jetzt spenden';

$show_engage  = true;
$show_events  = true;
$show_contact = true;
$events_limit = 3;

get_header();
?>

<main id="main">
<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <p class="gk-hero__kicker"><?php echo esc_html( gk_get_ov_header( $term_id ) ); ?></p>
        <h1 class="gk-hero__title"><?php echo esc_html( $config['label_mitmachen'] ); ?></h1>
    </div>
</section>

<div class="home-gk ov-homepage ov-mitmachen">
    <?php if ( '' !== trim( $config['mitmachen_text'] ) ) : ?>
    <section class="ov-content">
        <div class="inner ov-content__body">
            <?php echo wp_kses_post( wpautop( make_clickable( esc_html( $config['mitmachen_text'] ) ) ) ); ?>
        </div>
    </section>
    <?php endif; ?>

    <?php
    // Fixed whitelist of homepage sections.
    foreach ( array( 'engage', 'events', 'contact' ) as $section ) {
        require GK_DIR . '/template-parts/ov-home/' . $section . '.php';
    }
    ?>
</div>
</main>

<?php get_footer(); ?>
