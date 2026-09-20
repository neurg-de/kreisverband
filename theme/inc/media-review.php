<?php
/**
 * Administrator-reviewed media assignment with preview and rollback.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Read-only preview. Parent/author evidence is a suggestion, never an automatic fix.
 *
 * @param int $attachment_id Attachment ID.
 */
function gk_media_scope_preview( $attachment_id ) {
    if ( ! current_user_can( 'manage_options' ) ) {
        return new WP_Error( 'gk_media_permission', __( 'Nur Administratoren dürfen Medienzuordnungen prüfen.', 'neurg-kreisverband' ) );
    }
    $post = get_post( $attachment_id );
    if ( ! $post || 'attachment' !== $post->post_type ) {
        return new WP_Error( 'gk_media_invalid', __( 'Medium nicht gefunden.', 'neurg-kreisverband' ) );
    }
    $before = gk_object_scope_ids( $attachment_id );
    sort( $before );
    $parent      = $post->post_parent ? gk_object_scope_ids( $post->post_parent ) : array();
    $author      = get_userdata( $post->post_author );
    $author_term = $author ? get_term_by( 'slug', $author->user_login, 'gk_zuordnung' ) : false;
    $proposed    = 1 === count( $parent ) ? $parent[0] : ( $author_term ? (int) $author_term->term_id : 0 );
    $conflict    = count( $parent ) > 1 || ( $author_term && $parent && ! in_array( (int) $author_term->term_id, $parent, true ) );
    return array(
        'id'          => (int) $attachment_id,
        'before'      => $before,
        'proposed'    => $conflict ? 0 : $proposed,
        'conflict'    => (bool) $conflict,
        'fingerprint' => hash( 'sha256', wp_json_encode( $before ) ),
    );
}

/**
 * Explicit per-item migration; compare with preview and save durable rollback first.
 *
 * @param int    $attachment_id Attachment ID.
 * @param int    $term_id Reviewed target scope.
 * @param string $fingerprint Hash of the previewed assignment.
 * @param string $nonce Per-object security nonce.
 */
function gk_apply_media_scope( $attachment_id, $term_id, $fingerprint, $nonce ) {
    if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $attachment_id ) || ! wp_verify_nonce( $nonce, 'gk_media_scope_' . $attachment_id ) ) {
        return new WP_Error( 'gk_media_permission', __( 'Keine Berechtigung oder ungültige Sicherheitsprüfung.', 'neurg-kreisverband' ) );
    }
    $preview = gk_media_scope_preview( $attachment_id );
    $term    = get_term( $term_id, 'gk_zuordnung' );
    if ( is_wp_error( $preview ) || ! $term_id || ! $term || is_wp_error( $term ) || ! hash_equals( $preview['fingerprint'], (string) $fingerprint ) ) {
        return new WP_Error( 'gk_media_changed', __( 'Zuordnung geändert oder ungültig. Bitte die Vorschau neu laden.', 'neurg-kreisverband' ) );
    }
    $snapshot = array(
		'before' => $preview['before'],
		'after'  => array( (int) $term_id ),
	);
    if ( ! add_post_meta( $attachment_id, '_gk_media_scope_rollback', $snapshot, true ) ) {
        return new WP_Error( 'gk_media_snapshot', __( 'Für dieses Medium liegt bereits eine Rücknahme vor. Zuerst diese prüfen oder rückgängig machen.', 'neurg-kreisverband' ) );
    }
    $result = wp_set_object_terms( $attachment_id, $snapshot['after'], 'gk_zuordnung' );
    if ( is_wp_error( $result ) ) {
        delete_post_meta( $attachment_id, '_gk_media_scope_rollback', $snapshot );
        return $result;
    }
    return true;
}

/**
 * Roll back only if no later edit has changed the reviewed assignment.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $nonce Per-object security nonce.
 */
function gk_rollback_media_scope( $attachment_id, $nonce ) {
    if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'edit_post', $attachment_id ) || ! wp_verify_nonce( $nonce, 'gk_media_scope_' . $attachment_id ) || 'attachment' !== get_post_type( $attachment_id ) ) {
        return new WP_Error( 'gk_media_permission', __( 'Keine Berechtigung oder ungültige Sicherheitsprüfung.', 'neurg-kreisverband' ) );
    }
    $snapshot = get_post_meta( $attachment_id, '_gk_media_scope_rollback', true );
    if ( ! is_array( $snapshot ) || gk_object_scope_ids( $attachment_id ) !== $snapshot['after'] ) {
        return new WP_Error( 'gk_media_conflict', __( 'Keine passende Rücknahme vorhanden oder zwischenzeitlich geändert. Bitte manuell prüfen.', 'neurg-kreisverband' ) );
    }
    foreach ( $snapshot['before'] as $term_id ) {
        if ( ! term_exists( $term_id, 'gk_zuordnung' ) ) {
            return new WP_Error( 'gk_media_missing_term', __( 'Eine ursprüngliche Zuordnung wurde entfernt. Bitte manuell prüfen.', 'neurg-kreisverband' ) );
        }
    }
    $result = wp_set_object_terms( $attachment_id, $snapshot['before'], 'gk_zuordnung' );
    if ( is_wp_error( $result ) ) {
        return $result;
    }
    delete_post_meta( $attachment_id, '_gk_media_scope_rollback', $snapshot );
    return true;
}

/** Add review below Media. */
function gk_register_media_review() {
    add_submenu_page( 'upload.php', __( 'Medienzuordnung prüfen', 'neurg-kreisverband' ), __( 'Zuordnung prüfen', 'neurg-kreisverband' ), 'manage_options', 'gk-media-review', 'gk_render_media_review' );
}
add_action( 'admin_menu', 'gk_register_media_review', 30 );

/** Render a bounded, read-only-by-default review list with explicit changes. */
function gk_render_media_review() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'neurg-kreisverband' ), '', array( 'response' => 403 ) );
    }
    echo '<div class="wrap"><h1>' . esc_html__( 'Medienzuordnung prüfen', 'neurg-kreisverband' ) . '</h1><p>' . esc_html__( 'Diese Vorschau verändert nichts. Vor Änderungen Datenbank und Dateien sichern. Vorschläge beruhen auf Elternbeitrag oder dem OV-Benutzernamen; widersprüchliche und gemeinsam genutzte Bilder manuell prüfen. Nur die Zuordnung des gewählten Mediums wird geändert; Datei, Bild-ID und bestehende Beitragsbilder bleiben erhalten. Rücknahme ist möglich, solange die Zuordnung danach nicht verändert wurde.', 'neurg-kreisverband' ) . '</p>';
    // phpcs:disable WordPress.Security.NonceVerification.Missing -- Both mutation helpers verify the per-attachment nonce before mutation.
    if ( isset( $_POST['gk_media_id'], $_POST['gk_media_nonce'], $_POST['gk_media_action'] ) ) {
        $id     = absint( $_POST['gk_media_id'] );
        $nonce  = sanitize_text_field( wp_unslash( $_POST['gk_media_nonce'] ) );
        $action = sanitize_key( wp_unslash( $_POST['gk_media_action'] ) );
        $result = 'rollback' === $action ? gk_rollback_media_scope( $id, $nonce ) : gk_apply_media_scope( $id, absint( $_POST['gk_media_term'] ?? 0 ), sanitize_text_field( wp_unslash( $_POST['gk_media_fingerprint'] ?? '' ) ), $nonce );
        echo '<div class="notice ' . ( is_wp_error( $result ) ? 'notice-error' : 'notice-success' ) . '"><p>' . esc_html( is_wp_error( $result ) ? $result->get_error_message() : __( 'Zuordnung gespeichert. Datei und Verwendungen bleiben unverändert.', 'neurg-kreisverband' ) ) . '</p></div>';
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only pagination.
    $page  = max( 1, absint( $_GET['paged'] ?? 1 ) );
    $query = new WP_Query(
        array(
			'post_type'      => 'attachment',
			'post_status'    => array( 'inherit', 'private', 'trash' ),
			'posts_per_page' => 50,
			'paged'          => $page,
			'orderby'        => 'ID',
			'order'          => 'ASC',
        )
    );
    $terms = get_terms(
        array(
			'taxonomy'   => 'gk_zuordnung',
			'hide_empty' => false,
        )
    );
    echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Medium', 'neurg-kreisverband' ) . '</th><th>' . esc_html__( 'Aktuelle Zuordnung', 'neurg-kreisverband' ) . '</th><th>' . esc_html__( 'Geprüfte Änderung', 'neurg-kreisverband' ) . '</th></tr></thead><tbody>';
    foreach ( $query->posts as $post ) {
        $preview = gk_media_scope_preview( $post->ID );
        $labels  = wp_get_object_terms( $post->ID, 'gk_zuordnung', array( 'fields' => 'names' ) );
        echo '<tr><td><a href="' . esc_url( get_edit_post_link( $post->ID ) ) . '">' . esc_html( '#' . $post->ID . ' ' . $post->post_title ) . '</a></td><td>' . esc_html( $labels ? implode( ', ', $labels ) : __( 'Unzugeordnet (Altbestand)', 'neurg-kreisverband' ) ) . '</td><td>';
        echo '<form method="post"><input type="hidden" name="gk_media_id" value="' . esc_attr( $post->ID ) . '"><input type="hidden" name="gk_media_fingerprint" value="' . esc_attr( $preview['fingerprint'] ) . '">';
        wp_nonce_field( 'gk_media_scope_' . $post->ID, 'gk_media_nonce' );
        if ( get_post_meta( $post->ID, '_gk_media_scope_rollback', true ) ) {
            echo '<button class="button" name="gk_media_action" value="rollback">' . esc_html__( 'Letzte Zuordnung rückgängig machen', 'neurg-kreisverband' ) . '</button>';
        } else {
            echo '<label class="screen-reader-text" for="gk-media-' . esc_attr( $post->ID ) . '">' . esc_html__( 'Geprüfte Zielzuordnung', 'neurg-kreisverband' ) . '</label><select name="gk_media_term" id="gk-media-' . esc_attr( $post->ID ) . '"><option value="0">' . esc_html__( 'Bitte prüfen und auswählen', 'neurg-kreisverband' ) . '</option>';
            foreach ( $terms as $term ) {
                // Deliberately no automatic selection: even a proposal requires review.
                echo '<option value="' . esc_attr( $term->term_id ) . '">' . esc_html( $term->name ) . '</option>';
            }
            echo '</select> <button class="button" name="gk_media_action" value="apply">' . esc_html__( 'Geprüfte Zuordnung übernehmen', 'neurg-kreisverband' ) . '</button>';
            $proposal = $preview['proposed'] ? get_term( $preview['proposed'], 'gk_zuordnung' ) : null;
            /* translators: %s: suggested KV or OV name. */
            echo '<p>' . esc_html( $preview['conflict'] ? __( 'Widersprüchliche Hinweise – manuell prüfen.', 'neurg-kreisverband' ) : ( $proposal ? sprintf( __( 'Vorschlag: %s', 'neurg-kreisverband' ), $proposal->name ) : __( 'Kein eindeutiger Vorschlag.', 'neurg-kreisverband' ) ) ) . '</p>';
        }
        echo '</form></td></tr>';
    }
    echo '</tbody></table>';
    if ( $page > 1 ) {
        echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $page - 1 ) ) . '">' . esc_html__( 'Vorherige Seite', 'neurg-kreisverband' ) . '</a> ';
    }
    if ( $page < $query->max_num_pages ) {
        echo '<a class="button" href="' . esc_url( add_query_arg( 'paged', $page + 1 ) ) . '">' . esc_html__( 'Nächste Seite', 'neurg-kreisverband' ) . '</a>';
    }
    echo '</div>';
}
