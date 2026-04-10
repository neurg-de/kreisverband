<?php
$nr        = get_post_meta( get_the_ID(), 'kr8mb_pers_pos_listenplatz', true );
$email     = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_email', true );
$www       = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_www', true );
$facebook  = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_facebook', true );
$twitter   = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_twitter', true );
$insta     = get_post_meta( get_the_ID(), 'kr8mb_pers_contact_insta', true );
$wahlkreis = get_post_meta( get_the_ID(), 'kr8mb_pers_pos_wahlkreis', true );
$details   = get_post_meta( get_the_ID(), 'kr8mb_pers_pos_details', true );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> role="article">
    <?php if ( has_post_thumbnail() ) : ?>
        <?php the_post_thumbnail( 'thumbnail' ); ?>
    <?php endif; ?>

    <?php if ( $nr ) : ?><p class="listenplatz"><?php echo esc_html( $nr ); ?></p><?php endif; ?>

    <header class="article-header">
        <h3><?php the_title(); ?></h3>
    </header>

    <section class="entry-content">
        <?php if ( $wahlkreis ) : ?><p class="short"><?php echo esc_html( $wahlkreis ); ?></p><?php endif; ?>
        <p class="contact">
            <?php if ( $www ) : ?><a href="<?php echo esc_url( $www ); ?>" title="Website" class="www" target="_blank"><i class="fa fa-home"></i></a><?php endif; ?>
            <?php if ( $facebook ) : ?><a href="<?php echo esc_url( $facebook ); ?>" title="Facebook" class="facebook" target="_blank"><i class="fab fa-facebook"></i></a><?php endif; ?>
            <?php if ( $insta ) : ?><a href="https://www.instagram.com/<?php echo esc_attr( $insta ); ?>" title="Instagram" class="instagram" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
            <?php if ( $twitter ) : ?><a href="https://x.com/<?php echo esc_attr( $twitter ); ?>" title="X" class="twitter" target="_blank"><i class="fab fa-x-twitter"></i></a><?php endif; ?>
            <?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>" title="E-Mail" class="email"><i class="fas fa-envelope"></i></a><?php endif; ?>
        </p>
        <?php if ( $details === 'yes' ) : ?><p class="details"><a href="<?php the_permalink(); ?>">Details &raquo;</a></p><?php endif; ?>
    </section>
</article>
