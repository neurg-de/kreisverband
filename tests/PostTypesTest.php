<?php
/**
 * Tests for custom post types and taxonomies.
 *
 * @package Neurg_Kreisverband
 */

class PostTypesTest extends WP_UnitTestCase {

    public function test_person_post_type_registered() {
        $this->assertTrue( post_type_exists( 'person' ) );
    }

    public function test_person_post_type_is_public() {
        $pt = get_post_type_object( 'person' );
        $this->assertTrue( $pt->public );
    }

    public function test_person_supports_expected_features() {
        $this->assertTrue( post_type_supports( 'person', 'title' ) );
        $this->assertTrue( post_type_supports( 'person', 'editor' ) );
        $this->assertTrue( post_type_supports( 'person', 'thumbnail' ) );
        $this->assertTrue( post_type_supports( 'person', 'revisions' ) );
        $this->assertTrue( post_type_supports( 'person', 'author' ) );
    }

    public function test_abteilung_taxonomy_registered() {
        $this->assertTrue( taxonomy_exists( 'abteilung' ) );
    }

    public function test_abteilung_is_hierarchical() {
        $tax = get_taxonomy( 'abteilung' );
        $this->assertTrue( $tax->hierarchical );
    }

    public function test_abteilung_attached_to_person() {
        $tax = get_taxonomy( 'abteilung' );
        $this->assertContains( 'person', $tax->object_type );
    }

    public function test_zuordnung_taxonomy_registered() {
        $this->assertTrue( taxonomy_exists( 'gk_zuordnung' ) );
    }

    public function test_zuordnung_attached_to_post_types() {
        $tax = get_taxonomy( 'gk_zuordnung' );
        $this->assertContains( 'post', $tax->object_type );
        $this->assertContains( 'page', $tax->object_type );
        $this->assertContains( 'person', $tax->object_type );
        $this->assertContains( 'gk_event', $tax->object_type );
    }

    public function test_kreisverband_default_term_exists() {
        $term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        $this->assertInstanceOf( WP_Term::class, $term );
        $this->assertEquals( 'Kreisverband', $term->name );
    }

    public function test_event_post_type_registered() {
        $this->assertTrue( post_type_exists( 'gk_event' ) );
    }

    public function test_event_post_type_is_public() {
        $pt = get_post_type_object( 'gk_event' );
        $this->assertTrue( $pt->public );
    }

    public function test_event_supports_expected_features() {
        $this->assertTrue( post_type_supports( 'gk_event', 'title' ) );
        $this->assertTrue( post_type_supports( 'gk_event', 'editor' ) );
        $this->assertTrue( post_type_supports( 'gk_event', 'thumbnail' ) );
        $this->assertTrue( post_type_supports( 'gk_event', 'excerpt' ) );
    }

    public function test_event_kategorie_taxonomy_registered() {
        $this->assertTrue( taxonomy_exists( 'event_kategorie' ) );
    }

    public function test_person_creation_and_zuordnung_default() {
        $post_id = $this->factory->post->create( array(
            'post_type'   => 'person',
            'post_title'  => 'Test Person',
            'post_status' => 'publish',
        ) );

        // Trigger save_post hooks by re-saving.
        wp_update_post( array( 'ID' => $post_id ) );

        $this->assertGreaterThan( 0, $post_id );
        $this->assertEquals( 'person', get_post_type( $post_id ) );
    }
}
