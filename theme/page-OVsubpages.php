<?php
/**
 * Template Name: OV-Unterseite
 *
 * Subpages within an Ortsverband section.
 *
 * @package Neurg_Kreisverband
 */

get_header();

global $post;
// Get zuordnung slug from top-level OV parent.
$ancestors   = get_post_ancestors( $post->ID );
$top_parent  = ! empty( $ancestors ) ? get_post( end( $ancestors ) ) : $post;
$ov_slug     = gk_get_post_zuordnung_slug( $top_parent->ID );
$ov_term     = get_term_by( 'slug', $ov_slug, 'gk_zuordnung' );
$ov_header   = $ov_term ? gk_get_ov_header( $ov_term->term_id ) : '';
$homepage_id = $ov_term ? gk_get_ov_homepage_id( $ov_term->term_id ) : 0;
$ov_home     = $homepage_id ? get_permalink( $homepage_id ) : home_url( '/' );
?>

<section id="content" class="subpage"><div class="inner subpage__grid">

    <nav role="navigation" class="subpage__nav">
        <a href="<?php echo esc_url( $ov_home ); ?>" class="subpage__nav-home">
            <?php echo esc_html( $ov_header ); ?>
        </a>
        <?php gk_ov_navi( $ov_slug ); ?>
    </nav>

    <div id="main" class="subpage__main" role="main">
        <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?> role="article">
                <?php if ( has_post_thumbnail() ) : ?>
                    <div class="subpage__hero"><?php the_post_thumbnail( 'large' ); ?></div>
                <?php endif; ?>

                <header class="article-header">
                    <h1 class="entry-title"><?php the_title(); ?></h1>
                </header>

                <section class="entry-content clearfix">
                    <?php the_content(); ?>
                </section>
            </article>

        <?php endwhile; endif; ?>
    </div>

</div></section>

<?php get_footer(); ?>
