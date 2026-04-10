<?php
get_header();

global $post;

// Determine page hierarchy for sidebar navigation.
$ancestors    = get_post_ancestors( $post->ID );
$section_root = ! empty( $ancestors ) ? end( $ancestors ) : $post->ID;
$children     = get_pages( array( 'child_of' => $section_root, 'sort_column' => 'menu_order, post_title' ) );
$has_sidebar  = ! empty( $children );
$section_page = get_post( $section_root );
?>

<?php if ( $has_sidebar ) : ?>

<section id="content" class="subpage"><div class="inner subpage__grid">

    <nav role="navigation" class="subpage__nav">
        <a href="<?php echo esc_url( get_permalink( $section_root ) ); ?>" class="subpage__nav-home">
            <?php echo esc_html( get_the_title( $section_root ) ); ?>
        </a>
        <ul class="subpage-nav">
            <?php wp_list_pages( array(
                'child_of'  => $section_root,
                'title_li'  => '',
                'depth'     => 2,
            ) ); ?>
        </ul>
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

<?php else : ?>

<section id="content" class="subpage"><div class="inner">
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

<?php endif; ?>

<?php get_footer(); ?>
