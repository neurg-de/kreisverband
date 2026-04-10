<?php
$amt      = get_post_meta( get_the_ID(), 'kr8mb_pers_pos_amt', true );
$email    = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_email', true );
$telefon  = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_telefon', true );
$shortbio = get_post_meta( get_the_ID(), 'kr8mb_pers_excerpt', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> role="article">
    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'thumbnail' ); ?></a>
    <?php endif; ?>

    <header class="article-header">
        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
    </header>

    <section class="entry-content">
        <?php if ( $amt ) : ?><p class="funktion"><?php echo esc_html( $amt ); ?></p><?php endif; ?>
        <?php if ( $shortbio ) : ?><p class="short"><?php echo esc_html( $shortbio ); ?></p><?php endif; ?>
        <?php if ( $email ) : ?><p><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
        <?php if ( $telefon ) : ?><p><a href="tel:<?php echo esc_attr( $telefon ); ?>"><?php echo esc_html( $telefon ); ?></a></p><?php endif; ?>
    </section>
</article>
