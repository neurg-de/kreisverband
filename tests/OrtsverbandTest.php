<?php
/**
 * OV links, contact data and legal-page regressions.
 *
 * @package Neurg_Kreisverband
 */

/** Regression coverage for public OV configuration. */
class OrtsverbandTest extends WP_UnitTestCase {
    public function test_fallback_page_uses_configured_contact_and_rejects_unknown_places() {
        $id = $this->ov();
        $term = get_term( $id );
        update_option( 'gk_kv_info', array( 'email' => 'kontakt@example.org', 'phone' => '01234 567890' ) );
        $this->go_to( gk_ov_info_url( $term->slug ) );
        $this->assertSame( $term->name, gk_ov_info_context()['name'] );
        $this->assertStringContainsString( $term->name, gk_ov_info_document_title( '' ) );
        ob_start();
        include GK_DIR . '/page-ov-info.php';
        $html = ob_get_clean();
        $this->assertStringContainsString( 'mailto:kontakt@example.org', $html );
        $this->assertStringContainsString( 'tel:01234567890', $html );
        $this->go_to( gk_ov_info_url( 'not-a-configured-place' ) );
        $this->assertNull( gk_ov_info_context() );
    }
    public function test_public_website_rejects_placeholders_and_malformed_urls() {
        foreach ( array( '#', 'https://example.com/', 'https://www.example.com/path', 'https://example.org/', 'https://example.net/', 'javascript:alert(1)', '//www.gruene.de/', 'https://', 'https://user:password@www.gruene.de/', 'https://www.gruene.de/#' ) as $url ) {
            $this->assertSame( '', gk_public_website_url( $url ), $url );
        }
        $this->assertSame( 'https://www.gruene.de/', gk_public_website_url( 'https://www.gruene.de/' ) );
    }

    public function test_municipality_without_page_is_not_a_false_link() {
        $data = gk_get_kreiskarte_data();
        $slug = array_key_first( $data['municipalities'] );
        $this->assertSame( '', gk_get_municipality_url( $slug, array( 'type' => 'ov' ), array() ) );
        $this->assertStringContainsString( 'Im Aufbau', gk_render_kreiskarte_responsive() );
    }
    /**
     * Create an OV fixture.
     *
     * @param string $type Stored OV type.
     * @return int Term ID.
     */
    private function ov( $type = 'ov' ) {
        $id = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung' ) );
        update_term_meta( $id, '_gk_ov_type', $type );
        return $id;
    }

    /**
     * Verify local homepage precedes external website.
     */
    public function test_local_homepage_precedes_external_website() {
        $id   = $this->ov();
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        update_term_meta( $id, '_gk_homepage_id', $page );
        update_term_meta( $id, '_gk_contact_www', 'https://external.example.org/' );
        $html = gk_shortcode_ortsverband_liste( array() );
        $this->assertStringContainsString( esc_url( get_permalink( $page ) ), $html );
        $this->assertStringNotContainsString( 'external.example.org', $html );
    }

    /**
     * Verify external website is fallback for each existing type.
     */
    public function test_external_website_is_fallback_for_each_existing_type() {
        foreach ( array( 'ov', 'ortsgruppe', 'full' ) as $type ) {
            $id = $this->ov( $type );
            update_term_meta( $id, '_gk_contact_www', 'https://' . $type . '.example.org/' );
        }
        $html = gk_shortcode_ortsverband_liste( array() );
        foreach ( array( 'ov', 'ortsgruppe', 'full' ) as $type ) {
            $this->assertStringContainsString( 'href="https://' . $type . '.example.org/"', $html );
        }
    }

    /**
     * Verify draft homepage falls back and legacy type never links empty.
     */
    public function test_draft_homepage_falls_back_and_legacy_type_never_links_empty() {
        $id = $this->ov( 'full' );
        $this->assertStringNotContainsString( 'href=""', gk_shortcode_ortsverband_liste( array() ) );
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'draft',
            )
        );
        update_term_meta( $id, '_gk_homepage_id', $page );
        update_term_meta( $id, '_gk_contact_www', 'https://fallback.example.org/' );
        $this->assertStringContainsString( 'https://fallback.example.org/', gk_shortcode_ortsverband_liste( array() ) );
    }

    /**
     * Verify placeholder does not link even with website.
     */
    public function test_placeholder_does_not_link_even_with_website() {
        $id = $this->ov( 'werbung' );
        update_term_meta( $id, '_gk_contact_www', 'https://placeholder.example.org/' );
        $html = gk_shortcode_ortsverband_liste( array() );
        $this->assertStringContainsString( 'Im Aufbau', $html );
        $this->assertStringNotContainsString( 'placeholder.example.org', $html );
    }

    /**
     * Verify invalid public email is never displayed.
     */
    public function test_invalid_public_email_is_never_displayed() {
        $id = $this->ov();
        update_term_meta( $id, '_gk_contact_email', 'invalid-address' );
        $this->assertSame( '', gk_get_ov_contact( $id )['email'] );
        update_option( 'gk_kv_info', array( 'email' => 'invalid-address' ) );
        $this->assertSame( '', gk_get_kv_info( 'email' ) );
    }

    /**
     * Verify legal pages use published pages and safe kv fallback.
     */
    public function test_legal_pages_use_published_pages_and_safe_kv_fallback() {
        $id   = $this->ov();
        $slug = get_term( $id )->slug;
        $own  = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        $kv   = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        foreach ( array( 'impressum', 'datenschutz' ) as $type ) {
            update_option( 'gk_kv_info', array( $type . '_page' => $kv ) );
            $this->assertSame( $kv, gk_get_zuordnung_legal_page( $slug, $type ) );
            update_term_meta( $id, '_gk_' . $type . '_page', $own );
            $this->assertSame( $own, gk_get_zuordnung_legal_page( $slug, $type ) );
            wp_update_post(
                array(
					'ID'          => $own,
					'post_status' => 'draft',
                )
            );
            $this->assertSame( $kv, gk_get_zuordnung_legal_page( $slug, $type ) );
            wp_update_post(
                array(
					'ID'          => $kv,
					'post_status' => 'draft',
                )
            );
            $this->assertSame( 0, gk_get_zuordnung_legal_page( $slug, $type ) );
            wp_update_post(
                array(
					'ID'          => $own,
					'post_status' => 'publish',
                )
            );
            wp_update_post(
                array(
					'ID'          => $kv,
					'post_status' => 'publish',
                )
            );
        }
        $post = self::factory()->post->create( array( 'post_status' => 'publish' ) );
        update_term_meta( $id, '_gk_datenschutz_page', $post );
        $this->assertSame( $kv, gk_get_zuordnung_legal_page( $slug, 'datenschutz' ) );
        $this->assertSame( 0, gk_get_zuordnung_legal_page( $slug, 'homepage_id' ) );
    }
    /**
     * Verify legacy full mode alias and unsafe urls.
     */
    public function test_legacy_full_mode_alias_and_unsafe_urls() {
        $id = $this->ov( 'full' );
        update_term_meta( $id, '_gk_contact_www', 'https://legacy.example.org/?x=1&y=2' );
        foreach ( array( 'ov', 'full' ) as $mode ) {
            $this->assertStringContainsString( esc_url( 'https://legacy.example.org/?x=1&y=2' ), gk_shortcode_ortsverband_liste( array( 'mode' => $mode ) ) );
        }
        update_term_meta( $id, '_gk_contact_www', 'javascript:alert(1)' );
        $this->assertSame( '', gk_get_ov_url( $id ) );
        $this->assertStringNotContainsString( 'href=""', gk_shortcode_ortsverband_liste( array() ) );
        $this->assertSame( '', gk_public_website_url( 'mailto:test@example.org' ) );
        $this->assertSame( '', gk_public_website_url( array() ) );
    }

    /**
     * Verify contact save validation and legacy read validation.
     */
    public function test_contact_save_validation_and_legacy_read_validation() {
        $id = $this->ov();
        update_term_meta( $id, '_gk_contact_email', ' info@example.org ' );
        $this->assertSame( 'info@example.org', get_term_meta( $id, '_gk_contact_email', true ) );
        update_term_meta( $id, '_gk_contact_email', "info@example.org\r\nBcc: evil@example.org" );
        $this->assertSame( '', get_term_meta( $id, '_gk_contact_email', true ) );
        remove_filter( 'sanitize_term_meta__gk_contact_email_for_gk_zuordnung', 'gk_validate_public_email' );
        update_term_meta( $id, '_gk_contact_email', 'legacy-invalid' );
        gk_register_public_contact_meta();
        $this->assertSame( '', gk_get_ov_contact( $id )['email'] );
        $clean = gk_sanitize_kv_info( array( 'email' => 'valid@example.org' ) );
        $this->assertSame( 'valid@example.org', $clean['email'] );
        $clean = gk_sanitize_kv_info( array( 'email' => 'bad@example' ) );
        $this->assertSame( '', $clean['email'] );
    }

    /**
     * Verify legal context inherits parent and cookie notice uses ov page.
     */
    public function test_legal_context_inherits_parent_and_cookie_notice_uses_ov_page() {
        $id     = $this->ov();
        $slug   = get_term( $id )->slug;
        $parent = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        wp_set_object_terms( $parent, array( $id ), 'gk_zuordnung' );
        $child = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_parent' => $parent,
            )
        );
        wp_set_object_terms( $child, array(), 'gk_zuordnung' );
        $privacy = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        update_term_meta( $id, '_gk_datenschutz_page', $privacy );
        $this->go_to( get_permalink( $child ) );
        $this->assertSame( $slug, gk_legal_context_slug() );
        ob_start();
        gk_cookie_consent_banner();
        $html = ob_get_clean();
        $this->assertStringContainsString( esc_url( get_permalink( $privacy ) ), $html );
        wp_update_post(
            array(
				'ID'            => $privacy,
				'post_password' => 'private',
            )
        );
        $this->assertSame( 0, gk_get_zuordnung_legal_page( $slug, 'datenschutz' ) );
    }

    /**
     * Verify admin rejects bad email before changing ov.
     */
    public function test_admin_rejects_bad_email_before_changing_ov() {
        $id    = $this->ov();
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        wp_get_current_user()->add_cap( 'gk_manage_ov' );
        $original_name = get_term( $id )->name;
        $_POST         = array(
            'gk_ov_save_nonce' => wp_create_nonce( 'gk_ov_save' ),
            'term_id'          => $id,
			'ov_name'          => 'Must not save',
			'ov_contact_email' => 'invalid',
        );
        try {
            gk_ortsverband_handle_save();
            $this->fail( 'Invalid email must reject the save.' );
        } catch ( WPDieException $exception ) {
            $this->assertStringContainsString( 'gültige öffentliche', $exception->getMessage() );
            $this->assertSame( $original_name, get_term( $id )->name );
        } finally {
            $_POST = array();
            wp_set_current_user( 0 );
        }
    }

    /**
     * Verify admin legal selectors offer explicit kv fallback.
     */
    public function test_admin_legal_selectors_offer_explicit_kv_fallback() {
        $id    = $this->ov();
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        $_GET['term_id'] = $id;
        ob_start();
        gk_ortsverband_edit_page();
        $html = ob_get_clean();
        unset( $_GET['term_id'] );
        wp_set_current_user( 0 );
        $this->assertStringContainsString( 'KV-Impressum verwenden', $html );
        $this->assertStringContainsString( 'KV-Datenschutz verwenden', $html );
        $this->assertStringContainsString( 'aria-describedby="ov_contact_email_help"', $html );
    }

    /**
     * Verify related ov shortcodes and map use external fallback.
     */
    public function test_related_ov_shortcodes_and_map_use_external_fallback() {
        $id = $this->ov( 'full' );
        update_term_meta( $id, '_gk_contact_www', 'https://legacy-map.example.org/' );
        foreach ( array( 'gliederungen', 'arbeitsgemeinschaften' ) as $shortcode ) {
            $html = do_shortcode( '[' . $shortcode . ' type="full"]' );
            $this->assertStringContainsString( 'href="https://legacy-map.example.org/"', $html );
            $this->assertStringNotContainsString( 'href=""', $html );
        }
        $terms = gk_get_ov_terms_by_slug();
        $this->assertSame( 'https://legacy-map.example.org/', $terms[ get_term( $id )->slug ]->homepage_url );
    }
    /**
     * Reject migration requests without a nonce before touching assignments.
     */
    public function test_migration_requires_nonce_without_php_warnings() {
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        delete_option( 'gk_zuordnung_migrated' );
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Preserve the test request before exercising a missing-nonce request.
        $request = $_GET;
        $_GET    = array( 'gk_run_zuordnung_migration' => '1' );
        ob_start();
        gk_zuordnung_migration_notice();
        $html = ob_get_clean();
        $_GET = $request;
        wp_set_current_user( 0 );
        $this->assertFalse( get_option( 'gk_zuordnung_migrated' ) );
        $this->assertStringContainsString( 'Jetzt zuordnen', $html );
        $this->assertStringNotContainsString( 'abgeschlossen', $html );
    }

    /**
     * Store normal apostrophes from WordPress-slashed admin form values.
     */
    public function test_ov_save_unslashes_text_before_storage() {
        $id    = $this->ov();
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        wp_get_current_user()->add_cap( 'gk_manage_ov' );
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Save and restore the test fixture request around the nonce-checked handler.
        $request       = $_POST;
        $_POST         = wp_slash(
            array(
				'gk_ov_save_nonce' => wp_create_nonce( 'gk_ov_save' ),
				'term_id'          => $id,
				'ov_name'          => "OV O'Connor",
				'ov_header'        => "Grüne O'Connor",
				'ov_contact_email' => 'public@example.org',
            )
        );
        $stop_redirect = static function () {
            throw new RuntimeException( 'Test intercepted redirect.' );
        };
        add_filter( 'wp_redirect', $stop_redirect );
        try {
            gk_ortsverband_handle_save();
            $this->fail( 'A successful save redirects.' );
        } catch ( RuntimeException $exception ) {
            $this->assertSame( 'Test intercepted redirect.', $exception->getMessage() );
            $this->assertSame( "OV O'Connor", get_term( $id )->name );
            $this->assertSame( "Grüne O'Connor", get_term_meta( $id, '_gk_ov_header', true ) );
        } finally {
            remove_filter( 'wp_redirect', $stop_redirect );
            $_POST = $request;
            wp_set_current_user( 0 );
        }
    }
    /**
     * Resolve an existing canonical OV page without writing homepage metadata.
     */
    public function test_canonical_legacy_page_precedes_external_and_rejects_other_scope() {
        $this->set_permalink_structure( '/%postname%/' );
        $id   = $this->ov();
        $term = get_term( $id );
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $term->slug,
            )
        );
        wp_set_object_terms( $page, array( $id ), 'gk_zuordnung' );
        update_post_meta( $page, '_wp_page_template', 'page-OV.php' );
        update_term_meta( $id, '_gk_contact_www', 'https://external.example.org/' );
        $this->assertSame( get_permalink( $page ), gk_get_ov_url( $id ) );
        $this->assertSame( '', get_term_meta( $id, '_gk_homepage_id', true ) );
        $this->go_to( home_url( '/' . $term->slug . '/' ) );
        $this->assertTrue( is_page( $page ) );
        $this->assertSame( $term->slug, gk_legal_context_slug() );
        $other = $this->ov();
        wp_set_object_terms( $page, array( $other ), 'gk_zuordnung' );
        $this->assertSame( 'https://external.example.org/', gk_get_ov_url( $id ) );
        wp_set_object_terms( $page, array( $id ), 'gk_zuordnung' );
        wp_update_post(
            array(
				'ID'          => $page,
				'post_status' => 'draft',
            )
        );
        $this->assertSame( 'https://external.example.org/', gk_get_ov_url( $id ) );
        wp_update_post(
            array(
				'ID'          => $page,
				'post_status' => 'publish',
            )
        );
        wp_set_object_terms( $page, array(), 'gk_zuordnung' );
        $this->assertSame( get_permalink( $page ), gk_get_ov_url( $id ) );
        $this->assertSame( $term->slug, gk_get_post_zuordnung_slug( $page ) );
        wp_update_post(
            array(
				'ID'            => $page,
				'post_password' => 'not-public',
            )
        );
        $this->assertSame( 'https://external.example.org/', gk_get_ov_url( $id ) );
    }

    /**
     * Use identical real targets in the SVG and mobile list, with no fake links.
     */
    public function test_actual_map_and_mobile_list_share_local_external_and_missing_targets() {
        $data           = gk_get_kreiskarte_data();
        $municipalities = array_filter(
            $data['municipalities'],
            static function ( $item ) {
				return 'ov' === $item['type'];
			}
        );
        $slugs          = array_slice( array_keys( $municipalities ), 0, 3 );
        $this->assertCount( 3, $slugs );
        $ids = array();
        foreach ( $slugs as $slug ) {
            $ids[] = self::factory()->term->create(
                array(
					'taxonomy' => 'gk_zuordnung',
					'slug'     => $slug,
                )
            );
        }
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post_name'   => $slugs[0],
            )
        );
        wp_set_object_terms( $page, array( $ids[0] ), 'gk_zuordnung' );
        update_post_meta( $page, '_wp_page_template', 'page-OV.php' );
        update_term_meta( $ids[1], '_gk_contact_www', 'https://external-map.example.org/' );
        $html     = gk_render_kreiskarte_responsive();
        $previous = libxml_use_internal_errors( true );
        $document = new DOMDocument();
        $document->loadHTML( '<?xml encoding="utf-8" ?>' . $html );
        libxml_clear_errors();
        libxml_use_internal_errors( $previous );
        $xpath = new DOMXPath( $document );
        foreach ( array( get_permalink( $page ), 'https://external-map.example.org/' ) as $index => $url ) {
            $nodes = $xpath->query( '//a[@data-ov-slug="' . $slugs[ $index ] . '"]' );
            $this->assertSame( 2, $nodes->length, 'Both SVG and list must provide a real link.' );
            foreach ( $nodes as $node ) {
                $this->assertSame( $url, $node->getAttribute( 'href' ) );
            }
        }
        $this->assertSame( 0, $xpath->query( '//a[@data-ov-slug="' . $slugs[2] . '"]' )->length );
        $placeholders = $xpath->query( '//*[@data-ov-slug="' . $slugs[2] . '"]' );
        $this->assertSame( 2, $placeholders->length );
        $placeholder = $placeholders->item( 0 );
        $this->assertNotNull( $placeholder );
        // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase -- DOMNode provides this standard read-only property.
        $this->assertStringContainsString( 'Im Aufbau', $placeholder->textContent );
        $this->assertFalse( $placeholder->hasAttribute( 'tabindex' ) );
        $this->assertStringNotContainsString( 'href="#"', $html );
        $this->assertStringNotContainsString( 'role="img"', $html );
    }
    /**
     * Re-check current privileges when an old administrator nonce is reused.
     */
    public function test_downgraded_ov_admin_cannot_delete_foreign_term() {
        $own     = $this->ov();
        $foreign = $this->ov();
        $user    = self::factory()->user->create(
            array(
				'role'       => 'administrator',
				'user_login' => get_term( $own )->slug,
            )
        );
        wp_set_current_user( $user );
        $nonce = wp_create_nonce( 'gk_ov_delete' );
        wp_get_current_user()->set_role( 'gk_ovadmin' );
        // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Theme capability is registered in roles.php; this assertion reproduces the downgraded role boundary.
        $this->assertTrue( current_user_can( 'gk_manage_ov' ) );
        $this->assertNotFalse( wp_verify_nonce( $nonce, 'gk_ov_delete' ) );
        $_POST         = array(
			'gk_ov_delete_nonce' => $nonce,
			'term_id'            => $foreign,
		);
        $stop_redirect = static function () {
			throw new RuntimeException( 'Test intercepted redirect.' );
		};
        add_filter( 'wp_redirect', $stop_redirect );
        try {
            gk_ortsverband_handle_delete();
        } catch ( RuntimeException $exception ) {
            $this->assertSame( 'Test intercepted redirect.', $exception->getMessage() );
        } finally {
            remove_filter( 'wp_redirect', $stop_redirect );
            $_POST = array();
            wp_set_current_user( 0 );
        }
        $this->assertInstanceOf( WP_Term::class, get_term( $foreign, 'gk_zuordnung' ) );
    }
    /** Map actions determine the public destination without hidden fallbacks. */
    public function test_map_configuration_rejects_unsafe_and_inactive_targets() {
        $this->assertSame( 'link', gk_municipality_link_type( home_url( '/ov-fixture/' ), 'link' ) );
        $this->assertSame( 'ov', gk_municipality_link_type( 'https://external.example.org/', 'ov' ) );
        $this->assertSame( 'keine', gk_municipality_link_type( '', 'ov' ) );
        $this->assertSame( 'keine', gk_municipality_link_type( '', 'keine' ) );
        $this->assertSame(
            '',
            gk_get_municipality_url(
                'fixture',
                array(
					'type' => 'link',
					'link' => 'javascript:alert(1)',
                ),
                array()
            )
        );
        $this->assertSame(
            'https://external.example.org/',
            gk_get_municipality_url(
                'fixture',
                array(
					'type' => 'link',
					'link' => 'https://external.example.org/',
                ),
                array()
            )
        );
        $term = $this->ov( 'werbung' );
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        update_term_meta( $term, '_gk_homepage_id', $page );
        $slug = get_term( $term )->slug;
        $this->assertSame( get_permalink( $page ), gk_get_ov_url( $term ) );
        $ov_data = gk_get_ov_terms_by_slug();
        $this->assertSame( get_permalink( $page ), gk_get_municipality_url( $slug, array( 'type' => 'ov' ), $ov_data ) );
        $this->assertSame( '', gk_get_municipality_url( $slug, array( 'type' => 'keine' ), $ov_data ) );
        $this->assertSame( gk_ov_info_url( $slug ), gk_get_municipality_url( $slug, array( 'type' => 'werbung' ), $ov_data ) );
        $this->assertSame( '', gk_get_municipality_url( 'fixture', array( 'type' => 'ov' ), array() ) );
        $this->assertSame( '', gk_get_municipality_url( 'fixture', array( 'type' => 'keine' ), array() ) );
    }

    /** Saved map settings take precedence over bundled defaults and affect list and SVG together. */
    public function test_saved_map_actions_control_list_and_svg() {
        $data = gk_get_kreiskarte_data();
        $slug = array_key_first( $data['municipalities'] );
        $data['municipalities'][ $slug ]['type'] = 'keine';
        update_option( 'gk_kreiskarte_data', $data );
        $this->assertSame( 'keine', gk_get_kreiskarte_data()['municipalities'][ $slug ]['type'] );
        $this->assertSame( '', gk_get_municipality_url( $slug, gk_get_kreiskarte_data()['municipalities'][ $slug ], array() ) );
        $html = gk_render_kreiskarte_responsive();
        $this->assertStringContainsString( 'gk-ov-chip--inactive', $html );
        $this->assertStringContainsString( 'ov-inactive ov-keine', $html );
        $data['municipalities'][ $slug ]['type'] = 'link';
        $data['municipalities'][ $slug ]['link'] = 'https://external.example.org/';
        update_option( 'gk_kreiskarte_data', $data );
        $html = gk_render_kreiskarte_responsive();
        $this->assertStringContainsString( 'href="https://external.example.org/"', $html );
        $this->assertStringContainsString( 'class="ov-link"', $html );
    }

    /** Two municipality rows may resolve through one OV without losing either row. */
    public function test_shared_ov_assignment_keeps_both_municipalities() {
        $term = $this->ov();
        $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
        update_term_meta( $term, '_gk_homepage_id', $page );
        $ov_slug = get_term( $term )->slug;
        $data = gk_get_kreiskarte_data();
        $slugs = array_slice( array_keys( $data['municipalities'] ), 0, 2 );
        foreach ( $slugs as $slug ) {
            $data['municipalities'][ $slug ]['type'] = 'ov';
            $data['municipalities'][ $slug ]['ovSlug'] = $ov_slug;
        }
        update_option( 'gk_kreiskarte_data', $data );
        $this->assertCount( count( $data['municipalities'] ), gk_get_kreiskarte_data()['municipalities'] );
        foreach ( $slugs as $slug ) {
            $this->assertSame( get_permalink( $page ), gk_get_municipality_url( $slug, $data['municipalities'][ $slug ], gk_get_ov_terms_by_slug() ) );
        }
    }

    /** An explicit no-link action also applies to the public OV directory. */
    public function test_no_link_action_does_not_leave_ov_directory_linked() {
        $data = gk_get_kreiskarte_data();
        $slug = array_key_first( $data['municipalities'] );
        $term = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => $slug ) );
        $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
        update_term_meta( $term, '_gk_homepage_id', $page );
        $data['municipalities'][ $slug ]['type'] = 'keine';
        update_option( 'gk_kreiskarte_data', $data );
        $this->assertSame( '', gk_get_ov_navigation_url( $term ) );
        $this->assertStringNotContainsString( 'href="' . esc_url( get_permalink( $page ) ) . '"', gk_shortcode_ortsverband_liste( array() ) );
    }
    /**
     * Reject a new foreign image while retaining existing legacy references.
     */
    public function test_ov_homepage_media_selection_respects_scope_and_legacy_references() {
        $own     = $this->ov();
        $user    = self::factory()->user->create(
            array(
				'role'       => 'gk_ovadmin',
				'user_login' => get_term( $own )->slug,
            )
        );
        $foreign = self::factory()->post->create(
            array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image/jpeg',
            )
        );
        $local   = self::factory()->post->create(
            array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_mime_type' => 'image/jpeg',
            )
        );
        update_post_meta( $foreign, '_wp_attached_file', 'fixture-foreign.jpg' );
        update_post_meta( $local, '_wp_attached_file', 'fixture-local.jpg' );
        wp_set_object_terms( $local, array( $own ), 'gk_zuordnung' );
        wp_set_current_user( $user );
        $redirect      = '';
        $stop_redirect = static function ( $url ) use ( &$redirect ) {
            $redirect = $url;
            throw new RuntimeException( 'Test intercepted redirect.' );
        };
        add_filter( 'wp_redirect', $stop_redirect );
        try {
            foreach ( array( $foreign, $local ) as $image ) {
                $_POST = array(
					'gk_ov_save_nonce' => wp_create_nonce( 'gk_ov_save' ),
					'term_id'          => $own,
					'ov_name'          => get_term( $own )->name,
					'ov_hp'            => array(
						'hero_image'      => $image,
						'candidate_image' => $image,
					),
				);
                try {
                    gk_ortsverband_handle_save();
                } catch ( RuntimeException $exception ) {
                    $this->assertSame( 'Test intercepted redirect.', $exception->getMessage() );
                }
                $saved = get_term_meta( $own, '_gk_ov_homepage', true );
                $this->assertSame( $image === $local ? $local : 0, $saved['hero_image'] );
                $this->assertSame( $image === $local ? $local : 0, $saved['candidate_image'] );
                $this->assertStringContainsString( $image === $local ? 'message=saved' : 'message=media-rejected', $redirect );
            }
            update_term_meta(
                $own,
                '_gk_ov_homepage',
                array(
					'hero_image'      => $foreign,
					'candidate_image' => $foreign,
                )
            );
            $_POST['ov_hp'] = array(
				'hero_image'      => $foreign,
				'candidate_image' => $foreign,
			);
            try {
                gk_ortsverband_handle_save();
            } catch ( RuntimeException $exception ) {
                $this->assertSame( 'Test intercepted redirect.', $exception->getMessage() );
            }
            $this->assertSame( $foreign, get_term_meta( $own, '_gk_ov_homepage', true )['hero_image'] );
            $this->assertStringContainsString( 'message=saved', $redirect );
        } finally {
            remove_filter( 'wp_redirect', $stop_redirect );
            $_POST = array();
            wp_set_current_user( 0 );
        }
    }

    /**
     * OV admins receive the picker without gaining theme editing privileges.
     */
    public function test_ov_edit_form_loads_shared_media_picker() {
        $own  = $this->ov();
        $user = self::factory()->user->create(
            array(
				'role'       => 'gk_ovadmin',
				'user_login' => get_term( $own )->slug,
            )
        );
        wp_set_current_user( $user );
        $this->assertFalse( current_user_can( 'edit_theme_options' ) );
        wp_dequeue_script( 'media-editor' );
        gk_enqueue_settings_media();
        $this->assertTrue( wp_script_is( 'media-editor', 'enqueued' ) );
        $_GET['term_id'] = $own;
        ob_start();
        gk_ortsverband_edit_page();
        $html = ob_get_clean();
        unset( $_GET['term_id'] );
        wp_set_current_user( 0 );
        $this->assertStringContainsString( 'wp.media', $html );
    }
}
