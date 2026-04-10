<?php
/**
 * Template Name: Startseite
 *
 * Thin loader that includes the selected homepage variant.
 * Variants live in template-parts/home/ and are auto-discovered
 * by their file headers (@variant, @variant_name, etc.).
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area homepage">
    <main id="main" class="site-main" role="main">

        <?php get_template_part( 'template-parts/home/neue-energie' ); ?>

    </main>
</div>

<?php get_footer(); ?>
