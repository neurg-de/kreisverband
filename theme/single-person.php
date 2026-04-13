<?php
/**
 * Single Person Profile Page
 *
 * Redesigned from a voter UX perspective:
 * 1. Instant recognition — large photo + name + role
 * 2. Personal connection — short bio / personal statement
 * 3. Easy contact — prominent email, phone, social links
 * 4. Deeper engagement — full bio content
 * 5. Navigation — back to team/department context
 *
 * @package Neurg_Kreisverband
 */

get_header();

$post_id = get_the_ID();

// ── Meta fields ────────────────────────────────────────────────────────────
$amt       = get_post_meta( $post_id, 'kr8mb_pers_pos_amt', true );
$shortbio  = get_post_meta( $post_id, 'kr8mb_pers_excerpt', true );
$email     = get_post_meta( $post_id, 'kr8mb_pers_contact_email', true );
$telefon   = get_post_meta( $post_id, 'kr8mb_pers_contact_telefon', true );
$www       = get_post_meta( $post_id, 'kr8mb_pers_contact_www', true );
$anschrift = get_post_meta( $post_id, 'kr8mb_pers_contact_anschrift', true );
$wahlkreis = get_post_meta( $post_id, 'kr8mb_pers_pos_wahlkreis', true );

$contact = array(
    'facebook'  => get_post_meta( $post_id, 'kr8mb_pers_contact_facebook', true ),
    'twitter'   => get_post_meta( $post_id, 'kr8mb_pers_contact_twitter', true ),
    'insta'     => get_post_meta( $post_id, 'kr8mb_pers_contact_insta', true ),
    'tiktok'    => get_post_meta( $post_id, 'kr8mb_pers_contact_tiktok', true ),
    'threads'   => get_post_meta( $post_id, 'kr8mb_pers_contact_threads', true ),
    'mastodon'  => get_post_meta( $post_id, 'kr8mb_pers_contact_mastodon', true ),
);
$social_links = gk_build_social_links( $contact );

// ── Organizational context ─────────────────────────────────────────────────
$ov_slug = gk_get_post_zuordnung_slug( $post_id );
$ov_term = ( $ov_slug && $ov_slug !== 'kreisverband' ) ? gk_get_ov_term( $ov_slug ) : false;

// Department(s)
$departments = wp_get_post_terms( $post_id, 'abteilung' );
if ( is_wp_error( $departments ) ) {
    $departments = array();
}

// ── Check if there's actual editor content ─────────────────────────────────
$has_content = trim( get_the_content() ) !== '';

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-profile' ); ?>>

    <?php // ── Hero section: photo + identity ──────────────────────────────── ?>
    <header class="gk-profile__header">
        <div class="gk-profile__header-inner inner">

            <?php if ( has_post_thumbnail() ) : ?>
                <div class="gk-profile__photo">
                    <?php the_post_thumbnail( 'medium', array( 'class' => 'gk-profile__img', 'loading' => 'eager' ) ); ?>
                </div>
            <?php endif; ?>

            <div class="gk-profile__identity">
                <h1 class="gk-profile__name"><?php the_title(); ?></h1>

                <?php if ( $amt ) : ?>
                    <p class="gk-profile__role"><?php echo esc_html( $amt ); ?></p>
                <?php endif; ?>

                <?php // Organizational context badges ?>
                <div class="gk-profile__context">
                    <?php if ( $ov_term ) : ?>
                        <span class="gk-profile__badge">
                            <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                            <?php echo esc_html( $ov_term->name ); ?>
                        </span>
                    <?php endif; ?>

                    <?php if ( $wahlkreis ) : ?>
                        <span class="gk-profile__badge">
                            <i class="fas fa-landmark" aria-hidden="true"></i>
                            <?php echo esc_html( $wahlkreis ); ?>
                        </span>
                    <?php endif; ?>

                    <?php foreach ( $departments as $dept ) : ?>
                        <a href="<?php echo esc_url( gk_abteilung_url( $dept, $ov_slug ?: 'kreisverband' ) ); ?>" class="gk-profile__badge">
                            <?php echo esc_html( $dept->name ); ?>
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if ( $shortbio ) : ?>
                    <p class="gk-profile__shortbio"><?php echo esc_html( $shortbio ); ?></p>
                <?php endif; ?>
            </div>

        </div>
    </header>

    <?php // ── Contact bar ─────────────────────────────────────────────────── ?>
    <?php if ( $email || $telefon || $www || ! empty( $social_links ) ) : ?>
    <section class="gk-profile__contact" aria-label="Kontakt">
        <div class="gk-profile__contact-inner inner">

            <?php if ( $email || $telefon || $www ) : ?>
                <div class="gk-profile__contact-direct">
                    <?php if ( $email ) : ?>
                        <a href="mailto:<?php echo esc_attr( $email ); ?>" class="gk-profile__contact-link" aria-label="E-Mail an <?php the_title(); ?> schreiben">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <span>E-Mail schreiben</span>
                        </a>
                    <?php endif; ?>

                    <?php if ( $telefon ) : ?>
                        <a href="tel:<?php echo esc_attr( $telefon ); ?>" class="gk-profile__contact-link" aria-label="<?php the_title(); ?> anrufen">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <span><?php echo esc_html( $telefon ); ?></span>
                        </a>
                    <?php endif; ?>

                    <?php if ( $www ) : ?>
                        <a href="<?php echo esc_url( $www ); ?>" class="gk-profile__contact-link" target="_blank" rel="noopener noreferrer" aria-label="Website von <?php the_title(); ?> besuchen">
                            <i class="fas fa-globe" aria-hidden="true"></i>
                            <span>Website</span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $social_links ) ) : ?>
                <?php gk_social_links_bar( $social_links, array( 'label' => esc_attr( get_the_title() ) . ' auf Social Media' ) ); ?>
            <?php endif; ?>

        </div>
    </section>
    <?php endif; ?>

    <?php // ── Content body ────────────────────────────────────────────────── ?>
    <?php if ( $has_content || $anschrift ) : ?>
    <section class="gk-profile__body">
        <div class="gk-profile__body-inner inner">

            <?php if ( $has_content ) : ?>
                <div class="gk-profile__content entry-content">
                    <?php the_content(); ?>
                </div>
            <?php endif; ?>

            <?php if ( $anschrift ) : ?>
                <div class="gk-profile__address">
                    <h3><i class="fas fa-location-dot" aria-hidden="true"></i> Adresse</h3>
                    <p><?php echo nl2br( esc_html( $anschrift ) ); ?></p>
                </div>
            <?php endif; ?>

        </div>
    </section>
    <?php endif; ?>

</article>

<?php get_footer(); ?>
