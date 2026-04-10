<?php
/**
 * Cookie Consent Banner (GDPR / DSGVO)
 *
 * Simple, lightweight cookie consent — no plugin required.
 * Shows a banner on first visit, stores consent in a cookie.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function gk_cookie_consent_banner() {
    if ( is_admin() ) return;

    $privacy_url = '';
    if ( function_exists( 'gk_get_kv_info' ) ) {
        $page_id = gk_get_kv_info( 'datenschutz_page' );
        if ( $page_id ) $privacy_url = get_permalink( $page_id );
    }
    if ( ! $privacy_url ) {
        $page_id = get_option( 'wp_page_for_privacy_policy' );
        if ( $page_id ) $privacy_url = get_permalink( $page_id );
    }
    ?>
    <div id="gk-cookie-consent" class="gk-cookie-consent" role="dialog" aria-label="Cookie-Hinweis">
        <div class="gk-cookie-inner">
            <p>
                Diese Website verwendet technisch notwendige Cookies, um die bestmögliche Funktionalität zu gewährleisten.
                <?php if ( $privacy_url ) : ?>
                    Mehr dazu in unserer <a href="<?php echo esc_url( $privacy_url ); ?>">Datenschutzerklärung</a>.
                <?php endif; ?>
            </p>
            <button type="button" id="gk-cookie-accept" class="gk-cookie-accept">Verstanden</button>
        </div>
    </div>
    <script>
    (function() {
        if (document.cookie.indexOf('gk_cookie_consent=1') !== -1) return;
        var banner = document.getElementById('gk-cookie-consent');
        if (!banner) return;
        // Trigger slide-up after a short delay so the transition plays
        requestAnimationFrame(function() {
            requestAnimationFrame(function() {
                banner.classList.add('is-visible');
            });
        });
        // Stay above the sticky "Finde deinen Ortsverband" bar
        var stickyOv = document.querySelector('.gk-sticky-ov');
        if (stickyOv) {
            function adjustForStickyOv() {
                banner.style.bottom = stickyOv.classList.contains('is-visible') ? stickyOv.offsetHeight + 'px' : '';
            }
            new MutationObserver(adjustForStickyOv).observe(stickyOv, { attributes: true, attributeFilter: ['class'] });
            adjustForStickyOv();
        }
        var btn = document.getElementById('gk-cookie-accept');
        if (btn) btn.addEventListener('click', function() {
            document.cookie = 'gk_cookie_consent=1; path=/; max-age=' + (365*24*60*60) + '; SameSite=Lax';
            banner.classList.remove('is-visible');
            banner.addEventListener('transitionend', function() {
                banner.remove();
            }, { once: true });
        });
    })();
    </script>
    <?php
}
add_action( 'wp_footer', 'gk_cookie_consent_banner', 100 );
