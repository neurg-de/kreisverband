<?php
/**
 * Template Name: Landingpage
 *
 * Landing page with category/tag-filtered posts.
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area landingpage">
    <main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="titelbild">
                    <?php the_post_thumbnail( 'titelbild' ); ?>
                </div>
            <?php endif; ?>

            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <div class="entry-content inner">
                <?php the_content(); ?>
            </div>
        </article>

        <?php
        // Load posts filtered by meta-configured tags/categories
        $themen_id = get_post_meta( get_the_ID(), 'kr8mb_page_themen_id', true );
        $format_id = get_post_meta( get_the_ID(), 'kr8mb_page_format_id', true );

        if ( $themen_id || $format_id ) :
            $args = array(
                'post_type'      => 'post',
                'posts_per_page' => 12,
                'paged'          => max( 1, get_query_var( 'paged' ) ),
            );

            if ( $themen_id ) {
                $args['tag'] = $themen_id;
            }
            if ( $format_id ) {
                $args['category_name'] = $format_id;
            }

            $landing_query = new WP_Query( $args );

            if ( $landing_query->have_posts() ) :
        ?>
            <section class="landing-posts inner clearfix">
                <?php while ( $landing_query->have_posts() ) : $landing_query->the_post(); ?>
                    <?php get_template_part( 'template-parts/content-list' ); ?>
                <?php endwhile; ?>
            </section>

            <?php
            $big_query = $GLOBALS['wp_query'];
            $GLOBALS['wp_query'] = $landing_query;
            the_posts_pagination( array(
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ) );
            $GLOBALS['wp_query'] = $big_query;
            ?>

            <?php wp_reset_postdata(); ?>
        <?php endif; endif; ?>

    <?php endwhile; ?>

    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
