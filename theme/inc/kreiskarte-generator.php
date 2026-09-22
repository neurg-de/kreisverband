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

/**
 * Kreiskarte generator menu.
 */
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

add_action(
    'admin_enqueue_scripts',
    function ( $hook ) {
		if ( 'verband_page_kreiskarte-generator' !== $hook ) {
			return;
		}
		wp_enqueue_script(
            'gk-kreiskarte-generator',
            GK_URI . '/lib/js/kreiskarte-generator.js',
            array(),
            GK_VERSION,
            true
		);
		wp_localize_script(
            'gk-kreiskarte-generator',
            'gkKreiskarte',
            array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'gk_kreiskarte' ),
            )
		);
		wp_enqueue_style(
            'gk-kreiskarte-generator',
            GK_URI . '/lib/css/kreiskarte-generator.css',
            array(),
            GK_VERSION
		);
	}
);

/**
 * Kreiskarte generator page.
 */
function gk_kreiskarte_generator_page() {
    $map_data = gk_get_kreiskarte_data();
    $has_map  = is_array( $map_data ) && ! empty( $map_data['municipalities'] );
    ?>
    <div class="wrap gk-kreiskarte-generator">

        <!-- ============================================================
            PHASE 1 — Load a map (hidden once a map exists)
            ============================================================ -->
        <div id="gk-phase-load"
        <?php
        if ( $has_map ) {
			echo 'style="display:none"';}
		?>
        >

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
        <div id="gk-phase-config"
        <?php
        if ( ! $has_map ) {
			echo 'style="display:none"';}
		?>
        >

            <h1>Kreiskarte</h1>

            <div id="gk-config-map"></div>

            <h2>Gemeinden konfigurieren</h2>
            <p class="description">
                W&auml;hle f&uuml;r jede Gemeinde den Typ und das gew&uuml;nschte Ziel. Die Spalte
                &bdquo;Klickziel auf der Website&ldquo; zeigt den aktuell gespeicherten Stand.
                &Auml;nderungen werden erst nach &bdquo;Speichern&ldquo; &ouml;ffentlich.
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
            <div id="gk-config-status" role="status" aria-live="polite"></div>

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
        window.gkExistingKreiskarte = <?php echo wp_json_encode( $has_map ? $map_data : null, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT ); ?>;
    </script>
    <?php
}


// ── AJAX Handlers ──────────────────────────────────────────────────────────

/**
 * Ajax save kreiskarte.
 */
function gk_ajax_save_kreiskarte() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The JSON decoder validates scalar input and sanitizes every nested string.
    $data = gk_sanitize_kreiskarte_json( isset( $_POST['kreiskarte_data'] ) ? wp_unslash( $_POST['kreiskarte_data'] ) : '' );
    if ( ! is_array( $data ) || empty( $data['municipalities'] ) || ! is_array( $data['municipalities'] ) ) {
        wp_send_json_error( 'Ungültige Kartendaten.' );
    }

    update_option( 'gk_kreiskarte_data', $data, false );
    wp_send_json_success( array( 'bytes' => strlen( wp_json_encode( $data ) ) ) );
}

/**
 * Ajax update kreiskarte mappings.
 */
function gk_ajax_update_kreiskarte_mappings() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    $data = gk_get_kreiskarte_data();
    if ( ! is_array( $data ) || empty( $data['municipalities'] ) || ! is_array( $data['municipalities'] ) ) {
        wp_send_json_error( 'Ungültige Kartendaten.' );
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The JSON decoder validates scalar input and sanitizes every nested string.
    $mappings = gk_sanitize_kreiskarte_json( isset( $_POST['mappings'] ) ? wp_unslash( $_POST['mappings'] ) : '' );
    if ( ! is_array( $mappings ) ) {
        wp_send_json_error( 'Ungültige Zuordnungsdaten.' );
    }

    $new_munis = array();
    foreach ( $data['municipalities'] as $slug => $muni ) {
        if ( ! is_array( $muni ) ) {
            continue;
        }
        if ( isset( $mappings[ $slug ] ) && is_array( $mappings[ $slug ] ) ) {
            $m    = $mappings[ $slug ];
            $type = sanitize_text_field( $m['type'] ?? $muni['type'] ?? 'ov' );
            if ( ! in_array( $type, array( 'ov', 'ortsgruppe', 'werbung', 'link', 'keine' ), true ) ) {
                wp_send_json_error( 'Ungültiger Gemeindetyp.' );
            }
            $muni['type'] = $type;

            // Link field only for the 'link' type.
            if ( 'link' === $type ) {
                $link = gk_public_website_url( $m['link'] ?? '' );
                if ( $link ) {
                    $muni['link'] = $link;
                } else {
                    wp_send_json_error( sprintf( 'Für %s fehlt eine gültige externe Website.', sanitize_text_field( $muni['name'] ?? $slug ) ) );
                }
            } else {
                unset( $muni['link'] );
            }

            // Keep the municipality key stable; multiple municipalities may share one OV.
            $needs_wp = in_array( $type, array( 'ov', 'ortsgruppe', 'werbung' ), true );
            $ov_slug  = $needs_wp ? sanitize_title( $m['ovSlug'] ?? '' ) : '';
            if ( $ov_slug ) {
                if ( ! get_term_by( 'slug', $ov_slug, 'gk_zuordnung' ) ) {
                    wp_send_json_error( sprintf( 'Der zugeordnete Ortsverband für %s existiert nicht.', sanitize_text_field( $muni['name'] ?? $slug ) ) );
                }
                $muni['ovSlug'] = $ov_slug;
            } else {
                unset( $muni['ovSlug'] );
            }
            $new_munis[ $slug ] = $muni;
        } else {
            $new_munis[ $slug ] = $muni;
        }
    }
    $data['municipalities'] = $new_munis;

    update_option( 'gk_kreiskarte_data', $data, false );
    wp_send_json_success( array( 'bytes' => strlen( wp_json_encode( $data ) ) ) );
}

/**
 * Ajax create ortsverbaende.
 */
function gk_ajax_create_ortsverbaende() {
    check_ajax_referer( 'gk_kreiskarte', 'nonce' );
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Keine Berechtigung.' );
    }

    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- The JSON decoder validates scalar input and sanitizes every nested string.
    $municipalities = gk_sanitize_kreiskarte_json( isset( $_POST['municipalities'] ) ? wp_unslash( $_POST['municipalities'] ) : '' );
    if ( ! is_array( $municipalities ) ) {
        wp_send_json_error( 'Ungültige Daten.' );
    }

    $created = 0;
    $skipped = 0;
    $errors  = array();

    foreach ( $municipalities as $muni ) {
        if ( ! is_array( $muni ) ) {
            continue;
        }
        $slug = sanitize_title( $muni['slug'] ?? '' );
        $name = sanitize_text_field( $muni['name'] ?? '' );
        if ( ! $slug || ! $name ) {
			continue;
        }

        $existing = get_term_by( 'slug', $slug, 'gk_zuordnung' );
        if ( $existing ) {
			++$skipped;
			continue; }

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

        if ( 'ov' === $type || 'ortsgruppe' === $type ) {
            $page_id = wp_insert_post(
                array(
					'post_type'     => 'page',
					'post_title'    => $display_name,
					'post_name'     => $slug,
					'post_status'   => 'publish',
					'post_content'  => '',
					'page_template' => 'page-OV.php',
                )
            );

            if ( ! is_wp_error( $page_id ) ) {
                wp_set_object_terms( $page_id, array( $term_id ), 'gk_zuordnung' );
                update_term_meta( $term_id, '_gk_homepage_id', $page_id );
            }
        }

        ++$created;
    }

    wp_send_json_success(
        array(
			'created' => $created,
			'skipped' => $skipped,
			'errors'  => $errors,
        )
    );
}

/**
 * Ajax get ortsverbaende.
 */
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
    $map     = gk_get_kreiskarte_data();
    $targets = array();
    if ( is_array( $map ) && ! empty( $map['municipalities'] ) ) {
        $ov_data = gk_get_ov_terms_by_slug();
        foreach ( $map['municipalities'] as $slug => $municipality ) {
            $url  = gk_get_municipality_url( $slug, $municipality, $ov_data );
            $kind = 'keine' === ( $municipality['type'] ?? '' ) ? 'Ohne Link' : 'Im Aufbau';
            if ( $url ) {
                if ( 'link' === ( $municipality['type'] ?? '' ) ) {
                    $kind = 'Externe Website';
                } elseif ( str_contains( $url, 'gk_ov_info=' ) ) {
                    $kind = 'Kontaktseite';
                } elseif ( str_starts_with( $url, trailingslashit( home_url() ) ) ) {
                    $kind = 'Lokale OV-Seite';
                } else {
                    $kind = 'OV-Website';
                }
            }
            $targets[ $slug ] = array(
                'url'  => $url,
                'kind' => $kind,
            );
        }
    }
    wp_send_json_success(
        array(
            'ovs'     => $result,
            'map'     => $map,
            'targets' => $targets,
        )
    );
}

/**
 * Decode structured map input and sanitize text while retaining numeric geometry.
 *
 * @param mixed $raw Submitted JSON string.
 * @return array|null
 */
function gk_sanitize_kreiskarte_json( $raw ) {
    if ( ! is_string( $raw ) ) {
        return null;
    }
    $data = json_decode( $raw, true );
    if ( ! is_array( $data ) ) {
        return null;
    }
    return map_deep(
        $data,
        static function ( $value ) {
			return is_string( $value ) ? sanitize_text_field( $value ) : $value;
		}
    );
}
