<?php
/**
 * Template Name: Einspaltig
 *
 * Full-width page without sidebar.
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area fullpage">
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
    <?php endwhile; ?>

    </main>
</div>

<?php get_footer(); ?>
