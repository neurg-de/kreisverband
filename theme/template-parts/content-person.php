<?php
/**
 * Template Part: Unified Person Display
 *
 * Replaces all variant-specific person templates with a single file.
 * Pass the variant via get_template_part's 3rd argument:
 *
 *   get_template_part( 'template-parts/content-person', null, array( 'variant' => 'team' ) );
 *
 * Variants: team, vorstand, mandat, kontakt, glv, landesliste, personenliste
 *
 * @package Neurg_Kreisverband
 */

$variant = $args['variant'] ?? 'team';
$post_id = get_the_ID();

// ── Gather all possible meta fields ─────────────────────────────────────────

$meta = array(
    'amt'          => get_post_meta( $post_id, 'kr8mb_pers_pos_amt', true ),
    'email'        => get_post_meta( $post_id, 'kr8mb_pers_contact_email', true ),
    'telefon'      => get_post_meta( $post_id, 'kr8mb_pers_contact_telefon', true ),
    'www'          => get_post_meta( $post_id, 'kr8mb_pers_contact_www', true ),
    'facebook'     => get_post_meta( $post_id, 'kr8mb_pers_contact_facebook', true ),
    'twitter'      => get_post_meta( $post_id, 'kr8mb_pers_contact_twitter', true ),
    'insta'        => get_post_meta( $post_id, 'kr8mb_pers_contact_insta', true ),
    'tiktok'       => get_post_meta( $post_id, 'kr8mb_pers_contact_tiktok', true ),
    'threads'      => get_post_meta( $post_id, 'kr8mb_pers_contact_threads', true ),
    'mastodon'     => get_post_meta( $post_id, 'kr8mb_pers_contact_mastodon', true ),
    'shortbio'     => get_post_meta( $post_id, 'kr8mb_pers_excerpt', true ),
    'details'      => get_post_meta( $post_id, 'kr8mb_pers_pos_details', true ),
    'wahlkreis'    => get_post_meta( $post_id, 'kr8mb_pers_pos_wahlkreis', true ),
    'listenplatz'  => get_post_meta( $post_id, 'kr8mb_pers_pos_listenplatz', true ),
    'funktion2'    => get_post_meta( $post_id, 'kr8mb_pers_funktion2', true ),
);

// ── Variant configuration ───────────────────────────────────────────────────

$link_thumb = in_array( $variant, array( 'team', 'personenliste' ), true );
$link_name  = in_array( $variant, array( 'team', 'personenliste' ), true );
$show_social = in_array( $variant, array( 'vorstand', 'mandat', 'glv', 'landesliste' ), true );
$show_details_link = in_array( $variant, array( 'vorstand', 'mandat', 'glv', 'landesliste' ), true );

// ── Social links (shared by several variants) ───────────────────────────────

$social_links = $show_social ? gk_build_social_links( $meta ) : array();

// ── Personenliste variant ───────────────────────────────────────────────────

if ( $variant === 'personenliste' ) :
    global $gk_personenliste_typ;
    $typ = $gk_personenliste_typ;

    // Resolve position/role based on type
    $amt = '';
    if ( $typ === 'ovvorstand' ) {
        $amt = get_post_meta( $post_id, 'kr8mb_pers_funktion1', true );
    } elseif ( in_array( $typ, array( 'ba', 'stadtrat', 'gemeinderat' ), true ) ) {
        $amt = get_post_meta( $post_id, 'kr8mb_pers_funktion2', true );
    } else {
        $amt = $meta['amt'];
    }

    // List position for council lists
    $listenplatz      = $meta['listenplatz'];
    $show_listenplatz = in_array( $typ, array( 'gemeinderatsliste', 'stadtratsliste' ), true ) && ! empty( $listenplatz );
    if ( $show_listenplatz ) {
        $listenplatz = ltrim( $listenplatz, '0' );
    }

    $permalink = esc_url( get_permalink( $post_id ) );
    $title     = esc_html( get_the_title( $post_id ) );
?>
<article class="gk-person gk-person--personenliste" id="post-<?php echo $post_id; ?>">
    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <a class="postimglist" href="<?php echo $permalink; ?>"><?php echo get_the_post_thumbnail( $post_id, '350uncropped', array( 'alt' => $title ) ); ?></a>
    <?php else : ?>
        <a class="postimglist" href="<?php echo $permalink; ?>"><img src="<?php echo esc_url( GK_IMAGE_DIR . 'nopic.jpg' ); ?>" alt="Kein Foto vorhanden" /></a>
    <?php endif; ?>

    <h4><a href="<?php echo $permalink; ?>">
        <?php echo $title; ?>
        <?php if ( $show_listenplatz ) : ?>
            <span class="listenplatz">(<?php echo esc_html( $listenplatz ); ?>)</span>
        <?php endif; ?>
    </a></h4>

    <?php if ( $amt ) : ?>
        <div class="amt"><?php echo esc_html( $amt ); ?></div>
    <?php endif; ?>
</article>

<?php
// ── Standard variants (team, vorstand, mandat, kontakt, glv, landesliste) ───

else : ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-person gk-person--' . esc_attr( $variant ) . ' clearfix' ); ?> role="article">
    <?php if ( has_post_thumbnail() ) : ?>
        <?php if ( $link_thumb ) : ?>
            <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
        <?php else : ?>
            <?php the_post_thumbnail( 'thumbnail' ); ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php // Landesliste: show listenplatz number above the name ?>
    <?php if ( $variant === 'landesliste' && $meta['listenplatz'] ) : ?>
        <p class="listenplatz"><?php echo esc_html( $meta['listenplatz'] ); ?></p>
    <?php endif; ?>

    <header class="article-header">
        <h3>
            <?php if ( $link_name ) : ?>
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php else : ?>
                <?php the_title(); ?>
            <?php endif; ?>
        </h3>
    </header>

    <section class="entry-content">
        <?php // Amt — shown by team, mandat, kontakt, glv ?>
        <?php if ( in_array( $variant, array( 'team', 'mandat', 'kontakt', 'glv' ), true ) && $meta['amt'] ) : ?>
            <p class="funktion"><?php echo esc_html( $meta['amt'] ); ?></p>
        <?php endif; ?>

        <?php // Wahlkreis — shown by mandat, landesliste ?>
        <?php if ( in_array( $variant, array( 'mandat', 'landesliste' ), true ) && $meta['wahlkreis'] ) : ?>
            <p class="short"><?php echo esc_html( $meta['wahlkreis'] ); ?></p>
        <?php endif; ?>

        <?php // Short bio — shown by team, vorstand, glv ?>
        <?php if ( in_array( $variant, array( 'team', 'vorstand', 'glv' ), true ) && $meta['shortbio'] ) : ?>
            <p class="short"><?php echo esc_html( $meta['shortbio'] ); ?></p>
        <?php endif; ?>

        <?php // Team: email + telefon as plain links ?>
        <?php if ( $variant === 'team' ) : ?>
            <?php if ( $meta['email'] ) : ?><p><a href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"><?php echo esc_html( $meta['email'] ); ?></a></p><?php endif; ?>
            <?php if ( $meta['telefon'] ) : ?><p><a href="tel:<?php echo esc_attr( $meta['telefon'] ); ?>"><?php echo esc_html( $meta['telefon'] ); ?></a></p><?php endif; ?>
        <?php endif; ?>

        <?php // Kontakt: email + telefon with icons ?>
        <?php if ( $variant === 'kontakt' ) : ?>
            <?php if ( $meta['email'] ) : ?><p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo esc_attr( $meta['email'] ); ?>"><?php echo esc_html( $meta['email'] ); ?></a></p><?php endif; ?>
            <?php if ( $meta['telefon'] ) : ?><p><i class="fa fa-phone"></i> <?php echo esc_html( $meta['telefon'] ); ?></p><?php endif; ?>
        <?php endif; ?>

        <?php // Social links — shared by vorstand, mandat, glv, landesliste ?>
        <?php if ( ! empty( $social_links ) ) : ?>
            <?php gk_social_links_bar( $social_links, array( 'class' => 'gk-social-links--inline' ) ); ?>
        <?php endif; ?>

        <?php // Details link — shown by vorstand, mandat, glv, landesliste ?>
        <?php if ( $show_details_link && $meta['details'] === 'yes' ) : ?>
            <p class="details"><a href="<?php the_permalink(); ?>">Details &raquo;</a></p>
        <?php endif; ?>
    </section>
</article>
<?php endif; ?>
