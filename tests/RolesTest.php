<?php
/** Role capability regression tests. */
class RolesTest extends WP_UnitTestCase {
    public function test_ov_admin_can_use_page_templates_and_patterns_within_scope() {
        $term = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-layout' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovadmin', 'user_login' => 'ov-layout' ) );
        $own = self::factory()->post->create( array( 'post_type' => 'page' ) );
        $foreign = self::factory()->post->create( array( 'post_type' => 'page' ) );
        wp_set_object_terms( $own, array( $term ), 'gk_zuordnung' );
        wp_set_current_user( $user );
        $this->assertTrue( current_user_can( 'edit_pages' ) );
        foreach ( array( 'page-OV.php', 'page-OVsubpages.php' ) as $template ) {
            $request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $own );
            $request->set_param( 'template', $template );
            $this->assertSame( 200, rest_do_request( $request )->get_status() );
            $this->assertSame( $template, get_page_template_slug( $own ) );
            $request = new WP_REST_Request( 'POST', '/wp/v2/pages/' . $foreign );
            $request->set_param( 'template', $template );
            $this->assertSame( 403, rest_do_request( $request )->get_status() );
        }
        foreach ( array( 'introduction', 'priorities', 'participation' ) as $pattern ) {
            $this->assertTrue( WP_Block_Patterns_Registry::get_instance()->is_registered( 'gk/' . $pattern ) );
        }
        $this->assertFalse( current_user_can( 'edit_theme_options' ) );
        $report = gk_ov_access_report( wp_get_current_user() );
        $this->assertTrue( $report['upload'] );
        $this->assertTrue( $report['page_templates'] );
        $this->assertGreaterThanOrEqual( 1, $report['own_editable'] );
        $this->assertSame( 0, $report['foreign_editable'] );
        $this->assertFalse( $report['global_settings'] );
    }
    public function test_upgrade_repairs_legacy_upload_capabilities_without_global_access() {
        foreach ( array( 'gk_ovadmin', 'gk_ovautor' ) as $name ) {
            get_role( $name )->remove_cap( 'upload_files' );
        }
        gk_upgrade_editorial_roles();
        foreach ( array( 'gk_ovadmin', 'gk_ovautor' ) as $name ) {
            $role = get_role( $name );
            $this->assertTrue( $role->has_cap( 'upload_files' ) );
            $this->assertFalse( $role->has_cap( 'edit_theme_options' ) );
            $this->assertFalse( $role->has_cap( 'manage_options' ) );
        }
    }
    public function set_up(): void {
        parent::set_up();
        gk_upgrade_editorial_roles();
        gk_ensure_zuordnung_defaults();
    }

    public function tear_down(): void {
        wp_set_current_user( 0 );
        parent::tear_down();
    }

    public function test_office_role_remains_editorial() {
        $user = self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) );
        $this->assertTrue( user_can( $user, 'edit_others_posts' ) );
        $this->assertFalse( user_can( $user, 'manage_options' ) );
        $this->assertFalse( user_can( $user, 'promote_users' ) );
        $ovadmin = self::factory()->user->create( array( 'role' => 'gk_ovadmin' ) );
        $this->assertFalse( user_can( $ovadmin, 'edit_theme_options' ) );
    }

    public function test_own_draft_can_be_trashed_and_restored() {
        $user = self::factory()->user->create( array( 'role' => 'gk_kvautor' ) );
        $post = self::factory()->post->create( array( 'post_author' => $user, 'post_status' => 'draft' ) );
        wp_set_current_user( $user );
        $this->assertTrue( current_user_can( 'delete_post', $post ), wp_json_encode( array( gk_user_scope(), gk_object_scope_ids( $post ), wp_get_current_user()->allcaps ) ) );
        $this->assertNotFalse( wp_trash_post( $post ) );
        $this->assertSame( 'trash', get_post_status( $post ) );
        $this->assertNotFalse( wp_untrash_post( $post ) );
        $this->assertSame( 'draft', get_post_status( $post ) );
    }

    public function test_regression_published_ov_post_can_be_trashed() {
        $term = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-test' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-test' ) );
        $post = self::factory()->post->create( array( 'post_author' => $user ) );
        wp_set_object_terms( $post, array( $term ), 'gk_zuordnung' );
        $this->assertTrue( user_can( $user, 'delete_post', $post ) );
    }

    public function test_regression_own_post_outside_scope_cannot_be_edited() {
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-test' ) );
        $post = self::factory()->post->create( array( 'post_author' => $user ) );
        $this->assertFalse( user_can( $user, 'edit_post', $post ) );
    }

    public function test_regression_permanent_deletion_is_blocked() {
        $user = self::factory()->user->create( array( 'role' => 'gk_kvautor' ) );
        $post = self::factory()->post->create( array( 'post_author' => $user ) );
        wp_set_current_user( $user );
        $this->assertFalse( wp_delete_post( $post, true ) );
        $this->assertNotNull( get_post( $post ) );
    }

    public function test_scope_matrix_for_posts_pages_persons_and_events() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-matrix' ) );
        $admin = self::factory()->user->create( array( 'role' => 'gk_ovadmin', 'user_login' => 'ov-matrix' ) );
        $office = self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) );
        $kv = self::factory()->user->create( array( 'role' => 'gk_kvautor' ) );
        foreach ( array( 'post', 'page', 'person', 'gk_event' ) as $type ) {
            $post = self::factory()->post->create( array( 'post_type' => $type ) );
            wp_set_object_terms( $post, array( $ov ), 'gk_zuordnung' );
            $this->assertTrue( user_can( $admin, 'edit_post', $post ), $type );
            $this->assertTrue( user_can( $admin, 'delete_post', $post ), $type );
            $this->assertTrue( user_can( $office, 'delete_post', $post ), $type );
            $this->assertFalse( user_can( $kv, 'edit_post', $post ), $type );
            wp_set_current_user( $office );
            $this->assertNotFalse( wp_trash_post( $post ) );
            $this->assertTrue( current_user_can( 'delete_post', $post ) );
            $this->assertFalse( wp_delete_post( $post, true ) );
            $this->assertNotFalse( wp_untrash_post( $post ) );
            wp_set_current_user( 0 );
        }
    }

    public function test_rest_foreign_update_and_forced_delete_are_forbidden() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-rest' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-rest' ) );
        $foreign = self::factory()->post->create( array( 'post_author' => $user ) );
        $own = self::factory()->post->create( array( 'post_author' => $user ) );
        wp_set_object_terms( $own, array( $ov ), 'gk_zuordnung' );
        wp_set_current_user( $user );
        $request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $foreign );
        $request->set_param( 'title', 'Forged change' );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
        $request = new WP_REST_Request( 'DELETE', '/wp/v2/posts/' . $own );
        $request->set_param( 'force', true );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
        $this->assertNotNull( get_post( $own ) );
        $request->set_param( 'force', false );
        $this->assertSame( 200, rest_do_request( $request )->get_status() );
        $this->assertSame( 'trash', get_post_status( $own ) );
    }

    public function test_rest_create_uses_own_scope_and_rejects_forged_assignment() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-create' ) );
        $other = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-create' ) );
        wp_set_current_user( $user );
        $request = new WP_REST_Request( 'POST', '/wp/v2/gk_event' );
        $request->set_param( 'title', 'New event' );
        $request->set_param( 'status', 'publish' );
        $response = rest_do_request( $request );
        $this->assertSame( 201, $response->get_status(), wp_json_encode( $response->get_data() ) );
        $this->assertSame( array( $ov ), gk_object_scope_ids( $response->get_data()['id'] ) );
        $request->set_param( 'gk_zuordnung', array( $other ) );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
        $request->set_param( 'gk_zuordnung', array( $ov, $other ) );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
    }

    public function test_role_changes_require_admin_nonce_and_valid_scope() {
        $office = self::factory()->user->create( array( 'role' => 'gk_kvautor_ov', 'user_login' => 'geschaeftsstelle' ) );
        $target = self::factory()->user->create( array( 'role' => 'gk_kvautor' ) );
        wp_set_current_user( $office );
        $this->assertWPError( gk_change_editorial_role( $target, 'gk_kvautor_ov', wp_create_nonce( 'gk_role_change_' . $target ) ) );
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        $this->assertWPError( gk_change_editorial_role( $target, 'gk_kvautor_ov', 'invalid' ) );
        $nonce = wp_create_nonce( 'gk_role_change_' . $target );
        $this->assertWPError( gk_change_editorial_role( $target, 'administrator', $nonce ) );
        $this->assertWPError( gk_change_editorial_role( $target, 'gk_ovautor', $nonce ) );
        $this->assertTrue( gk_change_editorial_role( $target, 'gk_kvautor_ov', $nonce ) );
        $this->assertWPError( gk_change_editorial_role( $office, 'gk_kvautor', wp_create_nonce( 'gk_role_change_' . $office ) ) );
        $this->assertSame( array( 'gk_kvautor_ov' ), get_userdata( $office )->roles );
        $this->assertFalse( user_can( $office, 'manage_options' ) );
    }

    public function test_admin_can_permanently_delete_and_upgrade_preserves_users() {
        $office = self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) );
        gk_upgrade_editorial_roles();
        $this->assertSame( array( 'gk_kvautor_ov' ), get_userdata( $office )->roles );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );
        $post = self::factory()->post->create();
        $this->assertNotFalse( wp_delete_post( $post, true ) );
        $this->assertNull( get_post( $post ) );
    }

    public function test_nonpublic_foreign_authored_content_cannot_be_read() {
        self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-read' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-read' ) );
        foreach ( array( 'private', 'draft', 'pending', 'future' ) as $status ) {
            wp_set_current_user( 0 );
            $post = self::factory()->post->create( array( 'post_author' => $user, 'post_status' => $status, 'post_date' => '2030-01-01 12:00:00' ) );
            wp_set_current_user( $user );
            $this->assertFalse( current_user_can( 'read_post', $post ), $status );
            $response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/posts/' . $post ) );
            $this->assertSame( 403, $response->get_status(), $status );
        }
        wp_set_current_user( 0 );
        $public = self::factory()->post->create( array( 'post_author' => $user, 'post_status' => 'publish' ) );
        wp_set_current_user( $user );
        $this->assertTrue( current_user_can( 'read_post', $public ) );
        $request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $public );
        $request->set_param( 'context', 'edit' );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
    }
}
