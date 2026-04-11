
    <footer id="footer" class="site-footer" role="contentinfo">

        <div class="footer-social">
            <div class="inner">
                <?php gk_footer_social_bar(); ?>
            </div>
        </div>

        <?php if ( is_active_sidebar( 'fussleiste' ) ) : ?>
        <div class="footer-widgets">
            <div class="inner">
                <ul class="footer-widgets-grid">
                    <?php dynamic_sidebar( 'fussleiste' ); ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <?php $verbaende = gk_get_verbaende(); if ( ! empty( $verbaende ) ) : ?>
        <div class="footer-verbaende">
            <div class="inner">
                <ul class="footer-verbaende-list">
                    <?php foreach ( $verbaende as $v ) : ?>
                        <li><a href="<?php echo esc_url( $v['url'] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $v['name'] ); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>

        <div class="footer-bottom">
            <div class="inner">
                <?php
                // Detect zuordnung context (same pattern as header.php).
                global $post;
                $gk_footer_slug = isset( $post ) ? gk_get_post_zuordnung_slug( $post->ID ) : '';
                if ( empty( $gk_footer_slug ) ) {
                    $gk_footer_slug = 'kreisverband';
                }
                ?>
                <nav class="footer-nav" role="navigation" aria-label="Footer-Navigation">
                    <?php gk_nav_footer( $gk_footer_slug ); ?>
                </nav>
                <ul class="footer-legal">
                    <?php
                    // Check for zuordnung-specific legal pages, fall back to KV.
                    $impressum_id   = gk_get_zuordnung_legal_page( $gk_footer_slug, 'impressum' );
                    $datenschutz_id = gk_get_zuordnung_legal_page( $gk_footer_slug, 'datenschutz' );
                    if ( $impressum_id && get_post_status( $impressum_id ) === 'publish' ) :
                    ?>
                        <li><a href="<?php echo esc_url( get_permalink( $impressum_id ) ); ?>">Impressum</a></li>
                    <?php endif; ?>
                    <?php if ( $datenschutz_id && get_post_status( $datenschutz_id ) === 'publish' ) : ?>
                        <li><a href="<?php echo esc_url( get_permalink( $datenschutz_id ) ); ?>">Datenschutz</a></li>
                    <?php endif; ?>
                </ul>
                <?php
                if ( function_exists( 'gk_taurus_render_footer_taurus' ) ) {
                    gk_taurus_render_footer_taurus();
                }
                ?>
                <p class="footer-copyright">
                    &copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>
                </p>
            </div>
        </div>

    </footer>

    <a href="#header" class="back-to-top" title="Zum Seitenanfang" aria-label="Zum Seitenanfang">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
    </a>

    <?php wp_footer(); ?>

</body>
</html>
