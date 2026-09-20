<?php
/**
 * Taxonomy-based media selection and non-destructive assignment review.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Replace author-only media selection and the unconditional KV save default.
remove_filter( 'ajax_query_attachments_args', 'gk_limit_media_library' );
remove_action( 'save_post', 'gk_default_zuordnung_on_save', 10 );
remove_action( 'save_post', 'gk_zuordnung_meta_box_save', 5 );

/**
 * Add the scope as an AND clause, preserving search and explicit filters.
 *
 * @param array $args Query or capability arguments.
 */
function gk_scope_query_args( $args ) {
    $scope = gk_user_scope();
    if ( null === $scope ) {
        return $args;
    }
    $clause = array(
		'taxonomy'         => 'gk_zuordnung',
		'field'            => 'term_id',
		'terms'            => array( $scope ? $scope : -1 ),
		'include_children' => false,
	);
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Taxonomy predicates enforce the editorial access boundary.
    $args['tax_query'] = empty( $args['tax_query'] ) ? array( $clause ) : array(
		'relation' => 'AND',
		$args['tax_query'],
		$clause,
	);
    // Reject ambiguous multi-assigned legacy objects as well as foreign objects.
    $other = get_terms(
        array(
			'taxonomy'   => 'gk_zuordnung',
			'hide_empty' => false,
			'fields'     => 'ids',
			'exclude'    => array( $scope ),
        )
    );
    if ( ! is_wp_error( $other ) && $other ) {
        $args['tax_query'][] = array(
			'taxonomy'         => 'gk_zuordnung',
			'field'            => 'term_id',
			'terms'            => array_map( 'intval', $other ),
			'operator'         => 'NOT IN',
			'include_children' => false,
		);
    }
    return $args;
}
add_filter( 'ajax_query_attachments_args', 'gk_scope_query_args', 20 );
add_filter( 'rest_attachment_query', 'gk_scope_query_args', 20 );

/**
 * Scope admin lists and REST editorial collections; frontend stays public.
 *
 * @param WP_Query $query Content query.
 */
function gk_scope_admin_query( $query ) {
    if ( ! is_admin() || ( ! $query->is_main_query() && 'attachment' !== $query->get( 'post_type' ) ) ) {
        return;
    }
    $type = $query->get( 'post_type' ) ? $query->get( 'post_type' ) : 'post';
    if ( array_diff( (array) $type, gk_scoped_post_types() ) ) {
        return;
    }
    $args = gk_scope_query_args( $query->query_vars );
    if ( isset( $args['tax_query'] ) ) {
        $query->set( 'tax_query', $args['tax_query'] );
    }
}
add_action( 'pre_get_posts', 'gk_scope_admin_query', 100 );
foreach ( array( 'post', 'page', 'person', 'gk_event' ) as $gk_type ) {
    add_filter( 'rest_' . $gk_type . '_query', 'gk_scope_query_args', 20 );
}

/**
 * Set defaults only for new content; never guess a legacy attachment's scope.
 *
 * @param int     $post_id Content ID.
 * @param WP_Post $post Content object.
 * @param bool    $update Whether updating existing content.
 */
function gk_safe_default_scope( $post_id, $post, $update ) {
    if ( wp_is_post_revision( $post_id ) || ! in_array( $post->post_type, gk_scoped_post_types(), true ) || $update || gk_object_scope_ids( $post_id ) ) {
        return;
    }
    $scope = gk_user_scope();
    if ( null === $scope ) {
        $parent_scope = $post->post_parent ? gk_object_scope_ids( $post->post_parent ) : array();
        $kv           = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        $scope        = 1 === count( $parent_scope ) ? $parent_scope[0] : ( $kv ? (int) $kv->term_id : 0 );
    }
    if ( $scope ) {
        wp_set_object_terms( $post_id, array( $scope ), 'gk_zuordnung' );
    }
}
add_action( 'save_post', 'gk_safe_default_scope', 10, 3 );

/**
 * Attachments use add_attachment rather than save_post in WordPress core.
 *
 * @param int $post_id Content ID.
 */
function gk_new_attachment_scope( $post_id ) {
    gk_safe_default_scope( $post_id, get_post( $post_id ), false );
}
add_action( 'add_attachment', 'gk_new_attachment_scope' );

/**
 * Save a verified dropdown choice; invalid submissions leave existing data alone.
 *
 * @param int $post_id Content ID.
 */
function gk_save_scope_selection( $post_id ) {
    if ( ! isset( $_POST['gk_zuordnung_nonce'], $_POST['gk_zuordnung_select'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_zuordnung_nonce'] ) ), 'gk_zuordnung_save' ) || ! current_user_can( 'edit_post', $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }
    $scope = gk_user_scope();
    $id    = absint( $_POST['gk_zuordnung_select'] );
    $term  = get_term( $id, 'gk_zuordnung' );
    if ( $id && $term && ! is_wp_error( $term ) && ( null === $scope || $scope === $id ) ) {
        wp_set_object_terms( $post_id, array( $id ), 'gk_zuordnung' );
    }
}
add_action( 'save_post', 'gk_save_scope_selection', 20 );
add_action( 'edit_attachment', 'gk_save_scope_selection', 20 );

/**
 * Restore previous term relationships if a restricted request crosses scope.
 *
 * @param int    $object_id Content ID.
 * @param array  $terms Requested terms.
 * @param int[]  $tt_ids New taxonomy relationship IDs.
 * @param string $taxonomy Taxonomy name.
 * @param bool   $append Whether terms were appended.
 * @param int[]  $old_tt_ids Previous taxonomy relationship IDs.
 */
function gk_protect_scope_assignment( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    static $restoring = false;
    global $gk_scope_before_append;
    if ( $restoring || 'gk_zuordnung' !== $taxonomy || ! gk_is_restricted_editor() || ! in_array( get_post_type( $object_id ), gk_scoped_post_types(), true ) ) {
        return;
    }
    $scope   = gk_user_scope();
    $old_ids = array();
    foreach ( $old_tt_ids as $tt_id ) {
        $term = get_term_by( 'term_taxonomy_id', $tt_id, $taxonomy );
        if ( $term ) {
            $old_ids[] = (int) $term->term_id;
        }
    }
    if ( $append ) {
        $old_ids = $gk_scope_before_append[ $object_id ] ?? gk_object_scope_ids( $object_id );
    }
    unset( $gk_scope_before_append[ $object_id ] );
    $post               = get_post( $object_id );
    $previously_allowed = null === $scope || array( $scope ) === $old_ids || ( ! $old_ids && get_current_user_id() === (int) $post->post_author );
    $current_ids        = gk_object_scope_ids( $object_id );
    if ( $previously_allowed && ( ( null === $scope && 1 === count( $current_ids ) ) || array( $scope ) === $current_ids ) ) {
        return;
    }
    $restoring = true;
    wp_set_object_terms( $object_id, $old_ids, $taxonomy );
    $restoring = false;
    unset( $gk_scope_before_append[ $object_id ] );
}
add_action( 'set_object_terms', 'gk_protect_scope_assignment', 20, 6 );

/**
 * Block a newly forged featured image ID, preserving existing image references.
 *
 * @param mixed  $check Previous filter result.
 * @param int    $post_id Content ID.
 * @param string $key Metadata key.
 * @param mixed  $value Requested metadata value.
 */
function gk_guard_thumbnail_scope( $check, $post_id, $key, $value ) {
    if ( '_thumbnail_id' === $key && gk_is_restricted_editor() && (int) $value && get_post_thumbnail_id( $post_id ) !== (int) $value && ! gk_object_in_scope( (int) $value ) ) {
        return false;
    }
    return $check;
}
add_filter( 'add_post_metadata', 'gk_guard_thumbnail_scope', 10, 4 );
add_filter( 'update_post_metadata', 'gk_guard_thumbnail_scope', 10, 4 );

/**
 * Restrict available Zuordnung choices in the classic editor.
 *
 * @param WP_Post $post Content object.
 */
function gk_scoped_scope_meta_box( $post ) {
    $scope = gk_user_scope();
    if ( null === $scope ) {
        gk_zuordnung_meta_box_cb( $post );
        return;
    }
    $term = $scope ? get_term( $scope, 'gk_zuordnung' ) : null;
    echo '<p>' . esc_html( $term && ! is_wp_error( $term ) ? $term->name : __( 'Keine gültige Zuordnung. Bitte Administration kontaktieren.', 'neurg-kreisverband' ) ) . '</p>';
    if ( $term && ! is_wp_error( $term ) ) {
        wp_nonce_field( 'gk_zuordnung_save', 'gk_zuordnung_nonce' );
        echo '<input type="hidden" name="gk_zuordnung_select" value="' . esc_attr( $scope ) . '">';
    }
}

/** Replace only the rendering callback after the original box registration. */
function gk_scope_meta_boxes() {
    foreach ( gk_scoped_post_types() as $type ) {
        add_meta_box( 'gk_zuordnung_select', __( 'Zuordnung (KV/OV)', 'neurg-kreisverband' ), 'gk_scoped_scope_meta_box', $type, 'side', 'high' );
    }
}
add_action( 'add_meta_boxes', 'gk_scope_meta_boxes', 20 );

require_once __DIR__ . '/media-review.php';

/**
 * Expose the same assignment field to the block editor and REST clients.
 *
 * @param array  $args Query or capability arguments.
 * @param string $taxonomy Taxonomy name.
 */
function gk_scope_taxonomy_rest_args( $args, $taxonomy ) {
    if ( 'gk_zuordnung' === $taxonomy ) {
        $args['show_in_rest'] = true;
    }
    return $args;
}
add_filter( 'register_taxonomy_args', 'gk_scope_taxonomy_rest_args', 10, 2 );

/**
 * Reject uploads from an OV account whose responsibility is not configured.
 *
 * @param array $file Incoming upload information.
 */
function gk_validate_upload_scope( $file ) {
    if ( 0 === gk_user_scope() ) {
        $file['error'] = __( 'Keine gültige OV-Zuordnung. Bitte Administration kontaktieren.', 'neurg-kreisverband' );
    }
    return $file;
}
add_filter( 'wp_handle_upload_prefilter', 'gk_validate_upload_scope' );
add_filter( 'wp_handle_sideload_prefilter', 'gk_validate_upload_scope' );

/**
 * Preserve original relationships because core omits old IDs for append operations.
 *
 * @param int    $object_id Content ID.
 * @param int    $tt_id Taxonomy relationship ID.
 * @param string $taxonomy Taxonomy name.
 */
function gk_capture_scope_before_append( $object_id, $tt_id, $taxonomy ) {
    global $gk_scope_before_append;
    if ( 'gk_zuordnung' === $taxonomy && gk_is_restricted_editor() && ! isset( $gk_scope_before_append[ $object_id ] ) ) {
        $gk_scope_before_append[ $object_id ] = gk_object_scope_ids( $object_id );
    }
}
add_action( 'add_term_relationship', 'gk_capture_scope_before_append', 10, 3 );

/**
 * Restored attachments must retain inherit status to reappear in the media library.
 *
 * @param string $status Default restoration status.
 * @param int    $post_id Content ID.
 * @param string $previous_status Status before trashing.
 */
function gk_restore_attachment_status( $status, $post_id, $previous_status ) {
    return 'attachment' === get_post_type( $post_id ) && 'inherit' === $previous_status ? 'inherit' : $status;
}
add_filter( 'wp_untrash_post_status', 'gk_restore_attachment_status', 10, 3 );

/** Hide the permanent-delete control in the media modal for editorial users.
 *
 * @param array $response Prepared attachment data.
 * @return array
 */
function gk_safe_media_modal_actions( $response ) {
    if ( gk_is_restricted_editor() ) {
        $response['nonces']['delete'] = false;
    }
    return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'gk_safe_media_modal_actions' );
