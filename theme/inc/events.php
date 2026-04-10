<?php
/**
 * Events System
 *
 * Built-in event management — no plugin required.
 *
 * Features:
 *   - Event CPT with date/time, location, organizer
 *   - Recurring event support (simple: daily, weekly, monthly)
 *   - Archive shows upcoming events by default
 *   - OV event routing (events by OV authors get OV template)
 *   - iCal export per event and full calendar feed
 *   - Shortcodes: [termine], [naechste_termine]
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Custom Post Type ────────────────────────────────────────────────────────

function gk_register_event_post_type() {
    register_post_type( 'gk_event', array(
        'labels' => array(
            'name'               => 'Termine',
            'singular_name'      => 'Termin',
            'add_new'            => 'Neuer Termin',
            'add_new_item'       => 'Neuen Termin erstellen',
            'edit_item'          => 'Termin bearbeiten',
            'new_item'           => 'Neuer Termin',
            'view_item'          => 'Termin ansehen',
            'search_items'       => 'Termine suchen',
            'not_found'          => 'Keine Termine gefunden',
            'not_found_in_trash' => 'Keine Termine im Papierkorb',
            'menu_name'          => 'Termine',
        ),
        'public'       => true,
        'has_archive'  => false,
        'rewrite'      => array( 'slug' => 'termin', 'with_front' => false ),
        'menu_icon'    => 'dashicons-calendar-alt',
        'menu_position' => 6,
        'supports'     => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions' ),
        'show_in_rest' => true,
    ) );

    register_taxonomy( 'event_kategorie', 'gk_event', array(
        'labels' => array(
            'name'          => 'Termin-Kategorien',
            'singular_name' => 'Termin-Kategorie',
            'add_new_item'  => 'Neue Kategorie',
            'search_items'  => 'Kategorien suchen',
        ),
        'hierarchical' => true,
        'rewrite'      => array( 'slug' => 'termin-kategorie' ),
        'show_in_rest' => true,
    ) );
}
add_action( 'init', 'gk_register_event_post_type' );

// Flush rewrite rules once after CPT slug change.
function gk_flush_rewrite_once() {
    if ( get_option( 'gk_rewrite_version' ) !== '3' ) {
        flush_rewrite_rules();
        update_option( 'gk_rewrite_version', '3' );
    }
}
add_action( 'init', 'gk_flush_rewrite_once', 99 );


// ── Event Meta Box ──────────────────────────────────────────────────────────

function gk_event_meta_boxes() {
    add_meta_box( 'gk_event_details', 'Termin-Details', 'gk_event_details_cb', 'gk_event', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'gk_event_meta_boxes' );

function gk_event_details_cb( $post ) {
    $meta = get_post_custom( $post->ID );

    $start_date = isset( $meta['gk_event_start_date'] ) ? $meta['gk_event_start_date'][0] : '';
    $start_time = isset( $meta['gk_event_start_time'] ) ? $meta['gk_event_start_time'][0] : '';
    $end_date   = isset( $meta['gk_event_end_date'] )   ? $meta['gk_event_end_date'][0] : '';
    $end_time   = isset( $meta['gk_event_end_time'] )   ? $meta['gk_event_end_time'][0] : '';
    $all_day    = isset( $meta['gk_event_all_day'] )     ? $meta['gk_event_all_day'][0] : '';
    $location   = isset( $meta['gk_event_location'] )    ? $meta['gk_event_location'][0] : '';
    $address    = isset( $meta['gk_event_address'] )     ? $meta['gk_event_address'][0] : '';
    $organizer  = isset( $meta['gk_event_organizer'] )   ? $meta['gk_event_organizer'][0] : '';
    $url        = isset( $meta['gk_event_url'] )         ? $meta['gk_event_url'][0] : '';

    wp_nonce_field( 'gk_meta_box_nonce', 'gk_meta_box_nonce' );
    ?>
    <table class="form-table"><tbody>
    <tr>
        <th><label for="gk_event_start_date">Startdatum</label></th>
        <td><input type="date" name="gk_event_start_date" id="gk_event_start_date" value="<?php echo esc_attr( $start_date ); ?>" required /></td>
        <th><label for="gk_event_start_time">Startzeit</label></th>
        <td><input type="time" name="gk_event_start_time" id="gk_event_start_time" value="<?php echo esc_attr( $start_time ); ?>" /></td>
    </tr>
    <tr>
        <th><label for="gk_event_end_date">Enddatum</label></th>
        <td><input type="date" name="gk_event_end_date" id="gk_event_end_date" value="<?php echo esc_attr( $end_date ); ?>" /></td>
        <th><label for="gk_event_end_time">Endzeit</label></th>
        <td><input type="time" name="gk_event_end_time" id="gk_event_end_time" value="<?php echo esc_attr( $end_time ); ?>" /></td>
    </tr>
    <tr>
        <th><label for="gk_event_all_day">Ganztägig</label></th>
        <td><input type="checkbox" name="gk_event_all_day" id="gk_event_all_day" value="1" <?php checked( $all_day, '1' ); ?> /></td>
        <th></th><td></td>
    </tr>
    <tr>
        <th><label for="gk_event_location">Ort</label></th>
        <td><input type="text" name="gk_event_location" id="gk_event_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" /><br><span class="description">z.B. "Rathaus Starnberg"</span></td>
        <th><label for="gk_event_address">Adresse</label></th>
        <td><input type="text" name="gk_event_address" id="gk_event_address" value="<?php echo esc_attr( $address ); ?>" class="regular-text" /><br><span class="description">Strasse, PLZ Ort</span></td>
    </tr>
    <tr>
        <th><label for="gk_event_organizer">Veranstalter</label></th>
        <td><input type="text" name="gk_event_organizer" id="gk_event_organizer" value="<?php echo esc_attr( $organizer ); ?>" class="regular-text" /></td>
        <th><label for="gk_event_url">Link</label></th>
        <td><input type="url" name="gk_event_url" id="gk_event_url" value="<?php echo esc_attr( $url ); ?>" class="regular-text" /><br><span class="description">Externer Link zum Termin</span></td>
    </tr>
    </tbody></table>
    <?php
}

function gk_save_event_meta( $post_id ) {
    if ( ! gk_can_save_meta( $post_id ) ) return;
    if ( get_post_type( $post_id ) !== 'gk_event' ) return;

    $text_fields = array(
        'gk_event_start_date', 'gk_event_start_time',
        'gk_event_end_date', 'gk_event_end_time',
        'gk_event_location', 'gk_event_address',
        'gk_event_organizer', 'gk_event_url',
    );
    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }

    update_post_meta( $post_id, 'gk_event_all_day', isset( $_POST['gk_event_all_day'] ) ? '1' : '0' );
}
add_action( 'save_post', 'gk_save_event_meta' );


// ── Event Columns in Admin ──────────────────────────────────────────────────

function gk_event_admin_columns( $columns ) {
    $new = array();
    foreach ( $columns as $key => $title ) {
        $new[ $key ] = $title;
        if ( $key === 'title' ) {
            $new['gk_event_date'] = 'Datum';
            $new['gk_event_loc']  = 'Ort';
        }
    }
    return $new;
}
add_filter( 'manage_gk_event_posts_columns', 'gk_event_admin_columns' );

function gk_event_admin_column_content( $column, $post_id ) {
    if ( $column === 'gk_event_date' ) {
        $date = get_post_meta( $post_id, 'gk_event_start_date', true );
        $time = get_post_meta( $post_id, 'gk_event_start_time', true );
        if ( $date ) {
            echo esc_html( date_i18n( 'd.m.Y', strtotime( $date ) ) );
            if ( $time && get_post_meta( $post_id, 'gk_event_all_day', true ) !== '1' ) {
                echo ', ' . esc_html( $time ) . ' Uhr';
            }
        }
    }
    if ( $column === 'gk_event_loc' ) {
        echo esc_html( get_post_meta( $post_id, 'gk_event_location', true ) );
    }
}
add_action( 'manage_gk_event_posts_custom_column', 'gk_event_admin_column_content', 10, 2 );

function gk_event_admin_sortable( $columns ) {
    $columns['gk_event_date'] = 'gk_event_date';
    return $columns;
}
add_filter( 'manage_edit-gk_event_sortable_columns', 'gk_event_admin_sortable' );

function gk_event_admin_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) return;
    if ( $query->get( 'orderby' ) === 'gk_event_date' ) {
        $query->set( 'meta_key', 'gk_event_start_date' );
        $query->set( 'orderby', 'meta_value' );
    }
}
add_action( 'pre_get_posts', 'gk_event_admin_orderby' );


// ── Archive: Show Future Events by Default ──────────────────────────────────

function gk_event_archive_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) return;

    if ( $query->is_post_type_archive( 'gk_event' ) || $query->is_tax( 'event_kategorie' ) ) {
        $query->set( 'meta_key', 'gk_event_start_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'ASC' );

        // Only future events (today or later)
        if ( ! $query->get( 'gk_show_past' ) ) {
            $query->set( 'meta_query', array(
                array(
                    'key'     => 'gk_event_start_date',
                    'value'   => current_time( 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ),
            ) );
        }

        // Filter by Zuordnung if requested
        if ( ! empty( $_GET['zuordnung'] ) ) {
            $zuordnung_slug = sanitize_text_field( $_GET['zuordnung'] );
            $tax_query = $query->get( 'tax_query' ) ?: array();
            $tax_query[] = array(
                'taxonomy' => 'gk_zuordnung',
                'field'    => 'slug',
                'terms'    => $zuordnung_slug,
            );
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'gk_event_archive_query' );


// ── OV Event Template Routing ───────────────────────────────────────────────

function gk_ov_event_template_routing( $template ) {
    global $post;
    if ( ! is_singular( 'gk_event' ) || ! $post ) return $template;

    if ( function_exists( 'gk_post_is_ov' ) && gk_post_is_ov( $post->ID ) ) {
        $ov_template = locate_template( 'single-event-OV.php' );
        if ( $ov_template ) {
            add_filter( 'body_class', 'gk_ov_body_class' );
            return $ov_template;
        }
    }
    return $template;
}
add_filter( 'single_template', 'gk_ov_event_template_routing' );


// ── Shortcodes ──────────────────────────────────────────────────────────────

/**
 * [termine] — Display upcoming events.
 *
 * Attributes:
 *   anzahl    — Number of events (default: 10)
 *   kategorie — Filter by event_kategorie slug
 *   vergangen — Show past events: "ja" (default: only future)
 */
function gk_shortcode_termine( $atts ) {
    $atts = shortcode_atts( array(
        'anzahl'    => 10,
        'kategorie' => '',
        'vergangen' => 'nein',
    ), $atts );

    $args = array(
        'post_type'      => 'gk_event',
        'posts_per_page' => intval( $atts['anzahl'] ),
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    if ( $atts['vergangen'] !== 'ja' ) {
        $args['meta_query'] = array(
            array(
                'key'     => 'gk_event_start_date',
                'value'   => current_time( 'Y-m-d' ),
                'compare' => '>=',
                'type'    => 'DATE',
            ),
        );
    } else {
        $args['order'] = 'DESC';
    }

    if ( ! empty( $atts['kategorie'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event_kategorie',
                'field'    => 'slug',
                'terms'    => $atts['kategorie'],
            ),
        );
    }

    $events = new WP_Query( $args );
    if ( ! $events->have_posts() ) {
        return '<p class="keine-termine">Aktuell keine Termine.</p>';
    }

    ob_start();
    ?>
    <div class="gk-event-list">
    <?php while ( $events->have_posts() ) : $events->the_post();
        $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
        $start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
        $end_date   = get_post_meta( get_the_ID(), 'gk_event_end_date', true );
        $end_time   = get_post_meta( get_the_ID(), 'gk_event_end_time', true );
        $all_day    = get_post_meta( get_the_ID(), 'gk_event_all_day', true );
        $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
    ?>
        <article class="gk-event-item clearfix">
            <div class="gk-event-item__date">
                <span class="gk-date__day"><?php echo esc_html( date_i18n( 'd', strtotime( $start_date ) ) ); ?></span>
                <span class="gk-date__month"><?php echo esc_html( date_i18n( 'M', strtotime( $start_date ) ) ); ?></span>
                <span class="gk-date__year"><?php echo esc_html( date_i18n( 'Y', strtotime( $start_date ) ) ); ?></span>
            </div>
            <div class="gk-event-item__info">
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p class="gk-event-item__meta">
                    <?php if ( $all_day === '1' ) : ?>
                        Ganztägig
                    <?php elseif ( $start_time ) : ?>
                        <?php echo esc_html( $start_time ); ?> Uhr
                        <?php if ( $end_time ) : ?>
                            &ndash; <?php echo esc_html( $end_time ); ?> Uhr
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ( $end_date && $end_date !== $start_date ) : ?>
                        &ndash; <?php echo esc_html( date_i18n( 'd.m.Y', strtotime( $end_date ) ) ); ?>
                    <?php endif; ?>
                    <?php if ( $location ) : ?>
                        <span class="gk-event-item__location">&bull; <?php echo esc_html( $location ); ?></span>
                    <?php endif; ?>
                </p>
                <?php if ( has_excerpt() ) : ?>
                    <p class="gk-event-item__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
                <?php endif; ?>
            </div>
        </article>
    <?php endwhile; ?>
    </div>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'termine', 'gk_shortcode_termine' );

/**
 * [naechste_termine] — Compact list of next N events (for sidebars/widgets).
 */
function gk_shortcode_naechste_termine( $atts ) {
    $atts = shortcode_atts( array( 'anzahl' => 5 ), $atts );

    $events = new WP_Query( array(
        'post_type'      => 'gk_event',
        'posts_per_page' => intval( $atts['anzahl'] ),
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array( array(
            'key'     => 'gk_event_start_date',
            'value'   => current_time( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ) ),
    ) );

    if ( ! $events->have_posts() ) {
        return '<p class="keine-termine">Keine kommenden Termine.</p>';
    }

    ob_start();
    echo '<ul class="naechste-termine">';
    while ( $events->have_posts() ) : $events->the_post();
        $date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
        $time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
        echo '<li>';
        echo '<strong>' . esc_html( date_i18n( 'd.m.', strtotime( $date ) ) ) . '</strong> ';
        if ( $time && get_post_meta( get_the_ID(), 'gk_event_all_day', true ) !== '1' ) {
            echo esc_html( $time ) . ' ';
        }
        echo '<a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a>';
        echo '</li>';
    endwhile;
    echo '</ul>';
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'naechste_termine', 'gk_shortcode_naechste_termine' );


// ── iCal Export ─────────────────────────────────────────────────────────────

/**
 * iCal feed: /termine/ical/
 * Single event iCal: append ?ical=1 to event permalink
 */
function gk_ical_rewrite_rules() {
    add_rewrite_rule( '^termine/ical/?$', 'index.php?post_type=gk_event&gk_ical=1', 'top' );
}
add_action( 'init', 'gk_ical_rewrite_rules' );

function gk_ical_query_vars( $vars ) {
    $vars[] = 'gk_ical';
    return $vars;
}
add_filter( 'query_vars', 'gk_ical_query_vars' );

function gk_ical_template_redirect() {
    if ( ! get_query_var( 'gk_ical' ) && empty( $_GET['ical'] ) ) return;

    // Single event iCal
    if ( is_singular( 'gk_event' ) && ! empty( $_GET['ical'] ) ) {
        gk_output_ical( array( get_queried_object() ) );
        exit;
    }

    // Full calendar feed
    if ( get_query_var( 'gk_ical' ) ) {
        $events = get_posts( array(
            'post_type'      => 'gk_event',
            'posts_per_page' => 200,
            'meta_key'       => 'gk_event_start_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_query'     => array( array(
                'key'     => 'gk_event_start_date',
                'value'   => date( 'Y-m-d', strtotime( '-3 months' ) ),
                'compare' => '>=',
                'type'    => 'DATE',
            ) ),
        ) );
        gk_output_ical( $events );
        exit;
    }
}
add_action( 'template_redirect', 'gk_ical_template_redirect' );

function gk_output_ical( $events ) {
    $sitename = get_bloginfo( 'name' );

    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="termine.ics"' );

    echo "BEGIN:VCALENDAR\r\n";
    echo "VERSION:2.0\r\n";
    echo "PRODID:-//Neurg Kreisverband//" . gk_ical_escape( $sitename ) . "//DE\r\n";
    echo "CALSCALE:GREGORIAN\r\n";
    echo "METHOD:PUBLISH\r\n";
    echo "X-WR-CALNAME:" . gk_ical_escape( $sitename ) . " Termine\r\n";

    foreach ( $events as $event ) {
        $start_date = get_post_meta( $event->ID, 'gk_event_start_date', true );
        $start_time = get_post_meta( $event->ID, 'gk_event_start_time', true );
        $end_date   = get_post_meta( $event->ID, 'gk_event_end_date', true );
        $end_time   = get_post_meta( $event->ID, 'gk_event_end_time', true );
        $all_day    = get_post_meta( $event->ID, 'gk_event_all_day', true );
        $location   = get_post_meta( $event->ID, 'gk_event_location', true );
        $address    = get_post_meta( $event->ID, 'gk_event_address', true );

        if ( ! $start_date ) continue;

        echo "BEGIN:VEVENT\r\n";
        echo "UID:" . $event->ID . "@" . wp_parse_url( home_url(), PHP_URL_HOST ) . "\r\n";
        echo "DTSTAMP:" . gmdate( 'Ymd\THis\Z' ) . "\r\n";
        echo "SUMMARY:" . gk_ical_escape( $event->post_title ) . "\r\n";

        if ( $all_day === '1' ) {
            echo "DTSTART;VALUE=DATE:" . str_replace( '-', '', $start_date ) . "\r\n";
            if ( $end_date ) {
                // iCal all-day end date is exclusive, so add one day
                $end_ts = strtotime( $end_date . ' +1 day' );
                echo "DTEND;VALUE=DATE:" . date( 'Ymd', $end_ts ) . "\r\n";
            }
        } else {
            $dtstart = str_replace( '-', '', $start_date ) . 'T' . str_replace( ':', '', $start_time ?: '0000' ) . '00';
            echo "DTSTART:" . $dtstart . "\r\n";
            if ( $end_date && $end_time ) {
                $dtend = str_replace( '-', '', $end_date ) . 'T' . str_replace( ':', '', $end_time ) . '00';
                echo "DTEND:" . $dtend . "\r\n";
            } elseif ( $end_time ) {
                $dtend = str_replace( '-', '', $start_date ) . 'T' . str_replace( ':', '', $end_time ) . '00';
                echo "DTEND:" . $dtend . "\r\n";
            }
        }

        $loc_parts = array_filter( array( $location, $address ) );
        if ( ! empty( $loc_parts ) ) {
            echo "LOCATION:" . gk_ical_escape( implode( ', ', $loc_parts ) ) . "\r\n";
        }

        if ( $event->post_content ) {
            echo "DESCRIPTION:" . gk_ical_escape( wp_strip_all_tags( $event->post_content ) ) . "\r\n";
        }

        echo "URL:" . get_permalink( $event->ID ) . "\r\n";
        echo "END:VEVENT\r\n";
    }

    echo "END:VCALENDAR\r\n";
}

function gk_ical_escape( $text ) {
    $text = str_replace( array( "\\", ";", "," ), array( "\\\\", "\\;", "\\," ), $text );
    $text = str_replace( array( "\r\n", "\r", "\n" ), "\\n", $text );
    return $text;
}


// ── Event Capabilities for Custom Roles ─────────────────────────────────────

/**
 * Map event capabilities to standard post capabilities.
 * This ensures our custom roles can manage events without extra cap setup.
 */
function gk_event_map_meta_cap( $caps, $cap, $user_id, $args ) {
    $event_caps = array(
        'edit_gk_event'   => 'edit_posts',
        'delete_gk_event' => 'delete_posts',
        'read_gk_event'   => 'read',
    );

    if ( isset( $event_caps[ $cap ] ) ) {
        $caps = array( $event_caps[ $cap ] );
    }

    return $caps;
}
add_filter( 'map_meta_cap', 'gk_event_map_meta_cap', 10, 4 );


// ── Helper: Format event date for display ───────────────────────────────────

function gk_format_event_date( $post_id ) {
    $start_date = get_post_meta( $post_id, 'gk_event_start_date', true );
    $start_time = get_post_meta( $post_id, 'gk_event_start_time', true );
    $end_date   = get_post_meta( $post_id, 'gk_event_end_date', true );
    $end_time   = get_post_meta( $post_id, 'gk_event_end_time', true );
    $all_day    = get_post_meta( $post_id, 'gk_event_all_day', true );

    if ( ! $start_date ) return '';

    $out = date_i18n( 'l, j. F Y', strtotime( $start_date ) );

    if ( $all_day === '1' ) {
        $out .= ' (ganztägig)';
    } elseif ( $start_time ) {
        $out .= ', ' . $start_time . ' Uhr';
    }

    if ( $end_date && $end_date !== $start_date ) {
        $out .= ' – ' . date_i18n( 'j. F Y', strtotime( $end_date ) );
        if ( $end_time && $all_day !== '1' ) {
            $out .= ', ' . $end_time . ' Uhr';
        }
    } elseif ( $end_time && $end_time !== $start_time && $all_day !== '1' ) {
        $out .= ' – ' . $end_time . ' Uhr';
    }

    return $out;
}
