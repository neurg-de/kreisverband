<?php
/**
 * Setup Wizard
 *
 * Forces essential configuration on first activation:
 *   - Kreisverband name, address, contact
 *   - Impressum page (legally required in Germany)
 *   - Datenschutz page (GDPR required)
 *
 * Shows an admin notice until setup is complete.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Setup Status ────────────────────────────────────────────────────────────

function gk_setup_is_complete() {
    $kv = get_option( 'gk_kv_info', array() );
    return ! empty( $kv['name'] )
        && ! empty( $kv['impressum_page'] )
        && ! empty( $kv['datenschutz_page'] );
}


// ── Admin Notice ────────────────────────────────────────────────────────────

function gk_setup_admin_notice() {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'gk-setup' ) return;
    if ( ! current_user_can( 'edit_theme_options' ) ) return;

    $kv             = get_option( 'gk_kv_info', array() );
    $impressum_id   = ! empty( $kv['impressum_page'] ) ? (int) $kv['impressum_page'] : 0;
    $datenschutz_id = ! empty( $kv['datenschutz_page'] ) ? (int) $kv['datenschutz_page'] : 0;
    $url            = admin_url( 'admin.php?page=gk-setup' );

    // Full setup incomplete — generic notice.
    if ( ! gk_setup_is_complete() ) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Neurg Kreisverband:</strong> Bitte schliesse die ';
        echo '<a href="' . esc_url( $url ) . '">Ersteinrichtung</a> ab ';
        echo '(Impressum, Datenschutz, KV-Daten).</p>';
        echo '</div>';
        return;
    }

    // Setup complete but legal pages have issues — specific warnings.
    $problems = array();

    if ( ! $impressum_id || get_post_status( $impressum_id ) !== 'publish' ) {
        $problems[] = 'Impressum-Seite ist nicht veröffentlicht';
    }
    if ( ! $datenschutz_id || get_post_status( $datenschutz_id ) !== 'publish' ) {
        $problems[] = 'Datenschutz-Seite ist nicht veröffentlicht';
    }

    if ( ! empty( $problems ) ) {
        echo '<div class="notice notice-error">';
        echo '<p><strong>Rechtliche Pflichtseiten fehlen im Footer:</strong> ';
        echo esc_html( implode( ', ', $problems ) ) . '. ';
        echo 'Bitte unter <a href="' . esc_url( $url ) . '">KV-Setup</a> prüfen. ';
        echo 'Impressum und Datenschutz müssen veröffentlicht sein, damit sie im Footer erscheinen.</p>';
        echo '</div>';
    }
}
add_action( 'admin_notices', 'gk_setup_admin_notice' );


// ── Setup Page ──────────────────────────────────────────────────────────────

function gk_setup_menu() {
    add_menu_page(
        'Ersteinrichtung',
        'KV-Setup',
        'edit_theme_options',
        'gk-setup',
        'gk_setup_page_cb',
        'dashicons-admin-generic',
        2
    );
}
add_action( 'admin_menu', 'gk_setup_menu' );

function gk_register_setup_settings() {
    register_setting( 'gk_setup', 'gk_kv_info', array(
        'type'              => 'array',
        'sanitize_callback' => 'gk_sanitize_kv_info',
        'default'           => array(),
    ) );
}
add_action( 'admin_init', 'gk_register_setup_settings' );

function gk_sanitize_kv_info( $input ) {
    if ( ! is_array( $input ) ) return array();

    $clean = array(
        'name'              => sanitize_text_field( $input['name'] ?? '' ),
        'short_name'        => sanitize_text_field( $input['short_name'] ?? '' ),
        'address'           => sanitize_textarea_field( $input['address'] ?? '' ),
        'email'             => sanitize_email( $input['email'] ?? '' ),
        'phone'             => sanitize_text_field( $input['phone'] ?? '' ),
        'website'           => esc_url_raw( $input['website'] ?? '' ),
        'impressum_page'    => absint( $input['impressum_page'] ?? 0 ),
        'datenschutz_page'  => absint( $input['datenschutz_page'] ?? 0 ),
        'social_facebook'   => sanitize_text_field( $input['social_facebook'] ?? '' ),
        'social_instagram'  => sanitize_text_field( $input['social_instagram'] ?? '' ),
        'social_x'          => sanitize_text_field( $input['social_x'] ?? '' ),
        'social_tiktok'     => sanitize_text_field( $input['social_tiktok'] ?? '' ),
        'social_threads'    => sanitize_text_field( $input['social_threads'] ?? '' ),
        'social_mastodon'   => sanitize_text_field( $input['social_mastodon'] ?? '' ),
        'social_bluesky'    => sanitize_text_field( $input['social_bluesky'] ?? '' ),
        'social_youtube'    => sanitize_text_field( $input['social_youtube'] ?? '' ),
    );

    // Sanitize Verbände list.
    $verbaende = array();
    if ( ! empty( $input['verbaende'] ) && is_array( $input['verbaende'] ) ) {
        foreach ( $input['verbaende'] as $entry ) {
            $name = sanitize_text_field( $entry['name'] ?? '' );
            $url  = esc_url_raw( $entry['url'] ?? '' );
            if ( $name && $url ) {
                $verbaende[] = array( 'name' => $name, 'url' => $url );
            }
        }
    }
    $clean['verbaende'] = $verbaende;

    // Auto-create Impressum page if requested
    if ( ! empty( $input['create_impressum'] ) && empty( $clean['impressum_page'] ) ) {
        $page_id = wp_insert_post( array(
            'post_title'   => 'Impressum',
            'post_content' => gk_impressum_template( $clean ),
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ) );
        if ( ! is_wp_error( $page_id ) ) {
            $clean['impressum_page'] = $page_id;
        }
    }

    // Auto-create Datenschutz page if requested
    if ( ! empty( $input['create_datenschutz'] ) && empty( $clean['datenschutz_page'] ) ) {
        $page_id = wp_insert_post( array(
            'post_title'   => 'Datenschutzerklärung',
            'post_content' => '<!-- Bitte ergänze hier deine Datenschutzerklärung -->',
            'post_status'  => 'draft',
            'post_type'    => 'page',
        ) );
        if ( ! is_wp_error( $page_id ) ) {
            $clean['datenschutz_page'] = $page_id;
            // Set as WordPress privacy page
            update_option( 'wp_page_for_privacy_policy', $page_id );
        }
    }

    return $clean;
}

function gk_impressum_template( $kv ) {
    $content  = "<h2>Angaben gemäß § 5 TMG</h2>\n";
    $content .= "<p>" . esc_html( $kv['name'] ) . "<br>\n";
    $content .= nl2br( esc_html( $kv['address'] ) ) . "</p>\n\n";
    $content .= "<h3>Kontakt</h3>\n<p>";
    if ( $kv['phone'] ) $content .= "Telefon: " . esc_html( $kv['phone'] ) . "<br>\n";
    if ( $kv['email'] ) $content .= "E-Mail: " . esc_html( $kv['email'] );
    $content .= "</p>\n\n";
    $content .= "<h3>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h3>\n";
    $content .= "<p>" . esc_html( $kv['name'] ) . "<br>\n";
    $content .= nl2br( esc_html( $kv['address'] ) ) . "</p>\n";
    return $content;
}


function gk_setup_page_cb() {
    if ( ! current_user_can( 'edit_theme_options' ) ) return;

    $kv = get_option( 'gk_kv_info', array() );

    // Migrate social_twitter → social_x (one-time, on display).
    if ( empty( $kv['social_x'] ) && ! empty( $kv['social_twitter'] ) ) {
        $kv['social_x'] = $kv['social_twitter'];
    }

    $kv = wp_parse_args( $kv, array(
        'name' => '', 'short_name' => '', 'address' => '', 'email' => '',
        'phone' => '', 'website' => '', 'impressum_page' => 0, 'datenschutz_page' => 0,
        'social_facebook' => '', 'social_instagram' => '', 'social_x' => '',
        'social_tiktok' => '', 'social_threads' => '', 'social_mastodon' => '',
        'social_bluesky' => '', 'social_youtube' => '',
        'verbaende' => array(),
    ) );

    // Pre-fill defaults when empty.
    if ( empty( $kv['verbaende'] ) ) {
        $kv['verbaende'] = gk_verbaende_defaults();
    }

    $complete = gk_setup_is_complete();
    $pages = get_pages( array( 'sort_order' => 'ASC', 'sort_column' => 'post_title' ) );
    ?>
    <div class="wrap">
        <h1>Kreisverband Ersteinrichtung</h1>

        <?php if ( $complete ) : ?>
            <div class="notice notice-success"><p>Einrichtung abgeschlossen!</p></div>
        <?php else : ?>
            <div class="notice notice-info"><p>Bitte füll alle Pflichtfelder (*) aus, um die Einrichtung abzuschliessen.</p></div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'gk_setup' ); ?>

            <h2>Kreisverband</h2>
            <table class="form-table">
            <tr>
                <th><label for="gk_kv_name">Name *</label></th>
                <td><input type="text" name="gk_kv_info[name]" id="gk_kv_name" value="<?php echo esc_attr( $kv['name'] ); ?>" class="regular-text" required /><br><span class="description">z.B. "BÜNDNIS 90/DIE GRÜNEN Kreisverband Starnberg"</span></td>
            </tr>
            <tr>
                <th><label for="gk_kv_short">Kurzname</label></th>
                <td><input type="text" name="gk_kv_info[short_name]" id="gk_kv_short" value="<?php echo esc_attr( $kv['short_name'] ); ?>" class="regular-text" /><br><span class="description">z.B. "Grüne Starnberg" (für Anzeige in Navigation etc.)</span></td>
            </tr>
            <tr>
                <th><label for="gk_kv_address">Anschrift *</label></th>
                <td><textarea name="gk_kv_info[address]" id="gk_kv_address" rows="3" cols="50" required><?php echo esc_textarea( $kv['address'] ); ?></textarea></td>
            </tr>
            <tr>
                <th><label for="gk_kv_email">E-Mail *</label></th>
                <td><input type="email" name="gk_kv_info[email]" id="gk_kv_email" value="<?php echo esc_attr( $kv['email'] ); ?>" class="regular-text" required /></td>
            </tr>
            <tr>
                <th><label for="gk_kv_phone">Telefon</label></th>
                <td><input type="text" name="gk_kv_info[phone]" id="gk_kv_phone" value="<?php echo esc_attr( $kv['phone'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_kv_website">Website</label></th>
                <td><input type="url" name="gk_kv_info[website]" id="gk_kv_website" value="<?php echo esc_attr( $kv['website'] ); ?>" class="regular-text" /></td>
            </tr>
            </table>

            <h2>Soziale Medien</h2>
            <p class="description">URL oder Username &mdash; beides funktioniert.</p>
            <table class="form-table">
            <tr>
                <th><label for="gk_social_instagram">Instagram</label></th>
                <td><input type="text" name="gk_kv_info[social_instagram]" id="gk_social_instagram" value="<?php echo esc_attr( $kv['social_instagram'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_facebook">Facebook</label></th>
                <td><input type="text" name="gk_kv_info[social_facebook]" id="gk_social_facebook" value="<?php echo esc_attr( $kv['social_facebook'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_x">X</label></th>
                <td><input type="text" name="gk_kv_info[social_x]" id="gk_social_x" value="<?php echo esc_attr( $kv['social_x'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_tiktok">TikTok</label></th>
                <td><input type="text" name="gk_kv_info[social_tiktok]" id="gk_social_tiktok" value="<?php echo esc_attr( $kv['social_tiktok'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_threads">Threads</label></th>
                <td><input type="text" name="gk_kv_info[social_threads]" id="gk_social_threads" value="<?php echo esc_attr( $kv['social_threads'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_bluesky">Bluesky</label></th>
                <td><input type="text" name="gk_kv_info[social_bluesky]" id="gk_social_bluesky" value="<?php echo esc_attr( $kv['social_bluesky'] ); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th><label for="gk_social_mastodon">Mastodon</label></th>
                <td><input type="text" name="gk_kv_info[social_mastodon]" id="gk_social_mastodon" value="<?php echo esc_attr( $kv['social_mastodon'] ); ?>" class="regular-text" /><br><span class="description">URL oder @user@instanz</span></td>
            </tr>
            <tr>
                <th><label for="gk_social_youtube">YouTube</label></th>
                <td><input type="text" name="gk_kv_info[social_youtube]" id="gk_social_youtube" value="<?php echo esc_attr( $kv['social_youtube'] ); ?>" class="regular-text" /></td>
            </tr>
            </table>

            <h2>Verbände</h2>
            <p class="description">Links zu übergeordneten Verbänden, die im Footer angezeigt werden. Grüne.de und die Bundestagsfraktion sind vorausgefüllt.</p>
            <table class="form-table" id="gk-verbaende-table">
                <thead>
                <tr>
                    <th style="width:40%">Name</th>
                    <th style="width:50%">URL</th>
                    <th style="width:10%"></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ( $kv['verbaende'] as $i => $v ) : ?>
                <tr class="gk-verband-row">
                    <td><input type="text" name="gk_kv_info[verbaende][<?php echo $i; ?>][name]" value="<?php echo esc_attr( $v['name'] ); ?>" class="regular-text" /></td>
                    <td><input type="url" name="gk_kv_info[verbaende][<?php echo $i; ?>][url]" value="<?php echo esc_attr( $v['url'] ); ?>" class="regular-text" /></td>
                    <td><button type="button" class="button gk-remove-verband" title="Entfernen">&times;</button></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p><button type="button" class="button" id="gk-add-verband">+ Verband hinzufügen</button></p>

            <script>
            (function(){
                var table = document.getElementById('gk-verbaende-table');
                var tbody = table.querySelector('tbody');

                document.getElementById('gk-add-verband').addEventListener('click', function(){
                    var idx = tbody.querySelectorAll('tr').length;
                    var tr = document.createElement('tr');
                    tr.className = 'gk-verband-row';
                    tr.innerHTML =
                        '<td><input type="text" name="gk_kv_info[verbaende][' + idx + '][name]" value="" class="regular-text" /></td>' +
                        '<td><input type="url" name="gk_kv_info[verbaende][' + idx + '][url]" value="" class="regular-text" /></td>' +
                        '<td><button type="button" class="button gk-remove-verband" title="Entfernen">&times;</button></td>';
                    tbody.appendChild(tr);
                });

                tbody.addEventListener('click', function(e){
                    if ( e.target.classList.contains('gk-remove-verband') ) {
                        e.target.closest('tr').remove();
                        // Re-index names so PHP receives a clean array.
                        tbody.querySelectorAll('tr').forEach(function(tr, i){
                            tr.querySelectorAll('input').forEach(function(inp){
                                inp.name = inp.name.replace(/\[verbaende\]\[\d+\]/, '[verbaende][' + i + ']');
                            });
                        });
                    }
                });
            })();
            </script>

            <h2>Pflichtseiten</h2>
            <p class="description">Impressum und Datenschutz sind gesetzlich vorgeschrieben (TMG &sect; 5, DSGVO) und erscheinen automatisch im Footer der Website. Beide Seiten müssen veröffentlicht sein.</p>
            <table class="form-table">
            <tr>
                <th><label for="gk_impressum">Impressum *</label></th>
                <td>
                    <select name="gk_kv_info[impressum_page]" id="gk_impressum">
                        <option value="">-- Seite wählen --</option>
                        <?php foreach ( $pages as $p ) : ?>
                            <option value="<?php echo $p->ID; ?>" <?php selected( $kv['impressum_page'], $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label style="margin-left:1em"><input type="checkbox" name="gk_kv_info[create_impressum]" value="1" /> Neue Impressum-Seite erstellen</label>
                </td>
            </tr>
            <tr>
                <th><label for="gk_datenschutz">Datenschutz *</label></th>
                <td>
                    <select name="gk_kv_info[datenschutz_page]" id="gk_datenschutz">
                        <option value="">-- Seite wählen --</option>
                        <?php foreach ( $pages as $p ) : ?>
                            <option value="<?php echo $p->ID; ?>" <?php selected( $kv['datenschutz_page'], $p->ID ); ?>><?php echo esc_html( $p->post_title ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label style="margin-left:1em"><input type="checkbox" name="gk_kv_info[create_datenschutz]" value="1" /> Neue Datenschutz-Seite erstellen (Entwurf)</label>
                </td>
            </tr>
            </table>

            <?php submit_button( 'Einstellungen speichern' ); ?>
        </form>
    </div>
    <?php
}


// ── Template Tags: Access KV Info ───────────────────────────────────────────

function gk_get_kv_info( $key = '' ) {
    $kv = get_option( 'gk_kv_info', array() );
    if ( $key ) {
        return isset( $kv[ $key ] ) ? $kv[ $key ] : '';
    }
    return $kv;
}

function gk_kv_name() {
    return gk_get_kv_info( 'name' );
}

function gk_kv_short_name() {
    $short = gk_get_kv_info( 'short_name' );
    return $short ? $short : gk_get_kv_info( 'name' );
}

function gk_verbaende_defaults() {
    return array(
        array( 'name' => 'Bundesverband',      'url' => 'https://www.gruene.de' ),
        array( 'name' => 'Bundestagsfraktion', 'url' => 'https://www.gruene-bundestag.de' ),
    );
}

function gk_get_verbaende() {
    $verbaende = gk_get_kv_info( 'verbaende' );
    if ( empty( $verbaende ) || ! is_array( $verbaende ) ) {
        return gk_verbaende_defaults();
    }
    return $verbaende;
}
