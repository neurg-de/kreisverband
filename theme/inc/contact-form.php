<?php
/**
 * Built-in Contact Form
 *
 * Simple, secure contact form — no plugin required.
 *
 * Shortcode: [kontaktformular]
 *   empfaenger - Override recipient email (default: KV email from settings)
 *   betreff    - Default subject line
 *
 * Features:
 *   - Honeypot spam protection
 *   - Nonce verification
 *   - Rate limiting (max 3 submissions per IP per hour)
 *   - Sends via wp_mail()
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function gk_shortcode_kontaktformular( $atts ) {
    $atts = shortcode_atts( array(
        'empfaenger' => '',
        'betreff'    => '',
    ), $atts );

    // Default recipient: KV email from setup
    $recipient = $atts['empfaenger'];
    if ( empty( $recipient ) && function_exists( 'gk_get_kv_info' ) ) {
        $recipient = gk_get_kv_info( 'email' );
    }
    if ( empty( $recipient ) ) {
        $recipient = get_option( 'admin_email' );
    }

    // Process form submission
    $message  = '';
    $success  = false;
    $errors   = array();

    if ( isset( $_POST['gk_contact_submit'] ) ) {
        // Nonce check
        if ( ! isset( $_POST['gk_contact_nonce'] ) || ! wp_verify_nonce( $_POST['gk_contact_nonce'], 'gk_contact_form' ) ) {
            $errors[] = 'Sicherheitsprüfung fehlgeschlagen. Bitte versuche es erneut.';
        }

        // Honeypot
        if ( ! empty( $_POST['gk_website_url'] ) ) {
            // Bot detected — silently do nothing
            return '';
        }

        if ( ! $success && empty( $errors ) ) {
            $name    = sanitize_text_field( $_POST['gk_contact_name'] ?? '' );
            $email   = sanitize_email( $_POST['gk_contact_email'] ?? '' );
            $subject = sanitize_text_field( $_POST['gk_contact_subject'] ?? $atts['betreff'] );
            $body    = sanitize_textarea_field( $_POST['gk_contact_message'] ?? '' );
            $privacy = ! empty( $_POST['gk_contact_privacy'] );

            if ( empty( $name ) ) $errors[] = 'Bitte gib deinen Namen ein.';
            if ( empty( $email ) || ! is_email( $email ) ) $errors[] = 'Bitte gib eine gültige E-Mail-Adresse ein.';
            if ( empty( $body ) ) $errors[] = 'Bitte gib eine Nachricht ein.';
            if ( ! $privacy ) $errors[] = 'Bitte bestätige die Datenschutzerklärung.';

            // Rate limiting
            if ( empty( $errors ) ) {
                $ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );
                $transient_key = 'gk_contact_' . md5( $ip );
                $count = (int) get_transient( $transient_key );
                if ( $count >= 3 ) {
                    $errors[] = 'Zu viele Nachrichten. Es sind maximal 3 Nachrichten pro Stunde erlaubt. Bitte versuche es später erneut.';
                } else {
                    set_transient( $transient_key, $count + 1, HOUR_IN_SECONDS );
                }
            }

            if ( empty( $errors ) ) {
                $site_name = function_exists( 'gk_kv_short_name' ) ? gk_kv_short_name() : get_bloginfo( 'name' );
                $mail_subject = '[' . $site_name . '] ';
                $mail_subject .= $subject ?: 'Kontaktformular';

                $mail_body  = "Name: $name\n";
                $mail_body .= "E-Mail: $email\n";
                if ( $subject ) $mail_body .= "Betreff: $subject\n";
                $mail_body .= "\nNachricht:\n$body\n";

                $headers = array(
                    'Reply-To: ' . $name . ' <' . $email . '>',
                    'Content-Type: text/plain; charset=UTF-8',
                );

                $sent = wp_mail( $recipient, $mail_subject, $mail_body, $headers );
                $success = $sent;

                if ( ! $sent ) {
                    $errors[] = 'Nachricht konnte nicht gesendet werden. Bitte versuche es später erneut.';
                }
            }
        }
    }

    // Get privacy page link
    $privacy_url = '';
    if ( function_exists( 'gk_get_kv_info' ) ) {
        $privacy_page = gk_get_kv_info( 'datenschutz_page' );
        if ( $privacy_page ) {
            $privacy_url = get_permalink( $privacy_page );
        }
    }
    if ( ! $privacy_url ) {
        $privacy_page = get_option( 'wp_page_for_privacy_policy' );
        if ( $privacy_page ) {
            $privacy_url = get_permalink( $privacy_page );
        }
    }

    ob_start();

    if ( $success ) :
    ?>
        <div class="gk-contact-success" role="status" aria-live="polite">
            <p><strong>Vielen Dank!</strong> Deine Nachricht wurde erfolgreich gesendet.</p>
        </div>
    <?php else : ?>

        <?php if ( ! empty( $errors ) ) : ?>
            <div class="gk-contact-errors" role="alert" aria-live="polite">
                <?php foreach ( $errors as $error ) : ?>
                    <p><?php echo esc_html( $error ); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="gk-contact-form">
            <?php wp_nonce_field( 'gk_contact_form', 'gk_contact_nonce' ); ?>

            <!-- Honeypot -->
            <div style="display:none" aria-hidden="true">
                <input type="text" name="gk_website_url" tabindex="-1" autocomplete="off" />
            </div>

            <p class="gk-field">
                <label for="gk_contact_name">Name *</label>
                <input type="text" name="gk_contact_name" id="gk_contact_name" value="<?php echo esc_attr( $_POST['gk_contact_name'] ?? '' ); ?>" required />
            </p>

            <p class="gk-field">
                <label for="gk_contact_email">E-Mail *</label>
                <input type="email" name="gk_contact_email" id="gk_contact_email" value="<?php echo esc_attr( $_POST['gk_contact_email'] ?? '' ); ?>" required />
            </p>

            <p class="gk-field">
                <label for="gk_contact_subject">Betreff</label>
                <input type="text" name="gk_contact_subject" id="gk_contact_subject" value="<?php echo esc_attr( $_POST['gk_contact_subject'] ?? $atts['betreff'] ); ?>" />
            </p>

            <p class="gk-field">
                <label for="gk_contact_message">Nachricht *</label>
                <textarea name="gk_contact_message" id="gk_contact_message" rows="6" required><?php echo esc_textarea( $_POST['gk_contact_message'] ?? '' ); ?></textarea>
            </p>

            <p class="gk-field gk-field-checkbox">
                <input type="checkbox" name="gk_contact_privacy" id="gk_contact_privacy" value="1" required aria-required="true" />
                <label for="gk_contact_privacy">
                    Ich habe die
                    <?php if ( $privacy_url ) : ?>
                        <a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank">Datenschutzerklärung</a>
                    <?php else : ?>
                        Datenschutzerklärung
                    <?php endif; ?>
                    gelesen und stimme der Verarbeitung meiner Daten zu. *
                </label>
            </p>

            <p class="gk-field gk-field-submit">
                <button type="submit" name="gk_contact_submit" class="button" onclick="this.disabled=true;this.form.submit();">Nachricht senden</button>
            </p>
        </form>
    <?php
    endif;

    return ob_get_clean();
}
add_shortcode( 'kontaktformular', 'gk_shortcode_kontaktformular' );
