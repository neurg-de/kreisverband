<?php
/**
 * Template Name: Mach mit – Newsletteranfrage
 *
 * @package Neurg_Kreisverband
 */

get_header();
?>
<main id="main" class="site-main inner">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class(); ?>>
			<h1><?php the_title(); ?></h1>
			<?php the_content(); ?>
			<?php if ( ! has_shortcode( get_the_content(), 'newsletter_anfrage' ) ) : ?>
				<section aria-labelledby="gk-newsletter-title">
					<h2 id="gk-newsletter-title"><?php esc_html_e( 'Newsletter anfragen', 'neurg-kreisverband' ); ?></h2>
					<?php echo do_shortcode( '[newsletter_anfrage]' ); ?>
				</section>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
