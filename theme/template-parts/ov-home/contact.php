<?php
/**
 * OV homepage section: contact.
 *
 * @package Neurg_Kreisverband
 */

$ov_social_links = $show_contact && $ov_term ? gk_build_social_links( $contact ) : array();
$has_contact     = $show_contact && $ov_term && (
    ! empty( $contact['anschrift'] ) || ! empty( $contact['email'] ) ||
    ! empty( $contact['telefon'] ) || ! empty( $contact['www'] ) ||
    ! empty( $ov_social_links )
);

if ( $has_contact ) :
	?>
<section class="gk-contact">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Kontakt</h2>
        </div>
        <div class="gk-contact__card">
            <?php if ( ! empty( $contact['anschrift'] ) || ! empty( $contact['telefon'] ) || ! empty( $contact['email'] ) || ! empty( $contact['www'] ) ) : ?>
            <div class="gk-contact__info">
                <h3><?php echo esc_html( $ov_term->name ); ?></h3>
                <address class="gk-contact__details">
                    <?php if ( ! empty( $contact['anschrift'] ) ) : ?>
                    <div class="gk-contact__item">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <span><?php echo nl2br( esc_html( $contact['anschrift'] ) ); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['telefon'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $contact['telefon'] ) ); ?>">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <span><?php echo esc_html( $contact['telefon'] ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <span><?php echo esc_html( $contact['email'] ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['www'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="<?php echo esc_url( $contact['www'] ); ?>" target="_blank" rel="noopener">
                            <i class="fas fa-globe" aria-hidden="true"></i>
                            <span><?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', rtrim( $contact['www'], '/' ) ) ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                </address>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $ov_social_links ) ) : ?>
            <div class="gk-contact__social">
                <h4 class="gk-contact__social-heading">Folge uns</h4>
                <?php gk_social_links_bar( $ov_social_links ); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
