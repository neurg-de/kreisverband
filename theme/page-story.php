<?php
/**
 * Template Name: Story
 *
 * Long-form story page with optional table of contents sidebar.
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area story-page">
    <main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post();
        $story_vz = get_post_meta( get_the_ID(), 'kr8mb_page_story_vz', true );
    ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="titelbild">
                    <?php the_post_thumbnail( 'titelbild' ); ?>
                </div>
            <?php endif; ?>

            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
            </header>

            <?php if ( $story_vz ) : ?>
                <nav class="inhaltvz">
                    <h3>Inhalt</h3>
                    <ul>
                        <?php echo wp_kses_post( $story_vz ); ?>
                    </ul>
                </nav>
            <?php endif; ?>

            <div class="entry-content inner">
                <?php the_content(); ?>
            </div>
        </article>

    <?php endwhile; ?>

    </main>
</div>

<?php get_footer(); ?>
