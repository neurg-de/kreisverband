<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- Existing template filename is required for WordPress routing and saved page templates.
/**
 * Template Name: OV-Startseite
 *
 * Homepage for an Ortsverband / Ortsgruppe.
 *
 * Sections (all toggleable via admin settings):
 *  1. Hero (6 modes — same as KV homepage)
 *  2. Page content (from WP editor — optional)
 *  3. Team (person grid)
 *  4. Aktuelles (news card grid)
 *  5. Termine (event track)
 *  6. Contact (address, phone, email, social)
 *  7. Engagement CTAs
 *
 * @package Neurg_Kreisverband
 */

get_header();

global $post;
$ov_slug = gk_get_post_zuordnung_slug( $post->ID );
$ov_term = gk_get_ov_term( $ov_slug );
$ov_type = $ov_term ? gk_get_ov_type( $ov_term->term_id ) : 'ov';
$contact = $ov_term ? gk_get_ov_contact( $ov_term->term_id ) : array();
$term_id = $ov_term ? $ov_term->term_id : 0;

// Homepage settings.
$landing_mode  = gk_get_ov_homepage_option( $term_id, 'landing_mode', 'standard' );
$hero_title    = gk_get_ov_homepage_option( $term_id, 'hero_title' );
$hero_subtitle = gk_get_ov_homepage_option( $term_id, 'hero_subtitle' );
$hero_img_id   = (int) gk_get_ov_homepage_option( $term_id, 'hero_image', 0 );
$cta_label     = gk_get_ov_homepage_option( $term_id, 'cta_label' );
$cta_url       = gk_get_ov_homepage_option( $term_id, 'cta_url' );

$show_team    = true;
$show_news    = true;
$show_events  = true;
$show_contact = true;
$show_engage  = true;

$ov_header = gk_get_ov_header( $term_id );

$donation     = function_exists( 'gk_get_donation_config' ) ? gk_get_donation_config() : array();
$has_donation = ! empty( $donation['enabled'] );
$donate_url   = gk_get_donate_page_url( $donation );
$donate_cta   = $donation['cta_text'] ?? 'Jetzt spenden';

// Fallbacks from the page itself.
while ( have_posts() ) :
	the_post();
    if ( ! $hero_title ) {
		$hero_title = get_the_title();
    }
    if ( ! $hero_img_id && has_post_thumbnail() ) {
		$hero_img_id = get_post_thumbnail_id();
    }
endwhile;

// ════════════════════════════════════════════════════════════════════════════
// WERBUNG — Dedicated engagement landing page (exits early)
// ════════════════════════════════════════════════════════════════════════════
if ( 'werbung' === $ov_type ) :
    $gemeinde = $ov_term ? $ov_term->name : '';
    // Strip "Grüne in " or "OV " prefix for cleaner display.
    $gemeinde_clean = preg_replace( '/^(Gr(ü|ue)ne in |OV |Ortsverband |Ortsgruppe )/u', '', $gemeinde );
    $contact_email  = $contact['email'] ?? '';
    $kv_info        = get_option( 'gk_kv_info', array() );
    $kv_email       = $kv_info['email'] ?? '';
    $email_target   = ( $contact_email ? $contact_email : $kv_email );
    get_template_part(
        'template-parts/ov-werbung',
        null,
        array(
			'gemeinde'      => $gemeinde_clean,
			'hero_img_id'   => $hero_img_id,
			'hero_title'    => $hero_title,
			'hero_subtitle' => $hero_subtitle,
			'email'         => $email_target,
			'contact'       => $contact,
			'ov_header'     => $ov_header,
        )
    );
    get_footer();
    return;
endif;
?>

<div id="primary" class="content-area">
<main id="main" class="site-main" role="main">
<div class="home-gk ov-homepage ov-type-<?php echo esc_attr( $ov_type ); ?>">

<?php
$home_config = gk_ov_home_config( $term_id );
if ( $home_config['title'] ) {
    $hero_title = $home_config['title'];
}
// Filenames come exclusively from the known section whitelist.
foreach ( gk_ov_home_sections( $term_id ) as $section => $visible ) {
    if ( $visible && isset( gk_ov_section_labels()[ $section ] ) ) {
        require GK_DIR . '/template-parts/ov-home/' . $section . '.php';
    }
}
?>

</div><!-- .home-gk.ov-homepage -->
</main>
</div>



<?php get_footer(); ?>
