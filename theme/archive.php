<?php get_header(); ?>

<section id="content"><div class="inner gk-layout clearfix">
    <div class="archive-title gk-layout__full">
        <h1><?php the_archive_title(); ?></h1>
        <?php the_archive_description( '<div class="archive-description">', '</div>' ); ?>
    </div>

    <div id="main" class="gk-layout__main first clearfix" role="main">
        <div class="list-article">
            <?php if ( have_posts() ) : ?>
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php get_template_part( 'template-parts/content-list', get_post_type() ); ?>
                <?php endwhile; ?>
        </div>

                <?php if ( function_exists( 'gk_page_navi' ) ) : ?>
                    <?php gk_page_navi(); ?>
                <?php endif; ?>

            <?php else : ?>
        </div>
                <p>Keine Beiträge gefunden.</p>
            <?php endif; ?>
    </div>

    <?php get_sidebar(); ?>
</div></section>

<?php get_footer(); ?>
