<?php
/**
 * Information and contact fallback for configured municipalities.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$ov_info = gk_ov_info_context();
if ( ! $ov_info ) {
    return;
}
$ov_contact       = $ov_info['term'] ? gk_get_ov_contact( $ov_info['term']->term_id ) : array();
$ov_email         = $ov_contact['email'] ?? '';
$ov_phone         = $ov_contact['telefon'] ?? '';
$ov_contact_is_kv = ! $ov_email && ! $ov_phone;
if ( $ov_contact_is_kv ) {
    $ov_email = gk_get_kv_info( 'email' );
    $ov_phone = gk_get_kv_info( 'phone' );
}
get_header();
?>
<main id="content" class="content-area">
    <article class="entry-content" style="max-width: 48rem; margin: 3rem auto; padding: 1.5rem;">
        <h1><?php echo esc_html( $ov_info['name'] ); ?></h1>
        <p><?php esc_html_e( 'Für diesen Ort ist derzeit noch keine eigene Website hinterlegt.', 'neurg-kreisverband' ); ?></p>
        <h2><?php esc_html_e( 'Kontakt und Mitmachen', 'neurg-kreisverband' ); ?></h2>
        <?php if ( $ov_contact_is_kv ) : ?>
            <p><?php esc_html_e( 'Die Geschäftsstelle hilft dir, die passende Ansprechperson vor Ort zu finden.', 'neurg-kreisverband' ); ?></p>
        <?php endif; ?>
        <?php if ( $ov_email ) : ?>
            <p><a href="mailto:<?php echo esc_attr( $ov_email ); ?>"><?php echo esc_html( $ov_email ); ?></a></p>
        <?php endif; ?>
        <?php if ( $ov_phone ) : ?>
            <p><a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $ov_phone ) ); ?>"><?php echo esc_html( $ov_phone ); ?></a></p>
        <?php endif; ?>
        <p><a class="gk-btn gk-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Zur Startseite', 'neurg-kreisverband' ); ?></a></p>
    </article>
</main>
<?php get_footer(); ?>
