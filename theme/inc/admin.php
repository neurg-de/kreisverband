<?php
/**
 * Admin customizations: security, editor, role-based restrictions
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * Check if the current user has the administrator role.
 *
 * Do NOT use current_user_can('edit_theme_options') for this purpose
 * because gk_ovadmin also has that capability (for menu management).
 */
function gk_current_user_is_admin() {
    return in_array( 'administrator', (array) wp_get_current_user()->roles, true );
}


// ── Pages Sidebar: KV + OV navigation ───────────────────────────────────────

/**
 * Build zuordnung-based submenus for a post type.
 *
 * KV roles see Kreisverband + all OVs. OV roles see only their own OV.
 *
 * @param string $menu_slug  Parent menu slug (e.g. 'edit.php' or 'edit.php?post_type=page').
 * @param string $capability Capability required to see the submenu items.
 */
function gk_zuordnung_submenu( $menu_slug, $capability ) {
    global $submenu;

    if ( isset( $submenu[ $menu_slug ] ) ) {
        $submenu[ $menu_slug ] = array();
    }

    $user  = wp_get_current_user();
    $roles = (array) $user->roles;
    $sep   = str_contains( $menu_slug, '?' ) ? '&' : '?';

    $kv_roles = array( 'administrator', 'gk_kvautor', 'gk_kvautor_ov' );
    $is_kv    = array_intersect( $kv_roles, $roles );

    $ov_roles = array( 'gk_ovadmin', 'gk_ovautor', 'ovautor' );
    $is_ov    = array_intersect( $ov_roles, $roles );

    if ( $is_kv ) {
        $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
        if ( $kv_term ) {
            add_submenu_page( $menu_slug, 'Kreisverband', 'Kreisverband', $capability,
                $menu_slug . $sep . 'gk_zuordnung=' . $kv_term->slug );
        }

        $ov_terms = get_terms( array(
            'taxonomy'   => 'gk_zuordnung',
            'hide_empty' => false,
            'exclude'    => $kv_term ? array( $kv_term->term_id ) : array(),
            'orderby'    => 'name',
        ) );

        if ( ! is_wp_error( $ov_terms ) ) {
            foreach ( $ov_terms as $term ) {
                add_submenu_page( $menu_slug, $term->name, $term->name, $capability,
                    $menu_slug . $sep . 'gk_zuordnung=' . $term->slug );
            }
        }
    } elseif ( $is_ov ) {
        $term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( $term ) {
            add_submenu_page( $menu_slug, $term->name, $term->name, 'read',
                $menu_slug . $sep . 'gk_zuordnung=' . $term->slug );
        }
    }
}

/**
 * Apply zuordnung submenus to Posts, Pages, Personen, Termine, and Media.
 */
function gk_register_zuordnung_submenus() {
    gk_zuordnung_submenu( 'edit.php',                       'edit_posts' );
    gk_zuordnung_submenu( 'edit.php?post_type=page',        'edit_pages' );
    gk_zuordnung_submenu( 'edit.php?post_type=person',      'edit_posts' );
    gk_zuordnung_submenu( 'edit.php?post_type=gk_event',    'edit_posts' );
    gk_zuordnung_submenu( 'upload.php',                     'upload_files' );

}
add_action( 'admin_menu', 'gk_register_zuordnung_submenus', 20 );


// ── Settings Admin Page ─────────────────────────────────────────────────────

/**
 * Register the "Verband" top-level admin page.
 * KV configuration (homepage) + Ortsverband management.
 */
function gk_settings_admin_menu() {
    $hook = add_menu_page(
        'Verband',
        'Verband',
        'gk_manage_ov',
        'gk-settings',
        'gk_settings_page',
        'dashicons-admin-generic',
        26
    );

    add_action( 'load-' . $hook, function () {
        if ( current_user_can( 'edit_theme_options' ) ) {
            wp_enqueue_media();
        }
    } );
}
add_action( 'admin_menu', 'gk_settings_admin_menu' );

/**
 * Router for the settings admin page.
 *
 * Admins/Kreisadmins see full settings + OV list.
 * OV-Admins only see their own OV edit form.
 */
function gk_settings_page() {
    $user  = wp_get_current_user();
    $roles = (array) $user->roles;

    $admin_roles = array( 'administrator' );
    $is_admin    = (bool) array_intersect( $admin_roles, $roles );

    // OV-Admins: show only their own OV.
    if ( ! $is_admin ) {
        $term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( $term ) {
            $_GET['term_id'] = $term->term_id;
            gk_ortsverband_edit_page();
        } else {
            echo '<div class="wrap"><h1>Verband</h1>';
            echo '<p>Kein Ortsverband f&uuml;r deinen Account gefunden.</p></div>';
        }
        return;
    }

    // Admin sub-views: OV edit / delete.
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['term_id'] ) ) {
        gk_ortsverband_edit_page();
        return;
    }
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['term_id'] ) ) {
        gk_ortsverband_delete_page();
        return;
    }

    // Admin: full settings + OV list.
    gk_settings_main_page();
}

/**
 * Handle save actions for OV term editing.
 */
function gk_ortsverband_handle_save() {
    if ( ! isset( $_POST['gk_ov_save_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['gk_ov_save_nonce'], 'gk_ov_save' ) ) return;
    if ( ! current_user_can( 'gk_manage_ov' ) ) return;

    $term_id  = isset( $_POST['term_id'] ) ? (int) $_POST['term_id'] : 0;
    $is_admin = gk_current_user_is_admin();

    // OV-Admins: can only edit their own OV, not create new ones.
    if ( ! $is_admin ) {
        $user     = wp_get_current_user();
        $own_term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( ! $own_term || $term_id !== (int) $own_term->term_id ) return;
    }

    $name = sanitize_text_field( $_POST['ov_name'] ?? '' );
    $slug = sanitize_title( $_POST['ov_slug'] ?? '' );

    if ( empty( $name ) ) return;

    if ( $term_id ) {
        // Update existing term. OV-Admins cannot change name/slug.
        if ( $is_admin ) {
            wp_update_term( $term_id, 'gk_zuordnung', array(
                'name' => $name,
                'slug' => $slug,
            ) );
        }
    } else {
        // Create new term (admin only — guarded above).
        $result = wp_insert_term( $name, 'gk_zuordnung', array(
            'slug' => $slug ?: sanitize_title( $name ),
        ) );
        if ( is_wp_error( $result ) ) return;
        $term_id = $result['term_id'];
    }

    // Save type (admin only).
    if ( $is_admin ) {
        $type = sanitize_text_field( $_POST['ov_type'] ?? 'ov' );
        if ( in_array( $type, array( 'ov', 'ortsgruppe', 'werbung' ), true ) ) {
            update_term_meta( $term_id, '_gk_ov_type', $type );
        }

        // Save homepage.
        $homepage_id = (int) ( $_POST['ov_homepage_id'] ?? 0 );
        update_term_meta( $term_id, '_gk_homepage_id', $homepage_id );
    }

    // Save header (both admin and OV-Admin).
    $header_value = sanitize_text_field( $_POST['ov_header'] ?? '' );
    $is_new_term  = ( (int) ( $_POST['term_id'] ?? 0 ) ) === 0;
    if ( $is_new_term && empty( $header_value ) ) {
        $type_for_header = sanitize_text_field( $_POST['ov_type'] ?? 'ov' );
        $header_value = match ( $type_for_header ) {
            'ortsgruppe' => 'Ortsgruppe Grüne ' . $name,
            'werbung'    => 'Grüne in ' . $name,
            default      => 'Ortsverband Grüne ' . $name,
        };
    }
    update_term_meta( $term_id, '_gk_ov_header', $header_value );

    // Save homepage settings (both admin and OV-Admin).
    if ( isset( $_POST['ov_hp'] ) && is_array( $_POST['ov_hp'] ) ) {
        $raw    = $_POST['ov_hp'];
        $hp     = array();
        $text_keys = array(
            'landing_mode', 'hero_title', 'hero_subtitle', 'cta_label', 'cta_url',
            'election_name', 'election_date', 'election_slogan',
            'candidate_name', 'candidate_role', 'candidate_quote',
            'candidate_cta_label', 'candidate_cta_url',
        );
        foreach ( $text_keys as $k ) {
            if ( isset( $raw[ $k ] ) ) {
                $hp[ $k ] = sanitize_text_field( $raw[ $k ] );
            }
        }
        $int_keys = array( 'hero_image', 'candidate_image', 'fundraising_goal', 'fundraising_current' );
        foreach ( $int_keys as $k ) {
            if ( isset( $raw[ $k ] ) ) {
                $hp[ $k ] = absint( $raw[ $k ] );
            }
        }
        // Checkboxes: present = '1', absent = '0'.
        foreach ( array( 'show_team', 'show_aktuelles', 'show_termine', 'show_contact', 'show_engage' ) as $k ) {
            $hp[ $k ] = isset( $raw[ $k ] ) ? '1' : '0';
        }
        update_term_meta( $term_id, '_gk_ov_homepage', $hp );
    }

    // Save contact fields (both admin and OV-Admin).
    $contact_fields = array( 'www', 'email', 'facebook', 'twitter', 'tiktok', 'threads', 'mastodon', 'insta', 'telefon' );
    foreach ( $contact_fields as $field ) {
        $value = sanitize_text_field( $_POST[ 'ov_contact_' . $field ] ?? '' );
        update_term_meta( $term_id, '_gk_contact_' . $field, $value );
    }
    if ( isset( $_POST['ov_contact_anschrift'] ) ) {
        update_term_meta( $term_id, '_gk_contact_anschrift', sanitize_textarea_field( $_POST['ov_contact_anschrift'] ) );
    }

    // Save legal page assignments.
    if ( isset( $_POST['ov_impressum_page'] ) ) {
        update_term_meta( $term_id, '_gk_impressum_page', absint( $_POST['ov_impressum_page'] ) );
    }
    if ( isset( $_POST['ov_datenschutz_page'] ) ) {
        update_term_meta( $term_id, '_gk_datenschutz_page', absint( $_POST['ov_datenschutz_page'] ) );
    }

    wp_redirect( admin_url( 'admin.php?page=gk-settings&message=saved' ) );
    exit;
}
add_action( 'admin_init', 'gk_ortsverband_handle_save' );

/**
 * Handle OV deletion with cascade.
 */
function gk_ortsverband_handle_delete() {
    if ( ! isset( $_POST['gk_ov_delete_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['gk_ov_delete_nonce'], 'gk_ov_delete' ) ) return;
    if ( ! current_user_can( 'gk_manage_ov' ) ) return;

    $term_id = (int) ( $_POST['term_id'] ?? 0 );
    $term    = get_term( $term_id, 'gk_zuordnung' );
    if ( ! $term || is_wp_error( $term ) || $term->slug === 'kreisverband' ) return;

    // Cascade: trash all content with this zuordnung.
    $related = get_posts( array(
        'post_type'      => array( 'post', 'page', 'person', 'gk_event', 'attachment' ),
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ) ),
    ) );
    foreach ( $related as $related_id ) {
        wp_trash_post( $related_id );
    }

    wp_delete_term( $term_id, 'gk_zuordnung' );

    wp_redirect( admin_url( 'admin.php?page=gk-settings&message=deleted' ) );
    exit;
}
add_action( 'admin_init', 'gk_ortsverband_handle_delete' );

/**
 * Main settings page: KV setup at top, then Ortsverbände list.
 */
function gk_settings_main_page() {
    if ( ! gk_current_user_is_admin() ) {
        return;
    }

    // Load variant settings files for render callbacks.
    gk_load_variant_settings_files();

    $hp               = get_option( 'gk_homepage', array() );
    $all_variant_data = $hp['variants'] ?? array();
    if ( isset( $_GET['message'] ) ) {
        $msg = $_GET['message'] === 'deleted' ? 'Ortsverband geloescht.' : 'Gespeichert.';
        echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
    }
    ?>
    <div class="wrap">
        <h1>Verband</h1>

        <!-- ── KV Setup: Startseite ──────────────────────── -->
        <form method="post" action="options.php">
            <?php settings_fields( 'gk_settings' ); ?>

            <h2>Startseite</h2>
            <input type="hidden" name="gk_homepage[home_variant]" value="neue-energie" />

            <?php
            $variant_data = $all_variant_data['neue-energie'] ?? array();
            if ( function_exists( 'gk_home_neue_energie_render' ) ) {
                gk_home_neue_energie_render( $variant_data );
            }
            ?>

            <hr />

            <!-- ── Spenden ────────────────────────────────────── -->
            <?php
            if ( function_exists( 'gk_donation_settings_section' ) ) {
                gk_donation_settings_section();
            }
            ?>

            <?php submit_button( 'Speichern' ); ?>
        </form>

        <hr />

        <!-- ── Ortsverbaende ─────────────────────────────────── -->
        <?php
        $ov_terms = gk_get_ov_terms();
        ?>
        <h2 class="wp-heading-inline">Ortsverbaende</h2>
        <a href="<?php echo esc_url( admin_url( 'admin.php?page=gk-settings&action=edit&term_id=0' ) ); ?>" class="page-title-action">Neuen Ortsverband hinzuf&uuml;gen</a>
        <hr class="wp-header-end" />

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Typ</th>
                    <th>Startseite</th>
                    <th>Inhalte</th>
                    <th>Aktionen</th>
                </tr>
            </thead>
            <tbody>
            <?php if ( empty( $ov_terms ) ) : ?>
                <tr><td colspan="6">Noch keine Ortsverb&auml;nde angelegt.</td></tr>
            <?php else : foreach ( $ov_terms as $term ) :
                $type        = gk_get_ov_type( $term->term_id );
                $homepage_id = gk_get_ov_homepage_id( $term->term_id );
                $homepage    = $homepage_id ? get_post( $homepage_id ) : null;
                $edit_url    = admin_url( 'admin.php?page=gk-settings&action=edit&term_id=' . $term->term_id );
                $delete_url  = admin_url( 'admin.php?page=gk-settings&action=delete&term_id=' . $term->term_id );

                $type_labels = array( 'ov' => 'Ortsverband', 'ortsgruppe' => 'Ortsgruppe', 'werbung' => 'Werbeseite' );
            ?>
                <tr>
                    <td><a href="<?php echo esc_url( $edit_url ); ?>"><strong><?php echo esc_html( $term->name ); ?></strong></a></td>
                    <td><code><?php echo esc_html( $term->slug ); ?></code></td>
                    <td><?php echo esc_html( $type_labels[ $type ] ?? $type ); ?></td>
                    <td><?php echo $homepage ? '<a href="' . esc_url( get_edit_post_link( $homepage_id ) ) . '">' . esc_html( $homepage->post_title ) . '</a>' : '&mdash;'; ?></td>
                    <td><?php echo esc_html( $term->count ); ?></td>
                    <td>
                        <a href="<?php echo esc_url( $edit_url ); ?>">Bearbeiten</a> |
                        <a href="<?php echo esc_url( $delete_url ); ?>" style="color:#d63638;">L&ouml;schen</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>

    <script>
    jQuery(function($) {
        // ── Generic media picker ──
        var mediaFrames = {};
        $(document).on('click', '.gk-media-pick', function(e) {
            e.preventDefault();
            var target = $(this).data('target');
            if (mediaFrames[target]) { mediaFrames[target].open(); return; }
            mediaFrames[target] = wp.media({
                title: 'Bild waehlen',
                button: { text: 'Bild verwenden' },
                multiple: false
            });
            mediaFrames[target].on('select', function() {
                var att = mediaFrames[target].state().get('selection').first().toJSON();
                $('#' + target).val(att.id);
                var src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                $('.gk-media-preview[data-target="' + target + '"]').html(
                    '<img src="' + src + '" style="max-width:300px;height:auto;" />'
                );
                var $pick = $('.gk-media-pick[data-target="' + target + '"]');
                if (!$pick.next('.gk-media-remove').length) {
                    $pick.after(' <button type="button" class="button gk-media-remove" data-target="' + target + '">Entfernen</button>');
                }
            });
            mediaFrames[target].open();
        });
        $(document).on('click', '.gk-media-remove', function() {
            var target = $(this).data('target');
            $('#' + target).val('');
            $('.gk-media-preview[data-target="' + target + '"]').empty();
            $(this).remove();
        });

        // ── Remove row ──
        $(document).on('click', '.gk-remove-row', function() {
            $(this).closest('tr').remove();
        });
    });
    </script>

    <style>
    .gk-media-preview { margin-bottom: 8px; }
    .gk-media-preview img { max-width: 300px; height: auto; border-radius: 4px; }
    </style>
    <?php
}

/**
 * Edit page: add/edit an OV zuordnung term with all meta.
 *
 * Admins see all fields. OV-Admins only see contact fields.
 */
function gk_ortsverband_edit_page() {
    $term_id  = (int) ( $_GET['term_id'] ?? 0 );
    $term     = $term_id ? get_term( $term_id, 'gk_zuordnung' ) : null;
    $is_new   = ! $term || is_wp_error( $term );
    $is_admin = gk_current_user_is_admin();

    $name      = $is_new ? '' : $term->name;
    $slug      = $is_new ? '' : $term->slug;
    $type      = $is_new ? 'ov' : gk_get_ov_type( $term_id );
    $homepage  = $is_new ? 0 : gk_get_ov_homepage_id( $term_id );
    $header    = $is_new ? '' : get_term_meta( $term_id, '_gk_ov_header', true );
    $contact   = $is_new ? array() : gk_get_ov_contact( $term_id );
    $hp        = $is_new ? array() : gk_get_ov_homepage_options( $term_id );

    $title = $is_new ? 'Neuen Ortsverband hinzuf&uuml;gen' : ( $is_admin ? 'Ortsverband bearbeiten' : esc_html( $name ) );
    ?>
    <div class="wrap">
        <h1><?php echo $title; ?></h1>

        <?php if ( isset( $_GET['message'] ) && $_GET['message'] === 'saved' ) : ?>
            <div class="notice notice-success is-dismissible"><p>Gespeichert.</p></div>
        <?php endif; ?>

        <form method="post" action="">
            <?php wp_nonce_field( 'gk_ov_save', 'gk_ov_save_nonce' ); ?>
            <input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>" />
            <?php if ( ! $is_admin ) : ?>
                <input type="hidden" name="ov_name" value="<?php echo esc_attr( $name ); ?>" />
            <?php endif; ?>

            <?php if ( $is_admin ) : ?>
            <table class="form-table">
                <tr>
                    <th><label for="ov_name">Name</label></th>
                    <td><input type="text" name="ov_name" id="ov_name" value="<?php echo esc_attr( $name ); ?>" class="regular-text" required /></td>
                </tr>
                <tr>
                    <th><label for="ov_slug">Slug</label></th>
                    <td><input type="text" name="ov_slug" id="ov_slug" value="<?php echo esc_attr( $slug ); ?>" class="regular-text" />
                    <p class="description">URL-Slug (z.B. "ov-starnberg"). Wird automatisch generiert wenn leer.</p></td>
                </tr>
                <tr>
                    <th>Typ</th>
                    <td>
                        <?php
                        $types = array(
                            'ov'         => array( 'Ortsverband', 'Vollständiger Ortsverband mit eigenem Vorstand' ),
                            'ortsgruppe' => array( 'Ortsgruppe', 'Aktive Mitglieder vor Ort, kein eigener Verband' ),
                            'werbung'    => array( 'Werbeseite', 'Einladung zum Mitmachen und Gründen eines OV' ),
                        );
                        foreach ( $types as $value => $info ) :
                        ?>
                        <label style="display:block; margin:4px 0;">
                            <input type="radio" name="ov_type" value="<?php echo esc_attr( $value ); ?>" <?php checked( $type, $value ); ?> />
                            <strong><?php echo esc_html( $info[0] ); ?></strong> &mdash; <?php echo esc_html( $info[1] ); ?>
                        </label>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <tr>
                    <th><label for="ov_homepage_id">Startseite</label></th>
                    <td>
                        <?php wp_dropdown_pages( array(
                            'name'              => 'ov_homepage_id',
                            'selected'          => $homepage,
                            'show_option_none'   => '&mdash; Keine Startseite &mdash;',
                            'option_none_value'  => 0,
                        ) ); ?>
                        <p class="description">Die Startseite des OV. Kann nicht geloescht werden.</p>
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <h2>Header</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ov_header">Header-Text</label></th>
                    <td>
                        <input type="text" name="ov_header" id="ov_header" value="<?php echo esc_attr( $header ); ?>" class="regular-text" />
                        <p class="description">Anzeigename im Header der Website (z.B. &bdquo;Ortsverband Gr&uuml;ne Starnberg&ldquo;). Wenn leer, wird der Name verwendet.</p>
                    </td>
                </tr>
            </table>

            <?php if ( ! $is_new ) : ?>
            <h2>Startseite</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ov_landing_mode">Hero-Modus</label></th>
                    <td>
                        <select name="ov_hp[landing_mode]" id="ov_landing_mode">
                            <?php
                            $modes = array(
                                'standard'    => 'Standard (Bild + Titel)',
                                'election'    => 'Wahl (Countdown)',
                                'candidate'   => 'Kandidat:in (Portrait)',
                                'news'        => 'Aktueller Beitrag',
                                'fundraising' => 'Spendenkampagne',
                                'minimal'     => 'Minimal (nur Titel)',
                            );
                            $current_mode = $hp['landing_mode'] ?? 'standard';
                            foreach ( $modes as $val => $label ) :
                            ?>
                                <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current_mode, $val ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>

            <!-- Standard fields -->
            <div class="gk-mode-fields" data-mode="standard election news fundraising">
                <table class="form-table">
                    <tr>
                        <th><label for="ov_hero_title">Hero-Titel</label></th>
                        <td>
                            <input type="text" name="ov_hp[hero_title]" id="ov_hero_title" value="<?php echo esc_attr( $hp['hero_title'] ?? '' ); ?>" class="regular-text" />
                            <p class="description">Wenn leer, wird der Seitentitel verwendet.</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ov_hero_subtitle">Untertitel</label></th>
                        <td><input type="text" name="ov_hp[hero_subtitle]" id="ov_hero_subtitle" value="<?php echo esc_attr( $hp['hero_subtitle'] ?? '' ); ?>" class="large-text" /></td>
                    </tr>
                    <tr>
                        <th>Hero-Bild</th>
                        <td>
                            <div class="gk-media-preview" data-target="ov_hero_image">
                                <?php if ( ! empty( $hp['hero_image'] ) ) :
                                    echo wp_get_attachment_image( (int) $hp['hero_image'], 'medium', false, array( 'style' => 'max-width:300px;height:auto;' ) );
                                endif; ?>
                            </div>
                            <input type="hidden" name="ov_hp[hero_image]" id="ov_hero_image" value="<?php echo esc_attr( $hp['hero_image'] ?? '' ); ?>" class="gk-media-value" />
                            <button type="button" class="button gk-media-pick" data-target="ov_hero_image">Bild w&auml;hlen</button>
                            <?php if ( ! empty( $hp['hero_image'] ) ) : ?>
                                <button type="button" class="button gk-media-remove" data-target="ov_hero_image">Entfernen</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="ov_cta_label">CTA-Button</label></th>
                        <td>
                            <input type="text" name="ov_hp[cta_label]" id="ov_cta_label" value="<?php echo esc_attr( $hp['cta_label'] ?? '' ); ?>" placeholder="Button-Text" style="width:200px" />
                            <input type="url" name="ov_hp[cta_url]" value="<?php echo esc_attr( $hp['cta_url'] ?? '' ); ?>" placeholder="https://..." class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Election fields -->
            <div class="gk-mode-fields" data-mode="election">
                <table class="form-table">
                    <tr>
                        <th><label>Wahl-Name</label></th>
                        <td><input type="text" name="ov_hp[election_name]" value="<?php echo esc_attr( $hp['election_name'] ?? '' ); ?>" class="regular-text" placeholder="z.B. Kommunalwahl 2026" /></td>
                    </tr>
                    <tr>
                        <th><label>Wahltag</label></th>
                        <td><input type="date" name="ov_hp[election_date]" value="<?php echo esc_attr( $hp['election_date'] ?? '' ); ?>" /></td>
                    </tr>
                    <tr>
                        <th><label>Slogan</label></th>
                        <td><input type="text" name="ov_hp[election_slogan]" value="<?php echo esc_attr( $hp['election_slogan'] ?? '' ); ?>" class="large-text" /></td>
                    </tr>
                </table>
            </div>

            <!-- Candidate fields -->
            <div class="gk-mode-fields" data-mode="candidate">
                <table class="form-table">
                    <tr>
                        <th><label>Name</label></th>
                        <td><input type="text" name="ov_hp[candidate_name]" value="<?php echo esc_attr( $hp['candidate_name'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label>Position</label></th>
                        <td><input type="text" name="ov_hp[candidate_role]" value="<?php echo esc_attr( $hp['candidate_role'] ?? '' ); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th><label>Zitat</label></th>
                        <td><input type="text" name="ov_hp[candidate_quote]" value="<?php echo esc_attr( $hp['candidate_quote'] ?? '' ); ?>" class="large-text" /></td>
                    </tr>
                    <tr>
                        <th>Portrait</th>
                        <td>
                            <div class="gk-media-preview" data-target="ov_candidate_image">
                                <?php if ( ! empty( $hp['candidate_image'] ) ) :
                                    echo wp_get_attachment_image( (int) $hp['candidate_image'], 'medium', false, array( 'style' => 'max-width:200px;height:auto;' ) );
                                endif; ?>
                            </div>
                            <input type="hidden" name="ov_hp[candidate_image]" id="ov_candidate_image" value="<?php echo esc_attr( $hp['candidate_image'] ?? '' ); ?>" class="gk-media-value" />
                            <button type="button" class="button gk-media-pick" data-target="ov_candidate_image">Bild w&auml;hlen</button>
                            <?php if ( ! empty( $hp['candidate_image'] ) ) : ?>
                                <button type="button" class="button gk-media-remove" data-target="ov_candidate_image">Entfernen</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th><label>CTA-Button</label></th>
                        <td>
                            <input type="text" name="ov_hp[candidate_cta_label]" value="<?php echo esc_attr( $hp['candidate_cta_label'] ?? '' ); ?>" placeholder="Button-Text" style="width:200px" />
                            <input type="url" name="ov_hp[candidate_cta_url]" value="<?php echo esc_attr( $hp['candidate_cta_url'] ?? '' ); ?>" placeholder="https://..." class="regular-text" />
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Fundraising fields -->
            <div class="gk-mode-fields" data-mode="fundraising">
                <table class="form-table">
                    <tr>
                        <th><label>Spendenziel (&euro;)</label></th>
                        <td><input type="number" name="ov_hp[fundraising_goal]" value="<?php echo esc_attr( $hp['fundraising_goal'] ?? '' ); ?>" style="width:120px" /></td>
                    </tr>
                    <tr>
                        <th><label>Aktueller Stand (&euro;)</label></th>
                        <td><input type="number" name="ov_hp[fundraising_current]" value="<?php echo esc_attr( $hp['fundraising_current'] ?? '' ); ?>" style="width:120px" /></td>
                    </tr>
                </table>
            </div>

            <!-- Section toggles -->
            <table class="form-table">
                <tr>
                    <th>Bereiche</th>
                    <td>
                        <label style="display:block;margin:4px 0;">
                            <input type="checkbox" name="ov_hp[show_team]" value="1" <?php checked( $hp['show_team'] ?? '1', '1' ); ?> />
                            Team anzeigen
                        </label>
                        <label style="display:block;margin:4px 0;">
                            <input type="checkbox" name="ov_hp[show_aktuelles]" value="1" <?php checked( $hp['show_aktuelles'] ?? '1', '1' ); ?> />
                            Aktuelles anzeigen
                        </label>
                        <label style="display:block;margin:4px 0;">
                            <input type="checkbox" name="ov_hp[show_termine]" value="1" <?php checked( $hp['show_termine'] ?? '1', '1' ); ?> />
                            Termine anzeigen
                        </label>
                        <label style="display:block;margin:4px 0;">
                            <input type="checkbox" name="ov_hp[show_contact]" value="1" <?php checked( $hp['show_contact'] ?? '1', '1' ); ?> />
                            Kontaktdaten anzeigen
                        </label>
                        <label style="display:block;margin:4px 0;">
                            <input type="checkbox" name="ov_hp[show_engage]" value="1" <?php checked( $hp['show_engage'] ?? '1', '1' ); ?> />
                            Engagement-CTAs anzeigen
                        </label>
                    </td>
                </tr>
            </table>

            <script>
            jQuery(function($) {
                var $mode = $('#ov_landing_mode');
                function toggleModeFields() {
                    var val = $mode.val();
                    $('.gk-mode-fields').each(function() {
                        var modes = $(this).data('mode').split(' ');
                        $(this).toggle(modes.indexOf(val) !== -1);
                    });
                }
                $mode.on('change', toggleModeFields);
                toggleModeFields();
            });
            </script>
            <?php endif; ?>

            <h2>Kontaktdaten</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ov_contact_www">Website</label></th>
                    <td><input type="url" name="ov_contact_www" id="ov_contact_www" value="<?php echo esc_attr( $contact['www'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_email">E-Mail</label></th>
                    <td><input type="email" name="ov_contact_email" id="ov_contact_email" value="<?php echo esc_attr( $contact['email'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_telefon">Telefon</label></th>
                    <td><input type="text" name="ov_contact_telefon" id="ov_contact_telefon" value="<?php echo esc_attr( $contact['telefon'] ?? '' ); ?>" class="regular-text" /></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_anschrift">Anschrift</label></th>
                    <td><textarea name="ov_contact_anschrift" id="ov_contact_anschrift" rows="3" cols="50"><?php echo esc_textarea( $contact['anschrift'] ?? '' ); ?></textarea></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_insta">Instagram</label></th>
                    <td><input type="text" name="ov_contact_insta" id="ov_contact_insta" value="<?php echo esc_attr( $contact['insta'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder Username</span></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_facebook">Facebook</label></th>
                    <td><input type="text" name="ov_contact_facebook" id="ov_contact_facebook" value="<?php echo esc_attr( $contact['facebook'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder Username</span></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_twitter">X</label></th>
                    <td><input type="text" name="ov_contact_twitter" id="ov_contact_twitter" value="<?php echo esc_attr( $contact['twitter'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder Username</span></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_tiktok">TikTok</label></th>
                    <td><input type="text" name="ov_contact_tiktok" id="ov_contact_tiktok" value="<?php echo esc_attr( $contact['tiktok'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder Username</span></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_threads">Threads</label></th>
                    <td><input type="text" name="ov_contact_threads" id="ov_contact_threads" value="<?php echo esc_attr( $contact['threads'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder Username</span></td>
                </tr>
                <tr>
                    <th><label for="ov_contact_mastodon">Mastodon</label></th>
                    <td><input type="text" name="ov_contact_mastodon" id="ov_contact_mastodon" value="<?php echo esc_attr( $contact['mastodon'] ?? '' ); ?>" class="regular-text" /><br><span class="description">URL oder @user@instanz</span></td>
                </tr>
            </table>

            <?php if ( ! $is_new ) : ?>
            <h2>Impressum &amp; Datenschutz</h2>
            <table class="form-table">
                <tr>
                    <th><label for="ov_impressum_page">Impressum</label></th>
                    <td>
                        <?php
                        $ov_impressum = (int) get_term_meta( $term_id, '_gk_impressum_page', true );
                        wp_dropdown_pages( array(
                            'name'              => 'ov_impressum_page',
                            'id'                => 'ov_impressum_page',
                            'selected'          => $ov_impressum,
                            'show_option_none'   => '&mdash; KV-Impressum verwenden &mdash;',
                            'option_none_value'  => 0,
                        ) ); ?>
                        <p class="description">Eigene Impressum-Seite. Wenn leer, wird die Kreisverbands-Seite verwendet.</p>
                    </td>
                </tr>
                <tr>
                    <th><label for="ov_datenschutz_page">Datenschutz</label></th>
                    <td>
                        <?php
                        $ov_datenschutz = (int) get_term_meta( $term_id, '_gk_datenschutz_page', true );
                        wp_dropdown_pages( array(
                            'name'              => 'ov_datenschutz_page',
                            'id'                => 'ov_datenschutz_page',
                            'selected'          => $ov_datenschutz,
                            'show_option_none'   => '&mdash; KV-Datenschutz verwenden &mdash;',
                            'option_none_value'  => 0,
                        ) ); ?>
                        <p class="description">Eigene Datenschutz-Seite. Wenn leer, wird die Kreisverbands-Seite verwendet.</p>
                    </td>
                </tr>
            </table>
            <?php endif; ?>

            <?php submit_button( $is_new ? 'Ortsverband anlegen' : 'Speichern' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Delete confirmation page for an OV.
 */
function gk_ortsverband_delete_page() {
    $term_id = (int) ( $_GET['term_id'] ?? 0 );
    $term    = get_term( $term_id, 'gk_zuordnung' );

    if ( ! $term || is_wp_error( $term ) || $term->slug === 'kreisverband' ) {
        wp_redirect( admin_url( 'admin.php?page=gk-settings' ) );
        exit;
    }

    $count = gk_get_zuordnung_content_count( $term_id );
    ?>
    <div class="wrap">
        <h1>Ortsverband l&ouml;schen</h1>

        <div class="notice notice-error inline">
            <p><strong><?php echo esc_html( $term->name ); ?></strong> hat
            <strong><?php echo esc_html( $count ); ?> zugeordnete Inhalte</strong>
            (Seiten, Beitr&auml;ge, Personen, Termine, Medien).</p>
            <p style="color:#d63638;font-weight:bold;">
            Beim L&ouml;schen werden alle diese Inhalte in den Papierkorb verschoben!</p>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field( 'gk_ov_delete', 'gk_ov_delete_nonce' ); ?>
            <input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>" />
            <p>
                <button type="submit" class="button button-primary" style="background:#d63638;border-color:#d63638;">
                    Ja, Ortsverband und alle Inhalte l&ouml;schen
                </button>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=gk-settings' ) ); ?>" class="button">Abbrechen</a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Count content items belonging to a zuordnung term.
 */
function gk_get_zuordnung_content_count( $term_id ) {
    $related = get_posts( array(
        'post_type'      => array( 'post', 'page', 'person', 'gk_event', 'attachment' ),
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ) ),
    ) );
    return count( $related );
}


// ── Homepage Page Protection ────────────────────────────────────────────────

/**
 * Get all page IDs set as an OV homepage (from zuordnung term meta).
 */
function gk_get_homepage_page_ids() {
    static $ids = null;
    if ( $ids !== null ) return $ids;

    $ids      = array();
    $ov_terms = gk_get_ov_terms();
    foreach ( $ov_terms as $term ) {
        $page_id = gk_get_ov_homepage_id( $term->term_id );
        if ( $page_id ) {
            $ids[ $page_id ] = $term->term_id;
        }
    }
    return $ids;
}

/**
 * Prevent deletion of pages set as an OV homepage.
 */
function gk_protect_homepage_pages( $caps, $cap, $user_id, $args ) {
    if ( $cap !== 'delete_post' || empty( $args[0] ) ) return $caps;

    $post = get_post( $args[0] );
    if ( ! $post || $post->post_type !== 'page' ) return $caps;

    $homepages = gk_get_homepage_page_ids();
    if ( isset( $homepages[ $post->ID ] ) ) {
        $caps[] = 'do_not_allow';
    }
    return $caps;
}
add_filter( 'map_meta_cap', 'gk_protect_homepage_pages', 10, 4 );

/**
 * Mark homepage pages in the admin list with "Startseite: OV Name".
 */
function gk_mark_homepage_in_list( $states, $post ) {
    if ( $post->post_type !== 'page' ) return $states;

    $homepages = gk_get_homepage_page_ids();
    if ( isset( $homepages[ $post->ID ] ) ) {
        $term = get_term( $homepages[ $post->ID ], 'gk_zuordnung' );
        $states['gk_homepage'] = sprintf(
            __( 'Startseite: %s', 'neurg-kreisverband' ),
            $term ? $term->name : '?'
        );
    }
    return $states;
}
add_filter( 'display_post_states', 'gk_mark_homepage_in_list', 10, 2 );


// ── Navigation Menus per Zuordnung ──────────────────────────────────────────

/**
 * Register nav menu locations for each zuordnung term.
 * Each gets exactly one header and one footer menu.
 */
function gk_register_zuordnung_nav_menus() {
    $terms = get_terms( array(
        'taxonomy'   => 'gk_zuordnung',
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) ) return;

    $locations = array();
    foreach ( $terms as $term ) {
        $locations[ 'nav-' . $term->slug ]              = $term->name . ' — Hauptmenü';
        $locations[ 'nav-' . $term->slug . '-footer' ]  = $term->name . ' — Footer';
    }

    register_nav_menus( $locations );
}
add_action( 'init', 'gk_register_zuordnung_nav_menus', 20 );

/**
 * Add "Menüs" top-level admin page with zuordnung submenus.
 *
 * Admins see all zuordnungen in the sidebar.
 * OV-Admins see only their own zuordnung.
 */
function gk_menus_admin_page() {
    add_menu_page(
        'Men&uuml;s',
        'Men&uuml;s',
        'edit_theme_options',
        'gk-menus',
        'gk_menus_page_cb',
        'dashicons-menu',
        27
    );

    $user     = wp_get_current_user();
    $is_admin = gk_current_user_is_admin();

    if ( $is_admin ) {
        $terms = get_terms( array(
            'taxonomy'   => 'gk_zuordnung',
            'hide_empty' => false,
            'orderby'    => 'name',
        ) );
        if ( ! is_wp_error( $terms ) ) {
            // Put Kreisverband first.
            usort( $terms, function ( $a, $b ) {
                if ( $a->slug === 'kreisverband' ) return -1;
                if ( $b->slug === 'kreisverband' ) return 1;
                return strcmp( $a->name, $b->name );
            } );
            foreach ( $terms as $term ) {
                add_submenu_page( 'gk-menus', $term->name . ' — Men&uuml;s', $term->name,
                    'edit_theme_options', 'gk-menus-' . $term->slug, 'gk_menus_page_cb' );
            }
        }
    } else {
        $own_term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( $own_term ) {
            add_submenu_page( 'gk-menus', $own_term->name . ' — Men&uuml;s', $own_term->name,
                'edit_theme_options', 'gk-menus-' . $own_term->slug, 'gk_menus_page_cb' );
        }
    }

    // WordPress auto-adds a parent duplicate as the first submenu entry.
    // Remove it so only the zuordnung entries remain.
    global $submenu;
    if ( isset( $submenu['gk-menus'][0] ) && $submenu['gk-menus'][0][2] === 'gk-menus' ) {
        unset( $submenu['gk-menus'][0] );
        $submenu['gk-menus'] = array_values( $submenu['gk-menus'] );
    }
}
add_action( 'admin_menu', 'gk_menus_admin_page' );

/**
 * Handle menu location assignments (scoped to one zuordnung).
 */
function gk_menus_handle_save() {
    if ( ! isset( $_POST['gk_menus_nonce'] ) ) return;
    if ( ! wp_verify_nonce( $_POST['gk_menus_nonce'], 'gk_menus_save' ) ) return;
    if ( ! current_user_can( 'edit_theme_options' ) ) return;

    $zuordnung_slug = sanitize_key( $_POST['gk_zuordnung_slug'] ?? '' );
    if ( empty( $zuordnung_slug ) ) return;

    // OV-Admin: can only save their own zuordnung.
    if ( ! gk_current_user_is_admin() ) {
        $user     = wp_get_current_user();
        $own_term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( ! $own_term || $own_term->slug !== $zuordnung_slug ) return;
    }

    $locations = get_nav_menu_locations();
    $submitted = isset( $_POST['menu_locations'] ) ? (array) $_POST['menu_locations'] : array();
    $prefix    = 'nav-' . $zuordnung_slug;

    foreach ( $submitted as $location => $menu_id ) {
        $location = sanitize_key( $location );
        if ( strpos( $location, $prefix ) !== 0 ) continue;
        $locations[ $location ] = absint( $menu_id );
    }

    set_theme_mod( 'nav_menu_locations', $locations );

    wp_redirect( admin_url( 'admin.php?page=gk-menus-' . $zuordnung_slug . '&message=saved' ) );
    exit;
}
add_action( 'admin_init', 'gk_menus_handle_save' );

/**
 * Render the menu management page for a single zuordnung.
 *
 * The parent page (gk-menus) redirects to the first available zuordnung.
 * Sub-pages (gk-menus-{slug}) show the two menu locations for that zuordnung
 * with dropdowns to assign menus and links to the native menu editor.
 */
function gk_menus_page_cb() {
    $page = $_GET['page'] ?? '';

    // Parent page: redirect to the first zuordnung.
    if ( $page === 'gk-menus' ) {
        if ( gk_current_user_is_admin() ) {
            $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
            if ( $kv_term ) {
                wp_redirect( admin_url( 'admin.php?page=gk-menus-kreisverband' ) );
                exit;
            }
        } else {
            $user = wp_get_current_user();
            $term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
            if ( $term ) {
                wp_redirect( admin_url( 'admin.php?page=gk-menus-' . $term->slug ) );
                exit;
            }
        }
        echo '<div class="wrap"><h1>Men&uuml;s</h1><p>Kein Bereich gefunden.</p></div>';
        return;
    }

    // Extract zuordnung slug from page slug.
    $zuordnung_slug = substr( $page, strlen( 'gk-menus-' ) );
    $term = get_term_by( 'slug', $zuordnung_slug, 'gk_zuordnung' );

    if ( ! $term ) {
        echo '<div class="wrap"><h1>Men&uuml;s</h1><p>Bereich nicht gefunden.</p></div>';
        return;
    }

    // Scope check for non-admins.
    if ( ! gk_current_user_is_admin() ) {
        $user     = wp_get_current_user();
        $own_term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( ! $own_term || $own_term->term_id !== $term->term_id ) {
            wp_die( 'Zugriff verweigert.' );
        }
    }

    $slug_main      = 'nav-' . $term->slug;
    $slug_footer    = 'nav-' . $term->slug . '-footer';
    $locations      = get_nav_menu_locations();
    $all_menus      = wp_get_nav_menus();
    $main_menu_id   = $locations[ $slug_main ] ?? 0;
    $footer_menu_id = $locations[ $slug_footer ] ?? 0;

    if ( isset( $_GET['message'] ) && $_GET['message'] === 'saved' ) {
        echo '<div class="notice notice-success is-dismissible"><p>Men&uuml;s gespeichert.</p></div>';
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( $term->name ); ?> &mdash; Men&uuml;s</h1>

        <form method="post" action="">
            <?php wp_nonce_field( 'gk_menus_save', 'gk_menus_nonce' ); ?>
            <input type="hidden" name="gk_zuordnung_slug" value="<?php echo esc_attr( $term->slug ); ?>" />

            <table class="form-table">
                <tr>
                    <th>Hauptmen&uuml;</th>
                    <td>
                        <select name="menu_locations[<?php echo esc_attr( $slug_main ); ?>]">
                            <option value="0">&mdash; Kein Men&uuml; &mdash;</option>
                            <?php foreach ( $all_menus as $menu ) : ?>
                                <option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $main_menu_id, $menu->term_id ); ?>>
                                    <?php echo esc_html( $menu->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $main_menu_id ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'nav-menus.php?action=edit&menu=' . $main_menu_id ) ); ?>" class="button">Bearbeiten</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th>Footer</th>
                    <td>
                        <select name="menu_locations[<?php echo esc_attr( $slug_footer ); ?>]">
                            <option value="0">&mdash; Kein Men&uuml; &mdash;</option>
                            <?php foreach ( $all_menus as $menu ) : ?>
                                <option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $footer_menu_id, $menu->term_id ); ?>>
                                    <?php echo esc_html( $menu->name ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( $footer_menu_id ) : ?>
                            <a href="<?php echo esc_url( admin_url( 'nav-menus.php?action=edit&menu=' . $footer_menu_id ) ); ?>" class="button">Bearbeiten</a>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p>
                <?php submit_button( 'Speichern', 'primary', 'submit', false ); ?>
                <a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button" style="margin-left:8px;">Neues Men&uuml; erstellen</a>
            </p>
        </form>
    </div>
    <?php
}

/**
 * On nav-menus.php, scope visible locations and available content
 * to the current user's zuordnung. Admins see everything.
 */
function gk_scope_nav_menus_for_user() {
    $screen = get_current_screen();
    if ( ! $screen || $screen->id !== 'nav-menus' ) return;
    if ( gk_current_user_is_admin() ) return;

    $user = wp_get_current_user();
    $term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
    if ( ! $term ) return;

    // Scope menu locations to this zuordnung only.
    global $_wp_registered_nav_menus;
    $prefix = 'nav-' . $term->slug;
    foreach ( array_keys( $_wp_registered_nav_menus ) as $location ) {
        if ( strpos( $location, $prefix ) !== 0 ) {
            unset( $_wp_registered_nav_menus[ $location ] );
        }
    }

    // Scope the "add items" meta boxes (Pages, Posts, etc.) to this zuordnung.
    add_filter( 'pre_get_posts', function ( $query ) use ( $term ) {
        // Only filter front-facing post type queries used by nav menu meta boxes.
        $post_type = $query->get( 'post_type' );
        if ( empty( $post_type ) ) return;

        // Skip nav_menu_item queries (those are the menu items themselves).
        $types = (array) $post_type;
        if ( in_array( 'nav_menu_item', $types, true ) ) return;

        $query->set( 'tax_query', array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'term_id',
            'terms'    => $term->term_id,
        ) ) );
    } );

    // Scope the menu selector dropdown to only menus assigned to this zuordnung.
    $locations  = get_nav_menu_locations();
    $allowed_ids = array();
    foreach ( $locations as $location => $menu_id ) {
        if ( strpos( $location, $prefix ) === 0 && $menu_id ) {
            $allowed_ids[] = (int) $menu_id;
        }
    }
    // Also include the menu currently being edited (e.g. just created).
    if ( isset( $_REQUEST['menu'] ) && (int) $_REQUEST['menu'] > 0 ) {
        $allowed_ids[] = (int) $_REQUEST['menu'];
    }
    $allowed_ids = array_unique( $allowed_ids );

    add_filter( 'wp_get_nav_menus', function ( $menus ) use ( $allowed_ids ) {
        return array_values( array_filter( $menus, function ( $menu ) use ( $allowed_ids ) {
            return in_array( (int) $menu->term_id, $allowed_ids, true );
        } ) );
    } );
}
add_action( 'current_screen', 'gk_scope_nav_menus_for_user' );


// ── Security: Block user enumeration ─────────────────────────────────────────

if ( ! is_admin() ) {
    if ( isset( $_SERVER['QUERY_STRING'] ) && preg_match( '/author=([0-9]*)/i', $_SERVER['QUERY_STRING'] ) ) {
        die();
    }
    add_filter( 'redirect_canonical', 'gk_block_enum', 10, 2 );
}

function gk_block_enum( $redirect, $request ) {
    if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
        die();
    }
    return $redirect;
}


// ── Editor: Custom MCE styles ────────────────────────────────────────────────

add_filter( 'mce_buttons_2', 'gk_mce_buttons' );
function gk_mce_buttons( $buttons ) {
    array_unshift( $buttons, 'styleselect' );
    return $buttons;
}

add_filter( 'tiny_mce_before_init', 'gk_mce_custom_styles' );
function gk_mce_custom_styles( $init_array ) {
    $style_formats = array(
        array(
            'title'   => 'Absatz Einleitung',
            'block'   => 'p',
            'classes' => 'intro',
            'wrapper' => false,
        ),
        array(
            'title'   => 'Link - Button',
            'block'   => 'span',
            'classes' => 'button',
            'wrapper' => true,
        ),
        array(
            'title'   => 'Überschrift - Kontrast',
            'block'   => 'span',
            'classes' => 'kontrast',
            'wrapper' => true,
        ),
    );
    $init_array['style_formats'] = wp_json_encode( $style_formats );
    return $init_array;
}


// ── Disable comments completely ──────────────────────────────────────────────

add_action( 'admin_init', 'gk_disable_comments_support' );
function gk_disable_comments_support() {
    foreach ( get_post_types() as $post_type ) {
        if ( post_type_supports( $post_type, 'comments' ) ) {
            remove_post_type_support( $post_type, 'comments' );
            remove_post_type_support( $post_type, 'trackbacks' );
        }
    }
}

add_action( 'admin_menu', 'gk_disable_comments_menu' );
function gk_disable_comments_menu() {
    remove_menu_page( 'edit-comments.php' );
}

add_action( 'wp_before_admin_bar_render', 'gk_disable_comments_adminbar' );
function gk_disable_comments_adminbar() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu( 'comments' );
}

add_filter( 'comments_array', 'gk_hide_existing_comments', 10, 2 );
function gk_hide_existing_comments( $comments ) {
    return array();
}


// ── Admin Menu Order ───────────────────────────────────────────────────────

add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'gk_custom_menu_order' );

function gk_custom_menu_order( $menu_order ) {
    $preferred = array(
        'index.php',                    // Dashboard
        'separator1',
        'gk-menus',                     // Menüs
        'edit.php?post_type=page',      // Pages
        'edit.php',                     // Posts
        'edit.php?post_type=gk_event',  // Events
        'edit.php?post_type=person',    // Personen
        'gk-settings',                  // Verband
        'upload.php',                   // Media
        'separator2',
    );

    // Items in $preferred come first in given order; everything else follows.
    $ordered   = array();
    $remaining = array();

    foreach ( $preferred as $item ) {
        if ( in_array( $item, $menu_order, true ) ) {
            $ordered[] = $item;
        }
    }

    foreach ( $menu_order as $item ) {
        if ( ! in_array( $item, $ordered, true ) ) {
            $remaining[] = $item;
        }
    }

    return array_merge( $ordered, $remaining );
}
