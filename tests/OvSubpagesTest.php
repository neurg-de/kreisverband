<?php
/** Virtual OV subpages (Termine/Mitmachen) with synthetic data. */
class OvSubpagesTest extends WP_UnitTestCase {
    private $ov;
    private $home;
    private $admin;

    public function set_up() {
        parent::set_up();
        gk_upgrade_editorial_roles();
        $this->set_permalink_structure( '/%postname%/' );
        $this->ov   = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-sub-a', 'name' => 'OV Beispiel' ) );
        $this->home = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'ov-beispiel', 'post_title' => 'OV Beispiel' ) );
        wp_set_object_terms( $this->home, $this->ov, 'gk_zuordnung' );
        update_post_meta( $this->home, '_wp_page_template', 'page-OV.php' );
        update_term_meta( $this->ov, '_gk_homepage_id', $this->home );
        $this->admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        delete_option( 'gk_ov_subpage_paths' );
    }

    private function enable( $termine = true, $mitmachen = true ) {
        update_term_meta(
            $this->ov,
            '_gk_home_editor',
            array(
                'menu_termine'   => $termine ? '1' : '0',
                'menu_mitmachen' => $mitmachen ? '1' : '0',
            )
        );
    }

    public function test_disabled_by_default_keeps_previous_behavior() {
        $this->assertSame( '', gk_ov_subpage_url( $this->ov, 'termine' ) );
        $this->assertSame( '', gk_ov_subpage_menu_html( get_term( $this->ov ) ) );
        $vars = gk_ov_subpage_request( array( 'gk_ov_page' => 'termine', 'gk_ov_context' => 'ov-sub-a' ) );
        $this->assertSame( array( 'pagename' => 'ov-beispiel/termine' ), $vars );
    }

    public function test_enabled_urls_live_below_the_ov_homepage() {
        $this->enable();
        $this->assertSame( home_url( '/ov-beispiel/termine/' ), gk_ov_subpage_url( $this->ov, 'termine' ) );
        $this->assertSame( home_url( '/ov-beispiel/mitmachen/' ), gk_ov_subpage_url( $this->ov, 'mitmachen' ) );
        $this->assertSame( '', gk_ov_subpage_url( $this->ov, 'unbekannt' ) );
        $this->assertSame( array( 'ov-beispiel' => 'ov-sub-a' ), gk_ov_subpage_paths() );
    }

    public function test_route_resolves_with_ov_context_and_header_menu() {
        $this->enable();
        $this->go_to( '/?gk_ov_page=termine&gk_ov_context=ov-sub-a' );
        $context = gk_ov_subpage_context();
        $this->assertSame( 'termine', $context['page'] );
        $this->assertSame( 'ov-sub-a', gk_legal_context_slug() );
        $this->assertSame( 'ov-sub-a', gk_event_scope() );
        $this->assertStringEndsWith( 'page-ov-termine.php', gk_ov_subpage_template( 'x.php' ) );
        $html = gk_ov_subpage_menu_html( get_term( $this->ov ) );
        $this->assertStringContainsString( 'aria-current="page"', $html );
        $this->assertSame( 1, substr_count( $html, '>Termine<' ) );
        $this->assertSame( 1, substr_count( $html, '>Mitmachen<' ) );
    }

    public function test_real_page_at_the_same_path_wins() {
        $this->enable();
        self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'termine', 'post_parent' => $this->home ) );
        $vars = gk_ov_subpage_request( array( 'gk_ov_page' => 'termine', 'gk_ov_context' => 'ov-sub-a' ) );
        $this->assertSame( array( 'pagename' => 'ov-beispiel/termine' ), $vars );
    }

    public function test_only_enabled_page_is_routed_and_labels_are_configurable() {
        update_term_meta( $this->ov, '_gk_home_editor', array( 'menu_termine' => '1', 'label_termine' => 'Kalender' ) );
        $this->go_to( '/?gk_ov_page=mitmachen&gk_ov_context=ov-sub-a' );
        $this->assertNull( gk_ov_subpage_context() );
        $html = gk_ov_subpage_menu_html( get_term( $this->ov ) );
        $this->assertStringContainsString( '>Kalender<', $html );
        $this->assertStringNotContainsString( 'mitmachen', $html );
    }

    public function test_kv_entries_are_hidden_only_in_the_enabled_ov_context() {
        $this->enable( true, false );
        $termine = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'termine' ) );
        $items   = array(
            (object) array( 'ID' => 1, 'menu_item_parent' => 0, 'classes' => array( '' ), 'type' => 'post_type', 'object' => 'page', 'object_id' => $termine ),
            (object) array( 'ID' => 2, 'menu_item_parent' => 1, 'classes' => array( '' ), 'type' => 'custom', 'object' => 'custom', 'object_id' => 2 ),
            (object) array( 'ID' => 3, 'menu_item_parent' => 0, 'classes' => array( 'gk-kv-mitmachen' ), 'type' => 'custom', 'object' => 'custom', 'object_id' => 3 ),
            (object) array( 'ID' => 4, 'menu_item_parent' => 0, 'classes' => array( 'gk-kv-termine' ), 'type' => 'custom', 'object' => 'custom', 'object_id' => 4 ),
        );
        $args    = (object) array( 'theme_location' => 'nav-main' );

        $this->go_to( '/' );
        $this->assertCount( 4, gk_ov_subpage_dedupe_kv_menu( $items, $args ) );

        $this->go_to( '/?gk_ov_page=termine&gk_ov_context=ov-sub-a' );
        $kept = wp_list_pluck( gk_ov_subpage_dedupe_kv_menu( $items, $args ), 'ID' );
        $this->assertSame( array( 3 ), $kept, 'Termine page, its child and the class-marked item are hidden; Mitmachen stays while disabled.' );
    }

    public function test_rest_requires_editor_and_updates_only_subpage_fields() {
        update_term_meta( $this->ov, '_gk_home_editor', array( 'news_count' => '9' ) );
        $request = new WP_REST_Request( 'POST', '/neurg/v1/ov-home/' . $this->ov );
        $request->set_param( 'termine', true );
        $request->set_param( 'mitmachen_text', "Treffen <b>dienstags</b>\nim Rathaus" );

        wp_set_current_user( 0 );
        $this->assertSame( 401, rest_get_server()->dispatch( $request )->get_status() );

        wp_set_current_user( $this->admin );
        $response = rest_get_server()->dispatch( $request );
        $this->assertSame( 200, $response->get_status() );
        $data = $response->get_data();
        $this->assertTrue( $data['subpages']['termine'] );
        $this->assertFalse( $data['subpages']['mitmachen'] );
        $this->assertSame( home_url( '/ov-beispiel/termine/' ), $data['subpages']['urls']['termine'] );
        $this->assertSame( "Treffen dienstags\nim Rathaus", $data['subpages']['mitmachen_text'] );
        $this->assertSame( 9, gk_ov_home_config( $this->ov )['news_count'] );
    }

    public function test_editor_save_keeps_switches_and_text() {
        wp_set_current_user( $this->admin );
        gk_ov_save_home(
            $this->ov,
            array(
                'menu_mitmachen'  => '1',
                'label_mitmachen' => 'Mach mit',
                'mitmachen_text'  => "Zeile 1\nZeile 2",
            )
        );
        $config = gk_ov_subpage_config( $this->ov );
        $this->assertFalse( $config['termine'] );
        $this->assertTrue( $config['mitmachen'] );
        $this->assertSame( 'Mach mit', $config['label_mitmachen'] );
        $this->assertSame( "Zeile 1\nZeile 2", $config['mitmachen_text'] );
    }

    public function test_hidden_pages_and_their_children_leave_the_area_navigation() {
        $shown  = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Sichtbare Seite' ) );
        $hidden = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Versteckte Seite' ) );
        $child  = self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Kapitel Eins', 'post_parent' => $hidden ) );
        foreach ( array( $shown, $hidden, $child ) as $id ) {
            wp_set_object_terms( $id, $this->ov, 'gk_zuordnung' );
        }
        update_post_meta( $hidden, '_gk_hide_in_ov_nav', true );
        ob_start();
        gk_ov_navi( 'ov-sub-a' );
        $html = ob_get_clean();
        $this->assertStringContainsString( 'Sichtbare Seite', $html );
        $this->assertStringNotContainsString( 'Versteckte Seite', $html );
        $this->assertStringNotContainsString( 'Kapitel Eins', $html );
    }

    public function test_person_initials_skip_titles_and_particles() {
        $this->assertSame( 'AB', gk_person_initials( 'Anna Beispiel' ) );
        $this->assertSame( 'ÖM', gk_person_initials( 'Dr. Ölgard von Muster-Frau' ) );
        $this->assertSame( 'E', gk_person_initials( 'Elvira' ) );
        $this->assertSame( '', gk_person_initials( '   ' ) );
    }

    public function test_readable_rubric_url_resolves_to_the_owned_category() {
        wp_set_current_user( $this->admin );
        $category = gk_ov_save_category( $this->ov, 'Aus dem Rat' );
        $this->assertSame( 'ov-sub-a-aus-dem-rat', get_term( $category )->slug );
        $this->assertSame( home_url( '/ov-beispiel/rubrik/aus-dem-rat/' ), gk_ov_news_url( $this->ov, $category ) );
        $vars = gk_ov_rubric_request( array( 'gk_ov_news' => '1', 'gk_ov_context' => 'ov-sub-a', 'gk_ov_rubric' => 'aus-dem-rat', 'gk_ov_rubric_path' => '1' ) );
        $this->assertSame( 'ov-sub-a-aus-dem-rat', $vars['gk_ov_rubric'] );
        $this->assertArrayNotHasKey( 'gk_ov_rubric_path', $vars );

        self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'rubrik', 'post_parent' => $this->home ) );
        $parent = get_page_by_path( 'ov-beispiel/rubrik' );
        self::factory()->post->create( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'aus-dem-rat', 'post_parent' => $parent->ID ) );
        $vars = gk_ov_rubric_request( array( 'gk_ov_news' => '1', 'gk_ov_context' => 'ov-sub-a', 'gk_ov_rubric' => 'aus-dem-rat', 'gk_ov_rubric_path' => '1' ) );
        $this->assertSame( array( 'pagename' => 'ov-beispiel/rubrik/aus-dem-rat' ), $vars, 'A real page at the same path wins.' );
    }

    public function test_menu_entries_can_be_placed_first() {
        update_term_meta( $this->ov, '_gk_home_editor', array( 'menu_termine' => '1', 'menu_first' => '1' ) );
        $html = gk_ov_subpage_nav_items( '<li>Bestehend</li>', (object) array( 'theme_location' => 'nav-ov-sub-a' ) );
        $this->assertStringStartsWith( '<li class="menu-item', $html );
        update_term_meta( $this->ov, '_gk_home_editor', array( 'menu_termine' => '1' ) );
        $html = gk_ov_subpage_nav_items( '<li>Bestehend</li>', (object) array( 'theme_location' => 'nav-ov-sub-a' ) );
        $this->assertStringStartsWith( '<li>Bestehend</li>', $html );
    }
}
