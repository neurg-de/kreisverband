<?php
/**
 * Per-OV homepage composition and public news archives.
 *
 * @package Neurg_Kreisverband
 */

/** Known section labels, in the legacy order. */
function gk_ov_section_labels() {
    return array(
        'hero'    => __( 'Titelbild', 'neurg-kreisverband' ),
        'content' => __( 'Seiteninhalt', 'neurg-kreisverband' ),
        'team'    => __( 'Unser Team', 'neurg-kreisverband' ),
        'news'    => __( 'Aktuelles', 'neurg-kreisverband' ),
        'events'  => __( 'Nächste Termine', 'neurg-kreisverband' ),
        'contact' => __( 'Kontakt', 'neurg-kreisverband' ),
        'engage'  => __( 'Werde aktiv', 'neurg-kreisverband' ),
    );
}

/**
 * Normalize a submitted order against a known whitelist, appending new keys.
 *
 * @param mixed $order Submitted keys.
 * @param array $allowed Known keys.
 * @return array
 */
function gk_ov_valid_order( $order, $allowed ) {
    $order = is_array( $order ) ? array_filter( $order, 'is_string' ) : array();
    return array_values( array_unique( array_merge( array_intersect( $order, $allowed ), $allowed ) ) );
}

/**
 * Read validated settings without a process-wide stale cache.
 *
 * @param int $term_id OV ID.
 * @return array
 */
function gk_ov_home_config( $term_id ) {
    $raw = get_term_meta( $term_id, '_gk_home_editor', true );
    $raw = is_array( $raw ) ? $raw : array();
    return array(
        'show_text'       => ! isset( $raw['show_text'] ) || '1' === $raw['show_text'],
        'show_label'      => ! isset( $raw['show_label'] ) || '1' === $raw['show_label'],
        'title'           => is_string( $raw['title'] ?? null ) ? $raw['title'] : '',
        'news_count'      => max( 3, min( 20, (int) ( $raw['news_count'] ?? 6 ) ) ),
        'archive'         => isset( $raw['archive'] ) && '1' === $raw['archive'],
        'excluded'        => gk_ov_owned_category_ids( $term_id, $raw['excluded'] ?? array() ),
        'team_order'      => is_array( $raw['team_order'] ?? null ) ? array_filter( $raw['team_order'], 'is_string' ) : array(),
        // Own subpages (inc/ov-subpages.php); off by default for compatibility.
        'menu_termine'    => isset( $raw['menu_termine'] ) && '1' === $raw['menu_termine'],
        'menu_mitmachen'  => isset( $raw['menu_mitmachen'] ) && '1' === $raw['menu_mitmachen'],
        'menu_first'      => isset( $raw['menu_first'] ) && '1' === $raw['menu_first'],
        'label_termine'   => is_string( $raw['label_termine'] ?? null ) ? $raw['label_termine'] : '',
        'label_mitmachen' => is_string( $raw['label_mitmachen'] ?? null ) ? $raw['label_mitmachen'] : '',
        'mitmachen_text'  => is_string( $raw['mitmachen_text'] ?? null ) ? $raw['mitmachen_text'] : '',
    );
}

/**
 * Resolve section visibility with legacy checkboxes as fallback.
 *
 * @param int $term_id OV ID.
 * @return array Key => visibility in configured order.
 */
function gk_ov_home_sections( $term_id ) {
    $raw = get_term_meta( $term_id, '_gk_home_sections', true );
    if ( is_array( $raw ) && isset( $raw['order'], $raw['visible'] ) ) {
        $order   = gk_ov_valid_order( $raw['order'], array_keys( gk_ov_section_labels() ) );
        $visible = is_array( $raw['visible'] ) ? $raw['visible'] : array();
        return array_combine( $order, array_map( static fn( $key ) => in_array( $key, $visible, true ), $order ) );
    }
    $legacy = array(
		'team'    => 'show_team',
		'news'    => 'show_aktuelles',
		'events'  => 'show_termine',
		'contact' => 'show_contact',
		'engage'  => 'show_engage',
	);
    $result = array();
    foreach ( gk_ov_section_labels() as $key => $label ) {
        $result[ $key ] = ! isset( $legacy[ $key ] ) || '1' === gk_get_ov_homepage_option( $term_id, $legacy[ $key ], '1' );
    }
    return $result;
}

/**
 * Available groups are those with published people in this OV.
 *
 * @param int $term_id OV ID.
 * @return array Slug => translated/stored name.
 */
function gk_ov_team_groups( $term_id ) {
    $ids    = get_posts(
        array(
            'post_type'      => 'person',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Exact OV constraint is required.
            'tax_query'      => array(
				array(
					'taxonomy' => 'gk_zuordnung',
					'terms'    => (int) $term_id,
				),
			),
        )
    );
    $terms  = $ids ? wp_get_object_terms( $ids, 'abteilung', array( 'orderby' => 'name' ) ) : array();
    $result = array();
    if ( ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            $result[ $term->slug ] = $term->name;
        }
    }
    return $result;
}

/**
 * Category IDs are usable only when explicitly owned by this OV.
 *
 * @param int   $term_id OV ID.
 * @param mixed $ids Candidate IDs.
 * @return int[]
 */
function gk_ov_owned_category_ids( $term_id, $ids ) {
    $ids = is_array( $ids ) ? array_map( 'absint', array_filter( $ids, 'is_scalar' ) ) : array();
    return array_values( array_unique( array_filter( $ids, static fn( $id ) => $id && get_term( $id, 'category' ) instanceof WP_Term && (int) get_term_meta( $id, '_gk_category_owner', true ) === (int) $term_id ) ) );
}

/**
 * Published OV news; the full archive retains excluded homepage rubrics.
 *
 * @param int  $term_id OV ID.
 * @param bool $homepage Whether exclusions apply.
 * @param int  $category Optional owned category.
 * @param int  $page Page number.
 * @return array
 */
function gk_ov_news_args( $term_id, $homepage = true, $category = 0, $page = 1 ) {
    $config = gk_ov_home_config( $term_id );
    $tax    = array(
		'relation' => 'AND',
		array(
			'taxonomy'         => 'gk_zuordnung',
			'terms'            => (int) $term_id,
			'include_children' => false,
		),
	);
    if ( $category ) {
        $tax[] = array(
			'taxonomy'         => 'category',
			'terms'            => (int) $category,
			'include_children' => false,
		);
    } elseif ( $homepage && $config['excluded'] ) {
        $tax[] = array(
			'taxonomy'         => 'category',
			'terms'            => $config['excluded'],
			'operator'         => 'NOT IN',
			'include_children' => false,
		);
    }
    return array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'has_password'        => false,
        'posts_per_page'      => $config['news_count'],
        'paged'               => max( 1, (int) $page ),
        'ignore_sticky_posts' => true,
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Exact OV/category boundaries are required.
        'tax_query'           => $tax,
    );
}

/**
 * Stable URLs also work without pretty permalinks.
 *
 * @param int $term_id OV ID.
 * @param int $category Optional category ID.
 * @return string
 */
function gk_ov_news_url( $term_id, $category = 0 ) {
    $term = get_term( $term_id, 'gk_zuordnung' );
    if ( ! $term instanceof WP_Term || ! gk_get_ov_public_homepage_id( $term_id ) ) {
        return '';
    }
    $cat = $category ? get_term( $category, 'category' ) : null;
    if ( $category && ( ! $cat instanceof WP_Term || ! gk_ov_owned_category_ids( $term_id, array( $category ) ) ) ) {
        return '';
    }
    // Readable rubric URL with pretty permalinks; query URLs keep working.
    if ( $cat && get_option( 'permalink_structure' ) && function_exists( 'gk_ov_rubric_url_slug' ) ) {
        return home_url( user_trailingslashit( get_page_uri( gk_get_ov_public_homepage_id( $term_id ) ) . '/rubrik/' . gk_ov_rubric_url_slug( $term, $cat ) ) );
    }
    $vars = array(
		'gk_ov_news'    => '1',
		'gk_ov_context' => $term->slug,
	);
    if ( $cat ) {
        $vars['gk_ov_rubric'] = $cat->slug;
    }
    return add_query_arg( $vars, home_url( '/' ) );
}

/**
 * Register archive variables without shadowing any existing page paths.
 *
 * @param array $vars Public variables.
 * @return array
 */
function gk_ov_news_vars( $vars ) {
    return array_merge( $vars, array( 'gk_ov_news', 'gk_ov_rubric' ) );
}
add_filter( 'query_vars', 'gk_ov_news_vars' );


/**
 * Validated archive context. Invalid OV/category combinations fail closed.
 *
 * @return array|null
 */
function gk_ov_news_context() {
    $marker       = get_query_var( 'gk_ov_news' );
    $context_slug = get_query_var( 'gk_ov_context', '' );
    $rubric_slug  = get_query_var( 'gk_ov_rubric', '' );
    if ( ! is_scalar( $marker ) || '1' !== (string) $marker || ! is_string( $context_slug ) || ! is_string( $rubric_slug ) ) {
        return null;
    }
    $term = gk_get_ov_term( sanitize_title( $context_slug ) );
    if ( ! $term || ! gk_get_ov_public_homepage_id( $term->term_id ) ) {
        return null;
    }
    $slug = sanitize_title( $rubric_slug );
    $cat  = $slug ? get_term_by( 'slug', $slug, 'category' ) : null;
    if ( $slug && ( ! $cat || ! gk_ov_owned_category_ids( $term->term_id, array( $cat->term_id ) ) ) ) {
        return null;
    }
    return array(
		'term'     => $term,
		'category' => $cat,
	);
}

/**
 * Apply scope to the main query, preserving a real paginated archive.
 *
 * @param WP_Query $query Main query.
 */
function gk_ov_news_query( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->get( 'gk_ov_news' ) ) {
        return;
    }
    $context = gk_ov_news_context();
    if ( ! $context ) {
        $query->parse_query(
            array(
				'post_type'     => 'post',
				'post__in'      => array( 0 ),
				'gk_ov_news'    => '1',
				'gk_ov_context' => '',
            )
        );
        return;
    }
    $args = gk_ov_news_args( $context['term']->term_id, false, $context['category'] ? $context['category']->term_id : 0, $query->get( 'paged' ) );
    // Reparse an allowlisted query: singular selectors would bypass WP's tax SQL.
    $args['gk_ov_news']    = '1';
    $args['gk_ov_context'] = $context['term']->slug;
    $args['gk_ov_rubric']  = $context['category'] ? $context['category']->slug : '';
    $query->parse_query( $args );
    $query->is_home    = false;
    $query->is_archive = true;
}
add_action( 'pre_get_posts', 'gk_ov_news_query', 50 );

/**
 * Use the shared card-list archive only for a validated route.
 *
 * @param string $template Default template.
 * @return string
 */
function gk_ov_news_template( $template ) {
    return gk_ov_news_context() && ! is_404() ? GK_DIR . '/page-ov-news.php' : $template;
}
add_filter( 'template_include', 'gk_ov_news_template', 99 );

/**
 * Empty valid archives are 200; nonexistent pages/combinations are 404.
 *
 * @param bool     $handled Existing decision.
 * @param WP_Query $query Main query.
 * @return bool
 */
function gk_ov_news_status( $handled, $query ) {
    if ( ! $query->get( 'gk_ov_news' ) ) {
        return $handled;
    }
    if ( ! gk_ov_news_context() || ( (int) $query->get( 'paged' ) > 1 && ! $query->post_count ) ) {
        $query->set_404();
        status_header( 404 );
    } else {
        status_header( 200 );
    }
    return true;
}
add_filter( 'pre_handle_404', 'gk_ov_news_status', 10, 2 );

/**
 * Custom archives must not redirect to the KV homepage.
 *
 * @param string|false $redirect Proposed redirect.
 * @return string|false
 */
function gk_ov_news_redirect( $redirect ) {
    return get_query_var( 'gk_ov_news' ) ? false : $redirect;
}
add_filter( 'redirect_canonical', 'gk_ov_news_redirect' );

/**
 * Archive titles include the OV even when no posts exist.
 *
 * @param array $parts Document title parts.
 * @return array
 */
function gk_ov_news_title( $parts ) {
    $context = gk_ov_news_context();
    if ( $context ) {
        $parts['title'] = ( $context['category'] ? $context['category']->name : __( 'Aktuelles', 'neurg-kreisverband' ) ) . ' – ' . $context['term']->name;
    }
    return $parts;
}
add_filter( 'document_title_parts', 'gk_ov_news_title', 99 );
