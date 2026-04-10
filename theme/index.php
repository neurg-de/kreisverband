<?php get_header(); ?>

<!-- aktuelles (index.php) -->
<section id="content"><div class="inner gk-layout clearfix">

    <?php if ( is_category() ) : ?>
        <div class="archive-title gk-layout__full">
            <h1><?php single_cat_title(); ?></h1>
            <?php echo category_description(); ?>
        </div>
    <?php elseif ( is_tag() ) : ?>
        <div class="archive-title gk-layout__full"><h1><?php single_tag_title(); ?></h1></div>
    <?php elseif ( is_author() ) : ?>
        <h1 class="archive-title gk-layout__full h2">
            <span><?php echo get_the_author_meta( 'display_name', get_queried_object_id() ); ?></span>
        </h1>
    <?php endif; ?>

    <h1 class="whitetext gk-layout__full">Aktuelles</h1>

    <div id="main" class="gk-layout__main first clearfix" role="main">
        <div class="list-article">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content-list', get_post_type() ); ?>
                <?php endwhile; ?>
        </div>

                <?php if ( function_exists( 'gk_page_navi' ) ) : ?>
                    <?php gk_page_navi(); ?>
                <?php else : ?>
                    <nav class="wp-prev-next">
                        <ul class="clearfix">
                            <li class="prev-link"><?php next_posts_link( '&laquo; Ältere Beiträge' ); ?></li>
                            <li class="next-link"><?php previous_posts_link( 'Neuere Beiträge &raquo;' ); ?></li>
                        </ul>
                    </nav>
                <?php endif; ?>

            <?php else : ?>
        </div>
                <article id="post-not-found" class="hentry clearfix">
                    <header class="article-header">
                        <h1>Nichts gefunden</h1>
                    </header>
                    <section class="entry-content">
                        <p>Leider wurde kein passender Beitrag gefunden.</p>
                    </section>
                </article>
            <?php endif; ?>

    </div> <!-- end #main -->

    <?php get_sidebar(); ?>
</div></section>

<?php get_footer(); ?>
