<?php get_header(); ?>

<section id="content"><div class="inner gk-layout clearfix">
    <div class="archive-title gk-layout__main first">
        <h1>Suchergebnisse für: &ldquo;<?php echo esc_html( get_search_query() ); ?>&rdquo;</h1>
    </div>

    <div id="main" class="gk-layout__main first clearfix" role="main">
        <div class="list-article">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content-list', get_post_type() ); ?>
                <?php endwhile; ?>
        </div>
            <?php else : ?>
        </div>
                <article class="hentry clearfix">
                    <header class="article-header"><h2>Keine Ergebnisse</h2></header>
                    <section class="entry-content"><p>Zu diesem Suchbegriff wurden keine Ergebnisse gefunden.</p></section>
                </article>
            <?php endif; ?>
    </div>

    <?php get_sidebar(); ?>
</div></section>

<?php get_footer(); ?>
