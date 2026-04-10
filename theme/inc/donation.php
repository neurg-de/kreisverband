<?php
/**
 * Donation System
 *
 * Built-in donation integration:
 *   - [spenden] shortcode for donation call-to-action
 *   - [spendenbalken] already in shortcodes.php (progress bar)
 *   - [twingle] shortcode for embedding Twingle donation forms
 *   - Donation page template support
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Settings ────────────────────────────────────────────────────────────────

function gk_donation_settings() {
    register_setting( 'gk_settings', 'gk_donation', array(
        'type'              => 'array',
        'sanitize_callback' => 'gk_sanitize_donation_settings',
        'default'           => array(),
    ) );
}
add_action( 'admin_init', 'gk_donation_settings' );

function gk_sanitize_donation_settings( $input ) {
    if ( ! is_array( $input ) ) return array();
    return array(
        'enabled'       => ! empty( $input['enabled'] ),
        'twingle_url'   => esc_url_raw( $input['twingle_url'] ?? '' ),
        'bank_name'     => sanitize_text_field( $input['bank_name'] ?? '' ),
        'iban'          => sanitize_text_field( $input['iban'] ?? '' ),
        'bic'           => sanitize_text_field( $input['bic'] ?? '' ),
        'paypal_url'    => esc_url_raw( $input['paypal_url'] ?? '' ),
        'purpose'       => sanitize_text_field( $input['purpose'] ?? '' ),
        'cta_text'      => sanitize_text_field( $input['cta_text'] ?? 'Jetzt spenden' ),
        'cta_subtext'   => sanitize_text_field( $input['cta_subtext'] ?? '' ),
    );
}

function gk_get_donation_config() {
    return wp_parse_args( get_option( 'gk_donation', array() ), array(
        'enabled'     => false,
        'twingle_url' => '',
        'bank_name'   => '',
        'iban'        => '',
        'bic'         => '',
        'paypal_url'  => '',
        'purpose'     => '',
        'cta_text'    => 'Jetzt spenden',
        'cta_subtext' => '',
    ) );
}


// ── Donate Page URL ────────────────────────────────────────────────────────

/**
 * Get the URL for "Jetzt spenden" buttons.
 *
 * Prefers the dedicated Spenden page (which embeds the Twingle widget).
 * Falls back to PayPal URL or empty string.
 */
function gk_get_donate_page_url( $config = null ) {
    if ( $config === null ) {
        $config = gk_get_donation_config();
    }

    // Look for a published page with slug "spenden"
    $page = get_page_by_path( 'spenden' );
    if ( $page && $page->post_status === 'publish' ) {
        return get_permalink( $page );
    }

    // Fallback to PayPal (external link, safe to open directly)
    if ( ! empty( $config['paypal_url'] ) ) {
        return $config['paypal_url'];
    }

    return '';
}


// ── Shortcodes ──────────────────────────────────────────────────────────────

/**
 * [spenden] — Donation call-to-action block with bank details and optional Twingle/PayPal.
 *
 * Attributes override defaults from settings.
 */
function gk_shortcode_spenden( $atts ) {
    $config = gk_get_donation_config();

    $atts = shortcode_atts( array(
        'titel'   => $config['cta_text'],
        'text'    => $config['cta_subtext'],
        'paypal'  => $config['paypal_url'],
        'twingle' => $config['twingle_url'],
    ), $atts );

    ob_start();
    ?>
    <div class="spenden-block">
        <h3><?php echo esc_html( $atts['titel'] ); ?></h3>

        <?php if ( $atts['text'] ) : ?>
            <p><?php echo esc_html( $atts['text'] ); ?></p>
        <?php endif; ?>

        <?php if ( $config['iban'] ) : ?>
        <div class="spenden-bank">
            <h4>Bankverbindung</h4>
            <table>
                <?php if ( $config['bank_name'] ) : ?>
                    <tr><th>Bank</th><td><?php echo esc_html( $config['bank_name'] ); ?></td></tr>
                <?php endif; ?>
                <tr><th>IBAN</th><td><?php echo esc_html( $config['iban'] ); ?></td></tr>
                <?php if ( $config['bic'] ) : ?>
                    <tr><th>BIC</th><td><?php echo esc_html( $config['bic'] ); ?></td></tr>
                <?php endif; ?>
                <?php if ( $config['purpose'] ) : ?>
                    <tr><th>Verwendungszweck</th><td><?php echo esc_html( $config['purpose'] ); ?></td></tr>
                <?php endif; ?>
            </table>
            <p class="spenden-hinweis"><small>Spenden an BÜNDNIS 90/DIE GRÜNEN sind steuerlich absetzbar.</small></p>
        </div>
        <?php endif; ?>

        <div class="spenden-buttons">
            <?php if ( $atts['twingle'] ) : ?>
                <a href="<?php echo esc_url( $atts['twingle'] ); ?>" class="button spenden-button spenden-twingle" target="_blank" rel="noopener noreferrer" aria-label="Online spenden (öffnet in neuem Fenster)">Online spenden</a>
            <?php endif; ?>
            <?php if ( $atts['paypal'] ) : ?>
                <a href="<?php echo esc_url( $atts['paypal'] ); ?>" class="button spenden-button spenden-paypal" target="_blank" rel="noopener noreferrer" aria-label="Mit PayPal spenden (öffnet in neuem Fenster)">PayPal</a>
            <?php endif; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'spenden', 'gk_shortcode_spenden' );


// [twingle] shortcode is provided by the WP Twingle plugin (wp-twingle).


// ── Settings Section (rendered in settings.php page) ────────────────────────

function gk_donation_settings_section() {
    $config = gk_get_donation_config();
    ?>
    <h2>Spenden</h2>
    <p class="description">Konfiguriere die Spendenmöglichkeiten für den Kreisverband.</p>
    <table class="form-table">
    <tr>
        <th><label for="gk_donation_enabled">Spenden aktiviert</label></th>
        <td><input type="checkbox" name="gk_donation[enabled]" id="gk_donation_enabled" value="1" <?php checked( $config['enabled'] ); ?> /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_twingle">Twingle URL</label></th>
        <td><input type="url" name="gk_donation[twingle_url]" id="gk_donation_twingle" value="<?php echo esc_attr( $config['twingle_url'] ); ?>" class="regular-text" placeholder="https://spenden.twingle.de/embed/..." /><br><span class="description">Vollständige Embed-URL aus Twingle, z.B. <code>https://spenden.twingle.de/embed/organisation/projekt/tw.../widget</code><br>Verwendung: <code>[twingle]</code> bettet das Formular als iFrame ein, <code>[spenden]</code> verlinkt es als Button.</span></td>
    </tr>
    <tr>
        <th><label for="gk_donation_bank">Bank</label></th>
        <td><input type="text" name="gk_donation[bank_name]" id="gk_donation_bank" value="<?php echo esc_attr( $config['bank_name'] ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_iban">IBAN</label></th>
        <td><input type="text" name="gk_donation[iban]" id="gk_donation_iban" value="<?php echo esc_attr( $config['iban'] ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_bic">BIC</label></th>
        <td><input type="text" name="gk_donation[bic]" id="gk_donation_bic" value="<?php echo esc_attr( $config['bic'] ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_purpose">Verwendungszweck</label></th>
        <td><input type="text" name="gk_donation[purpose]" id="gk_donation_purpose" value="<?php echo esc_attr( $config['purpose'] ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_paypal">PayPal URL</label></th>
        <td><input type="url" name="gk_donation[paypal_url]" id="gk_donation_paypal" value="<?php echo esc_attr( $config['paypal_url'] ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="gk_donation_cta">Button-Text</label></th>
        <td><input type="text" name="gk_donation[cta_text]" id="gk_donation_cta" value="<?php echo esc_attr( $config['cta_text'] ); ?>" class="regular-text" /></td>
    </tr>
    </table>
    <?php
}
