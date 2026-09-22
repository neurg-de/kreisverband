<?php
/** Calendar participation is independent of the public municipality link. */
class EventAdministrationTest extends WP_UnitTestCase {
    public function tear_down(): void {
        $_GET = array();
        wp_set_current_user( 0 );
        set_current_screen( 'front' );
        parent::tear_down();
    }

    public function test_external_and_group_scopes_need_no_local_page() {
        $data = array( 'municipalities' => array(
            'ov-external-fixture' => array( 'name' => 'Beispielort', 'type' => 'link', 'link' => 'https://external.example.org/', 'eventsEnabled' => true ),
            'ov-group-fixture' => array( 'name' => 'Mustergruppe', 'type' => 'ortsgruppe', 'eventsEnabled' => true ),
        ) );
        $pages = wp_count_posts( 'page' );
        $saved = gk_ensure_map_event_scopes( $data );
        $this->assertNotWPError( $saved );
        update_option( 'gk_kreiskarte_data', $saved );
        $this->assertEquals( $pages, wp_count_posts( 'page' ) );
        $this->assertSame( 'https://external.example.org/', gk_get_municipality_url( 'ov-external-fixture', $saved['municipalities']['ov-external-fixture'], gk_get_ov_terms_by_slug() ) );
        foreach ( $saved['municipalities'] as $slug => $municipality ) {
            $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
            $this->assertInstanceOf( WP_Term::class, $term );
            $this->assertTrue( gk_ov_in_event_menu( $term ) );
            $this->assertSame( 0, gk_get_ov_homepage_id( $term->term_id ) );
            $event = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_title' => 'Termin ' . $slug, 'post_status' => 'publish' ) );
            update_post_meta( $event, 'gk_event_start_date', '2099-06-15' );
            wp_set_object_terms( $event, array( $term->term_id ), 'gk_zuordnung' );
            $this->assertStringContainsString( 'Termin ' . $slug, do_shortcode( '[termine]' ) );
            $this->assertCount( 1, gk_get_ical_events( $slug ) );
        }
        $group = get_term_by( 'slug', 'ov-group-fixture', 'gk_zuordnung' );
        $this->assertSame( 'ortsgruppe', gk_get_ov_type( $group->term_id ) );
        $this->assertSame( $saved, gk_ensure_map_event_scopes( $saved ) );
    }

    public function test_menu_opt_out_keeps_existing_events_and_shared_ov() {
        $id = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-shared-fixture' ) );
        $term = get_term( $id );
        $event = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => 'publish' ) );
        update_post_meta( $event, 'gk_event_start_date', '2099-06-15' );
        wp_set_object_terms( $event, array( $id ), 'gk_zuordnung' );
        $data = array( 'municipalities' => array(
            'place-one' => array( 'name' => 'Ort Eins', 'type' => 'link', 'ovSlug' => $term->slug, 'eventsEnabled' => false ),
            'place-two' => array( 'name' => 'Ort Zwei', 'type' => 'keine', 'ovSlug' => $term->slug, 'eventsEnabled' => true ),
        ) );
        update_option( 'gk_kreiskarte_data', gk_ensure_map_event_scopes( $data ) );
        $this->assertTrue( gk_ov_in_event_menu( $term ) );
        $data['municipalities']['place-two']['eventsEnabled'] = false;
        update_option( 'gk_kreiskarte_data', gk_ensure_map_event_scopes( $data ) );
        $this->assertFalse( gk_ov_in_event_menu( $term ) );
        $this->assertCount( 1, gk_get_ical_events( $term->slug ) );
        $this->assertSame( array( $id ), gk_object_scope_ids( $event ) );
        $this->assertTrue( gk_map_events_enabled( $term->slug, array( 'type' => 'link' ) ) );
    }

    public function test_new_event_keeps_selected_ov_for_cross_scope_editor() {
        global $pagenow;
        $previous_page = $pagenow;
        $id = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-calendar-fixture' ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) ) );
        $_GET['gk_zuordnung'] = 'ov-calendar-fixture';
        set_current_screen( 'edit-gk_event' );
        $this->assertStringContainsString( 'gk_zuordnung=ov-calendar-fixture', admin_url( 'post-new.php?post_type=gk_event' ) );
        try {
            set_current_screen( 'gk_event' );
            $pagenow = 'post-new.php';
            $event = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => 'auto-draft', 'post_author' => get_current_user_id() ) );
            $this->assertSame( array( $id ), gk_object_scope_ids( $event ) );
        } finally {
            $pagenow = $previous_page;
        }
    }

    public function test_forged_new_event_scope_cannot_cross_ov_boundary() {
        global $pagenow;
        $previous_page = $pagenow;
        $own = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-owner-fixture' ) );
        self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-foreign-fixture' ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'gk_ovadmin', 'user_login' => 'ov-owner-fixture' ) ) );
        $_GET['gk_zuordnung'] = 'ov-foreign-fixture';
        set_current_screen( 'edit-gk_event' );
        $this->assertFalse( gk_event_admin_selected_term() );
        $this->assertStringNotContainsString( 'ov-foreign-fixture', admin_url( 'post-new.php?post_type=gk_event' ) );
        try {
            set_current_screen( 'gk_event' );
            $pagenow = 'post-new.php';
            $event = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => 'auto-draft', 'post_author' => get_current_user_id() ) );
            $this->assertSame( array( $own ), gk_object_scope_ids( $event ) );
        } finally {
            $pagenow = $previous_page;
        }
    }
}
