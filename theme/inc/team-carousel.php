<?php
/**
 * Shared, taxonomy-driven team carousel for pages and OV homepages.
 *
 * @package Neurg_Kreisverband
 */

/**
 * Render published people grouped by department, without limiting membership.
 *
 * @param array $args Department slugs, optional scope, and heading.
 * @return string Team markup, or an empty string when no people match.
 */
function gk_render_team_carousel( $args = array() ) {
    $args      = wp_parse_args(
        $args,
        array(
			'abteilungen' => '',
			'zuordnung'   => '',
			'title'       => 'Unser Team',
            'group_order' => array(),
        )
    );
    $slugs     = array_values( array_filter( array_map( 'sanitize_title', explode( ',', $args['abteilungen'] ) ) ) );
    $tax_query = array( 'relation' => 'AND' );
    if ( $slugs ) {
        $tax_query[] = array(
			'taxonomy' => 'abteilung',
			'field'    => 'slug',
			'terms'    => $slugs,
		);
    }
    if ( $args['zuordnung'] ) {
        $tax_query[] = array(
			'taxonomy' => 'gk_zuordnung',
			'field'    => 'slug',
			'terms'    => sanitize_title( $args['zuordnung'] ),
		);
    }
    $query_args = array(
        'post_type'      => 'person',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => true,
        'orderby'        => 'title',
        'order'          => 'ASC',
    );
    if ( count( $tax_query ) > 1 ) {
        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Department and OV constraints are essential to this view.
        $query_args['tax_query'] = $tax_query;
    }
    $query  = new WP_Query( $query_args );
    $groups = array();
    foreach ( $query->posts as $person ) {
        $terms = get_the_terms( $person->ID, 'abteilung' );
        if ( ! $terms || is_wp_error( $terms ) ) {
            continue;
        }
        foreach ( $terms as $term ) {
            if ( $slugs && ! in_array( $term->slug, $slugs, true ) ) {
                continue;
            }
            $meta                              = gk_get_abteilung_meta_for( $person->ID, $term->slug );
            $groups[ $term->slug ]['label']    = $term->name;
            $groups[ $term->slug ]['people'][] = array(
                'id'       => $person->ID,
                'name'     => get_the_title( $person ),
                'role'     => $meta['function'],
                'position' => $meta['position'],
            );
        }
    }
    if ( ! $groups ) {
        return '';
    }
    if ( $slugs ) {
        $groups = array_replace( array_intersect_key( array_flip( $slugs ), $groups ), $groups );
    } else {
        uasort( $groups, static fn( $a, $b ) => strnatcasecmp( $a['label'], $b['label'] ) );
    }
    if ( ! empty( $args['group_order'] ) ) {
        $order  = gk_ov_valid_order( $args['group_order'], array_keys( $groups ) );
        $groups = array_replace( array_flip( $order ), $groups );
    }
    foreach ( $groups as &$group ) {
        usort(
            $group['people'],
            static function ( $a, $b ) {
                $a_pos = '' === $a['position'] ? PHP_INT_MAX : (int) $a['position'];
                $b_pos = '' === $b['position'] ? PHP_INT_MAX : (int) $b['position'];
                return ( $a_pos <=> $b_pos ) ? ( $a_pos <=> $b_pos ) : strnatcasecmp( $a['name'], $b['name'] );
            }
        );
    }
    unset( $group );

    wp_enqueue_script( 'gk-team-carousel', GK_URI . '/lib/js/team-carousel.js', array(), GK_VERSION, true );
    ob_start();
    get_template_part(
        'template-parts/team-carousel',
        null,
        array(
			'groups' => $groups,
			'title'  => $args['title'],
			'id'     => wp_unique_id( 'gk-team-' ),
        )
    );
    return ob_get_clean();
}

/**
 * Embed a carousel; an enclosing OV page always supplies its own scope.
 *
 * @param array $atts Shortcode attributes.
 * @return string Rendered team.
 */
function gk_shortcode_team_carousel( $atts ) {
    $atts    = shortcode_atts(
        array(
			'abteilungen' => '',
			'zuordnung'   => '',
			'title'       => 'Unser Team',
        ),
        $atts,
        'team_carousel'
    );
    $context = gk_embedded_ov_context();
    if ( $context ) {
        $atts['zuordnung'] = $context;
    }
    return gk_render_team_carousel( $atts );
}
add_shortcode( 'team_carousel', 'gk_shortcode_team_carousel' );

/**
 * Initials for a neutral photo placeholder ("Anna Beispiel" → "AB").
 *
 * @param string $name Display name.
 * @return string
 */
function gk_person_initials( $name ) {
    $words = preg_split( '/\s+/u', trim( wp_strip_all_tags( (string) $name ) ), -1, PREG_SPLIT_NO_EMPTY );
    // Skip titles ("Dr.") and lowercase particles ("von", "de") when real name parts exist.
    $names    = array_values( array_filter( $words, static fn( $word ) => (bool) preg_match( '/^\p{Lu}/u', $word ) && '.' !== mb_substr( $word, -1 ) ) );
    $words    = $names ? $names : array_values( array_filter( $words, static fn( $word ) => (bool) preg_match( '/^\p{L}/u', $word ) && '.' !== mb_substr( $word, -1 ) ) );
    $initials = '';
    if ( $words ) {
        $initials .= mb_substr( $words[0], 0, 1 );
        if ( count( $words ) > 1 ) {
            $initials .= mb_substr( end( $words ), 0, 1 );
        }
    }
    return mb_strtoupper( $initials );
}
