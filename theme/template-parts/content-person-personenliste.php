<?php
/**
 * Template Part: Person in OV list
 * Uses $gk_personenliste_typ global for type-aware display.
 */

$post_id = get_the_ID();
global $gk_personenliste_typ;
$typ = $gk_personenliste_typ;

// Resolve position/role based on type
$amt = '';
if ( $typ === 'ovvorstand' ) {
    $amt = get_post_meta( $post_id, 'kr8mb_pers_funktion1', true );
} elseif ( in_array( $typ, array( 'ba', 'stadtrat', 'gemeinderat' ), true ) ) {
    $amt = get_post_meta( $post_id, 'kr8mb_pers_funktion2', true );
} else {
    $amt = get_post_meta( $post_id, 'kr8mb_pers_pos_amt', true );
}

// List position for council lists
$listenplatz      = get_post_meta( $post_id, 'kr8mb_pers_pos_listenplatz', true );
$show_listenplatz = in_array( $typ, array( 'gemeinderatsliste', 'stadtratsliste' ), true ) && ! empty( $listenplatz );
if ( $show_listenplatz ) {
    $listenplatz = ltrim( $listenplatz, '0' );
}

$permalink = esc_url( get_permalink( $post_id ) );
$title     = esc_html( get_the_title( $post_id ) );
?>
<article class="custom post-<?php echo $post_id; ?>" id="post-<?php echo $post_id; ?>">
    <?php if ( has_post_thumbnail( $post_id ) ) : ?>
        <a class="postimglist" href="<?php echo $permalink; ?>"><?php echo get_the_post_thumbnail( $post_id, '350uncropped', array( 'alt' => $title ) ); ?></a>
    <?php else : ?>
        <a class="postimglist" href="<?php echo $permalink; ?>"><img src="<?php echo esc_url( GK_IMAGE_DIR . 'nopic.jpg' ); ?>" alt="" /></a>
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
