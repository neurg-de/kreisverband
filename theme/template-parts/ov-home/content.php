<?php
/**
 * OV homepage section: content.
 *
 * @package Neurg_Kreisverband
 */

rewind_posts();
while ( have_posts() ) :
	the_post();
    if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
		?>
<section class="ov-content">
    <div class="inner">
        <div class="ov-content__body entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
		<?php
    endif;
endwhile;
?>
