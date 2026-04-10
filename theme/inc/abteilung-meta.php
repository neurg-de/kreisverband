<?php
/**
 * Per-Abteilung Meta for Persons
 *
 * Stores position, function, and number-visibility per abteilung term
 * in a single structured meta field: _gk_abteilung_meta
 *
 * Structure (serialized array keyed by abteilung slug):
 *   [
 *       'ortsvorstand'   => [ 'position' => '1', 'function' => 'Sprecherin', 'hidden' => false ],
 *       'stadtratsliste' => [ 'position' => '3', 'function' => '',           'hidden' => false ],
 *   ]
 *
 * - position:  Optional number for sorting AND display (e.g. Listenplatz).
 *              Numbers sort first (ASC), empty = last.
 * - function:  Optional role label (e.g. "Sprecherin", "Fraktionsvorsitzende").
 * - hidden:    Hides the position number from display (sorting still works).
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GK_ABTEILUNG_META_KEY', '_gk_abteilung_meta' );


// ── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Get all per-abteilung data for a person.
 *
 * @param int $post_id
 * @return array Keyed by abteilung slug.
 */
function gk_get_abteilung_meta( $post_id ) {
    $data = get_post_meta( $post_id, GK_ABTEILUNG_META_KEY, true );
    return is_array( $data ) ? $data : array();
}

/**
 * Get per-abteilung data for a specific abteilung.
 *
 * @param int    $post_id
 * @param string $abt_slug
 * @return array { position: string, function: string, hidden: bool }
 */
function gk_get_abteilung_meta_for( $post_id, $abt_slug ) {
    $all = gk_get_abteilung_meta( $post_id );
    $entry = $all[ $abt_slug ] ?? array();
    return wp_parse_args( $entry, array(
        'position' => '',
        'function' => '',
        'hidden'   => false,
    ) );
}

/**
 * Save all per-abteilung data for a person.
 *
 * @param int   $post_id
 * @param array $data Keyed by abteilung slug.
 */
function gk_save_abteilung_meta( $post_id, $data ) {
    // Strip empty entries (abteilung no longer assigned)
    $clean = array();
    foreach ( $data as $slug => $entry ) {
        $clean[ $slug ] = array(
            'position' => sanitize_text_field( $entry['position'] ?? '' ),
            'function' => sanitize_text_field( $entry['function'] ?? '' ),
            'hidden'   => ! empty( $entry['hidden'] ),
        );
    }
    update_post_meta( $post_id, GK_ABTEILUNG_META_KEY, $clean );
}


// ── Meta Box ────────────────────────────────────────────────────────────────

add_action( 'add_meta_boxes', 'gk_add_abteilung_meta_box' );
add_action( 'save_post_person', 'gk_save_abteilung_meta_box' );

function gk_add_abteilung_meta_box() {
    add_meta_box(
        'gk_abteilung_meta',
        'Abteilungen',
        'gk_abteilung_meta_cb',
        'person',
        'normal',
        'high'
    );
}

function gk_abteilung_meta_cb( $post ) {
    $terms = wp_get_post_terms( $post->ID, 'abteilung', array( 'fields' => 'all' ) );
    $meta  = gk_get_abteilung_meta( $post->ID );

    wp_nonce_field( 'gk_abteilung_meta_nonce', 'gk_abteilung_meta_nonce' );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        echo '<p class="description">Keine Abteilungen zugewiesen. Weise zuerst Abteilungen in der Seitenleiste zu.</p>';
        return;
    }

    // Sort terms alphabetically
    usort( $terms, fn( $a, $b ) => strcasecmp( $a->name, $b->name ) );
    ?>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th style="width:25%">Abteilung</th>
                <th style="width:15%">Position</th>
                <th style="width:35%">Funktion</th>
                <th style="width:15%">Nr. verstecken</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $terms as $term ) :
            $slug  = $term->slug;
            $entry = wp_parse_args( $meta[ $slug ] ?? array(), array(
                'position' => '',
                'function' => '',
                'hidden'   => false,
            ) );
            $prefix = "gk_abt_meta[{$slug}]";
        ?>
            <tr>
                <td><strong><?php echo esc_html( $term->name ); ?></strong></td>
                <td>
                    <input type="text" name="<?php echo esc_attr( $prefix ); ?>[position]"
                           value="<?php echo esc_attr( $entry['position'] ); ?>"
                           class="small-text" placeholder="z.B. 1" />
                </td>
                <td>
                    <input type="text" name="<?php echo esc_attr( $prefix ); ?>[function]"
                           value="<?php echo esc_attr( $entry['function'] ); ?>"
                           class="regular-text" placeholder="z.B. Sprecherin" />
                </td>
                <td>
                    <label>
                        <input type="checkbox" name="<?php echo esc_attr( $prefix ); ?>[hidden]" value="1"
                               <?php checked( $entry['hidden'] ); ?> />
                        Verstecken
                    </label>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <p class="description">Position bestimmt die Sortierung (aufsteigend). Personen ohne Position werden alphabetisch nach Personen mit Position einsortiert.</p>
    <?php
}

function gk_save_abteilung_meta_box( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! isset( $_POST['gk_abteilung_meta_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['gk_abteilung_meta_nonce'], 'gk_abteilung_meta_nonce' ) ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $raw = $_POST['gk_abt_meta'] ?? array();
    if ( ! is_array( $raw ) ) return;

    gk_save_abteilung_meta( $post_id, $raw );
}
