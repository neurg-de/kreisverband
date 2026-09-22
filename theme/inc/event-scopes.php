<?php
/**
 * Keep calendar-only assignments separate from full editorial OVs.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Check the explicitly saved usage mode, never infer it from a missing page.
 *
 * @param int $term_id Assignment ID.
 * @return bool
 */
function gk_is_event_only_term( $term_id ) {
    return '1' === get_term_meta( $term_id, '_gk_event_only', true );
}

/**
 * Filter assignment choices for a specific content type.
 *
 * @param WP_Term[] $terms Assignment terms.
 * @param string    $post_type Content type, or empty for general OV management.
 * @return WP_Term[]
 */
function gk_terms_for_content( $terms, $post_type = '' ) {
    return array_values( array_filter( $terms, static fn( $term ) => 'gk_event' === $post_type || ! gk_is_event_only_term( $term->term_id ) ) );
}

/**
 * Validate narrowing an existing OV before hiding its other editorial areas.
 *
 * @param int $term_id Assignment ID.
 * @return bool|WP_Error
 */
function gk_validate_event_only_term( $term_id ) {
    if ( ! $term_id ) {
        return true;
    }
    $term = get_term( $term_id, 'gk_zuordnung' );
    if ( ! $term || is_wp_error( $term ) || 'kreisverband' === $term->slug || gk_get_ov_homepage_id( $term_id ) ) {
        return new WP_Error( 'gk_event_only', 'Diese Zuordnung hat eine lokale Startseite oder gehört zum Kreisverband.' );
    }
    foreach ( get_objects_in_term( $term_id, 'gk_zuordnung' ) as $post_id ) {
        if ( 'gk_event' !== get_post_type( $post_id ) ) {
            return new WP_Error( 'gk_event_only', 'Diese Zuordnung enthält andere Inhalte. Vor „Nur Termine“ müssen diese ausdrücklich einem anderen Bereich zugeordnet werden.' );
        }
    }
    return true;
}

/**
 * REST assignment choices default to full editorial areas.
 *
 * @param array           $args Term query.
 * @param WP_REST_Request $request REST request.
 * @return array
 */
function gk_event_only_rest_terms( $args, $request ) {
    if ( 'gk_event' !== $request->get_param( 'gk_content_type' ) ) {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Explicit usage mode limits the assignment picker before pagination.
        $args['meta_query'] = array(
			'relation' => 'AND',
			$args['meta_query'] ?? array(),
			array(
				'relation' => 'OR',
				array(
					'key'     => '_gk_event_only',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_gk_event_only',
					'value'   => '1',
					'compare' => '!=',
				),
			),
		);
    }
    return $args;
}
add_filter( 'rest_gk_zuordnung_query', 'gk_event_only_rest_terms', 10, 2 );

/** Pass the event editor's context to its taxonomy picker, including preloads. */
function gk_event_assignment_editor_context() {
    $screen = get_current_screen();
    if ( $screen && 'gk_event' === $screen->post_type ) {
        wp_enqueue_script( 'wp-api-fetch' );
        wp_add_inline_script( 'wp-api-fetch', 'wp.apiFetch.use(function(options,next){if(options.path && /^\\/wp\\/v2\\/gk_zuordnung(?:\\?|$)/.test(options.path)){options=Object.assign({},options,{path:options.path+(options.path.includes("?")?"&":"?")+"gk_content_type=gk_event"});}return next(options);});', 'after' );
    }
}
add_action( 'enqueue_block_editor_assets', 'gk_event_assignment_editor_context' );

/**
 * Keep the core Quick Edit checklist consistent with the content editor.
 *
 * @param array $terms Checklist terms.
 * @param array $taxonomies Taxonomies requested.
 * @param array $args Query arguments.
 * @return array
 */
function gk_event_only_checklist( $terms, $taxonomies, $args ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( $screen && 'all' === ( $args['get'] ?? '' ) && array( 'gk_zuordnung' ) === $taxonomies && 'all' === ( $args['fields'] ?? 'all' ) ) {
        return gk_terms_for_content( $terms, $screen->post_type );
    }
    return $terms;
}
add_filter( 'get_terms', 'gk_event_only_checklist', 10, 3 );
