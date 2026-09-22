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

/**
 * Register event post type.
 */
function gk_register_event_post_type() {
    register_post_type(
        'gk_event',
        array(
			'labels'        => array(
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
			'public'        => true,
			'has_archive'   => false,
			'rewrite'       => array(
				'slug'       => 'termin',
				'with_front' => false,
			),
			'menu_icon'     => 'dashicons-calendar-alt',
			'menu_position' => 6,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions', 'custom-fields' ),
			'show_in_rest'  => true,
        )
    );

    register_taxonomy(
        'event_kategorie',
        'gk_event',
        array(
			'labels'       => array(
				'name'          => 'Termin-Kategorien',
				'singular_name' => 'Termin-Kategorie',
				'add_new_item'  => 'Neue Kategorie',
				'search_items'  => 'Kategorien suchen',
			),
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'termin-kategorie' ),
			'show_in_rest' => true,
        )
    );
}
add_action( 'init', 'gk_register_event_post_type' );

/**
 * Whether a municipality should have a shortcut in the event menu.
 * Existing assignments remain visible unless explicitly unchecked.
 *
 * @param string $slug Municipality slug.
 * @param array  $municipality Saved map row.
 * @return bool
 */
function gk_map_events_enabled( $slug, $municipality ) {
    if ( array_key_exists( 'eventsEnabled', $municipality ) ) {
        return true === $municipality['eventsEnabled'];
    }
    return (bool) get_term_by( 'slug', $municipality['ovSlug'] ?? $slug, 'gk_zuordnung' );
}

/**
 * Shared OVs stay visible if at least one assigned municipality enables them.
 *
 * @param WP_Term $term OV or KV assignment.
 * @return bool
 */
function gk_ov_in_event_menu( $term ) {
    $map   = gk_get_kreiskarte_data();
    $found = false;
    foreach ( $map['municipalities'] ?? array() as $slug => $municipality ) {
        if ( ( $municipality['ovSlug'] ?? $slug ) !== $term->slug ) {
            continue;
        }
        $found = true;
        if ( gk_map_events_enabled( $slug, $municipality ) ) {
            return true;
        }
    }
    return ! $found || 'kreisverband' === $term->slug;
}

/**
 * Resolve the requested admin view without extending the current user's scope.
 *
 * @return WP_Term|false
 */
function gk_event_admin_selected_term() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view context; core checks creation capabilities and editorial scope is enforced below.
    $slug = isset( $_GET['gk_zuordnung'] ) && is_string( $_GET['gk_zuordnung'] ) ? sanitize_title( wp_unslash( $_GET['gk_zuordnung'] ) ) : '';
    $term = $slug ? get_term_by( 'slug', $slug, 'gk_zuordnung' ) : false;
    if ( ! $term || ! current_user_can( 'edit_posts' ) ) {
        return false;
    }
    $scope = gk_user_scope();
    return null === $scope || $scope === (int) $term->term_id ? $term : false;
}

/**
 * Keep the selected OV when the native New Event button opens the editor.
 *
 * @param string $url Generated admin URL.
 * @param string $path Relative admin path.
 * @return string
 */
function gk_event_scoped_new_url( $url, $path ) {
    if ( 'post-new.php?post_type=gk_event' !== $path || ! function_exists( 'get_current_screen' ) ) {
        return $url;
    }
    $screen = get_current_screen();
    $term   = $screen && 'edit-gk_event' === $screen->id ? gk_event_admin_selected_term() : false;
    return $term ? add_query_arg( 'gk_zuordnung', $term->slug, $url ) : $url;
}
add_filter( 'admin_url', 'gk_event_scoped_new_url', 10, 2 );

/** Clearly identify the selected event list and its creation context. */
function gk_event_admin_scope_notice() {
    $screen = get_current_screen();
    $term   = $screen && 'edit-gk_event' === $screen->id ? gk_event_admin_selected_term() : false;
    if ( $term ) {
        echo '<div class="notice notice-info"><p><strong>' . esc_html( 'Termine: ' . $term->name ) . '</strong> — ' . esc_html__( 'Neue Termine werden diesem Verband zugeordnet. Eine lokale OV-Unterseite ist dafür nicht erforderlich.', 'neurg-kreisverband' ) . '</p></div>';
    }
}
add_action( 'admin_notices', 'gk_event_admin_scope_notice' );

// Flush rewrite rules once after CPT slug change.
/**
 * Flush rewrite once.
 */
function gk_flush_rewrite_once() {
    if ( '3' !== get_option( 'gk_rewrite_version' ) ) {
        flush_rewrite_rules();
        update_option( 'gk_rewrite_version', '3' );
    }
}
add_action( 'init', 'gk_flush_rewrite_once', 99 );


// ── Register meta for REST / Block Editor ──────────────────────────────────

/**
 * Register event meta.
 */
function gk_register_event_meta() {
    $fields = array(
        'gk_event_start_date',
        'gk_event_start_time',
        'gk_event_end_date',
        'gk_event_end_time',
        'gk_event_all_day',
        'gk_event_location',
        'gk_event_address',
        'gk_event_organizer',
        'gk_event_url',
    );
    foreach ( $fields as $key ) {
        register_post_meta(
            'gk_event',
            $key,
            array(
				'show_in_rest'      => true,
				'single'            => true,
				'type'              => 'string',
				'sanitize_callback' => 'gk_sanitize_event_meta',
				'auth_callback'     => function ( $allowed, $meta_key, $post_id ) {
					return current_user_can( 'edit_post', $post_id ); },
            )
        );
    }
}
add_action( 'init', 'gk_register_event_meta' );

/**
 * Sanitize event metadata equally for the classic editor and REST requests.
 *
 * @param mixed  $value Value.
 * @param string $key Key.
 */
function gk_sanitize_event_meta( $value, $key ) {
    if ( ! is_scalar( $value ) ) {
        return '';
    }
    $value = trim( (string) $value );
    if ( in_array( $key, array( 'gk_event_start_date', 'gk_event_end_date' ), true ) ) {
        return gk_event_datetime( $value ) ? $value : '';
    }
    if ( in_array( $key, array( 'gk_event_start_time', 'gk_event_end_time' ), true ) ) {
        return preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value ) ? $value : '';
    }
    if ( 'gk_event_all_day' === $key ) {
        return '1' === $value ? '1' : '0';
    }
    if ( 'gk_event_url' === $key ) {
        return gk_public_website_url( $value );
    }
    return sanitize_text_field( $value );
}

/**
 * Enqueue event sidebar.
 */
function gk_enqueue_event_sidebar() {
    $screen = get_current_screen();
    if ( ! $screen || 'gk_event' !== $screen->post_type ) {
        return;
    }
    wp_enqueue_script(
        'gk-event-sidebar',
        GK_URI . '/lib/js/event-sidebar.js',
        array( 'wp-plugins', 'wp-edit-post', 'wp-element', 'wp-components', 'wp-data', 'wp-editor' ),
        GK_VERSION,
        true
    );
}
add_action( 'enqueue_block_editor_assets', 'gk_enqueue_event_sidebar' );


// ── Event Meta Box (classic editor fallback) ───────────────────────────────

/**
 * Event meta boxes.
 */
function gk_event_meta_boxes() {
    // Skip meta box when block editor is active — fields are in the sidebar.
    if ( function_exists( 'get_current_screen' ) ) {
        $screen = get_current_screen();
        if ( $screen && $screen->is_block_editor() ) {
            return;
        }
    }
    add_meta_box( 'gk_event_details', 'Termin-Details', 'gk_event_details_cb', 'gk_event', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'gk_event_meta_boxes' );

/**
 * Event details cb.
 *
 * @param WP_Post $post Post.
 */
function gk_event_details_cb( $post ) {
    $meta = get_post_custom( $post->ID );

    $start_date = isset( $meta['gk_event_start_date'] ) ? $meta['gk_event_start_date'][0] : '';
    $start_time = isset( $meta['gk_event_start_time'] ) ? $meta['gk_event_start_time'][0] : '';
    $end_date   = isset( $meta['gk_event_end_date'] ) ? $meta['gk_event_end_date'][0] : '';
    $end_time   = isset( $meta['gk_event_end_time'] ) ? $meta['gk_event_end_time'][0] : '';
    $all_day    = isset( $meta['gk_event_all_day'] ) ? $meta['gk_event_all_day'][0] : '';
    $location   = isset( $meta['gk_event_location'] ) ? $meta['gk_event_location'][0] : '';
    $address    = isset( $meta['gk_event_address'] ) ? $meta['gk_event_address'][0] : '';
    $organizer  = isset( $meta['gk_event_organizer'] ) ? $meta['gk_event_organizer'][0] : '';
    $url        = isset( $meta['gk_event_url'] ) ? $meta['gk_event_url'][0] : '';

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

/**
 * Save event meta.
 *
 * @param int $post_id Post id.
 */
function gk_save_event_meta( $post_id ) {
    if ( ! gk_can_save_meta( $post_id ) ) {
		return;
    }
    if ( 'gk_event' !== get_post_type( $post_id ) ) {
		return;
    }

    $text_fields = array(
        'gk_event_start_date',
		'gk_event_start_time',
        'gk_event_end_date',
		'gk_event_end_time',
        'gk_event_location',
		'gk_event_address',
        'gk_event_organizer',
		'gk_event_url',
    );
    foreach ( $text_fields as $field ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- gk_can_save_meta() checks the nonce, autosave state and edit_post capability before any input is processed.
        if ( isset( $_POST[ $field ] ) ) {
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Missing -- The dedicated sanitizer below rejects malformed scope/date/time values instead of repairing them. gk_can_save_meta() checks the nonce, autosave state and edit_post capability before any input is processed.
            update_post_meta( $post_id, $field, gk_sanitize_event_meta( wp_unslash( $_POST[ $field ] ), $field ) );
        }
    }

    // phpcs:ignore WordPress.Security.NonceVerification.Missing -- gk_can_save_meta() checks the nonce, autosave state and edit_post capability before any input is processed.
    update_post_meta( $post_id, 'gk_event_all_day', isset( $_POST['gk_event_all_day'] ) ? '1' : '0' );
}
add_action( 'save_post', 'gk_save_event_meta' );


// ── Event Columns in Admin ──────────────────────────────────────────────────

/**
 * Event admin columns.
 *
 * @param mixed $columns Columns.
 */
function gk_event_admin_columns( $columns ) {
    $new = array();
    foreach ( $columns as $key => $title ) {
        $new[ $key ] = $title;
        if ( 'title' === $key ) {
            $new['gk_event_date'] = 'Datum';
            $new['gk_event_loc']  = 'Ort';
        }
    }
    return $new;
}
add_filter( 'manage_gk_event_posts_columns', 'gk_event_admin_columns' );

/**
 * Event admin column content.
 *
 * @param mixed $column Column.
 * @param int   $post_id Post id.
 */
function gk_event_admin_column_content( $column, $post_id ) {
    if ( 'gk_event_date' === $column ) {
        $date = get_post_meta( $post_id, 'gk_event_start_date', true );
        $time = get_post_meta( $post_id, 'gk_event_start_time', true );
        if ( $date ) {
            echo esc_html( date_i18n( 'd.m.Y', strtotime( $date ) ) );
            if ( $time && '1' !== get_post_meta( $post_id, 'gk_event_all_day', true ) ) {
                echo ', ' . esc_html( $time ) . ' Uhr';
            }
        }
    }
    if ( 'gk_event_loc' === $column ) {
        echo esc_html( get_post_meta( $post_id, 'gk_event_location', true ) );
    }
}
add_action( 'manage_gk_event_posts_custom_column', 'gk_event_admin_column_content', 10, 2 );

/**
 * Event admin sortable.
 *
 * @param mixed $columns Columns.
 */
function gk_event_admin_sortable( $columns ) {
    $columns['gk_event_date'] = 'gk_event_date';
    return $columns;
}
add_filter( 'manage_edit-gk_event_sortable_columns', 'gk_event_admin_sortable' );

/**
 * Event admin orderby.
 *
 * @param WP_Query $query Query.
 */
function gk_event_admin_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
    }
    if ( 'gk_event_date' === $query->get( 'orderby' ) ) {
        $query->set( 'meta_key', 'gk_event_start_date' );
        $query->set( 'orderby', 'meta_value' );
    }
}
add_action( 'pre_get_posts', 'gk_event_admin_orderby' );


// ── Archive: Show Future Events by Default ──────────────────────────────────

/**
 * Event archive query.
 *
 * @param WP_Query $query Query.
 */
function gk_event_archive_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
		return;
    }

    if ( $query->is_post_type_archive( 'gk_event' ) || $query->is_tax( 'event_kategorie' ) ) {
        $query->set( 'meta_key', 'gk_event_start_date' );
        $query->set( 'orderby', 'meta_value' );
        $query->set( 'order', 'ASC' );

        // Only future events (today or later).
        if ( ! $query->get( 'gk_show_past' ) ) {
            $query->set(
                'meta_query',
                array(
					array(
						'key'     => 'gk_event_start_date',
						'value'   => current_time( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
                )
            );
        }

        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        $scoped = gk_scope_event_query( array( 'tax_query' => $query->get( 'tax_query' ) ), gk_event_scope() );
        foreach ( $scoped as $key => $value ) {
            $query->set( $key, $value );
        }
    }
}
add_action( 'pre_get_posts', 'gk_event_archive_query' );


// ── OV Event Template Routing ───────────────────────────────────────────────

/**
 * Ov event template routing.
 *
 * @param string $template Template.
 */
function gk_ov_event_template_routing( $template ) {
    global $post;
    if ( ! is_singular( 'gk_event' ) || ! $post ) {
		return $template;
    }

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


/**
 * Resolve an OV context before an optional filter. An unfiltered KV calendar
 * remains an overview of every area for backwards compatibility.
 *
 * @param string|null $requested Requested.
 */
function gk_event_scope( $requested = null ) {
    $context = gk_legal_context_slug();
    if ( $context && 'kreisverband' !== $context ) {
        return sanitize_title( $context );
    }
    if ( null === $requested ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Read-only view/filter parameter; no state changes occur here and write handlers check their own nonce. The dedicated sanitizer below rejects malformed scope/date/time values instead of repairing them.
        $requested = isset( $_GET['zuordnung'] ) ? wp_unslash( $_GET['zuordnung'] ) : '';
    }
    if ( ! is_string( $requested ) ) {
        return 'gk-invalid-scope';
    }
    $slug = sanitize_title( $requested );
    return '' !== $requested && '' === $slug ? 'gk-invalid-scope' : $slug;
}

/**
 * Compose area constraints with existing category constraints using AND.
 *
 * @param array  $args Args.
 * @param string $scope Scope.
 */
function gk_scope_event_query( $args, $scope ) {
    $args['post_status']  = 'publish';
    $args['has_password'] = false;
    if ( '' !== $scope ) {
        $scope_query = array(
            'taxonomy'         => 'gk_zuordnung',
            'field'            => 'slug',
            'terms'            => $scope,
            'include_children' => false,
        );
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        $args['tax_query'] = empty( $args['tax_query'] ) ? array( $scope_query ) : array(
            'relation' => 'AND',
			$args['tax_query'],
			$scope_query,
        );
    }
    return $args;
}

/**
 * Calendar subscription matching the currently selected area.
 *
 * @param string $scope Scope.
 */
function gk_event_ical_url( $scope = null ) {
    $scope = gk_event_scope( $scope );
    $url   = home_url( '/termine/ical/' );
    return '' !== $scope ? add_query_arg( 'zuordnung', $scope, $url ) : $url;
}

// ── Shortcodes ──────────────────────────────────────────────────────────────

/**
 * [termine] — Display upcoming events.
 *
 * Attributes:
 *   anzahl    — Number of events (default: 10)
 *   kategorie — Filter by event_kategorie slug
 *   vergangen — Show past events: "ja" (default: only future)
 *
 * @param array $atts Atts.
 */
function gk_shortcode_termine( $atts ) {
    $atts = shortcode_atts(
        array(
			'anzahl'    => 10,
			'kategorie' => '',
			'vergangen' => 'nein',
			'zuordnung' => '',
        ),
        $atts
    );

    $args = array(
        'post_type'      => 'gk_event',
        'posts_per_page' => intval( $atts['anzahl'] ),
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    );

    if ( 'ja' !== $atts['vergangen'] ) {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
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
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event_kategorie',
                'field'    => 'slug',
                'terms'    => $atts['kategorie'],
            ),
        );
    }

    $events = new WP_Query( gk_scope_event_query( $args, gk_event_scope( $atts['zuordnung'] ) ) );
    if ( ! $events->have_posts() ) {
        return '<p class="keine-termine">Aktuell keine Termine.</p>';
    }

    ob_start();
    ?>
    <div class="gk-event-list">
    <?php
    while ( $events->have_posts() ) :
		$events->the_post();
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
                    <?php if ( '1' === $all_day ) : ?>
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
 *
 * @param array $atts Atts.
 */
function gk_shortcode_naechste_termine( $atts ) {
    $atts = shortcode_atts(
        array(
			'anzahl'    => 5,
			'zuordnung' => '',
			'kategorie' => '',
        ),
        $atts
    );

    $args = array(
        'post_type'      => 'gk_event',
        'posts_per_page' => intval( $atts['anzahl'] ),
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        'meta_query'     => array(
			array(
				'key'     => 'gk_event_start_date',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
    );
    if ( ! empty( $atts['kategorie'] ) ) {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
        $args['tax_query'] = array(
			array(
				'taxonomy' => 'event_kategorie',
				'field'    => 'slug',
				'terms'    => $atts['kategorie'],
			),
		);
    }
    $events = new WP_Query( gk_scope_event_query( $args, gk_event_scope( $atts['zuordnung'] ) ) );

    if ( ! $events->have_posts() ) {
        return '<p class="keine-termine">Keine kommenden Termine.</p>';
    }

    ob_start();
    echo '<ul class="naechste-termine">';
    while ( $events->have_posts() ) :
		$events->the_post();
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
 * ICal feed: /termine/ical/
 * Single event iCal: append ?ical=1 to event permalink
 */
function gk_ical_rewrite_rules() {
    add_rewrite_rule( '^termine/ical/?$', 'index.php?post_type=gk_event&gk_ical=1', 'top' );
}
add_action( 'init', 'gk_ical_rewrite_rules' );

/**
 * Ical query vars.
 *
 * @param array $vars Vars.
 */
function gk_ical_query_vars( $vars ) {
    $vars[] = 'gk_ical';
    return $vars;
}
add_filter( 'query_vars', 'gk_ical_query_vars' );

/**
 * Ical template redirect.
 */
function gk_ical_template_redirect() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view/filter parameter; no state changes occur here and write handlers check their own nonce.
    if ( ! get_query_var( 'gk_ical' ) && empty( $_GET['ical'] ) ) {
		return;
    }

    // Single event iCal.
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only view/filter parameter; no state changes occur here and write handlers check their own nonce.
    if ( is_singular( 'gk_event' ) && ! empty( $_GET['ical'] ) ) {
        gk_output_ical( array( get_queried_object() ) );
        exit;
    }

    // Full calendar feed.
    if ( get_query_var( 'gk_ical' ) ) {
        $events = gk_get_ical_events( gk_event_scope() );
        gk_output_ical( $events );
        exit;
    }
}
add_action( 'template_redirect', 'gk_ical_template_redirect' );

/**
 * Published events for a calendar, optionally scoped to a single area.
 *
 * @param string $scope Scope.
 */
function gk_get_ical_events( $scope = '' ) {
    return get_posts(
        gk_scope_event_query(
            array(
				'post_type'      => 'gk_event',
				// phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page -- Preserve the existing bounded iCal subscription size of 200 published events.
				'posts_per_page' => 200,
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
				'meta_key'       => 'gk_event_start_date',
				'orderby'        => 'meta_value',
				'order'          => 'ASC',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
				'meta_query'     => array(
					array(
						'key'     => 'gk_event_start_date',
						'value'   => current_datetime()->modify( '-3 months' )->format( 'Y-m-d' ),
						'compare' => '>=',
						'type'    => 'DATE',
					),
				),
            ),
            $scope
        )
    );
}

/**
 * Parse stored local dates strictly so invalid legacy data cannot corrupt feeds.
 *
 * @param string $date Date.
 * @param string $time Time.
 */
function gk_event_datetime( $date, $time = '00:00' ) {
    if ( ! is_string( $date ) || ! is_string( $time ) ) {
        return false;
    }
    if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/D', $date ) || ! preg_match( '/^\d{2}:\d{2}$/D', $time ) ) {
        return false;
    }
    $value    = $date . ' ' . $time;
    $datetime = DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $value, wp_timezone() );
    return $datetime && $datetime->format( 'Y-m-d H:i' ) === $value ? $datetime : false;
}

/**
 * Fold iCalendar content lines at 75 octets without splitting UTF-8 characters.
 *
 * @param string $line Line.
 */
function gk_ical_fold_line( $line ) {
    $characters = preg_split( '//u', $line, -1, PREG_SPLIT_NO_EMPTY );
    if ( false === $characters ) {
        $characters = str_split( $line );
    }
    $output = '';
    $length = 0;
    foreach ( $characters as $character ) {
        $bytes = strlen( $character );
        if ( $length + $bytes > 75 ) {
            $output .= "\r\n ";
            $length  = 1;
        }
        $output .= $character;
        $length += $bytes;
    }
    return $output;
}

/**
 * Build a public calendar independently of HTTP headers for reuse and testing.
 *
 * @param WP_Post[] $events Events.
 */
function gk_build_ical( $events ) {
    $sitename = get_bloginfo( 'name' );
    $lines    = array(
        'BEGIN:VCALENDAR',
		'VERSION:2.0',
        'PRODID:-//Neurg Kreisverband//' . gk_ical_escape( $sitename ) . '//DE',
        'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
        'X-WR-CALNAME:' . gk_ical_escape( $sitename ) . ' Termine',
    );
    foreach ( $events as $event ) {
        $event = get_post( $event );
        if ( ! $event || 'gk_event' !== $event->post_type || 'publish' !== $event->post_status || '' !== $event->post_password ) {
            continue;
        }
        $start_date = get_post_meta( $event->ID, 'gk_event_start_date', true );
        $start_time = get_post_meta( $event->ID, 'gk_event_start_time', true );
        $end_date   = get_post_meta( $event->ID, 'gk_event_end_date', true );
        $end_time   = get_post_meta( $event->ID, 'gk_event_end_time', true );
        $all_day    = '1' === get_post_meta( $event->ID, 'gk_event_all_day', true );
        $start      = gk_event_datetime( $start_date, $all_day ? '00:00' : ( ( $start_time ? $start_time : '00:00' ) ) );
        if ( ! $start ) {
            continue;
        }
        $lines[] = 'BEGIN:VEVENT';
        $lines[] = 'UID:' . $event->ID . '@' . wp_parse_url( home_url(), PHP_URL_HOST );
        $lines[] = 'DTSTAMP:' . gmdate( 'Ymd\THis\Z' );
        $lines[] = 'SUMMARY:' . gk_ical_escape( $event->post_title );
        if ( $all_day ) {
            $end     = gk_event_datetime( ( $end_date ? $end_date : $start_date ) );
            $end     = $end && $end >= $start ? $end : $start;
            $lines[] = 'DTSTART;VALUE=DATE:' . $start->format( 'Ymd' );
            $lines[] = 'DTEND;VALUE=DATE:' . $end->modify( '+1 day' )->format( 'Ymd' );
        } else {
            $utc     = new DateTimeZone( 'UTC' );
            $lines[] = 'DTSTART:' . $start->setTimezone( $utc )->format( 'Ymd\THis\Z' );
            if ( $end_time || $end_date ) {
                $end = gk_event_datetime( ( $end_date ? $end_date : $start_date ), ( $end_time ? $end_time : ( $start_time ? $start_time : '00:00' ) ) );
                if ( $end && $end > $start ) {
                    $lines[] = 'DTEND:' . $end->setTimezone( $utc )->format( 'Ymd\THis\Z' );
                }
            }
        }
        $location = array_filter(
            array(
				get_post_meta( $event->ID, 'gk_event_location', true ),
				get_post_meta( $event->ID, 'gk_event_address', true ),
            )
        );
        if ( $location ) {
            $lines[] = 'LOCATION:' . gk_ical_escape( implode( ', ', $location ) );
        }
        if ( $event->post_content ) {
            $lines[] = 'DESCRIPTION:' . gk_ical_escape( wp_strip_all_tags( $event->post_content ) );
        }
        $lines[] = 'URL:' . str_replace( array( "\r", "\n" ), '', esc_url_raw( get_permalink( $event->ID ) ) );
        $lines[] = 'END:VEVENT';
    }
    $lines[] = 'END:VCALENDAR';
    return implode( "\r\n", array_map( 'gk_ical_fold_line', $lines ) ) . "\r\n";
}

/**
 * Output ical.
 *
 * @param WP_Post[] $events Events.
 */
function gk_output_ical( $events ) {
    header( 'Content-Type: text/calendar; charset=utf-8' );
    header( 'Content-Disposition: attachment; filename="termine.ics"' );
    // Calendar text is escaped and folded by the serializer, not HTML encoded.
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    echo gk_build_ical( $events );
}

/**
 * Ical escape.
 *
 * @param string $text Text.
 */
function gk_ical_escape( $text ) {
    $text = str_replace( array( '\\', ';', ',' ), array( '\\\\', '\\;', '\\,' ), $text );
    $text = str_replace( array( "\r\n", "\r", "\n" ), "\\n", $text );
    return $text;
}


// ── Event Capabilities for Custom Roles ─────────────────────────────────────

/**
 * Map event capabilities to standard post capabilities.
 * This ensures our custom roles can manage events without extra cap setup.
 *
 * @param array $caps Caps.
 * @param mixed $cap Cap.
 * @param int   $user_id User id.
 * @param array $args Args.
 */
function gk_event_map_meta_cap( $caps, $cap, $user_id, $args ) {
    $event_caps = array(
        'edit_gk_event'   => 'edit_post',
        'delete_gk_event' => 'delete_post',
        'read_gk_event'   => 'read_post',
    );
    if ( isset( $event_caps[ $cap ] ) ) {
        $post_id = isset( $args[0] ) ? absint( $args[0] ) : 0;
        return $post_id && 'gk_event' === get_post_type( $post_id )
            ? map_meta_cap( $event_caps[ $cap ], $user_id, $post_id )
            : array( 'do_not_allow' );
    }

    return $caps;
}
add_filter( 'map_meta_cap', 'gk_event_map_meta_cap', 10, 4 );


// ── Helper: Format event date for display ───────────────────────────────────

/**
 * Format event date.
 *
 * @param int $post_id Post id.
 */
function gk_format_event_date( $post_id ) {
    $start_date = get_post_meta( $post_id, 'gk_event_start_date', true );
    $start_time = get_post_meta( $post_id, 'gk_event_start_time', true );
    $end_date   = get_post_meta( $post_id, 'gk_event_end_date', true );
    $end_time   = get_post_meta( $post_id, 'gk_event_end_time', true );
    $all_day    = get_post_meta( $post_id, 'gk_event_all_day', true );

    if ( ! $start_date ) {
		return '';
    }

    $out = date_i18n( 'l, j. F Y', strtotime( $start_date ) );

    if ( '1' === $all_day ) {
        $out .= ' (ganztägig)';
    } elseif ( $start_time ) {
        $out .= ', ' . $start_time . ' Uhr';
    }

    if ( $end_date && $end_date !== $start_date ) {
        $out .= ' – ' . date_i18n( 'j. F Y', strtotime( $end_date ) );
        if ( $end_time && '1' !== $all_day ) {
            $out .= ', ' . $end_time . ' Uhr';
        }
    } elseif ( $end_time && $end_time !== $start_time && '1' !== $all_day ) {
        $out .= ' – ' . $end_time . ' Uhr';
    }

    return $out;
}
