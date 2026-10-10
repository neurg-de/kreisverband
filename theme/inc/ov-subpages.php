<?php
/**
 * Virtual OV subpages: "Termine" and "Mitmachen" inside the OV context.
 *
 * Routes: /{ov-homepage-path}/termine/ and /{ov-homepage-path}/mitmachen/
 * (fallback without pretty permalinks: ?gk_ov_page=termine&gk_ov_context={slug}).
 *
 * - No real pages are created; rules exist only for actual OV homepage paths.
 * - A real page at the same path always wins.
 * - A route is live only while its switch is enabled in the OV homepage
 *   settings; with both switches off the previous behavior is unchanged.
 *
 * @package Neurg_Kreisverband
 */

/**
 * Known virtual subpages and their default labels.
 *
 * @return array Key => default label.
 */
function gk_ov_subpage_types() {
    return array(
        'termine'   => __( 'Termine', 'neurg-kreisverband' ),
        'mitmachen' => __( 'Mitmachen', 'neurg-kreisverband' ),
    );
}

/**
 * Subpage settings of one OV, normalized.
 *
 * @param int $term_id OV ID.
 * @return array{termine: bool, mitmachen: bool, label_termine: string, label_mitmachen: string, mitmachen_text: string}
 */
function gk_ov_subpage_config( $term_id ) {
    $config = gk_ov_home_config( $term_id );
    $result = array();
    foreach ( gk_ov_subpage_types() as $type => $label ) {
        $result[ $type ]            = ! empty( $config[ 'menu_' . $type ] );
        $result[ 'label_' . $type ] = '' !== $config[ 'label_' . $type ] ? $config[ 'label_' . $type ] : $label;
    }
    $result['mitmachen_text'] = $config['mitmachen_text'];
    return $result;
}

/**
 * Whether a subpage is enabled and reachable for an OV.
 *
 * @param int    $term_id OV ID.
 * @param string $type    termine|mitmachen.
 * @return bool
 */
function gk_ov_subpage_enabled( $term_id, $type ) {
    if ( ! isset( gk_ov_subpage_types()[ $type ] ) || ! gk_get_ov_public_homepage_id( $term_id ) ) {
        return false;
    }
    return gk_ov_subpage_config( $term_id )[ $type ];
}

/**
 * Public URL of an OV subpage, or '' when it is not enabled.
 *
 * @param int    $term_id OV ID.
 * @param string $type    termine|mitmachen.
 * @return string
 */
function gk_ov_subpage_url( $term_id, $type ) {
    if ( ! gk_ov_subpage_enabled( $term_id, $type ) ) {
        return '';
    }
    $term = get_term( $term_id, 'gk_zuordnung' );
    if ( get_option( 'permalink_structure' ) ) {
        return home_url( user_trailingslashit( get_page_uri( gk_get_ov_public_homepage_id( $term_id ) ) . '/' . $type ) );
    }
    return add_query_arg(
        array(
			'gk_ov_page'    => $type,
			'gk_ov_context' => $term->slug,
		),
        home_url( '/' )
    );
}

/**
 * Homepage path => OV slug for every OV with a public homepage.
 * Cached in an option; invalidated whenever pages or OV terms change.
 *
 * @return array
 */
function gk_ov_subpage_paths() {
    $paths = get_option( 'gk_ov_subpage_paths' );
    if ( is_array( $paths ) ) {
        return $paths;
    }
    $paths = array();
    foreach ( gk_get_ov_terms() as $term ) {
        $page_id = gk_get_ov_public_homepage_id( $term->term_id );
        if ( $page_id ) {
            $paths[ get_page_uri( $page_id ) ] = $term->slug;
        }
    }
    update_option( 'gk_ov_subpage_paths', $paths, true );
    return $paths;
}

/** Drop the cached homepage paths; the next request rebuilds and re-flushes. */
function gk_ov_subpage_paths_invalidate() {
    delete_option( 'gk_ov_subpage_paths' );
}
add_action( 'save_post_page', 'gk_ov_subpage_paths_invalidate' );
add_action( 'deleted_post', 'gk_ov_subpage_paths_invalidate' );
add_action( 'created_gk_zuordnung', 'gk_ov_subpage_paths_invalidate' );
add_action( 'edited_gk_zuordnung', 'gk_ov_subpage_paths_invalidate' );
add_action( 'delete_gk_zuordnung', 'gk_ov_subpage_paths_invalidate' );
add_action( 'set_object_terms', 'gk_ov_subpage_paths_invalidate' );
add_action(
    'updated_term_meta',
    static function ( $meta_id, $object_id, $meta_key ) {
        if ( '_gk_homepage_id' === $meta_key ) {
            gk_ov_subpage_paths_invalidate();
        }
    },
    10,
    3
);

/**
 * Register the query variable for virtual OV subpages.
 *
 * @param array $vars Public query variables.
 * @return array
 */
function gk_ov_subpage_query_vars( $vars ) {
    $vars[] = 'gk_ov_page';
    return $vars;
}
add_filter( 'query_vars', 'gk_ov_subpage_query_vars' );

/**
 * Rewrite rules only for real OV homepage paths, so archives such as
 * /kategorie/termine/ are never shadowed. Rules are refreshed when the
 * set of homepage paths changes.
 */
function gk_ov_subpage_rewrite_rules() {
    $paths = gk_ov_subpage_paths();
    $types = implode( '|', array_keys( gk_ov_subpage_types() ) );
    foreach ( $paths as $path => $slug ) {
        add_rewrite_rule(
            '^' . preg_quote( $path, '#' ) . '/(' . $types . ')/?$',
            'index.php?gk_ov_page=$matches[1]&gk_ov_context=' . rawurlencode( $slug ),
            'top'
        );
    }
    $signature = md5( (string) wp_json_encode( $paths ) );
    if ( get_option( 'gk_ov_subpage_rules' ) !== $signature ) {
        update_option( 'gk_ov_subpage_rules', $signature, true );
        add_action(
            'wp_loaded',
            static function () {
                flush_rewrite_rules( false );
            }
        );
    }
}
add_action( 'init', 'gk_ov_subpage_rewrite_rules', 20 );

/**
 * Real pages win, and disabled or invalid routes fall back to normal resolution.
 *
 * @param array $vars Parsed request.
 * @return array
 */
function gk_ov_subpage_request( $vars ) {
    if ( empty( $vars['gk_ov_page'] ) || ! is_string( $vars['gk_ov_page'] ) ) {
        return $vars;
    }
    $type = sanitize_key( $vars['gk_ov_page'] );
    $term = isset( $vars['gk_ov_context'] ) && is_string( $vars['gk_ov_context'] ) ? gk_get_ov_term( sanitize_title( $vars['gk_ov_context'] ) ) : null;
    $home = $term ? gk_get_ov_public_homepage_id( $term->term_id ) : 0;
    if ( $home ) {
        $path = get_page_uri( $home ) . '/' . $type;
        $page = get_page_by_path( $path, OBJECT, 'page' );
        if ( $page && 'publish' === $page->post_status ) {
            return array( 'pagename' => $path );
        }
        if ( gk_ov_subpage_enabled( $term->term_id, $type ) ) {
            return $vars;
        }
        return array( 'pagename' => $path );
    }
    unset( $vars['gk_ov_page'] );
    return $vars;
}
add_filter( 'request', 'gk_ov_subpage_request' );

/**
 * Validated subpage context for the current request.
 *
 * @return array{term: WP_Term, page: string}|null
 */
function gk_ov_subpage_context() {
    $type = get_query_var( 'gk_ov_page' );
    $slug = get_query_var( 'gk_ov_context' );
    if ( ! is_string( $type ) || ! is_string( $slug ) || '' === $type || '' === $slug ) {
        return null;
    }
    $term = gk_get_ov_term( sanitize_title( $slug ) );
    if ( ! $term || ! gk_ov_subpage_enabled( $term->term_id, $type ) ) {
        return null;
    }
    return array(
		'term' => $term,
		'page' => $type,
	);
}

/**
 * Serve the subpage templates.
 *
 * @param string $template Default template.
 * @return string
 */
function gk_ov_subpage_template( $template ) {
    $context = gk_ov_subpage_context();
    if ( ! $context ) {
        return $template;
    }
    add_filter( 'body_class', 'gk_ov_body_class' );
    return GK_DIR . '/page-ov-' . $context['page'] . '.php';
}
add_filter( 'template_include', 'gk_ov_subpage_template', 99 );

/**
 * Valid subpages answer 200, everything else under the marker 404s.
 *
 * @param bool     $handled Existing decision.
 * @param WP_Query $query   Main query.
 * @return bool
 */
function gk_ov_subpage_status( $handled, $query ) {
    if ( ! $query->is_main_query() || ! $query->get( 'gk_ov_page' ) ) {
        return $handled;
    }
    if ( gk_ov_subpage_context() ) {
        status_header( 200 );
        return true;
    }
    $query->set_404();
    status_header( 404 );
    return true;
}
add_filter( 'pre_handle_404', 'gk_ov_subpage_status', 10, 2 );

/**
 * Keep virtual routes from being canonicalized to the KV homepage.
 *
 * @param string|false $redirect Proposed redirect.
 * @return string|false
 */
function gk_ov_subpage_canonical( $redirect ) {
    return get_query_var( 'gk_ov_page' ) ? false : $redirect;
}
add_filter( 'redirect_canonical', 'gk_ov_subpage_canonical' );

/**
 * Document title: "Termine – OV Name".
 *
 * @param array $parts Title parts.
 * @return array
 */
function gk_ov_subpage_title( $parts ) {
    $context = gk_ov_subpage_context();
    if ( $context ) {
        $config         = gk_ov_subpage_config( $context['term']->term_id );
        $parts['title'] = $config[ 'label_' . $context['page'] ] . ' – ' . $context['term']->name;
    }
    return $parts;
}
add_filter( 'document_title_parts', 'gk_ov_subpage_title', 99 );

/**
 * Old filter links /termine/?zuordnung={ov} move permanently to the OV page,
 * but only while that OV has its own Termine page.
 */
function gk_ov_subpage_legacy_redirect() {
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only redirect target lookup.
    $requested = isset( $_GET['zuordnung'] ) && is_string( $_GET['zuordnung'] ) ? sanitize_title( wp_unslash( $_GET['zuordnung'] ) ) : '';
    if ( '' === $requested || ! is_page() || 'page-termine.php' !== basename( (string) get_page_template() ) ) {
        return;
    }
    $term = gk_get_ov_term( $requested );
    $url  = $term ? gk_ov_subpage_url( $term->term_id, 'termine' ) : '';
    if ( $url ) {
        wp_safe_redirect( $url, 301 );
        exit;
    }
}
add_action( 'template_redirect', 'gk_ov_subpage_legacy_redirect' );

// ── Menus ────────────────────────────────────────────────────────────────────

/**
 * Menu entries an OV gets automatically, in display order.
 *
 * @param WP_Term $term OV term.
 * @return array[] Each: type, label, url, current.
 */
function gk_ov_subpage_menu_entries( $term ) {
    $context = gk_ov_subpage_context();
    $config  = gk_ov_subpage_config( $term->term_id );
    $entries = array();
    foreach ( array_keys( gk_ov_subpage_types() ) as $type ) {
        $url = gk_ov_subpage_url( $term->term_id, $type );
        if ( $url ) {
            $entries[] = array(
                'type'    => $type,
                'label'   => $config[ 'label_' . $type ],
                'url'     => $url,
                'current' => $context && $context['term']->term_id === $term->term_id && $context['page'] === $type,
            );
        }
    }
    return $entries;
}

/**
 * List items for the automatic entries.
 *
 * @param WP_Term $term OV term.
 * @return string
 */
function gk_ov_subpage_menu_html( $term ) {
    $html = '';
    foreach ( gk_ov_subpage_menu_entries( $term ) as $entry ) {
        $classes = 'menu-item page_item gk-ov-auto gk-ov-auto--' . $entry['type'] . ( $entry['current'] ? ' current-menu-item current_page_item' : '' );
        $html   .= '<li class="' . esc_attr( $classes ) . '"><a href="' . esc_url( $entry['url'] ) . '"' . ( $entry['current'] ? ' aria-current="page"' : '' ) . '>' . esc_html( $entry['label'] ) . '</a></li>';
    }
    return $html;
}

/**
 * Append the automatic entries to an OV header menu.
 *
 * @param string   $items Menu HTML.
 * @param stdClass $args  wp_nav_menu() arguments.
 * @return string
 */
function gk_ov_subpage_nav_items( $items, $args ) {
    $location = isset( $args->theme_location ) ? (string) $args->theme_location : '';
    if ( 0 !== strpos( $location, 'nav-' ) ) {
        return $items;
    }
    $term = gk_get_ov_term( substr( $location, 4 ) );
    return $term ? $items . gk_ov_subpage_menu_html( $term ) : $items;
}
add_filter( 'wp_nav_menu_items', 'gk_ov_subpage_nav_items', 10, 2 );

/**
 * OV term of the current request (route, archive or content), or null.
 *
 * @return WP_Term|null
 */
function gk_current_ov_term() {
    $context = gk_ov_subpage_context();
    if ( ! $context ) {
        $context = gk_ov_news_context();
    }
    if ( $context ) {
        return $context['term'];
    }
    $slug = gk_legal_context_slug();
    $term = 'kreisverband' !== $slug ? gk_get_ov_term( $slug ) : null;
    return $term ? $term : null;
}

/**
 * In an OV context, drop KV menu entries that the OV now provides itself.
 * Entries are recognized by their target page (the KV Termine page or a page
 * with the "Mach mit" template) or by the CSS class gk-kv-termine /
 * gk-kv-mitmachen set in Design > Menüs, never by their label.
 *
 * @param array    $items Menu item objects.
 * @param stdClass $args  wp_nav_menu() arguments.
 * @return array
 */
function gk_ov_subpage_dedupe_kv_menu( $items, $args ) {
    $location = isset( $args->theme_location ) ? (string) $args->theme_location : '';
    if ( ! in_array( $location, array( 'nav-main', 'nav-mobile' ), true ) ) {
        return $items;
    }
    $term = gk_current_ov_term();
    if ( ! $term ) {
        return $items;
    }
    $drop = array();
    foreach ( array_keys( gk_ov_subpage_types() ) as $type ) {
        if ( gk_ov_subpage_enabled( $term->term_id, $type ) ) {
            $drop[] = 'gk-kv-' . $type;
        }
    }
    if ( ! $drop ) {
        return $items;
    }
    $removed = array();
    foreach ( $items as $item ) {
        $classes = is_array( $item->classes ) ? $item->classes : array();
        $target  = gk_ov_subpage_kv_target( $item );
        if ( array_intersect( $drop, $classes ) || ( $target && in_array( 'gk-kv-' . $target, $drop, true ) ) || in_array( (int) $item->menu_item_parent, $removed, true ) ) {
            $removed[] = (int) $item->ID;
        }
    }
    return array_values( array_filter( $items, static fn( $item ) => ! in_array( (int) $item->ID, $removed, true ) ) );
}
add_filter( 'wp_nav_menu_objects', 'gk_ov_subpage_dedupe_kv_menu', 10, 2 );

/**
 * Which KV page a menu item points to: the Termine page (slug "termine",
 * served by page-termine.php) or a page using the "Mach mit" template.
 *
 * @param WP_Post $item Menu item.
 * @return string termine|mitmachen|''
 */
function gk_ov_subpage_kv_target( $item ) {
    if ( 'post_type' !== $item->type || 'page' !== $item->object ) {
        return '';
    }
    $page = get_post( (int) $item->object_id );
    if ( ! $page ) {
        return '';
    }
    if ( 'termine' === $page->post_name && ! $page->post_parent ) {
        return 'termine';
    }
    return 'page-templates/mach-mit.php' === get_page_template_slug( $page ) ? 'mitmachen' : '';
}

// ── REST ─────────────────────────────────────────────────────────────────────

/** Editorial REST access to the OV homepage settings, including the subpage switches. */
function gk_ov_subpage_rest_routes() {
    register_rest_route(
        'neurg/v1',
        '/ov-home/(?P<id>\d+)',
        array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => 'gk_ov_subpage_rest_get',
                'permission_callback' => 'gk_ov_subpage_rest_permission',
            ),
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => 'gk_ov_subpage_rest_update',
                'permission_callback' => 'gk_ov_subpage_rest_permission',
                'args'                => array(
                    'termine'         => array( 'type' => 'boolean' ),
                    'mitmachen'       => array( 'type' => 'boolean' ),
                    'label_termine'   => array( 'type' => 'string' ),
                    'label_mitmachen' => array( 'type' => 'string' ),
                    'mitmachen_text'  => array( 'type' => 'string' ),
                ),
            ),
        )
    );
}
add_action( 'rest_api_init', 'gk_ov_subpage_rest_routes' );

/**
 * Same object check as the admin editor.
 *
 * @param WP_REST_Request $request Request.
 * @return bool
 */
function gk_ov_subpage_rest_permission( $request ) {
    $term = gk_get_ov_term_by_id( (int) $request['id'] );
    return $term && gk_can_edit_ov_home( $term->term_id );
}

/**
 * OV term by ID, excluding the KV root term.
 *
 * @param int $term_id Term ID.
 * @return WP_Term|null
 */
function gk_get_ov_term_by_id( $term_id ) {
    $term = get_term( $term_id, 'gk_zuordnung' );
    return $term instanceof WP_Term && 'kreisverband' !== $term->slug ? $term : null;
}

/**
 * REST representation.
 *
 * @param WP_REST_Request $request Request.
 * @return array
 */
function gk_ov_subpage_rest_get( $request ) {
    $term_id = (int) $request['id'];
    $config  = gk_ov_home_config( $term_id );
    return array(
        'id'       => $term_id,
        'settings' => $config,
        'subpages' => array_merge(
            gk_ov_subpage_config( $term_id ),
            array(
                'urls' => array(
                    'termine'   => gk_ov_subpage_url( $term_id, 'termine' ),
                    'mitmachen' => gk_ov_subpage_url( $term_id, 'mitmachen' ),
                ),
            )
        ),
    );
}

/**
 * Update only the subpage fields; other settings stay untouched.
 *
 * @param WP_REST_Request $request Request.
 * @return array|WP_Error
 */
function gk_ov_subpage_rest_update( $request ) {
    $term_id = (int) $request['id'];
    $raw     = get_term_meta( $term_id, '_gk_home_editor', true );
    $raw     = is_array( $raw ) ? $raw : array();
    foreach ( array_keys( gk_ov_subpage_types() ) as $type ) {
        if ( null !== $request[ $type ] ) {
            $raw[ 'menu_' . $type ] = $request[ $type ] ? '1' : '0';
        }
        if ( null !== $request[ 'label_' . $type ] ) {
            $raw[ 'label_' . $type ] = sanitize_text_field( $request[ 'label_' . $type ] );
        }
    }
    if ( null !== $request['mitmachen_text'] ) {
        $raw['mitmachen_text'] = sanitize_textarea_field( $request['mitmachen_text'] );
    }
    update_term_meta( $term_id, '_gk_home_editor', $raw );
    return gk_ov_subpage_rest_get( $request );
}
