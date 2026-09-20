<?php
/** Contact and newsletter regression coverage. */
class ContactFormTest extends WP_UnitTestCase {
    private $mails = array();

    public function set_up() {
        parent::set_up();
        $_POST = array();
        $GLOBALS['gk_contact_processed'] = array();
        $_SERVER['REMOTE_ADDR'] = '192.0.2.' . wp_rand( 1, 254 );
        delete_transient( 'gk_contact_' . wp_hash( $_SERVER['REMOTE_ADDR'] ) );
        add_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10, 2 );
        $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
        update_option( 'wp_page_for_privacy_policy', $page );
    }

    public function tear_down() {
        $_POST = array();
        unset( $_SERVER['REMOTE_ADDR'] );
        remove_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10 );
        parent::tear_down();
    }

    public function capture_mail( $return, $args ) {
        $this->mails[] = $args;
        return true;
    }

    private function submit( $newsletter = false ) {
        $GLOBALS['gk_contact_processed'] = array();
        $mode = $newsletter ? 'newsletter' : 'contact';
        $recipient = $newsletter ? 'info@gruene-starnberg.de' : ( gk_get_kv_info( 'email' ) ?: get_option( 'admin_email' ) );
        $_POST = array(
            'gk_contact_submit' => '1',
            'gk_contact_form_id' => $mode,
            'gk_contact_target' => wp_hash( $mode . '|' . $recipient . '|' ),
            'gk_contact_nonce' => wp_create_nonce( 'gk_contact_form_' . $mode ),
            'gk_contact_name' => 'Test Person',
            'gk_contact_email' => 'test@example.org',
            'gk_contact_message' => 'Testnachricht',
            'gk_contact_privacy' => '1',
            'gk_contact_newsletter' => '1',
        );
    }

    public function test_form_uses_normal_submit_and_persistent_marker() {
        $html = do_shortcode( '[kontaktformular]' );
        $this->assertStringNotContainsString( 'this.form.submit()', $html );
        $this->assertStringContainsString( 'type="hidden" name="gk_contact_submit"', $html );
    }

    public function test_newsletter_requires_explicit_consent() {
        $this->submit( true );
        unset( $_POST['gk_contact_newsletter'] );
        $html = do_shortcode( '[newsletter_anfrage]' );
        $this->assertCount( 0, $this->mails );
        $this->assertStringContainsString( 'Bitte bestätige', $html );
    }

    public function test_newsletter_sends_only_interest_to_fixed_recipient() {
        $this->submit( true );
        $_POST['empfaenger'] = 'attacker@example.org';
        $html = do_shortcode( '[newsletter_anfrage]' );
        $this->assertCount( 1, $this->mails );
        $this->assertSame( 'info@gruene-starnberg.de', $this->mails[0]['to'] );
        $this->assertStringContainsString( 'noch kein Abonnement', $html );
    }

    public function test_missing_nonce_honeypot_and_array_input_do_not_send() {
        $this->submit();
        $_POST['gk_contact_nonce'] = array( 'bad' );
        do_shortcode( '[kontaktformular]' );
        $this->submit();
        $_POST['gk_website_url'] = 'bot';
        do_shortcode( '[kontaktformular]' );
        $this->submit();
        $_POST['gk_contact_email'] = array( 'bad' );
        do_shortcode( '[kontaktformular]' );
        $this->assertCount( 0, $this->mails );
    }

    public function test_rate_limit_and_form_isolation() {
        $this->submit( true );
        do_shortcode( '[kontaktformular]' );
        $this->assertCount( 0, $this->mails );
        for ( $i = 0; $i < 4; $i++ ) {
            $GLOBALS['gk_contact_processed'] = array();
            $html = do_shortcode( '[newsletter_anfrage]' );
        }
        $this->assertCount( 3, $this->mails );
        $this->assertStringContainsString( 'maximal 3', $html );
    }

    public function test_invalid_email_is_not_repaired_into_deliverable_address() {
        $this->submit();
        $_POST['gk_contact_email'] = "bad\r\nBcc: injected@example.org";
        do_shortcode( '[kontaktformular]' );
        $this->assertCount( 0, $this->mails );
    }

    public function test_mail_failure_has_accessible_error() {
        $this->submit();
        remove_filter( 'pre_wp_mail', array( $this, 'capture_mail' ), 10 );
        add_filter( 'pre_wp_mail', '__return_false' );
        $html = do_shortcode( '[kontaktformular]' );
        remove_filter( 'pre_wp_mail', '__return_false' );
        $this->assertStringContainsString( 'role="alert"', $html );
        $this->assertStringContainsString( 'nicht gesendet', $html );
    }

    public function test_duplicate_render_and_other_recipient_do_not_send_twice() {
        $this->submit();
        do_shortcode( '[kontaktformular]' );
        do_shortcode( '[kontaktformular]' );
        do_shortcode( '[kontaktformular empfaenger="other@example.org"]' );
        $this->assertCount( 1, $this->mails );
    }

    public function test_missing_published_privacy_page_disables_sending() {
        update_option( 'wp_page_for_privacy_policy', 0 );
        update_option( 'gk_kv_info', array() );
        $this->submit( true );
        $html = do_shortcode( '[newsletter_anfrage]' );
        $this->assertCount( 0, $this->mails );
        $this->assertStringContainsString( 'nicht vollständig eingerichtet', $html );
    }

    public function test_password_protected_privacy_page_is_not_public() {
        $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_password' => 'local-fixture' ) );
        update_option( 'gk_kv_info', array() );
        update_option( 'wp_page_for_privacy_policy', $page );
        $this->assertSame( '', gk_contact_privacy_url() );
        $this->submit( true );
        do_shortcode( '[newsletter_anfrage]' );
        $this->assertCount( 0, $this->mails );
    }
}
