<?php
/**
 * Single post/person template within OV context.
 *
 * Mirrors page-OVsubpages.php: same grid, same navigation with the OV name on
 * top, same main column — so a post inside an OV reads like the OV's subpages.
 * gk_ov_single_template() already tags these requests with the
 * page-template-page-OVsubpages body class; this template makes the markup
 * match that promise. The OV contact sidebar sits below the navigation.
 *
 * @package Neurg_Kreisverband
 */

get_header();

global $post;
$ov_slug     = gk_get_post_zuordnung_slug( $post->ID );
$ov_term     = get_term_by( 'slug', $ov_slug, 'gk_zuordnung' );
$ov_header   = $ov_term ? gk_get_ov_header( $ov_term->term_id ) : '';
$homepage_id = $ov_term ? gk_get_ov_homepage_id( $ov_term->term_id ) : 0;
$ov_home     = $homepage_id ? get_permalink( $homepage_id ) : home_url( '/' );
?>

<section id="content" class="subpage ov-content"><div class="inner subpage__grid subpage__grid--sidebar">

    <nav role="navigation" class="subpage__nav">
        <?php if ( '' !== $ov_header ) : ?>
            <a href="<?php echo esc_url( $ov_home ); ?>" class="subpage__nav-home">
                <?php echo esc_html( $ov_header ); ?>
            </a>
        <?php endif; ?>
        <?php gk_ov_navi( $ov_slug ); ?>
    </nav>

    <div id="main" class="subpage__main gk-layout__main first clearfix" role="main">
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
