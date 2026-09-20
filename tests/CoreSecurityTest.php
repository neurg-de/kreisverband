<?php
/** Security regressions for legacy admin modules. */
class CoreSecurityTest extends WP_UnitTestCase {
    public function tear_down(): void {
        $_POST = array();
        $_GET = array();
        $_REQUEST = array();
        wp_set_current_user( 0 );
        parent::tear_down();
    }

    public function test_contact_save_verifies_nonce_and_validates_public_email() {
        $post = self::factory()->post->create( array( 'post_type' => 'person' ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
        update_post_meta( $post, 'kr8mb_pers_contact_email', 'old@example.org' );
        $_POST['kr8mb_pers_contact_email'] = 'new@example.org';
        gk_save_person_contact( $post );
        $this->assertSame( 'old@example.org', get_post_meta( $post, 'kr8mb_pers_contact_email', true ) );
        $_POST['gk_meta_box_nonce'] = wp_create_nonce( 'gk_meta_box_nonce' );
        $_POST['kr8mb_pers_contact_telefon'] = wp_slash( "O'Reilly" );
        gk_save_person_contact( $post );
        $this->assertSame( 'new@example.org', get_post_meta( $post, 'kr8mb_pers_contact_email', true ) );
        $this->assertSame( "O'Reilly", get_post_meta( $post, 'kr8mb_pers_contact_telefon', true ) );
        $_POST['kr8mb_pers_contact_email'] = 'invalid-email';
        gk_save_person_contact( $post );
        $this->assertSame( '', get_post_meta( $post, 'kr8mb_pers_contact_email', true ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
        $_POST['gk_meta_box_nonce'] = wp_create_nonce( 'gk_meta_box_nonce' );
        $_POST['kr8mb_pers_contact_email'] = 'unauthorized@example.org';
        gk_save_person_contact( $post );
        $this->assertSame( '', get_post_meta( $post, 'kr8mb_pers_contact_email', true ) );
    }

    public function test_department_meta_rejects_malformed_rows_and_preserves_apostrophes() {
        $post = self::factory()->post->create( array( 'post_type' => 'person' ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
        $_POST['gk_abteilung_meta_nonce'] = wp_create_nonce( 'gk_abteilung_meta_nonce' );
        $_POST['gk_abt_meta'] = wp_slash( array( 'vorstand' => array( 'function' => "O'Reilly <script>alert(1)</script>", 'position' => '2' ), 'bad' => 'malformed' ) );
        gk_save_abteilung_meta_box( $post );
        $meta = gk_get_abteilung_meta( $post );
        $this->assertSame( "O'Reilly", $meta['vorstand']['function'] );
        $this->assertSame( '2', $meta['vorstand']['position'] );
        $this->assertArrayNotHasKey( 'bad', $meta );
    }

    public function test_map_decoder_handles_invalid_input_and_preserves_geometry() {
        $this->assertNull( gk_sanitize_kreiskarte_json( array( 'unexpected' ) ) );
        $this->assertNull( gk_sanitize_kreiskarte_json( '{invalid' ) );
        $raw = array( '_meta' => array( 'viewBox' => '0 0 400 621' ), 'municipalities' => array( 'ov-test' => array( 'name' => '<script>alert(1)</script>Test', 'polygon' => '127.705 126.599', 'x' => 12.5, 'label' => array( 'text' => "O'Reilly" ) ) ) );
        $clean = gk_sanitize_kreiskarte_json( wp_json_encode( $raw ) );
        $this->assertSame( 'Test', $clean['municipalities']['ov-test']['name'] );
        $this->assertSame( '127.705 126.599', $clean['municipalities']['ov-test']['polygon'] );
        $this->assertSame( 12.5, $clean['municipalities']['ov-test']['x'] );
        $this->assertSame( "O'Reilly", $clean['municipalities']['ov-test']['label']['text'] );
    }

    public function test_wordpress_filesystem_map_writer_writes_exact_json_to_temporary_file() {
        $path = wp_tempnam( 'gk-map-test.json' );
        $json = wp_json_encode( array( 'municipalities' => array( 'name' => 'Test' ) ) );
        try {
            $this->assertSame( strlen( $json ), gk_write_kreiskarte_json( $path, $json ) );
            $this->assertSame( $json, file_get_contents( $path ) );
        } finally {
            unlink( $path );
        }
    }

    public function test_admin_seed_trigger_requires_nonce_before_any_mutation() {
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
        $_GET['gk_seed'] = '1';
        $before = wp_count_posts()->publish;
        $handler = static function () {
            return static function () { throw new RuntimeException( 'nonce rejected' ); };
        };
        add_filter( 'wp_die_handler', $handler );
        ob_start();
        try {
            gk_seed_admin_trigger();
            $this->fail( 'Missing nonce did not stop the seed operation.' );
        } catch ( RuntimeException $error ) {
            $this->assertSame( 'nonce rejected', $error->getMessage() );
        } finally {
            ob_end_clean();
            remove_filter( 'wp_die_handler', $handler );
        }
        $this->assertSame( $before, wp_count_posts()->publish );
    }

    public function test_textarea_escapes_once_without_changing_saved_story_markup() {
        $post = self::factory()->post->create( array( 'post_type' => 'page' ) );
        update_post_meta( $post, 'kr8mb_page_story_vz', 'A & B <li>Item</li>' );
        ob_start();
        gk_page_story_cb( get_post( $post ) );
        $html = ob_get_clean();
        $this->assertStringContainsString( 'A &amp; B &lt;li&gt;Item&lt;/li&gt;', $html );
        $this->assertStringNotContainsString( '&amp;amp;', $html );
    }
}
