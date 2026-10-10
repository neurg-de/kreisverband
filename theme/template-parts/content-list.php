<article id="post-<?php
/**
 * Content list template.
 *
 * @package Neurg_Kreisverband
 */

the_ID(); ?>" <?php post_class( 'clearfix gk-post-list' ); ?> role="article">

    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="gk-post-list__image" aria-hidden="true" tabindex="-1"><?php the_post_thumbnail( '350uncropped' ); ?></a>
    <?php endif; ?>

    <header class="gk-post-list__header">
        <time class="thedate" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </header>

    <section class="gk-post-list__excerpt">
        <?php the_excerpt(); ?>
        <a href="<?php the_permalink(); ?>" rel="bookmark" class="gk-btn gk-btn--primary">weiterlesen</a>
    </section>

</article>
