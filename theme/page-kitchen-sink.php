<?php
/**
 * Template Name: Kitchen Sink
 *
 * Design system showcase with all components and dark mode toggle.
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="content">
<main id="main" class="site-main" role="main">
<div class="inner ks-page">

    <?php get_template_part( 'template-parts/kitchen-sink/controls' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/typography' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/spacings' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/colors' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/links' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/buttons' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/checkbox' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/radiobutton' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/input' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/select' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/switch' ); ?>
    <?php get_template_part( 'template-parts/kitchen-sink/shadows' ); ?>

</div><!-- .inner -->
</main>
</div><!-- #content -->

<script>
(function() {
    var toggle = document.getElementById('ks-dark-toggle');
    var body = document.body;

    // Restore preference
    if (localStorage.getItem('ks-dark') === '1') {
        body.classList.add('dark-mode');
        body.classList.remove('light-mode');
        toggle.checked = true;
    }

    toggle.addEventListener('change', function() {
        if (this.checked) {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            localStorage.setItem('ks-dark', '1');
        } else {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            localStorage.setItem('ks-dark', '0');
        }
    });

    // Tab switching
    document.querySelectorAll('.ks-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var tabId = this.getAttribute('data-tab');
            var section = this.closest('.ks-section');

            section.querySelectorAll('.ks-tab').forEach(function(t) {
                t.classList.remove('is-active');
                t.setAttribute('aria-selected', 'false');
            });
            section.querySelectorAll('.ks-tab-panel').forEach(function(p) {
                p.classList.remove('is-active');
            });

            this.classList.add('is-active');
            this.setAttribute('aria-selected', 'true');
            section.querySelector('[data-panel="' + tabId + '"]').classList.add('is-active');
        });
    });
})();
</script>

<?php get_footer(); ?>
