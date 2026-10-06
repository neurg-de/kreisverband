<?php
/**
 * Explicit public affiliation labels without changing editorial ownership.
 *
 * @package Neurg_Kreisverband
 */

/**
 * Public affiliation label for a person; blank means no special declaration.
 *
 * @param int $post_id Person ID.
 * @return string
 */
function gk_person_affiliation_label( $post_id ) {
    if ( 'person' !== get_post_type( $post_id ) ) {
        return '';
    }
    $labels = array(
		'external'    => 'Parteifremd',
		'independent' => 'Parteilos',
	);
    return $labels[ get_post_meta( $post_id, '_gk_person_affiliation', true ) ] ?? '';
}

/**
 * Public organizational context, separate from the scope used by permissions.
 *
 * @param int $post_id Content ID.
 * @return string
 */
function gk_public_post_zuordnung_slug( $post_id ) {
    $archive = gk_ov_news_context();
    if ( $archive ) {
        return $archive['term']->slug;
    }
    return gk_person_affiliation_label( $post_id ) ? '' : gk_get_post_zuordnung_slug( $post_id );
}

/** Register the editorial affiliation control. */
function gk_person_affiliation_meta_box() {
    add_meta_box( 'gk_person_affiliation', 'Parteizugehörigkeit', 'gk_person_affiliation_meta_box_cb', 'person', 'side' );
}
add_action( 'add_meta_boxes', 'gk_person_affiliation_meta_box' );

/**
 * Render the person affiliation selector.
 *
 * @param WP_Post $post Person.
 */
function gk_person_affiliation_meta_box_cb( $post ) {
    $value = get_post_meta( $post->ID, '_gk_person_affiliation', true );
    wp_nonce_field( 'gk_person_affiliation', 'gk_person_affiliation_nonce' );
    ?>
    <p><label for="gk_person_affiliation_status">Öffentliche Kennzeichnung</label></p>
    <select id="gk_person_affiliation_status" name="gk_person_affiliation_status" class="widefat">
        <option value="" <?php selected( $value, '' ); ?>>Keine besondere Kennzeichnung</option>
        <option value="external" <?php selected( $value, 'external' ); ?>>Parteifremdes Fraktionsmitglied</option>
        <option value="independent" <?php selected( $value, 'independent' ); ?>>Parteilos</option>
    </select>
    <p class="description">Bei einer Kennzeichnung wird die Person weiterhin in ihrer Fraktion angezeigt. Die Zugehörigkeit zur Untergliederung wird öffentlich ausgeblendet. Die interne Zuordnung und Bearbeitungsrechte bleiben erhalten. Nur bestätigte Angaben verwenden.</p>
    <?php
}

/**
 * Persist only explicit, authorized form submissions.
 *
 * @param int $post_id Person ID.
 */
function gk_save_person_affiliation( $post_id ) {
    if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || 'person' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    if ( ! isset( $_POST['gk_person_affiliation_nonce'], $_POST['gk_person_affiliation_status'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['gk_person_affiliation_nonce'] ) ), 'gk_person_affiliation' ) ) {
        return;
    }
    $value = sanitize_key( wp_unslash( $_POST['gk_person_affiliation_status'] ) );
    if ( ! in_array( $value, array( '', 'external', 'independent' ), true ) ) {
        return;
    }
    if ( '' === $value ) {
        delete_post_meta( $post_id, '_gk_person_affiliation' );
    } else {
        update_post_meta( $post_id, '_gk_person_affiliation', $value );
    }
}
add_action( 'save_post_person', 'gk_save_person_affiliation' );
