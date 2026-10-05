<?php
/** Homepage configuration and scoped editorial regressions with synthetic data. */
class OvHomeEditorTest extends WP_UnitTestCase {
    private $a;
    private $b;
    private $admin;
    private $ov;

    public function set_up() {
        parent::set_up();
        gk_upgrade_editorial_roles();
        $this->a = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-editor-a', 'name' => 'OV Beispiel A' ) );
        $this->b = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-editor-b', 'name' => 'OV Beispiel B' ) );
        $this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        $this->ov = self::factory()->user->create( array( 'role' => 'gk_ovadmin', 'user_login' => 'ov-editor-a' ) );
        foreach ( array( $this->a, $this->b ) as $id ) {
            $page = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Beispielstartseite', 'post_content' => '<p>Einführung</p>' ) );
            wp_set_object_terms( $page, $id, 'gk_zuordnung' );
            update_post_meta( $page, '_wp_page_template', 'page-OV.php' );
            update_term_meta( $id, '_gk_homepage_id', $page );
        }
    }

    private function post( $scope, $category = 0, $status = 'publish' ) {
        $id = self::factory()->post->create( array( 'post_status' => $status, 'post_title' => 'Beitrag ' . wp_unique_id(), 'post_type' => 'post' ) );
        wp_set_object_terms( $id, $scope, 'gk_zuordnung' );
        if ( $category ) { wp_set_object_terms( $id, $category, 'category' ); }
        return $id;
    }

    public function test_defaults_and_whitelisted_sections() {
        $this->assertSame( array( 'hero', 'content', 'team', 'news', 'events', 'contact', 'engage' ), array_keys( gk_ov_home_sections( $this->a ) ) );
        $this->assertSame( 6, gk_ov_home_config( $this->a )['news_count'] );
        $this->assertFalse( gk_ov_home_config( $this->a )['archive'] );
        update_term_meta( $this->a, '_gk_ov_homepage', array( 'show_team' => '0' ) );
        $this->assertFalse( gk_ov_home_sections( $this->a )['team'] );
        wp_set_current_user( $this->ov );
        $this->assertTrue( gk_ov_save_home( $this->a, array( 'section_order' => array( '../../bad', 'news', 'hero', 'news' ), 'visible' => array( 'news', 'hero', 'bad' ), 'news_count' => 100, 'title' => '<b>Hallo</b>' ) ) );
        $this->assertSame( 'news', array_key_first( gk_ov_home_sections( $this->a ) ) );
        $this->assertFalse( gk_ov_home_sections( $this->a )['team'] );
        $this->assertSame( 20, gk_ov_home_config( $this->a )['news_count'] );
        $this->assertSame( 'Hallo', gk_ov_home_config( $this->a )['title'] );
        $this->assertSame( 6, gk_ov_home_config( $this->b )['news_count'] );
        $this->assertWPError( gk_ov_save_home( $this->b, array() ) );
        $this->assertTrue( gk_ov_home_sections( $this->b )['team'] );
    }

    public function test_category_ownership_and_core_endpoints() {
        wp_set_current_user( $this->admin );
        $foreign = gk_ov_save_category( $this->b, 'Gemeinderat' );
        $shared = self::factory()->term->create( array( 'taxonomy' => 'category' ) );
        wp_set_current_user( $this->ov );
        $own = gk_ov_save_category( $this->a, 'Gemeinderat' );
        $this->assertIsInt( $own );
        $this->assertSame( (string) $this->a, get_term_meta( $own, '_gk_category_owner', true ) );
        $slug = get_term( $own )->slug;
        $this->assertSame( $own, gk_ov_save_category( $this->a, 'Aus dem Rat', $own ) );
        $this->assertSame( $slug, get_term( $own )->slug );
        $this->assertWPError( gk_ov_save_category( $this->a, 'Fremd', $foreign ) );
        $this->assertWPError( gk_ov_save_category( $this->a, 'KV', $shared ) );
        $this->assertWPError( gk_ov_save_category( $this->b, 'Fremd' ) );
        $this->assertWPError( wp_insert_term( 'Ungeprüft', 'category' ) );
        foreach ( array( $own, $foreign, $shared ) as $cat ) {
            $this->assertFalse( current_user_can( 'edit_term', $cat ) );
            $this->assertFalse( current_user_can( 'delete_term', $cat ) );
        }
        $this->assertFalse( current_user_can( 'manage_categories' ) );
        $this->assertFalse( current_user_can( 'edit_theme_options' ) );
        $this->assertFalse( current_user_can( 'assign_term', $foreign ) );
        $this->assertTrue( current_user_can( 'assign_term', $own ) );
        update_term_meta( $foreign, '_gk_category_owner', $this->a );
        $this->assertSame( (string) $this->b, get_term_meta( $foreign, '_gk_category_owner', true ) );
        delete_term_meta( $own, '_gk_category_owner' );
        $this->assertSame( (string) $this->a, get_term_meta( $own, '_gk_category_owner', true ) );
        $post = $this->post( $this->a );
        wp_set_object_terms( $post, array( $own, $foreign, $shared ), 'category' );
        $expected = array( $own, $shared ); sort( $expected );
        $this->assertSame( $expected, wp_get_object_terms( $post, 'category', array( 'fields' => 'ids', 'orderby' => 'term_id' ) ) );
    }

    public function test_news_count_exclusions_and_archive_scope() {
        wp_set_current_user( $this->admin );
        $category = gk_ov_save_category( $this->a, 'Aus dem Rat' );
        $foreign = gk_ov_save_category( $this->b, 'Andere Rubrik' );
        $excluded = $this->post( $this->a, $category );
        for ( $i = 0; $i < 14; ++$i ) { $this->post( $this->a ); }
        $other = $this->post( $this->b, $category );
        $this->post( $this->a, $category, 'draft' );
        gk_ov_save_home( $this->a, array( 'news_count' => 10, 'excluded' => array( $category, $foreign ), 'archive' => 1 ) );
        $home = new WP_Query( gk_ov_news_args( $this->a ) );
        $this->assertCount( 10, $home->posts );
        $this->assertNotContains( $excluded, wp_list_pluck( $home->posts, 'ID' ) );
        $this->assertNotContains( $other, wp_list_pluck( $home->posts, 'ID' ) );
        $archive = new WP_Query( gk_ov_news_args( $this->a, false ) );
        $this->assertSame( 15, (int) $archive->found_posts );
        $rubric = new WP_Query( gk_ov_news_args( $this->a, false, $category ) );
        $this->assertSame( array( $excluded ), wp_list_pluck( $rubric->posts, 'ID' ) );
        $this->assertSame( '', gk_ov_news_url( $this->a, $foreign ) );
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', gk_get_ov_homepage_id( $this->b ) );
        $this->go_to( gk_ov_news_url( $this->a, $category ) );
        $this->assertSame( array( $excluded ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
        $this->assertFalse( is_404() );
        // Singular arguments must never bypass the tax_query.
        $this->go_to( add_query_arg( 'p', $other, gk_ov_news_url( $this->a, $category ) ) );
        $this->assertSame( array( $excluded ), wp_list_pluck( $GLOBALS['wp_query']->posts, 'ID' ) );
        $this->assertFalse( is_singular() );
        $this->go_to( add_query_arg( 'paged', 2, gk_ov_news_url( $this->a ) ) );
        $this->assertCount( 5, $GLOBALS['wp_query']->posts );
        $this->go_to( add_query_arg( 'paged', 3, gk_ov_news_url( $this->a ) ) );
        $this->assertTrue( is_404() );
        $this->go_to( add_query_arg( 'gk_ov_rubric', get_term( $foreign )->slug, gk_ov_news_url( $this->a ) ) );
        $this->assertTrue( is_404() );
    }

    public function test_empty_and_malformed_archive_inputs() {
        $this->go_to( gk_ov_news_url( $this->a ) );
        $this->assertFalse( is_404() );
        $this->assertSame( 'ov-editor-a', gk_ov_news_context()['term']->slug );
        foreach ( array( 'gk_ov_context', 'gk_ov_rubric', 'gk_ov_news' ) as $key ) {
            $this->go_to( add_query_arg( $key, array( 'malformed' ), gk_ov_news_url( $this->a ) ) );
            $this->assertNull( gk_ov_news_context() );
            $this->assertTrue( is_404() );
        }
    }

    public function test_hero_controls_in_all_modes() {
        wp_set_current_user( $this->admin );
        $hero_post = $this->post( $this->a );
        foreach ( array( 'standard', 'minimal', 'candidate', 'election', 'news', 'fundraising' ) as $mode ) {
            update_term_meta( $this->a, '_gk_ov_homepage', array( 'landing_mode' => $mode, 'hero_subtitle' => 'Untertitel', 'candidate_name' => 'Person', 'election_slogan' => 'Wahltext' ) );
            gk_ov_save_home( $this->a, array( 'title' => 'Eigener Titel', 'show_text' => 1, 'visible' => array( 'hero' ) ) );
            $this->go_to( get_permalink( gk_get_ov_homepage_id( $this->a ) ) );
            ob_start(); include GK_DIR . '/page-OV.php'; $shown = ob_get_clean();
            $this->assertStringContainsString( 'Eigener Titel', $shown, $mode );
            gk_ov_save_home( $this->a, array( 'visible' => array( 'hero' ) ) );
            $this->go_to( get_permalink( gk_get_ov_homepage_id( $this->a ) ) );
            ob_start(); include GK_DIR . '/page-OV.php'; $hidden = ob_get_clean();
            $this->assertStringContainsString( 'gk-hero__title screen-reader-text', $hidden, $mode );
            $this->assertStringNotContainsString( 'gk-hero__subtitle', $hidden, $mode );
        }
        wp_delete_post( $hero_post, true );
        update_term_meta( $this->a, '_gk_ov_homepage', array( 'landing_mode' => 'news' ) );
        gk_ov_save_home( $this->a, array( 'title' => 'Titel ohne Beiträge', 'show_text' => 1, 'visible' => array( 'hero' ) ) );
        $this->go_to( get_permalink( gk_get_ov_homepage_id( $this->a ) ) );
        ob_start(); include GK_DIR . '/page-OV.php'; $empty = ob_get_clean();
        $this->assertStringContainsString( 'Titel ohne Beiträge', $empty );
    }

    public function test_team_group_order_keeps_positions_and_adds_new_groups() {
        $board = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'board', 'name' => 'Vorstand' ) );
        $council = self::factory()->term->create( array( 'taxonomy' => 'abteilung', 'slug' => 'council', 'name' => 'Gemeinderat' ) );
        foreach ( array( 'Zuerst' => 1, 'Danach' => 2 ) as $name => $position ) {
            $id = self::factory()->post->create( array( 'post_type' => 'person', 'post_title' => $name ) );
            wp_set_object_terms( $id, $this->a, 'gk_zuordnung' );
            wp_set_object_terms( $id, array( $board, $council ), 'abteilung' );
            gk_save_abteilung_meta( $id, array( 'board' => array( 'position' => (string) $position ) ) );
        }
        wp_set_current_user( $this->ov );
        gk_ov_save_home( $this->a, array( 'team_order' => array( 'board', 'bogus' ) ) );
        $this->assertSame( array( 'board', 'council' ), gk_ov_home_config( $this->a )['team_order'] );
        $html = gk_render_team_carousel( array( 'zuordnung' => 'ov-editor-a', 'group_order' => gk_ov_home_config( $this->a )['team_order'] ) );
        $this->assertLessThan( strpos( $html, '-tab-council' ), strpos( $html, '-tab-board' ) );
        $this->assertLessThan( strpos( $html, 'Danach' ), strpos( $html, 'Zuerst' ) );
    }

    public function test_menu_changes_are_exactly_scoped_and_keep_pages() {
        wp_set_current_user( $this->admin );
        $category = gk_ov_save_category( $this->a, 'Aus dem Rat' );
        $foreign = gk_ov_save_category( $this->b, 'Andere Rubrik' );
        $menu_a = wp_create_nav_menu( 'Beispiel A' );
        $menu_b = wp_create_nav_menu( 'Beispiel B' );
        set_theme_mod( 'nav_menu_locations', array( 'nav-ov-editor-a' => $menu_a, 'nav-ov-editor-b' => $menu_b ) );
        $page = gk_get_ov_homepage_id( $this->a );
        $item = wp_update_nav_menu_item( $menu_a, 0, array( 'menu-item-title' => 'Alte Seite', 'menu-item-type' => 'post_type', 'menu-item-object' => 'page', 'menu-item-object-id' => $page, 'menu-item-status' => 'publish' ) );
        $foreign_item = wp_update_nav_menu_item( $menu_b, 0, array( 'menu-item-title' => 'Fremd', 'menu-item-type' => 'custom', 'menu-item-url' => home_url(), 'menu-item-status' => 'publish' ) );
        wp_set_current_user( $this->ov );
        $this->assertWPError( gk_ov_change_menu( $this->a, $category, $foreign_item, 'replace' ) );
        $this->assertWPError( gk_ov_change_menu( $this->a, $foreign, $item, 'replace' ) );
        $this->assertSame( $item, gk_ov_change_menu( $this->a, $category, $item, 'replace' ) );
        $this->assertSame( 'publish', get_post_status( $page ) );
        $this->assertSame( gk_ov_news_url( $this->a, $category ), wp_get_nav_menu_items( $menu_a )[0]->url );
        $this->assertSame( $item, gk_ov_change_menu( $this->a, 0, $item, 'remove' ) );
        $this->assertSame( 'publish', get_post_status( $page ) );
        set_theme_mod( 'nav_menu_locations', array( 'nav-ov-editor-a' => $menu_a, 'nav-ov-editor-b' => $menu_a ) );
        $this->assertWPError( gk_ov_editable_menu( $this->a ) );
    }

    public function test_roles_missing_scope_and_existing_urls_stay_unchanged() {
        $staff = self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) );
        $before = get_role( 'gk_kvautor_ov' )->capabilities;
        wp_set_current_user( $staff );
        $this->assertFalse( gk_can_edit_ov_home( $this->a ) );
        $this->assertSame( $before, get_role( 'gk_kvautor_ov' )->capabilities );
        $unassigned = self::factory()->user->create( array( 'role' => 'gk_ovadmin', 'user_login' => 'no-assignment' ) );
        wp_set_current_user( $unassigned );
        $this->assertFalse( gk_can_edit_ov_home( $this->a ) );
        $author = new WP_User( $this->ov );
        $author->set_role( 'gk_ovautor' );
        wp_set_current_user( $this->ov );
        $this->assertTrue( gk_can_edit_ov_home( $this->a ) );
        $this->assertFalse( gk_can_edit_ov_home( $this->b ) );
        wp_set_current_user( $this->admin );
        $root = self::factory()->post->create( array( 'post_type' => 'page', 'post_name' => 'verband' ) );
        $child = self::factory()->post->create( array( 'post_type' => 'page', 'post_parent' => $root, 'post_name' => 'aktuelles' ) );
        $this->go_to( get_permalink( $child ) );
        $this->assertSame( $child, get_queried_object_id() );
        $this->assertNull( gk_ov_news_context() );
    }

    public function test_rendered_homepage_order_visibility_and_empty_legacy_news() {
        $this->go_to( get_permalink( gk_get_ov_homepage_id( $this->a ) ) );
        ob_start(); include GK_DIR . '/page-OV.php'; $legacy = ob_get_clean();
        $this->assertStringNotContainsString( 'class="gk-news"', $legacy );
        wp_set_current_user( $this->admin );
        $this->post( $this->a );
        gk_ov_save_home( $this->a, array( 'section_order' => array( 'news', 'hero' ), 'visible' => array( 'news', 'hero' ), 'archive' => 1, 'title' => 'Eigene Begrüßung' ) );
        $this->go_to( get_permalink( gk_get_ov_homepage_id( $this->a ) ) );
        ob_start(); include GK_DIR . '/page-OV.php'; $html = ob_get_clean();
        $this->assertLessThan( strpos( $html, 'class="gk-hero"' ), strpos( $html, 'class="gk-news"' ) );
        $this->assertStringNotContainsString( 'class="ov-content"', $html );
        $this->assertStringContainsString( 'gk-hero__title screen-reader-text', $html );
        $this->assertStringContainsString( 'Alle Beiträge', $html );
        $this->assertStringContainsString( esc_url( gk_ov_news_url( $this->a ) ), $html );
    }
}
