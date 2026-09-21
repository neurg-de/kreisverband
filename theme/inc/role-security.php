<?php
/**
 * Editorial scope and reversible deletion.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Return the theme roles, in decreasing scope order. */
function gk_editorial_roles() {
    return array( 'gk_kvautor_ov', 'gk_kvautor', 'gk_ovadmin', 'gk_ovautor' );
}

/**
 * Whether the user is subject to editorial restrictions.
 *
 * @param WP_User|null $user User to inspect, or the current user.
 */
function gk_is_restricted_editor( $user = null ) {
    $user = $user ? $user : wp_get_current_user();
    return ! user_can( $user, 'manage_options' ) && (bool) array_intersect( gk_editorial_roles(), (array) $user->roles );
}

/**
 * Null means all scopes; zero means a missing/invalid assignment (fail closed).
 *
 * @param WP_User|null $user User to inspect, or the current user.
 */
function gk_user_scope( $user = null ) {
    $user = $user ? $user : wp_get_current_user();
    if ( ! gk_is_restricted_editor( $user ) || in_array( 'gk_kvautor_ov', $user->roles, true ) ) {
        return null;
    }
    $slug = in_array( 'gk_kvautor', $user->roles, true ) ? 'kreisverband' : $user->user_login;
    $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
    return $term ? (int) $term->term_id : 0;
}

/** Theme-owned content types. */
function gk_scoped_post_types() {
    return array( 'post', 'page', 'person', 'gk_event', 'attachment' );
}

/**
 * Read canonical term IDs without author-based guesses.
 *
 * @param int $post_id Content ID.
 */
function gk_object_scope_ids( $post_id ) {
    $terms = wp_get_object_terms( $post_id, 'gk_zuordnung', array( 'fields' => 'ids' ) );
    return is_wp_error( $terms ) ? array() : array_map( 'intval', $terms );
}

/**
 * Check exact scope; ambiguous and legacy unassigned objects require review.
 *
 * @param int          $post_id Content ID.
 * @param WP_User|null $user User to inspect, or the current user.
 */
function gk_object_in_scope( $post_id, $user = null ) {
    $scope = gk_user_scope( $user );
    return null === $scope || ( $scope && array( $scope ) === gk_object_scope_ids( $post_id ) );
}

/** Upgrade role capabilities without changing any user's role. */
function gk_upgrade_editorial_roles() {
    gk_register_roles();
    foreach ( gk_editorial_roles() as $name ) {
        $role = get_role( $name );
        $role->add_cap( 'upload_files' );
        $role->add_cap( 'delete_published_posts' );
        $role->remove_cap( 'edit_theme_options' );
        if ( in_array( $name, array( 'gk_kvautor_ov', 'gk_ovadmin' ), true ) ) {
            foreach ( array( 'edit_others_posts', 'delete_others_posts', 'edit_others_pages', 'delete_others_pages', 'delete_others_persons' ) as $cap ) {
                $role->add_cap( $cap );
            }
        }
    }
    update_option( 'gk_editorial_roles_version', '3', false );
}

/** Apply upgrade once on existing installations as well as activation. */
function gk_maybe_upgrade_editorial_roles() {
    if ( '3' !== get_option( 'gk_editorial_roles_version' ) ) {
        gk_upgrade_editorial_roles();
    }
}
add_action( 'init', 'gk_maybe_upgrade_editorial_roles', 30 );
add_action( 'after_switch_theme', 'gk_upgrade_editorial_roles', 20 );

/**
 * Enforce the same object boundary in admin, REST, XML-RPC and AJAX.
 *
 * @param string[] $caps Core primitive capabilities.
 * @param string   $cap Requested meta capability.
 * @param int      $user_id Target user ID.
 * @param array    $args Query or capability arguments.
 */
function gk_scope_meta_cap( $caps, $cap, $user_id, $args ) {
    if ( ! in_array( $cap, array( 'edit_post', 'delete_post', 'read_post' ), true ) || empty( $args[0] ) ) {
        return $caps;
    }
    $user = get_userdata( $user_id );
    $post = get_post( $args[0] );
    if ( ! $user || ! $post || ! gk_is_restricted_editor( $user ) ) {
        return $caps;
    }
    if ( 'revision' === $post->post_type ) {
        $post = get_post( $post->post_parent );
    }
    if ( ! $post || ! in_array( $post->post_type, gk_scoped_post_types(), true ) ) {
        return $caps;
    }
    // Only publicly viewable, non-password-protected content bypasses editorial scope.
    if ( 'read_post' === $cap && 'attachment' !== $post->post_type && is_post_publicly_viewable( $post ) && empty( $post->post_password ) ) {
        return $caps;
    }
    // Core creates an unassigned auto-draft before our save hook runs.
    if ( 'auto-draft' === $post->post_status && (int) $post->post_author === $user_id && ! gk_object_scope_ids( $post->ID ) && gk_user_scope( $user ) ) {
        return $caps;
    }
    return gk_object_in_scope( $post->ID, $user ) ? $caps : array( 'do_not_allow' );
}
add_filter( 'map_meta_cap', 'gk_scope_meta_cap', 20, 4 );

/**
 * Permanent deletion has its own guard: delete_post is also needed to restore.
 *
 * @param mixed   $check Previous filter result.
 * @param WP_Post $post Content object.
 */
function gk_prevent_editorial_delete( $check, $post ) {
    if ( gk_is_restricted_editor() && in_array( $post->post_type, gk_scoped_post_types(), true ) ) {
        return false;
    }
    return $check;
}
add_filter( 'pre_delete_post', 'gk_prevent_editorial_delete', 10, 2 );
add_filter( 'pre_delete_attachment', 'gk_prevent_editorial_delete', 10, 2 );

/**
 * Guard direct calls too; WordPress trash functions do not check capabilities.
 *
 * @param mixed   $check Previous filter result.
 * @param WP_Post $post Content object.
 */
function gk_authorize_editorial_trash( $check, $post ) {
    if ( gk_is_restricted_editor() && ! current_user_can( 'delete_post', $post->ID ) ) {
        return false;
    }
    return $check;
}
add_filter( 'pre_trash_post', 'gk_authorize_editorial_trash', 10, 2 );
add_filter( 'pre_untrash_post', 'gk_authorize_editorial_trash', 10, 2 );

/**
 * Remove destructive actions while preserving restore and trash.
 *
 * @param array   $actions Available admin actions.
 * @param WP_Post $post Content object.
 */
function gk_safe_editorial_row_actions( $actions, $post ) {
    if ( gk_is_restricted_editor() ) {
        unset( $actions['delete'] );
        if ( 'attachment' === $post->post_type && current_user_can( 'delete_post', $post->ID ) && EMPTY_TRASH_DAYS ) {
            $action             = 'trash' === $post->post_status ? 'untrash' : 'trash';
            $url                = wp_nonce_url( admin_url( 'post.php?action=' . $action . '&post=' . $post->ID ), $action . '-post_' . $post->ID );
            $actions[ $action ] = '<a href="' . esc_url( $url ) . '">' . ( 'trash' === $action ? esc_html__( 'Papierkorb', 'neurg-kreisverband' ) : esc_html__( 'Wiederherstellen', 'neurg-kreisverband' ) ) . '</a>';
        }
    }
    return $actions;
}
add_filter( 'post_row_actions', 'gk_safe_editorial_row_actions', 20, 2 );
add_filter( 'page_row_actions', 'gk_safe_editorial_row_actions', 20, 2 );
add_filter( 'media_row_actions', 'gk_safe_editorial_row_actions', 20, 2 );

/**
 * Remove permanently-delete bulk operations.
 *
 * @param array $actions Available admin actions.
 */
function gk_safe_editorial_bulk_actions( $actions ) {
    if ( gk_is_restricted_editor() ) {
        unset( $actions['delete'] );
    }
    return $actions;
}
foreach ( array( 'edit-post', 'edit-page', 'edit-person', 'edit-gk_event', 'upload' ) as $gk_screen ) {
    add_filter( 'bulk_actions-' . $gk_screen, 'gk_safe_editorial_bulk_actions' );
}

/** Explain draft, publication, trash and restore on relevant admin screens. */
function gk_editorial_safety_notice() {
    $screen = get_current_screen();
    if ( ! gk_is_restricted_editor() || ! $screen || ! in_array( $screen->base, array( 'edit', 'post', 'upload' ), true ) ) {
        return;
    }
    echo '<div class="notice notice-info"><p>' . esc_html__( 'Entwürfe sind noch nicht öffentlich. Veröffentlichen macht Inhalte sichtbar; Bearbeiten korrigiert sie. Im Papierkorb lassen sich Inhalte wiederherstellen (anschließend als Entwurf prüfen). Dauerhaftes Löschen ist Administratoren vorbehalten. Medien bitte in der Listenansicht in den Papierkorb verschieben; die WordPress-Aufbewahrungsfrist gilt weiterhin.', 'neurg-kreisverband' ) . '</p>';
    if ( 'upload' === $screen->base ) {
        echo '<p><a href="' . esc_url( admin_url( 'upload.php?mode=list&attachment-filter=trash' ) ) . '">' . esc_html__( 'Medien-Papierkorb öffnen', 'neurg-kreisverband' ) . '</a></p>';
    }
    echo '</div>';
}
add_action( 'admin_notices', 'gk_editorial_safety_notice' );

/**
 * Explicit errors for REST force-delete, foreign assignments and thumbnails.
 *
 * @param object|WP_Error $prepared Prepared REST content.
 * @param WP_REST_Request $request REST request.
 */
function gk_validate_editorial_rest( $prepared, $request ) {
    if ( is_wp_error( $prepared ) ) {
        return $prepared;
    }
    $scope = gk_user_scope();
    $terms = $request->get_param( 'gk_zuordnung' );
    if ( 0 === $scope || ( null !== $terms && ( 1 !== count( (array) $terms ) || ( null !== $scope && array( $scope ) !== array_map( 'intval', (array) $terms ) ) ) ) ) {
        return new WP_Error( 'gk_scope_forbidden', __( 'Bitte nur den eigenen Zuständigkeitsbereich wählen.', 'neurg-kreisverband' ), array( 'status' => 403 ) );
    }
    $image = (int) $request->get_param( 'featured_media' );
    if ( $image && get_post_thumbnail_id( (int) $request['id'] ) !== $image && ! gk_object_in_scope( $image ) ) {
        return new WP_Error( 'gk_media_forbidden', __( 'Dieses Bild gehört nicht zu Ihrem Zuständigkeitsbereich.', 'neurg-kreisverband' ), array( 'status' => 403 ) );
    }
    if ( ! empty( $prepared->post_parent ) && ! gk_object_in_scope( $prepared->post_parent ) ) {
        return new WP_Error( 'gk_parent_forbidden', __( 'Der übergeordnete Inhalt gehört zu einem anderen Bereich.', 'neurg-kreisverband' ), array( 'status' => 403 ) );
    }
    return $prepared;
}
foreach ( gk_scoped_post_types() as $gk_post_type ) {
    add_filter( 'rest_pre_insert_' . $gk_post_type, 'gk_validate_editorial_rest', 10, 2 );
}

/**
 * Deny forced REST deletion with an actionable error before any mutation.
 *
 * @param mixed           $result Previous filter result.
 * @param WP_REST_Server  $server REST server.
 * @param WP_REST_Request $request REST request.
 */
function gk_rest_safe_delete( $result, $server, $request ) {
    if ( gk_is_restricted_editor() && 'DELETE' === $request->get_method() && $request->get_param( 'force' ) && preg_match( '#^/wp/v2/(posts|pages|media|person|gk_event)/\d+$#', $request->get_route() ) ) {
        return new WP_Error( 'gk_permanent_delete_forbidden', __( 'Dauerhaftes Löschen ist nicht erlaubt. Bitte den Papierkorb verwenden.', 'neurg-kreisverband' ), array( 'status' => 403 ) );
    }
    if ( gk_is_restricted_editor() && preg_match( '#^/wp/v2/media/(\d+)$#', $request->get_route(), $matches ) && ! gk_object_in_scope( (int) $matches[1] ) ) {
        return new WP_Error( 'gk_media_forbidden', __( 'Dieses Medium gehört zu einem anderen Bereich.', 'neurg-kreisverband' ), array( 'status' => 403 ) );
    }
    return $result;
}
add_filter( 'rest_pre_dispatch', 'gk_rest_safe_delete', 10, 3 );
