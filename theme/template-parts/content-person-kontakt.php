<?php
$amt     = get_post_meta( get_the_ID(), 'kr8mb_pers_pos_amt', true );
$email   = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_email', true );
$telefon = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_telefon', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> role="article">
    <?php if ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'thumbnail' ); ?>
    <?php endif; ?>

    <header class="article-header">
        <h3><?php the_title(); ?></h3>
    </header>

    <section class="entry-content">
        <?php if ( $amt ) : ?><p class="funktion"><?php echo esc_html( $amt ); ?></p><?php endif; ?>
        <?php if ( $email ) : ?><p><i class="fas fa-envelope"></i> <a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
        <?php if ( $telefon ) : ?><p><i class="fa fa-phone"></i> <?php echo esc_html( $telefon ); ?></p><?php endif; ?>
    </section>
</article>
