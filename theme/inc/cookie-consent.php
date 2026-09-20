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


/**
 * Cookie consent banner.
 */
function gk_cookie_consent_banner() {
    if ( is_admin() ) {
		return;
    }

    $page_id = gk_get_zuordnung_legal_page( gk_legal_context_slug(), 'datenschutz' );
    if ( ! $page_id ) {
        $page_id = gk_public_page_id( get_option( 'wp_page_for_privacy_policy' ) );
    }
    $privacy_url = $page_id ? get_permalink( $page_id ) : '';
    ?>
    <div id="gk-cookie-consent" class="gk-cookie-consent" role="dialog" aria-label="Cookie-Hinweis">
        <div class="gk-cookie-inner">
            <p>
                Diese Website verwendet ausschließlich technisch notwendige Cookies. Es werden keine Tracking- oder Marketing-Cookies eingesetzt.
                <?php if ( $privacy_url ) : ?>
                    Weitere Informationen findest du in unserer <a href="<?php echo esc_url( $privacy_url ); ?>">Datenschutzerklärung</a>.
                <?php endif; ?>
            </p>
            <details class="gk-cookie-details">
                <summary>Welche Cookies werden gesetzt?</summary>
                <ul>
                    <li><strong>gk_cookie_consent</strong> — Speichert, dass du diesen Hinweis bestätigt hast. Gültigkeit: 1 Jahr.</li>
                </ul>
            </details>
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


/**
 * [cookie_einstellungen] — Renders a link to revoke cookie consent and re-show the banner.
 *
 * Intended for the footer or privacy page.
 *
 * @param array $atts Atts.
 */
function gk_shortcode_cookie_einstellungen( $atts ) {
    $atts = shortcode_atts(
        array(
			'text' => 'Cookie-Einstellungen',
        ),
        $atts
    );

    return '<a href="#" class="gk-cookie-revoke" onclick="document.cookie=\'gk_cookie_consent=; path=/; max-age=0\'; location.reload(); return false;">'
        . esc_html( $atts['text'] )
        . '</a>';
}
add_shortcode( 'cookie_einstellungen', 'gk_shortcode_cookie_einstellungen' );
