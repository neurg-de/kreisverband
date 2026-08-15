<?php
/**
 * Single post/person template within OV context.
 *
 * @package Neurg_Kreisverband
 */

get_header();

global $post;
$ov_slug = gk_get_post_zuordnung_slug( $post->ID );
?>

<section id="content" class="ov-content"><div class="inner ov-layout clearfix">

    <nav role="navigation" class="ovnavi-desktop">
        <?php gk_ov_navi( $ov_slug ); ?>
    </nav>

    <div id="main" class="gk-layout__main first clearfix" role="main">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <?php if ( get_post_type() === 'person' ) : ?>
                <?php get_template_part( 'template-parts/content-single-person-OV' ); ?>
            <?php else : ?>
                <?php get_template_part( 'template-parts/content-single-post-OV' ); ?>
            <?php endif; ?>

        <?php endwhile; endif; ?>
    </div>

    <?php get_sidebar(); ?>
</div></section>

<?php get_footer(); ?>
