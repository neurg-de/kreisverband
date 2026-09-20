<?php
/**
 * Contact and explicit newsletter-interest forms.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read a scalar, unslashed form value. Arrays are never accepted as text.
 *
 * @param string $key Field name.
 * @return string
 */
function gk_contact_value( $key ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Scalar reader only; each caller validates/sanitizes for its field, submission verifies nonce.
	return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : '';
}

/**
 * Resolve a published privacy page without linking to drafts or deleted pages.
 *
 * @return string
 */
function gk_contact_privacy_url() {
	$ids = array( gk_get_kv_info( 'datenschutz_page' ), get_option( 'wp_page_for_privacy_policy' ) );
	foreach ( $ids as $id ) {
		if ( gk_public_page_id( $id ) ) {
			return get_permalink( $id );
		}
	}
	return '';
}

/**
 * Render the existing contact shortcode or a newsletter-interest variant.
 *
 * @param array $atts Shortcode attributes.
 * @return string
 */
function gk_shortcode_kontaktformular( $atts ) {
	$atts        = shortcode_atts(
        array(
			'empfaenger' => '',
			'betreff'    => '',
			'newsletter' => false,
        ),
        $atts
    );
	$newsletter  = (bool) $atts['newsletter'];
	$mode        = $newsletter ? 'newsletter' : 'contact';
	$recipient   = $atts['empfaenger'] ? $atts['empfaenger'] : gk_get_kv_info( 'email' );
	$recipient   = $recipient ? $recipient : get_option( 'admin_email' );
	$recipient   = $newsletter ? 'info@gruene-starnberg.de' : $recipient;
	$privacy_url = gk_contact_privacy_url();
	$errors      = array();
	$success     = false;
	$submitted   = '1' === gk_contact_value( 'gk_contact_submit' ) && gk_contact_value( 'gk_contact_form_id' ) === $mode;
	// Bind a submission to this configured form; multiple shortcodes may share a page.
	$target = wp_hash( $mode . '|' . $recipient . '|' . $atts['betreff'] );
	if ( $submitted && ! hash_equals( $target, gk_contact_value( 'gk_contact_target' ) ) ) {
		$submitted = false;
	}
	if ( $submitted && ! empty( $GLOBALS['gk_contact_processed'][ $target ] ) ) {
		$submitted = false;
	}
	if ( $submitted ) {
		$GLOBALS['gk_contact_processed'][ $target ] = true;
	}
	$name    = $submitted ? sanitize_text_field( gk_contact_value( 'gk_contact_name' ) ) : '';
	$email   = $submitted ? trim( gk_contact_value( 'gk_contact_email' ) ) : '';
	$subject = $submitted ? sanitize_text_field( gk_contact_value( 'gk_contact_subject' ) ) : $atts['betreff'];
	$body    = $submitted ? sanitize_textarea_field( gk_contact_value( 'gk_contact_message' ) ) : '';

	if ( $submitted ) {
		if ( ! wp_verify_nonce( gk_contact_value( 'gk_contact_nonce' ), 'gk_contact_form_' . $mode ) ) {
			$errors[] = __( 'Sicherheitsprüfung fehlgeschlagen. Bitte lade die Seite neu und versuche es erneut.', 'neurg-kreisverband' );
		}
		if ( gk_contact_value( 'gk_website_url' ) ) {
			$errors[] = __( 'Die Anfrage konnte nicht verarbeitet werden.', 'neurg-kreisverband' );
		}
		if ( ! $newsletter && '' === $name ) {
			$errors[] = __( 'Bitte gib deinen Namen ein.', 'neurg-kreisverband' );
		}
		if ( ! is_email( $email ) || strlen( $email ) > 254 ) {
			$errors[] = __( 'Bitte gib eine gültige E-Mail-Adresse ein.', 'neurg-kreisverband' );
		}
		if ( ! $newsletter && '' === $body ) {
			$errors[] = __( 'Bitte gib eine Nachricht ein.', 'neurg-kreisverband' );
		}
		if ( strlen( $name ) > 200 || strlen( $subject ) > 200 || strlen( $body ) > 10000 ) {
			$errors[] = __( 'Die Eingabe ist zu lang. Bitte kürze Name, Betreff oder Nachricht.', 'neurg-kreisverband' );
		}
		if ( '1' !== gk_contact_value( 'gk_contact_privacy' ) || ( $newsletter && '1' !== gk_contact_value( 'gk_contact_newsletter' ) ) ) {
			$errors[] = __( 'Bitte bestätige die Datenschutzhinweise und die gewünschte Verarbeitung.', 'neurg-kreisverband' );
		}
		if ( ! $privacy_url || ! is_email( $recipient ) ) {
			$errors[] = __( 'Das Formular ist noch nicht vollständig eingerichtet. Bitte kontaktiere die Geschäftsstelle direkt.', 'neurg-kreisverband' );
		}
		if ( empty( $errors ) ) {
			$ip    = isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$key   = 'gk_contact_' . wp_hash( $ip );
			$count = (int) get_transient( $key );
			if ( $count >= 3 ) {
				$errors[] = __( 'Zu viele Nachrichten. Es sind maximal 3 Nachrichten pro Stunde erlaubt. Bitte versuche es später erneut.', 'neurg-kreisverband' );
			} else {
				set_transient( $key, $count + 1, HOUR_IN_SECONDS );
			}
		}
		if ( empty( $errors ) ) {
			$mail_subject = $newsletter ? __( 'Newsletter: Bitte um Kontaktaufnahme', 'neurg-kreisverband' ) : ( $subject ? $subject : __( 'Kontaktformular', 'neurg-kreisverband' ) );
			$mail_body    = "Name: $name\nE-Mail: $email\n\n";
			$mail_body   .= $newsletter
				? "Die Person bittet ausdrücklich um Kontaktaufnahme zur Newsletteraufnahme. Einwilligung zur Übermittlung an die Geschäftsstelle bestätigt. Noch kein Abonnement; Adresse und erforderliche Einwilligung vor Aufnahme prüfen.\n"
				: "Nachricht:\n$body\n";
			$success      = wp_mail( $recipient, $mail_subject, $mail_body, array( 'Reply-To: ' . $email, 'Content-Type: text/plain; charset=UTF-8' ) );
			if ( ! $success ) {
				$errors[] = __( 'Nachricht konnte nicht gesendet werden. Bitte versuche es später erneut.', 'neurg-kreisverband' );
			}
		}
	}

	$id = wp_unique_id( 'gk-contact-' );
	ob_start();
	if ( $success ) {
		$message = $newsletter
			? __( 'Deine Anfrage wurde an die Geschäftsstelle übermittelt. Dies ist noch kein Abonnement. Die Geschäftsstelle meldet sich zur Bestätigung.', 'neurg-kreisverband' )
			: __( 'Vielen Dank! Deine Nachricht wurde erfolgreich gesendet.', 'neurg-kreisverband' );
		echo '<div class="gk-contact-success" role="status" aria-live="polite"><p>' . esc_html( $message ) . '</p></div>';
		return ob_get_clean();
	}
	if ( $errors ) {
		echo '<div class="gk-contact-errors" role="alert" tabindex="-1">';
		foreach ( $errors as $error ) {
			echo '<p>' . esc_html( $error ) . '</p>';
		}
		echo '</div>';
	}
	?>
	<form method="post" class="gk-contact-form">
		<input type="hidden" name="gk_contact_nonce" value="<?php echo esc_attr( wp_create_nonce( 'gk_contact_form_' . $mode ) ); ?>" />
		<input type="hidden" name="gk_contact_submit" value="1" />
		<input type="hidden" name="gk_contact_form_id" value="<?php echo esc_attr( $mode ); ?>" />
		<input type="hidden" name="gk_contact_target" value="<?php echo esc_attr( $target ); ?>" />
		<div hidden aria-hidden="true"><label><?php esc_html_e( 'Bitte leer lassen', 'neurg-kreisverband' ); ?><input type="text" name="gk_website_url" tabindex="-1" autocomplete="off" /></label></div>
		<?php if ( ! $newsletter ) : ?>
		<p class="gk-field"><label for="<?php echo esc_attr( $id ); ?>name"><?php esc_html_e( 'Name *', 'neurg-kreisverband' ); ?></label><input id="<?php echo esc_attr( $id ); ?>name" name="gk_contact_name" value="<?php echo esc_attr( $name ); ?>" maxlength="200" autocomplete="name" required /></p>
		<?php endif; ?>
		<p class="gk-field"><label for="<?php echo esc_attr( $id ); ?>email"><?php esc_html_e( 'E-Mail *', 'neurg-kreisverband' ); ?></label><input type="email" id="<?php echo esc_attr( $id ); ?>email" name="gk_contact_email" value="<?php echo esc_attr( $email ); ?>" maxlength="254" autocomplete="email" required /></p>
		<?php if ( ! $newsletter ) : ?>
		<p class="gk-field"><label for="<?php echo esc_attr( $id ); ?>subject"><?php esc_html_e( 'Betreff', 'neurg-kreisverband' ); ?></label><input id="<?php echo esc_attr( $id ); ?>subject" name="gk_contact_subject" value="<?php echo esc_attr( $subject ); ?>" maxlength="200" /></p>
		<p class="gk-field"><label for="<?php echo esc_attr( $id ); ?>message"><?php esc_html_e( 'Nachricht *', 'neurg-kreisverband' ); ?></label><textarea id="<?php echo esc_attr( $id ); ?>message" name="gk_contact_message" rows="6" maxlength="10000" required><?php echo esc_textarea( $body ); ?></textarea></p>
		<?php else : ?>
		<p><?php esc_html_e( 'Wir übermitteln deine E-Mail-Adresse an info@gruene-starnberg.de. Die Geschäftsstelle prüft deine Anfrage und bestätigt mit dir die Aufnahme. Das Formular trägt dich nicht automatisch in einen Verteiler ein.', 'neurg-kreisverband' ); ?></p>
		<p><label><input type="checkbox" name="gk_contact_newsletter" value="1" required /> <?php esc_html_e( 'Ich möchte zum Newsletter kontaktiert werden und willige in die Übermittlung meiner E-Mail-Adresse an die Geschäftsstelle zu diesem Zweck ein. Diese Einwilligung kann ich dort jederzeit widerrufen. *', 'neurg-kreisverband' ); ?></label></p>
		<?php endif; ?>
		<p><label><input type="checkbox" name="gk_contact_privacy" value="1" required /> <?php esc_html_e( 'Ich habe den Datenschutzhinweis gelesen und stimme der Verarbeitung meiner Angaben zur Bearbeitung dieser Anfrage zu. *', 'neurg-kreisverband' ); ?></label>
		<?php if ( $privacy_url ) : ?>
		<a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Datenschutzerklärung', 'neurg-kreisverband' ); ?></a>
		<?php else : ?>
			<?php esc_html_e( 'Das Formular wird nach Einrichtung der Datenschutzerklärung freigeschaltet.', 'neurg-kreisverband' ); ?>
		<?php endif; ?></p>
		<p><button type="submit" class="gk-btn" <?php disabled( ! $privacy_url ); ?>><?php echo esc_html( $newsletter ? __( 'Newsletter anfragen', 'neurg-kreisverband' ) : __( 'Nachricht senden', 'neurg-kreisverband' ) ); ?></button></p>
	</form>
	<?php
	return ob_get_clean();
}
add_shortcode( 'kontaktformular', 'gk_shortcode_kontaktformular' );

/**
 * Newsletter interest uses the existing contact transport, without subscriptions.
 *
 * @return string
 */
function gk_shortcode_newsletter_anfrage() {
	return gk_shortcode_kontaktformular( array( 'newsletter' => true ) );
}
add_shortcode( 'newsletter_anfrage', 'gk_shortcode_newsletter_anfrage' );
