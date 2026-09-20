<?php
/**
 * Tests for the events system.
 *
 * @package Neurg_Kreisverband
 */

class EventsTest extends WP_UnitTestCase {

    public function test_event_creation_with_meta() {
        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Kreismitgliederversammlung',
            'post_status' => 'publish',
        ) );

        update_post_meta( $event_id, 'gk_event_start_date', '2026-06-15' );
        update_post_meta( $event_id, 'gk_event_start_time', '19:00' );
        update_post_meta( $event_id, 'gk_event_location', 'Rathaus Musterstadt' );

        $this->assertEquals( '2026-06-15', get_post_meta( $event_id, 'gk_event_start_date', true ) );
        $this->assertEquals( '19:00', get_post_meta( $event_id, 'gk_event_start_time', true ) );
        $this->assertEquals( 'Rathaus Musterstadt', get_post_meta( $event_id, 'gk_event_location', true ) );
    }

    public function test_event_date_formatting() {
        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Stammtisch',
            'post_status' => 'publish',
        ) );

        update_post_meta( $event_id, 'gk_event_start_date', '2026-06-15' );
        update_post_meta( $event_id, 'gk_event_start_time', '19:30' );

        $formatted = gk_format_event_date( $event_id );
        $this->assertNotEmpty( $formatted );
        $this->assertStringContainsString( '19:30', $formatted );
    }

    public function test_all_day_event() {
        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Klausur',
            'post_status' => 'publish',
        ) );

        update_post_meta( $event_id, 'gk_event_start_date', '2026-07-01' );
        update_post_meta( $event_id, 'gk_event_all_day', '1' );

        $formatted = gk_format_event_date( $event_id );
        $this->assertStringContainsString( 'ganztägig', $formatted );
    }

    public function test_multi_day_event() {
        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Parteitag',
            'post_status' => 'publish',
        ) );

        update_post_meta( $event_id, 'gk_event_start_date', '2026-09-12' );
        update_post_meta( $event_id, 'gk_event_end_date', '2026-09-13' );
        update_post_meta( $event_id, 'gk_event_start_time', '10:00' );
        update_post_meta( $event_id, 'gk_event_end_time', '16:00' );

        $formatted = gk_format_event_date( $event_id );
        $this->assertStringContainsString( '10:00', $formatted );
    }

    public function test_ical_escape() {
        $this->assertEquals( 'Hello\\nWorld', gk_ical_escape( "Hello\nWorld" ) );
        $this->assertEquals( 'A\\;B\\,C', gk_ical_escape( 'A;B,C' ) );
        $this->assertEquals( 'Simple text', gk_ical_escape( 'Simple text' ) );
    }

    public function test_event_kategorie_assignment() {
        $term = wp_insert_term( 'Sitzung', 'event_kategorie' );
        $this->assertFalse( is_wp_error( $term ) );

        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Fraktionssitzung',
            'post_status' => 'publish',
        ) );

        wp_set_object_terms( $event_id, array( $term['term_id'] ), 'event_kategorie' );
        $terms = wp_get_object_terms( $event_id, 'event_kategorie' );
        $this->assertCount( 1, $terms );
        $this->assertEquals( 'Sitzung', $terms[0]->name );
    }
    public function test_shortcodes_scope_to_ov_and_combine_category() {
        $a = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-alpha' ) );
        $b = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-beta' ) );
        $category = self::factory()->term->create( array( 'taxonomy' => 'event_kategorie', 'slug' => 'sitzung' ) );
        foreach ( array( 'Alpha Termin' => $a, 'Beta Termin' => $b ) as $title => $term ) {
            $event = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_title' => $title, 'post_status' => 'publish' ) );
            update_post_meta( $event, 'gk_event_start_date', '2099-06-15' );
            wp_set_object_terms( $event, array( $term ), 'gk_zuordnung' );
            wp_set_object_terms( $event, array( $category ), 'event_kategorie' );
        }
        foreach ( array( 'termine', 'naechste_termine' ) as $shortcode ) {
            $html = do_shortcode( '[' . $shortcode . ' zuordnung="ov-alpha" kategorie="sitzung"]' );
            $this->assertStringContainsString( 'Alpha Termin', $html );
            $this->assertStringNotContainsString( 'Beta Termin', $html );
        }
        $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish' ) );
        wp_set_object_terms( $page, array( $a ), 'gk_zuordnung' );
        $this->go_to( get_permalink( $page ) );
        $this->assertStringNotContainsString( 'Beta Termin', do_shortcode( '[termine]' ) );
        $this->assertStringNotContainsString( 'Beta Termin', do_shortcode( '[naechste_termine]' ) );
    }

    public function test_ical_scoping_filters_drafts_private_and_password_protected_events() {
        $a = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'calendar-a' ) );
        $b = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'calendar-b' ) );
        $ids = array();
        foreach ( array( 'publish', 'draft', 'private', 'publish', 'publish' ) as $i => $status ) {
            $id = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => $status, 'post_password' => 3 === $i ? 'secret' : '' ) );
            update_post_meta( $id, 'gk_event_start_date', '2099-06-15' );
            wp_set_object_terms( $id, array( 4 === $i ? $b : $a ), 'gk_zuordnung' );
            $ids[] = $id;
        }
        $this->assertSame( array( $ids[0] ), wp_list_pluck( gk_get_ical_events( 'calendar-a' ), 'ID' ) );
        $this->assertCount( 2, gk_get_ical_events() );
        $this->assertCount( 0, gk_get_ical_events( 'unknown-area' ) );
        $calendar = gk_build_ical( array_map( 'get_post', $ids ) );
        $this->assertSame( 2, substr_count( $calendar, 'BEGIN:VEVENT' ) );
        $this->assertStringContainsString( 'zuordnung=calendar-a', gk_event_ical_url( 'calendar-a' ) );
    }

    public function test_ical_dates_use_site_timezone_and_exclusive_all_day_end() {
        update_option( 'timezone_string', 'Europe/Berlin' );
        $id = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => 'publish' ) );
        update_post_meta( $id, 'gk_event_start_date', '2099-06-15' );
        update_post_meta( $id, 'gk_event_start_time', '19:30' );
        update_post_meta( $id, 'gk_event_end_time', '21:00' );
        $calendar = gk_build_ical( array( get_post( $id ) ) );
        $this->assertStringContainsString( 'DTSTART:20990615T173000Z', $calendar );
        $this->assertStringContainsString( 'DTEND:20990615T190000Z', $calendar );
        update_post_meta( $id, 'gk_event_all_day', '1' );
        update_post_meta( $id, 'gk_event_end_date', '2099-06-17' );
        $calendar = gk_build_ical( array( get_post( $id ) ) );
        $this->assertStringContainsString( 'DTSTART;VALUE=DATE:20990615', $calendar );
        $this->assertStringContainsString( 'DTEND;VALUE=DATE:20990618', $calendar );
        delete_post_meta( $id, 'gk_event_end_date' );
        $this->assertStringContainsString( 'DTEND;VALUE=DATE:20990616', gk_build_ical( array( get_post( $id ) ) ) );
    }

    public function test_ical_folding_and_escaping_prevent_injected_properties() {
        $title = str_repeat( 'Grüne ÄÖÜ ', 25 ) . ";,\\" . "\r\nATTENDEE:evil";
        $id = self::factory()->post->create( array( 'post_type' => 'gk_event', 'post_status' => 'publish', 'post_title' => $title ) );
        update_post_meta( $id, 'gk_event_start_date', '2099-06-15' );
        $calendar = gk_build_ical( array( get_post( $id ) ) );
        foreach ( explode( "\r\n", $calendar ) as $line ) {
            $this->assertLessThanOrEqual( 75, strlen( $line ) );
            $this->assertSame( 1, preg_match( '//u', $line ) );
        }
        $this->assertStringNotContainsString( "\r\nATTENDEE:", $calendar );
        $this->assertStringContainsString( '\\nATTENDEE:', str_replace( "\r\n ", '', $calendar ) );
    }

    public function test_event_meta_rejects_invalid_dates_times_and_unsafe_urls() {
        $id = self::factory()->post->create( array( 'post_type' => 'gk_event' ) );
        foreach ( array( 'gk_event_start_date' => '2026-02-30', 'gk_event_start_time' => '25:90', 'gk_event_url' => 'javascript:alert(1)' ) as $key => $value ) {
            update_post_meta( $id, $key, $value );
            $this->assertSame( '', get_post_meta( $id, $key, true ) );
        }
        $this->assertFalse( gk_event_datetime( '2026-02-30' ) );
        $this->assertSame( 0, substr_count( gk_build_ical( array( get_post( $id ) ) ), 'BEGIN:VEVENT' ) );
    }

}
