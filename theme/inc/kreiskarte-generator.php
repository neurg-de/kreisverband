<?php
/**
 * Kreiskarte Generator — Admin Tool
 *
 * Lets the admin search for a Landkreis on OpenStreetMap,
 * fetches all Gemeinden boundaries via Overpass API,
 * generates the SVG map, and creates zuordnung terms + homepage pages.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'gk_kreiskarte_generator_menu' );
add_action( 'wp_ajax_gk_save_kreiskarte', 'gk_ajax_save_kreiskarte' );
add_action( 'wp_ajax_gk_create_ortsverbaende', 'gk_ajax_create_ortsverbaende' );
add_action( 'wp_ajax_gk_get_ortsverbaende', 'gk_ajax_get_ortsverbaende' );

/**
 * Register admin page under "Einstellungen".
 */
function gk_kreiskarte_generator_menu() {
    add_submenu_page(
        'gk-settings',
        'Kreiskarte erstellen',
        'Kreiskarte erstellen',
        'manage_options',
        'kreiskarte-generator',
        'gk_kreiskarte_generator_page'
    );
}

/**
 * Enqueue generator scripts only on our admin page.
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( $hook !== 'verband_page_kreiskarte-generator' ) {
        return;
    }
    wp_enqueue_script(
        'gk-kreiskarte-generator',
        GK_URI . '/lib/js/kreiskarte-generator.js',
        array(),
        GK_VERSION,
        true
    );
    wp_localize_script( 'gk-kreiskarte-generator', 'gkKreiskarte', array(
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'gk_kreiskarte' ),
    ) );
    wp_enqueue_style(
        'gk-kreiskarte-generator',
        GK_URI . '/lib/css/kreiskarte-generator.css',
        array(),
        GK_VERSION
    );
} );

/**
 * Render the admin page.
 */
function gk_kreiskarte_generator_page() {
    $existing = file_exists( GK_DIR . '/lib/data/kreiskarte.json' );
    ?>
    <div class="wrap gk-kreiskarte-generator">
        <h1>Kreiskarte erstellen</h1>
        <p class="description">
            Suche deinen Landkreis auf OpenStreetMap. Das Tool l&auml;dt automatisch alle Gemeindegrenzen
            und erstellt eine interaktive SVG-Karte sowie die Ortsverband-Eintr&auml;ge.
        </p>

        <?php if ( $existing ) : ?>
            <div class="notice notice-info inline">
                <p>Es existiert bereits eine Kreiskarte. Du kannst sie unten neu generieren.</p>
            </div>
        <?php endif; ?>

        <!-- Step 1: Search -->
        <div id="gk-step-search" class="gk-step">
            <h2>1. Landkreis suchen</h2>
            <div class="gk-search-row">
                <input type="text" id="gk-search-input"
                       placeholder="z.B. Landkreis Starnberg, Landkreis M&uuml;nchen..."
                       class="regular-text" />
                <button id="gk-search-btn" class="button button-primary">Suchen</button>
            </div>
            <div id="gk-search-results"></div>
        </div>

        <!-- Step 2: Loading -->
        <div id="gk-step-loading" class="gk-step" style="display:none;">
            <h2>2. Gemeindegrenzen laden...</h2>
            <div class="gk-progress">
                <div class="gk-progress-bar"></div>
            </div>
            <p id="gk-loading-status">Abfrage an Overpass API...</p>
        </div>

        <!-- Step 3: Preview & Save -->
        <div id="gk-step-preview" class="gk-step" style="display:none;">
            <h2>2. Vorschau</h2>
            <div id="gk-preview-info"></div>
            <div id="gk-preview-map"></div>

            <h2>3. Ortsverb&auml;nde zuordnen</h2>
            <p class="description">
                Ordne die Gemeinden auf der Karte bestehenden Ortsverb&auml;nden zu.
                Gemeinden ohne Zuordnung werden als neue Ortsverb&auml;nde angelegt.
            </p>
            <div id="gk-mapping-table"></div>

            <h2>4. &Uuml;bernehmen</h2>
            <p>
                <label>
                    <input type="checkbox" id="gk-create-ovs" checked />
                    Eintr&auml;ge automatisch anlegen (Ortsverb&auml;nde, Ortsgruppen, Werbeseiten)
                </label>
            </p>
            <button id="gk-save-btn" class="button button-primary button-hero">
                Kreiskarte speichern &amp; Eintr&auml;ge anlegen
            </button>
            <div id="gk-save-status"></div>
        </div>
    </div>
    <?php
}

/**
 * AJAX: Save the generated kreiskarte.json.
 */
function gk_ajax_save_kreiskarte() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $data = json_decode( stripslashes( $_POST['kreiskarte_data'] ?? '' ), true );
    if ( ! $data || empty( $data['municipalities'] ) ) {
        wp_send_json_error( 'Ungültige Kartendaten.' );
    }

    $dir = GK_DIR . '/lib/data';
    if ( ! is_dir( $dir ) ) {
        wp_mkdir_p( $dir );
    }

    $json = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    $written = file_put_contents( $dir . '/kreiskarte.json', $json );

    if ( $written === false ) {
        wp_send_json_error( 'Datei konnte nicht geschrieben werden.' );
    }

    wp_send_json_success( array( 'bytes' => $written ) );
}

/**
 * AJAX: Create zuordnung terms (and optionally homepage pages) for each municipality.
 */
function gk_ajax_create_ortsverbaende() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $municipalities = json_decode( stripslashes( $_POST['municipalities'] ?? '' ), true );
    if ( ! is_array( $municipalities ) ) {
        wp_send_json_error( 'Ungültige Daten.' );
    }

    $created  = 0;
    $skipped  = 0;
    $errors   = array();

    foreach ( $municipalities as $muni ) {
        $slug = sanitize_title( $muni['slug'] ?? '' );
        $name = sanitize_text_field( $muni['name'] ?? '' );

        if ( ! $slug || ! $name ) {
            continue;
        }

        // Check if zuordnung term with this slug already exists.
        $existing = get_term_by( 'slug', $slug, 'gk_zuordnung' );
        if ( $existing ) {
            $skipped++;
            continue;
        }

        $type = sanitize_text_field( $muni['type'] ?? 'ov' );

        // Build display name based on type.
        switch ( $type ) {
            case 'ortsgruppe':
                $display_name = 'Ortsgruppe ' . $name;
                break;
            case 'werbung':
                $display_name = 'Grüne in ' . $name;
                break;
            default:
                $display_name = 'OV ' . $name;
                break;
        }

        // Create the zuordnung term.
        $result = wp_insert_term( $display_name, 'gk_zuordnung', array(
            'slug' => $slug,
        ) );

        if ( is_wp_error( $result ) ) {
            $errors[] = $name . ': ' . $result->get_error_message();
            continue;
        }

        $term_id = $result['term_id'];

        // Store type as term meta.
        update_term_meta( $term_id, '_gk_ov_type', $type );

        // Set default header text.
        $header_text = match ( $type ) {
            'ortsgruppe' => 'Ortsgruppe Grüne ' . $name,
            'werbung'    => 'Grüne in ' . $name,
            default      => 'Ortsverband Grüne ' . $name,
        };
        update_term_meta( $term_id, '_gk_ov_header', $header_text );

        // Create a homepage page for full OVs.
        if ( $type === 'ov' || $type === 'ortsgruppe' ) {
            $page_content = sprintf(
                '<h2>Willkommen bei den Grünen in %s!</h2>' . "\n" .
                '<p>Der %s %s von BÜNDNIS 90/DIE GRÜNEN stellt sich hier vor.</p>',
                esc_html( $name ),
                $type === 'ortsgruppe' ? 'Die Ortsgruppe' : 'Der Ortsverband',
                esc_html( $name )
            );

            $page_id = wp_insert_post( array(
                'post_type'    => 'page',
                'post_title'   => $display_name,
                'post_name'    => $slug,
                'post_status'  => 'publish',
                'post_content' => $page_content,
                'page_template' => 'page-OV.php',
            ) );

            if ( ! is_wp_error( $page_id ) ) {
                // Assign zuordnung to the page.
                wp_set_object_terms( $page_id, array( $term_id ), 'gk_zuordnung' );
                // Store homepage reference on the term.
                update_term_meta( $term_id, '_gk_homepage_id', $page_id );
            }
        }

        $created++;
    }

    wp_send_json_success( array(
        'created' => $created,
        'skipped' => $skipped,
        'errors'  => $errors,
    ) );
}

/**
 * AJAX: Return all existing OV zuordnung terms for the mapping UI.
 */
function gk_ajax_get_ortsverbaende() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );

    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $ov_terms = gk_get_ov_terms();

    $result = array();
    foreach ( $ov_terms as $term ) {
        $result[] = array(
            'id'     => $term->term_id,
            'slug'   => $term->slug,
            'title'  => $term->name,
            'type'   => gk_get_ov_type( $term->term_id ),
        );
    }

    wp_send_json_success( $result );
}
