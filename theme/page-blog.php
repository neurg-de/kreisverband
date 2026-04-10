<?php
/**
 * Template Name: Artikel-Archiv
 *
 * Blog/news archive with pagination.
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

        <header class="page-header">
            <h1 class="page-title"><?php the_title(); ?></h1>
        </header>

        <?php
        $paged = max( 1, get_query_var( 'paged' ) );

        $blog_query = new WP_Query( array_merge( array(
            'post_type'      => 'post',
            'posts_per_page' => 10,
            'paged'          => $paged,
        ), gk_kv_query_args() ) );

        if ( $blog_query->have_posts() ) : ?>
            <div class="blog-archive inner">
            <?php while ( $blog_query->have_posts() ) : $blog_query->the_post(); ?>
                <?php get_template_part( 'template-parts/content-list' ); ?>
            <?php endwhile; ?>
            </div>

            <?php
            $big_query = $GLOBALS['wp_query'];
            $GLOBALS['wp_query'] = $blog_query;
            the_posts_pagination( array(
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ) );
            $GLOBALS['wp_query'] = $big_query;
            wp_reset_postdata();
            ?>

        <?php else : ?>
            <p>Keine Beiträge gefunden.</p>
        <?php endif; ?>

    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
