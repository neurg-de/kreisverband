<?php
/**
 * Kreiskarte — Admin Tool
 *
 * Two-phase workflow:
 *   Phase 1 (one-time): Search Landkreis → preview map → confirm & save
 *   Phase 2 (ongoing):  Configure Gemeinden — types, links, create OV entries
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'gk_kreiskarte_generator_menu' );
add_action( 'wp_ajax_gk_save_kreiskarte', 'gk_ajax_save_kreiskarte' );
add_action( 'wp_ajax_gk_update_kreiskarte_mappings', 'gk_ajax_update_kreiskarte_mappings' );
add_action( 'wp_ajax_gk_create_ortsverbaende', 'gk_ajax_create_ortsverbaende' );
add_action( 'wp_ajax_gk_get_ortsverbaende', 'gk_ajax_get_ortsverbaende' );

function gk_kreiskarte_generator_menu() {
    add_submenu_page(
        'gk-settings',
        'Kreiskarte',
        'Kreiskarte',
        'manage_options',
        'kreiskarte-generator',
        'gk_kreiskarte_generator_page'
    );
}

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

function gk_kreiskarte_generator_page() {
    $json_path = GK_DIR . '/lib/data/kreiskarte.json';
    $has_map   = file_exists( $json_path );
    $json_data = $has_map ? file_get_contents( $json_path ) : '';
    ?>
    <div class="wrap gk-kreiskarte-generator">

        <!-- ============================================================
             PHASE 1 — Load a map (hidden once a map exists)
             ============================================================ -->
        <div id="gk-phase-load" <?php if ( $has_map ) echo 'style="display:none"'; ?>>

            <h1>Kreiskarte einrichten</h1>
            <p class="description">
                Suche deinen Landkreis. Das Tool l&auml;dt die Gemeindegrenzen von OpenStreetMap
                und erstellt eine interaktive Karte.
            </p>

            <!-- Search -->
            <div id="gk-search" class="gk-card">
                <div class="gk-search-row">
                    <input type="text" id="gk-search-input"
                           placeholder="z.B. Landkreis Starnberg, Landkreis Neuburg-Schrobenhausen..."
                           class="regular-text" autofocus />
                    <button type="button" id="gk-search-btn" class="button button-primary">Suchen</button>
                </div>
                <div id="gk-search-results"></div>
            </div>

            <!-- Loading -->
            <div id="gk-loading" class="gk-card" style="display:none">
                <div class="gk-progress">
                    <div class="gk-progress-bar"></div>
                </div>
                <p id="gk-loading-status">Lade Gemeindegrenzen...</p>
            </div>

            <!-- Preview (after loading, before saving) -->
            <div id="gk-preview" class="gk-card" style="display:none">
                <div id="gk-preview-map"></div>
                <div id="gk-preview-info"></div>
                <div class="gk-preview-actions">
                    <button type="button" id="gk-confirm-btn" class="button button-primary button-hero">
                        Karte &uuml;bernehmen
                    </button>
                    <button type="button" id="gk-back-btn" class="button">
                        Andere suchen
                    </button>
                </div>
                <div id="gk-confirm-status"></div>
            </div>
        </div>

        <!-- ============================================================
             PHASE 2 — Configure Gemeinden (permanent workspace)
             ============================================================ -->
        <div id="gk-phase-config" <?php if ( ! $has_map ) echo 'style="display:none"'; ?>>

            <h1>Kreiskarte</h1>

            <div id="gk-config-map"></div>

            <h2>Gemeinden konfigurieren</h2>
            <p class="description">
                W&auml;hle f&uuml;r jede Gemeinde den Typ, ordne sie einem bestehenden Ortsverband zu
                oder setze einen eigenen Link.
            </p>

            <div id="gk-config-table"></div>

            <div class="gk-config-actions">
                <button type="button" id="gk-save-btn" class="button button-primary button-hero">
                    Speichern
                </button>
                <button type="button" id="gk-create-btn" class="button button-hero">
                    Markierte Eintr&auml;ge anlegen
                </button>
            </div>
            <div id="gk-config-status"></div>

            <hr />
            <details class="gk-reload-section">
                <summary>Karte neu laden</summary>
                <p class="description">
                    Lade die Karte von OpenStreetMap neu. Die Gemeinde-Konfiguration bleibt erhalten,
                    sofern die Gemeinde-Slugs &uuml;bereinstimmen.
                </p>
                <div class="gk-search-row">
                    <input type="text" id="gk-reload-input"
                           placeholder="z.B. Landkreis Starnberg..."
                           class="regular-text" />
                    <button type="button" id="gk-reload-btn" class="button button-primary">Suchen</button>
                </div>
                <div id="gk-reload-results"></div>
                <div id="gk-reload-loading" style="display:none">
                    <div class="gk-progress"><div class="gk-progress-bar"></div></div>
                    <p id="gk-reload-status">Lade Gemeindegrenzen...</p>
                </div>
            </details>
        </div>

    </div>

    <script>
        window.gkExistingKreiskarte = <?php echo $has_map ? $json_data : 'null'; ?>;
    </script>
    <?php
}


// ── AJAX Handlers ──────────────────────────────────────────────────────────

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
        wp_send_json_error( 'Datei konnte nicht geschrieben werden. Prüfe die Schreibrechte für ' . $dir );
    }

    wp_send_json_success( array( 'bytes' => $written ) );
}

function gk_ajax_update_kreiskarte_mappings() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $json_path = GK_DIR . '/lib/data/kreiskarte.json';
    if ( ! file_exists( $json_path ) ) {
        wp_send_json_error( 'Keine Kreiskarte vorhanden.' );
    }

    $data = json_decode( file_get_contents( $json_path ), true );
    if ( ! $data || empty( $data['municipalities'] ) ) {
        wp_send_json_error( 'Ungültige Kartendaten.' );
    }

    $mappings = json_decode( stripslashes( $_POST['mappings'] ?? '' ), true );
    if ( ! is_array( $mappings ) ) {
        wp_send_json_error( 'Ungültige Zuordnungsdaten.' );
    }

    $new_munis = array();
    foreach ( $data['municipalities'] as $slug => $muni ) {
        if ( isset( $mappings[ $slug ] ) ) {
            $m    = $mappings[ $slug ];
            $type = sanitize_text_field( $m['type'] ?? $muni['type'] ?? 'ov' );
            $muni['type'] = $type;

            // Link field only for the 'link' type.
            if ( $type === 'link' ) {
                $link = esc_url_raw( $m['link'] ?? '' );
                if ( $link ) {
                    $muni['link'] = $link;
                } else {
                    unset( $muni['link'] );
                }
            } else {
                unset( $muni['link'] );
            }

            // Slug remapping only for WP types that have an OV assignment.
            $needs_wp = in_array( $type, array( 'ov', 'ortsgruppe', 'werbung' ), true );
            $new_slug = $needs_wp ? sanitize_title( $m['ovSlug'] ?? '' ) : '';
            $new_munis[ $new_slug ?: $slug ] = $muni;
        } else {
            $new_munis[ $slug ] = $muni;
        }
    }
    $data['municipalities'] = $new_munis;

    $json    = json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
    $written = file_put_contents( $json_path, $json );

    if ( $written === false ) {
        wp_send_json_error( 'Datei konnte nicht geschrieben werden.' );
    }

    wp_send_json_success( array( 'bytes' => $written ) );
}

function gk_ajax_create_ortsverbaende() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $municipalities = json_decode( stripslashes( $_POST['municipalities'] ?? '' ), true );
    if ( ! is_array( $municipalities ) ) {
        wp_send_json_error( 'Ungültige Daten.' );
    }

    $created = 0;
    $skipped = 0;
    $errors  = array();

    foreach ( $municipalities as $muni ) {
        $slug = sanitize_title( $muni['slug'] ?? '' );
        $name = sanitize_text_field( $muni['name'] ?? '' );
        if ( ! $slug || ! $name ) continue;

        $existing = get_term_by( 'slug', $slug, 'gk_zuordnung' );
        if ( $existing ) { $skipped++; continue; }

        $type = sanitize_text_field( $muni['type'] ?? 'ov' );

        $display_name = match ( $type ) {
            'ortsgruppe' => 'Ortsgruppe ' . $name,
            'werbung'    => 'Grüne in ' . $name,
            default      => 'OV ' . $name,
        };

        $result = wp_insert_term( $display_name, 'gk_zuordnung', array( 'slug' => $slug ) );
        if ( is_wp_error( $result ) ) {
            $errors[] = $name . ': ' . $result->get_error_message();
            continue;
        }

        $term_id = $result['term_id'];
        update_term_meta( $term_id, '_gk_ov_type', $type );

        $header_text = match ( $type ) {
            'ortsgruppe' => 'Ortsgruppe Grüne ' . $name,
            'werbung'    => 'Grüne in ' . $name,
            default      => 'Ortsverband Grüne ' . $name,
        };
        update_term_meta( $term_id, '_gk_ov_header', $header_text );

        if ( $type === 'ov' || $type === 'ortsgruppe' ) {
            $page_id = wp_insert_post( array(
                'post_type'     => 'page',
                'post_title'    => $display_name,
                'post_name'     => $slug,
                'post_status'   => 'publish',
                'post_content'  => '',
                'page_template' => 'page-OV.php',
            ) );

            if ( ! is_wp_error( $page_id ) ) {
                wp_set_object_terms( $page_id, array( $term_id ), 'gk_zuordnung' );
                update_term_meta( $term_id, '_gk_homepage_id', $page_id );
            }
        }

        $created++;
    }

    wp_send_json_success( array( 'created' => $created, 'skipped' => $skipped, 'errors' => $errors ) );
}

function gk_ajax_get_ortsverbaende() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $result = array();
    foreach ( gk_get_ov_terms() as $term ) {
        $result[] = array(
            'id'    => $term->term_id,
            'slug'  => $term->slug,
            'title' => $term->name,
            'type'  => gk_get_ov_type( $term->term_id ),
        );
    }
    wp_send_json_success( $result );
}
