<?php
/** Media boundary regression tests. */
class MediaTest extends WP_UnitTestCase {
    public function set_up(): void {
        parent::set_up();
        gk_upgrade_editorial_roles();
        gk_ensure_zuordnung_defaults();
    }

    public function tear_down(): void {
        wp_set_current_user( 0 );
        parent::tear_down();
    }

    public function test_existing_attachment_thumbnail_reference_is_preserved() {
        $attachment = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit' ) );
        $post = self::factory()->post->create();
        update_post_meta( $post, '_thumbnail_id', $attachment );
        wp_update_post( array( 'ID' => $attachment, 'post_title' => 'Updated caption' ) );
        $this->assertSame( $attachment, get_post_thumbnail_id( $post ) );
    }

    public function test_regression_media_modal_uses_taxonomy_not_author() {
        $term = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-media' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-media' ) );
        wp_set_current_user( $user );
        $query = apply_filters( 'ajax_query_attachments_args', array() );
        $this->assertArrayNotHasKey( 'author', $query );
        $this->assertArrayHasKey( 'tax_query', $query );
    }

    public function test_regression_unassigned_legacy_media_is_not_silently_reassigned() {
        $attachment = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit' ) );
        wp_set_object_terms( $attachment, array(), 'gk_zuordnung' );
        wp_update_post( array( 'ID' => $attachment, 'post_title' => 'Updated caption' ) );
        $this->assertSame( array(), wp_get_object_terms( $attachment, 'gk_zuordnung', array( 'fields' => 'ids' ) ) );
    }

    public function test_media_queries_use_scope_across_authors_and_preserve_constraints() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-query' ) );
        $other = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-query' ) );
        $ids = array();
        foreach ( array( array( $ov ), array( $other ), array(), array( $ov, $other ) ) as $terms ) {
            $id = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit' ) );
            wp_set_object_terms( $id, $terms, 'gk_zuordnung' );
            $ids[] = $id;
        }
        wp_set_current_user( $user );
        $args = apply_filters( 'ajax_query_attachments_args', array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'fields' => 'ids' ) );
        $this->assertSame( array( $ids[0] ), get_posts( $args ) );
        $args['tax_query'] = array( array( 'taxonomy' => 'gk_zuordnung', 'terms' => array( $other ) ) );
        $this->assertSame( array(), get_posts( gk_scope_query_args( $args ) ) );
        $this->assertFalse( current_user_can( 'edit_post', $ids[1] ) );
        $this->assertFalse( current_user_can( 'read_post', $ids[1] ) );
        $response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/media/' . $ids[1] ) );
        $this->assertSame( 403, $response->get_status() );
        $response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/media' ) );
        $this->assertSame( array( $ids[0] ), array_column( $response->get_data(), 'id' ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) ) );
        $this->assertSame( array(), gk_scope_query_args( array() ) );
    }

    public function test_upload_scope_and_safe_media_trash_restore() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-upload' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-upload' ) );
        wp_set_current_user( $user );
        $id = wp_insert_attachment( array( 'post_title' => 'Photo', 'post_status' => 'inherit', 'post_author' => $user ) );
        $this->assertSame( array( $ov ), gk_object_scope_ids( $id ) );
        $this->assertFalse( wp_delete_attachment( $id, true ) );
        $this->assertNotFalse( wp_trash_post( $id ) );
        $this->assertNotFalse( wp_untrash_post( $id ) );
        $this->assertSame( 'inherit', get_post( $id )->post_status );
    }

    public function test_foreign_featured_image_cannot_be_newly_selected_but_legacy_reference_survives() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-thumb' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-thumb' ) );
        $image = self::factory()->post->create( array( 'post_type' => 'attachment', 'post_status' => 'inherit' ) );
        $post = self::factory()->post->create( array( 'post_author' => $user ) );
        wp_set_object_terms( $post, array( $ov ), 'gk_zuordnung' );
        update_post_meta( $post, '_thumbnail_id', $image );
        wp_set_current_user( $user );
        wp_update_post( array( 'ID' => $post, 'post_title' => 'Changed' ) );
        $this->assertSame( $image, get_post_thumbnail_id( $post ) );
        delete_post_meta( $post, '_thumbnail_id' );
        $this->assertFalse( update_post_meta( $post, '_thumbnail_id', $image ) );
        $request = new WP_REST_Request( 'POST', '/wp/v2/posts/' . $post );
        $request->set_param( 'featured_media', $image );
        $this->assertSame( 403, rest_do_request( $request )->get_status() );
    }

    public function test_scope_reassignment_and_append_cannot_escape_or_destroy_existing_scope() {
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung', 'slug' => 'ov-assignment' ) );
        $other = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung' ) );
        $user = self::factory()->user->create( array( 'role' => 'gk_ovautor', 'user_login' => 'ov-assignment' ) );
        wp_set_current_user( $user );
        $id = wp_insert_attachment( array( 'post_title' => 'Photo', 'post_status' => 'inherit', 'post_author' => $user ) );
        wp_set_object_terms( $id, array( $other ), 'gk_zuordnung' );
        $this->assertSame( array( $ov ), gk_object_scope_ids( $id ) );
        wp_set_object_terms( $id, array( $other ), 'gk_zuordnung', true );
        $this->assertSame( array( $ov ), gk_object_scope_ids( $id ) );
    }

    public function test_media_preview_apply_rollback_and_conflict_checks() {
        $admin = self::factory()->user->create( array( 'role' => 'administrator' ) );
        wp_set_current_user( $admin );
        $ov = self::factory()->term->create( array( 'taxonomy' => 'gk_zuordnung' ) );
        $id = wp_insert_attachment( array( 'post_title' => 'Legacy', 'post_status' => 'inherit' ) );
        wp_set_object_terms( $id, array(), 'gk_zuordnung' );
        $post = self::factory()->post->create();
        update_post_meta( $post, '_thumbnail_id', $id );
        $preview = gk_media_scope_preview( $id );
        $this->assertSame( array(), gk_object_scope_ids( $id ) );
        $nonce = wp_create_nonce( 'gk_media_scope_' . $id );
        $this->assertWPError( gk_apply_media_scope( $id, $ov, $preview['fingerprint'], 'invalid' ) );
        $this->assertWPError( gk_apply_media_scope( $id, $ov, 'stale-preview', $nonce ) );
        $this->assertTrue( gk_apply_media_scope( $id, $ov, $preview['fingerprint'], $nonce ) );
        $this->assertSame( array( $ov ), gk_object_scope_ids( $id ) );
        $this->assertSame( $id, get_post_thumbnail_id( $post ) );
        $this->assertTrue( gk_rollback_media_scope( $id, $nonce ) );
        $this->assertSame( array(), gk_object_scope_ids( $id ) );
        $this->assertTrue( gk_apply_media_scope( $id, $ov, $preview['fingerprint'], $nonce ) );
        wp_set_object_terms( $id, array(), 'gk_zuordnung' );
        $this->assertWPError( gk_rollback_media_scope( $id, $nonce ) );
        wp_set_current_user( self::factory()->user->create( array( 'role' => 'gk_kvautor_ov' ) ) );
        $this->assertWPError( gk_media_scope_preview( $id ) );
        $this->assertWPError( gk_apply_media_scope( $id, $ov, $preview['fingerprint'], $nonce ) );
    }
}
