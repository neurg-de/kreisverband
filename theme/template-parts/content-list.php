<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix gk-post-list' ); ?> role="article">

    <?php if ( has_post_thumbnail() ) : ?>
        <a href="<?php the_permalink(); ?>" class="gk-post-list__image"><?php the_post_thumbnail( '350uncropped' ); ?></a>
    <?php endif; ?>

    <header class="gk-post-list__header">
        <span class="thedate"><?php the_date(); ?></span>
        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    </header>

    <section class="gk-post-list__excerpt">
        <?php the_excerpt(); ?>
        <a href="<?php the_permalink(); ?>" rel="bookmark" class="gk-btn gk-btn--primary">weiterlesen</a>
    </section>

</article>
