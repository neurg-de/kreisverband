<?php
/**
 * Regression tests for configurable terminology and stable scope boundaries.
 *
 * @package Neurg_Kreisverband
 */

/** Verify configurable names independently of editorial permissions. */
class AssociationTest extends WP_UnitTestCase {
    /** Tear down. */
    public function tear_down(): void {
        delete_option( 'gk_kv_info' );
        wp_set_current_user( 0 );
        parent::tear_down();
    }

    /** Test setup requires both explicit choices and complete custom forms. */
    public function test_setup_requires_both_explicit_choices_and_complete_custom_forms() {
        $page = self::factory()->post->create(
            array(
				'post_type'   => 'page',
				'post_status' => 'publish',
            )
        );
        $info = array(
			'name'             => 'GRÜNE Musterregion',
			'impressum_page'   => $page,
			'datenschutz_page' => $page,
		);
        update_option( 'gk_kv_info', $info );
        $this->assertFalse( gk_setup_is_complete() );
        $info['primary_type'] = 'bezirksverband';
        update_option( 'gk_kv_info', $info );
        $this->assertFalse( gk_setup_is_complete() );
        $info['secondary_type'] = 'kreisverband';
        update_option( 'gk_kv_info', gk_sanitize_kv_info( $info ) );
        $this->assertTrue( gk_setup_is_complete() );
        $info['secondary_type']     = 'custom';
        $info['secondary_singular'] = 'Lokale Runde';
        $info['secondary_plural']   = 'Lokale Runden';
        $this->assertNull( gk_association_level( $info, 'secondary' ) );
        $info['secondary_abbreviation'] = 'LR';
        $info['secondary_prefix']       = 'Lokal';
        update_option( 'gk_kv_info', gk_sanitize_kv_info( $info ) );
        $this->assertTrue( gk_setup_is_complete() );
        $this->assertSame( 'Lokale Runden', gk_association_label( 'secondary', 'plural' ) );
        $this->assertSame( 'Lokal', gk_association_label( 'secondary', 'abbreviation' ) );
        $saved = get_option( 'gk_kv_info' );
        $this->assertSame( 'LR', $saved['secondary_abbreviation'] );
        $saved['secondary_prefix'] = '';
        update_option( 'gk_kv_info', gk_sanitize_kv_info( $saved ) );
        $this->assertSame( 'LR', gk_association_label( 'secondary', 'abbreviation' ) );
    }

    /** Test invalid submission preserves previous structure and reports error. */
    public function test_invalid_submission_preserves_previous_structure_and_reports_error() {
        update_option(
            'gk_kv_info',
            array(
				'primary_type'         => 'stadtverband',
				'secondary_type'       => 'stadtteilgruppe',
				'secondary_prefix'     => 'ST',
				'secondary_url_prefix' => 'gruppe',
            )
        );
        $clean = gk_sanitize_kv_info(
            array(
				'primary_type'   => array( 'bad' ),
				'secondary_type' => 'unsupported',
            )
        );
        $this->assertSame( 'stadtverband', $clean['primary_type'] );
        $this->assertSame( 'stadtteilgruppe', $clean['secondary_type'] );
        $this->assertSame( 'gruppe', $clean['secondary_url_prefix'] );
        $this->assertNotEmpty( get_settings_errors( 'gk_kv_info' ) );
    }

    /** Test labels and prefixes change but assignments and authorization do not. */
    public function test_labels_and_prefixes_change_but_assignments_and_authorization_do_not() {
        gk_ensure_zuordnung_defaults();
        $root       = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        $child      = self::factory()->term->create(
            array(
				'taxonomy' => 'gk_zuordnung',
				'slug'     => 'ov-musterort',
            )
        );
        $foreign    = self::factory()->term->create(
            array(
				'taxonomy' => 'gk_zuordnung',
				'slug'     => 'ov-anderer-ort',
            )
        );
        $user       = self::factory()->user->create(
            array(
				'role'       => 'gk_ovadmin',
				'user_login' => 'ov-musterort',
            )
        );
        $post       = self::factory()->post->create(
            array(
				'post_author' => $user,
				'post_status' => 'publish',
            )
        );
        $other_post = self::factory()->post->create(
            array(
				'post_author' => $user,
				'post_status' => 'publish',
            )
        );
        wp_set_object_terms( $post, array( $child ), 'gk_zuordnung' );
        wp_set_object_terms( $other_post, array( $foreign ), 'gk_zuordnung' );
        update_option(
            'gk_kv_info',
            gk_sanitize_kv_info(
                array(
					'primary_type'         => 'bezirksverband',
					'secondary_type'       => 'kreisverband',
					'primary_prefix'       => 'Bezirk',
					'secondary_prefix'     => 'Kreis',
					'secondary_url_prefix' => 'lokal',
                )
            )
        );
        $renamed = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        $this->assertSame( $root->term_id, $renamed->term_id );
        $this->assertSame( 'Bezirksverband', $renamed->name );
        $this->assertSame( 'Kreis-Admin', translate_user_role( 'OV-Admin' ) );
        $this->assertSame( 'Bezirk-Autor (mit Kreis-Zugang)', translate_user_role( 'KV-Autor (mit OV-Zugang)' ) );
        $this->assertSame( 'lokal-musterort', gk_association_new_slug( 'Musterort' ) );
        $this->assertSame( 'lokal-musterort', gk_association_new_slug( 'lokal-musterort' ) );
        $this->assertSame( 'ov-musterort', get_term( $child )->slug );
        $this->assertSame( $child, gk_user_scope( get_userdata( $user ) ) );
        $this->assertTrue( user_can( $user, 'edit_post', $post ) );
        $this->assertFalse( user_can( $user, 'edit_post', $other_post ) );
        $this->assertFalse( user_can( $user, 'manage_options' ) );
    }

    /** Test setup and public list use required selectors and selected names. */
    public function test_setup_and_public_list_use_required_selectors_and_selected_names() {
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
        update_option(
            'gk_kv_info',
            array(
				'primary_type'   => 'stadtverband',
				'secondary_type' => 'stadtteilgruppe',
            )
        );
        ob_start();
        gk_setup_page_cb();
        $setup = ob_get_clean();
        $this->assertStringContainsString( 'name="gk_kv_info[primary_type]"', $setup );
        $this->assertStringContainsString( 'name="gk_kv_info[secondary_type]"', $setup );
        $this->assertStringContainsString( 'data-level="secondary" required', $setup );
        $this->assertStringContainsString( 'name="gk_kv_info[secondary_url_prefix]"', $setup );
        $html = gk_render_kreiskarte_responsive();
        $this->assertStringContainsString( 'Stadtteilgruppe suchen', $html );
        $this->assertStringContainsString( 'aria-label="Stadtteilgruppen"', $html );
        $templates = gk_association_page_templates(
            array(
				'page-OV.php'      => 'OV-Startseite',
				'page-ov-info.php' => 'OV-Unterseite',
            )
        );
        $this->assertSame( 'STG-Startseite', $templates['page-OV.php'] );
    }

    /** New multipart SVG paths retain local links and truthful missing-target states. */
    public function test_map_paths_use_the_same_targets_as_legacy_polygons() {
        update_option(
            'gk_kv_info',
            array(
				'primary_type'   => 'stadtverband',
				'secondary_type' => 'stadtteilgruppe',
            )
        );
        $term = self::factory()->term->create(
            array(
				'taxonomy' => 'gk_zuordnung',
				'slug'     => 'stg-musterort',
				'name'     => 'STG Musterort',
            )
        );
        $page = self::factory()->post->create(
            array(
				'post_type'     => 'page',
				'post_status'   => 'publish',
				'page_template' => 'page-OV.php',
            )
        );
        wp_set_object_terms( $page, array( $term ), 'gk_zuordnung' );
        update_term_meta( $term, '_gk_homepage_id', $page );
        $path = 'M0,0L20,0L20,20L0,20Z M30,30L40,30L40,40L30,40Z';
        update_option(
            'gk_kreiskarte_data',
            array(
				'_meta'          => array(
					'title'            => 'Musterstadt',
					'viewBox'          => '0 0 100 100',
                    'subdivisionLevel' => 9,
				),
				'district'       => array(
					'fill'   => 'M0,0L100,0L100,100L0,100Z',
					'shadow' => '',
				),
				'water'          => array(),
				'municipalities' => array(
					'stg-musterort'   => array(
						'name'    => 'Musterort',
						'polygon' => '0 0 20 0 20 20 0 20',
						'path'    => $path,
					),
					'stg-anderer-ort' => array(
						'name'    => 'Anderer Ort',
						'polygon' => '50 50 70 50 70 70 50 70',
						'path'    => 'M50,50L70,50L70,70L50,70Z',
					),
				),
            )
        );
        $html = gk_render_kreiskarte_responsive();
        $this->assertStringContainsString( 'd="' . $path . '" fill-rule="evenodd"', $html );
        $this->assertStringContainsString( esc_url( get_permalink( $page ) ), $html );
        $this->assertStringContainsString( 'Anderer Ort – Im Aufbau', $html );
        $this->assertStringContainsString( '© OpenStreetMap-Mitwirkende', $html );
        $this->assertStringContainsString( 'Stadtteilgruppe suchen', $html );
        ob_start();
        gk_kreiskarte_generator_page();
        $generator = ob_get_clean();
        $this->assertStringContainsString( 'value="9"  selected=', $generator );
    }
}
