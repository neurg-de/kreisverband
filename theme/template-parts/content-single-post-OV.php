<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix' ); ?> role="article">

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="postpic"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high' ) ); ?></div>
    <?php endif; ?>

    <header class="article-header">
        <span class="thedate"><?php the_date(); ?></span>
        <h1 class="entry-title"><?php the_title(); ?></h1>
    </header>

    <section class="entry-content clearfix">
        <?php the_content(); ?>
    </section>

    <footer class="article-footer">
        <?php gk_social_share(); ?>
        <p class="tags"><?php the_tags( '<span class="tags-title">Tags:</span> ', ', ', '' ); ?></p>
    </footer>
</article>
