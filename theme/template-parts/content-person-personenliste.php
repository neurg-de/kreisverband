<?php
/**
 * Template Part: Person in OV list
 * Uses $gk_personenliste_typ global for type-aware display.
 *
 * @package Neurg_Kreisverband
 */

$gk_post_id = get_the_ID();
global $gk_personenliste_typ;
$typ = $gk_personenliste_typ;

// Resolve position/role based on type.
$amt = '';
if ( 'ovvorstand' === $typ ) {
    $amt = get_post_meta( $gk_post_id, 'kr8mb_pers_funktion1', true );
} elseif ( in_array( $typ, array( 'ba', 'stadtrat', 'gemeinderat' ), true ) ) {
    $amt = get_post_meta( $gk_post_id, 'kr8mb_pers_funktion2', true );
} else {
    $amt = get_post_meta( $gk_post_id, 'kr8mb_pers_pos_amt', true );
}

// List position for council lists.
$listenplatz      = get_post_meta( $gk_post_id, 'kr8mb_pers_pos_listenplatz', true );
$show_listenplatz = in_array( $typ, array( 'gemeinderatsliste', 'stadtratsliste' ), true ) && ! empty( $listenplatz );
if ( $show_listenplatz ) {
    $listenplatz = ltrim( $listenplatz, '0' );
}

$permalink = esc_url( get_permalink( $gk_post_id ) );
$gk_title  = esc_html( get_the_title( $gk_post_id ) );
?>
<article class="custom post-<?php echo esc_attr( $gk_post_id ); ?>" id="post-<?php echo esc_attr( $gk_post_id ); ?>">
    <?php if ( has_post_thumbnail( $gk_post_id ) ) : ?>
        <a class="postimglist" href="<?php echo esc_url( $permalink ); ?>"><?php echo get_the_post_thumbnail( $gk_post_id, '350uncropped', array( 'alt' => $gk_title ) ); ?></a>
    <?php else : ?>
        <a class="postimglist" href="<?php echo esc_url( $permalink ); ?>"><img src="<?php echo esc_url( GK_IMAGE_DIR . 'nopic.jpg' ); ?>" alt="Kein Foto vorhanden" /></a>
    <?php endif; ?>

    <h4><a href="<?php echo esc_url( $permalink ); ?>">
        <?php echo esc_html( $gk_title ); ?>
        <?php if ( $show_listenplatz ) : ?>
            <span class="listenplatz">(<?php echo esc_html( $listenplatz ); ?>)</span>
        <?php endif; ?>
    </a></h4>

    <?php if ( $amt ) : ?>
        <div class="amt"><?php echo esc_html( $amt ); ?></div>
    <?php endif; ?>
</article>
