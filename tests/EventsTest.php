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
        $this->assertStringContainsString( 'ganztaegig', $formatted );
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
}
