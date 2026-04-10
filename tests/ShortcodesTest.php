<?php
/**
 * Tests for shortcode registration.
 *
 * @package Neurg_Kreisverband
 */

class ShortcodesTest extends WP_UnitTestCase {

    public function test_termine_shortcode_registered() {
        $this->assertTrue( shortcode_exists( 'termine' ) );
    }

    public function test_naechste_termine_shortcode_registered() {
        $this->assertTrue( shortcode_exists( 'naechste_termine' ) );
    }

    public function test_termine_shortcode_no_events() {
        $output = do_shortcode( '[termine]' );
        $this->assertStringContainsString( 'keine Termine', $output );
    }

    public function test_naechste_termine_shortcode_no_events() {
        $output = do_shortcode( '[naechste_termine]' );
        $this->assertStringContainsString( 'Keine kommenden Termine', $output );
    }

    public function test_termine_shortcode_with_events() {
        // Create a future event.
        $event_id = $this->factory->post->create( array(
            'post_type'   => 'gk_event',
            'post_title'  => 'Zukunftstermin',
            'post_status' => 'publish',
        ) );
        update_post_meta( $event_id, 'gk_event_start_date', date( 'Y-m-d', strtotime( '+30 days' ) ) );
        update_post_meta( $event_id, 'gk_event_start_time', '18:00' );

        $output = do_shortcode( '[termine]' );
        $this->assertStringContainsString( 'Zukunftstermin', $output );
        $this->assertStringContainsString( 'gk-event-list', $output );
    }
}
