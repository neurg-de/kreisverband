<?php
/** Explicit person labels must not change editorial ownership. */
class PersonAffiliationTest extends WP_UnitTestCase {
    public function test_public_scope_and_schema_do_not_claim_party_affiliation() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-synthetic', 'name' => 'OV Beispielort' ) );
        $dept = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'synthetic-council', 'name' => 'Testfraktion' ) );
        $person = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => 'Alex Beispiel' ) );
        wp_set_object_terms( $person, $ov, 'gk_zuordnung' );
        wp_set_object_terms( $person, $dept, 'abteilung' );
        update_post_meta( $person, '_gk_person_affiliation', 'external' );
        $this->assertSame( 'Parteifremd', gk_person_affiliation_label( $person ) );
        $this->assertSame( '', gk_public_post_zuordnung_slug( $person ) );
        $this->assertSame( 'ov-synthetic', gk_get_post_zuordnung_slug( $person ) );
        $list = do_shortcode( '[abteilung slug="synthetic-council"]' );
        $this->assertStringContainsString( 'Parteifremd', $list );
        $this->assertStringNotContainsString( 'OV Beispielort', $list );
        $this->assertStringContainsString( 'Parteifremd', do_shortcode( '[team_carousel abteilungen="synthetic-council"]' ) );
        $this->go_to( get_permalink( $person ) );
        ob_start();
        gk_seo_jsonld_person();
        $schema = ob_get_clean();
        $this->assertStringNotContainsString( '"affiliation"', $schema );
        $this->assertSame( 'kreisverband', gk_legal_context_slug() );
    }

    public function test_only_authorized_explicit_submissions_change_the_label() {
        $person = self::factory()->post->create( array( 'post_type' => 'person' ) );
        update_post_meta( $person, '_gk_person_affiliation', 'external' );
        $previous = $_POST;
        try {
            $_POST = array( 'gk_person_affiliation_status' => 'independent' );
            gk_save_person_affiliation( $person );
            $this->assertSame( 'Parteifremd', gk_person_affiliation_label( $person ) );
            wp_set_current_user( self::factory()->user->create( array( 'role' => 'subscriber' ) ) );
            $_POST['gk_person_affiliation_nonce'] = wp_create_nonce( 'gk_person_affiliation' );
            gk_save_person_affiliation( $person );
            $this->assertSame( 'Parteifremd', gk_person_affiliation_label( $person ) );
            wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
            $_POST['gk_person_affiliation_nonce'] = wp_create_nonce( 'gk_person_affiliation' );
            gk_save_person_affiliation( $person );
            $this->assertSame( 'Parteilos', gk_person_affiliation_label( $person ) );
            $_POST['gk_person_affiliation_status'] = 'unexpected';
            gk_save_person_affiliation( $person );
            $this->assertSame( 'Parteilos', gk_person_affiliation_label( $person ) );
            $_POST['gk_person_affiliation_status'] = '';
            gk_save_person_affiliation( $person );
            $this->assertSame( '', gk_person_affiliation_label( $person ) );
        } finally {
            $_POST = $previous;
            wp_set_current_user( 0 );
        }
    }
}
