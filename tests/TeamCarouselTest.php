<?php
/** Tests for the shared team carousel's content and scope boundaries. */
class TeamCarouselTest extends WP_UnitTestCase {
    public function test_department_order_roles_and_published_membership() {
        $board = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'test-board', 'name' => 'Vorstand' ) );
        $council = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'test-council', 'name' => 'Fraktion' ) );
        $person = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => 'Alex Beispiel', 'post_status' => 'publish' ) );
        wp_set_object_terms( $person, array( $board, $council ), 'abteilung' );
        gk_save_abteilung_meta( $person, array( 'test-board' => array( 'function' => 'Sprecherin' ), 'test-council' => array( 'function' => 'Fraktionsvorsitzende' ) ) );
        $draft = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => 'Unveröffentlicht', 'post_status' => 'draft' ) );
        wp_set_object_terms( $draft, $board, 'abteilung' );
        $html = do_shortcode( '[team_carousel abteilungen="test-board,test-council"]' );
        $this->assertSame( 2, substr_count( $html, 'Alex Beispiel' ) );
        $this->assertStringContainsString( 'Sprecherin', $html );
        $this->assertStringContainsString( 'Fraktionsvorsitzende', $html );
        $this->assertStringNotContainsString( 'Unveröffentlicht', $html );
        $this->assertLessThan( strpos( $html, '-tab-test-council' ), strpos( $html, '-tab-test-board' ) );
        $second = do_shortcode( '[team_carousel abteilungen="test-board"]' );
        preg_match( '/id="(gk-team-\d+)-heading"/', $html, $first_id );
        $this->assertStringNotContainsString( $first_id[1] . '-heading', $second );
    }

    public function test_ov_context_cannot_include_another_ov() {
        $own = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-test-own' ) );
        $other = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-test-other' ) );
        $board = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'test-board' ) );
        foreach ( array( $own => 'Eigene Person', $other => 'Fremde Person' ) as $scope => $name ) {
            $person = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => $name ) );
            wp_set_object_terms( $person, $scope, 'gk_zuordnung' );
            wp_set_object_terms( $person, $board, 'abteilung' );
        }
        $page = self::factory()->post->create( array( 'post_type' => 'page' ) );
        wp_set_object_terms( $page, $own, 'gk_zuordnung' );
        $this->go_to( get_permalink( $page ) );
        $html = do_shortcode( '[team_carousel abteilungen="test-board" zuordnung="ov-test-other"]' );
        $this->assertStringContainsString( 'Eigene Person', $html );
        $this->assertStringNotContainsString( 'Fremde Person', $html );
    }

    public function test_all_members_and_department_positions_are_retained() {
        $term = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'test-many' ) );
        for ( $i = 1; $i <= 65; ++$i ) {
            $person = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => sprintf( 'Person %02d', $i ) ) );
            wp_set_object_terms( $person, $term, 'abteilung' );
            gk_save_abteilung_meta( $person, array( 'test-many' => array( 'position' => (string) ( 66 - $i ), 'hidden' => true ) ) );
        }
        $html = do_shortcode( '[team_carousel abteilungen="test-many"]' );
        $this->assertSame( 65, substr_count( $html, 'class="gk-team__card"' ) );
        $this->assertLessThan( strpos( $html, 'Person 01' ), strpos( $html, 'Person 65' ) );
        $this->assertSame( '', do_shortcode( '[team_carousel abteilungen="missing-department"]' ) );
    }
}
