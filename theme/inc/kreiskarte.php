<?php
/**
 * Kreiskarte (District Map) Component
 *
 * Renders an interactive SVG map of Landkreis Starnberg.
 * Geographic polygon data is stored in lib/data/kreiskarte.json.
 * Links are resolved dynamically from gk_zuordnung taxonomy terms.
 *
 * Usage:
 *   [kreiskarte]                    — full map with all OVs
 *   gk_render_kreiskarte()          — in templates
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ── Polylabel: Visual Center of Polygon ─────────────────────────────────────

/**
 * Parse SVG polygon points string into array of [x, y] pairs.
 */
function gk_parse_polygon_points( $points_str ) {
    $nums   = preg_split( '/\s+/', trim( $points_str ) );
    $coords = array();
    for ( $i = 0; $i < count( $nums ) - 1; $i += 2 ) {
        $coords[] = array( (float) $nums[ $i ], (float) $nums[ $i + 1 ] );
    }
    return $coords;
}

/**
 * Signed distance from a point to a polygon boundary.
 * Positive = inside, negative = outside.
 */
function gk_point_to_polygon_dist( $px, $py, $polygon ) {
    $inside   = false;
    $min_dist = PHP_FLOAT_MAX;
    $n        = count( $polygon );

    for ( $i = 0, $j = $n - 1; $i < $n; $j = $i++ ) {
        $ax = $polygon[ $i ][0];
        $ay = $polygon[ $i ][1];
        $bx = $polygon[ $j ][0];
        $by = $polygon[ $j ][1];

        if ( ( $ay > $py ) !== ( $by > $py ) &&
             $px < ( $bx - $ax ) * ( $py - $ay ) / ( $by - $ay ) + $ax ) {
            $inside = ! $inside;
        }

        $dx  = $bx - $ax;
        $dy  = $by - $ay;
        $len = $dx * $dx + $dy * $dy;
        $t   = $len > 0 ? max( 0, min( 1, ( ( $px - $ax ) * $dx + ( $py - $ay ) * $dy ) / $len ) ) : 0;
        $cx  = $ax + $t * $dx;
        $cy  = $ay + $t * $dy;
        $d   = sqrt( ( $px - $cx ) * ( $px - $cx ) + ( $py - $cy ) * ( $py - $cy ) );

        if ( $d < $min_dist ) {
            $min_dist = $d;
        }
    }

    return $inside ? $min_dist : -$min_dist;
}

/**
 * Compute the centroid of a polygon (area-weighted).
 */
function gk_polygon_centroid( $polygon ) {
    $area = 0;
    $cx   = 0;
    $cy   = 0;
    $n    = count( $polygon );

    for ( $i = 0, $j = $n - 1; $i < $n; $j = $i++ ) {
        $a  = $polygon[ $i ];
        $b  = $polygon[ $j ];
        $f  = $a[0] * $b[1] - $b[0] * $a[1];
        $cx += ( $a[0] + $b[0] ) * $f;
        $cy += ( $a[1] + $b[1] ) * $f;
        $area += $f;
    }

    $area *= 3;
    if ( abs( $area ) < 1e-10 ) {
        // Degenerate polygon: return average of points.
        $sx = $sy = 0;
        foreach ( $polygon as $p ) { $sx += $p[0]; $sy += $p[1]; }
        return array( $sx / $n, $sy / $n );
    }

    return array( $cx / $area, $cy / $area );
}

/**
 * Polylabel: find the visual center (pole of inaccessibility) of a polygon.
 *
 * Returns [ x, y, distance ] where distance is the radius of the largest
 * inscribed circle. Based on Mapbox's polylabel algorithm.
 *
 * @param array $polygon Array of [x, y] coordinate pairs.
 * @param float $precision Convergence precision in SVG units.
 * @return array [ x, y, distance ]
 */
function gk_polylabel( $polygon, $precision = 1.0 ) {
    $min_x = $min_y = PHP_FLOAT_MAX;
    $max_x = $max_y = -PHP_FLOAT_MAX;

    foreach ( $polygon as $p ) {
        $min_x = min( $min_x, $p[0] );
        $min_y = min( $min_y, $p[1] );
        $max_x = max( $max_x, $p[0] );
        $max_y = max( $max_y, $p[1] );
    }

    $width  = $max_x - $min_x;
    $height = $max_y - $min_y;
    $cell_size = max( $width, $height );

    if ( $cell_size < $precision ) {
        return array( $min_x, $min_y, 0 );
    }

    $h = $cell_size / 2;

    // Priority queue: max-heap by maximum possible distance.
    $queue = new SplPriorityQueue();

    for ( $x = $min_x; $x < $max_x; $x += $cell_size ) {
        for ( $y = $min_y; $y < $max_y; $y += $cell_size ) {
            $cx = $x + $h;
            $cy = $y + $h;
            $d  = gk_point_to_polygon_dist( $cx, $cy, $polygon );
            $queue->insert( array( $cx, $cy, $h, $d ), $d + $h * M_SQRT2 );
        }
    }

    // Start with the centroid as initial best guess.
    $centroid = gk_polygon_centroid( $polygon );
    $best_x   = $centroid[0];
    $best_y   = $centroid[1];
    $best_d   = gk_point_to_polygon_dist( $best_x, $best_y, $polygon );

    while ( ! $queue->isEmpty() ) {
        $cell = $queue->extract();
        list( $cx, $cy, $ch, $cd ) = $cell;

        if ( $cd > $best_d ) {
            $best_x = $cx;
            $best_y = $cy;
            $best_d = $cd;
        }

        // Max possible distance for this cell.
        if ( $cd + $ch * M_SQRT2 - $best_d <= $precision ) {
            continue;
        }

        $nh = $ch / 2;
        foreach ( array(
            array( $cx - $nh, $cy - $nh ),
            array( $cx + $nh, $cy - $nh ),
            array( $cx - $nh, $cy + $nh ),
            array( $cx + $nh, $cy + $nh ),
        ) as $sub ) {
            $d = gk_point_to_polygon_dist( $sub[0], $sub[1], $polygon );
            $queue->insert( array( $sub[0], $sub[1], $nh, $d ), $d + $nh * M_SQRT2 );
        }
    }

    return array( $best_x, $best_y, $best_d );
}

/**
 * Get polylabel results for all municipalities, cached per request.
 */
function gk_get_municipality_labels( $data ) {
    static $cache = null;
    if ( $cache !== null ) {
        return $cache;
    }

    $cache = array();
    foreach ( $data['municipalities'] as $slug => $muni ) {
        $polygon = gk_parse_polygon_points( $muni['polygon'] );
        $result  = gk_polylabel( $polygon, 1.0 );

        $name   = $muni['name'];
        $radius = $result[2];

        // Size category based on inscribed circle radius.
        if ( $radius >= 38 ) {
            $size = 'lg';
        } elseif ( $radius >= 22 ) {
            $size = 'md';
        } else {
            $size = 'sm';
        }

        // Word-wrap: split long names at the space nearest the midpoint.
        $lines = array( $name );
        $needs_wrap = ( $size === 'sm' && mb_strlen( $name ) > 8 )
                   || ( $size === 'md' && mb_strlen( $name ) > 14 )
                   || mb_strlen( $name ) > 18;

        if ( $needs_wrap && strpos( $name, ' ' ) !== false ) {
            $mid    = mb_strlen( $name ) / 2;
            $words  = explode( ' ', $name );
            $best   = PHP_INT_MAX;
            $best_i = 0;
            $pos    = 0;
            for ( $i = 0; $i < count( $words ) - 1; $i++ ) {
                $pos += mb_strlen( $words[ $i ] ) + 1;
                $diff = abs( $pos - $mid );
                if ( $diff < $best ) {
                    $best   = $diff;
                    $best_i = $i;
                }
            }
            $line1 = implode( ' ', array_slice( $words, 0, $best_i + 1 ) );
            $line2 = implode( ' ', array_slice( $words, $best_i + 1 ) );
            $lines = array( $line1, $line2 );
        }

        $cache[ $slug ] = array(
            'x'      => round( $result[0], 1 ),
            'y'      => round( $result[1], 1 ),
            'radius' => round( $radius, 1 ),
            'size'   => $size,
            'lines'  => $lines,
        );
    }

    return $cache;
}


// ── Data Loading ────────────────────────────────────────────────────────────

/**
 * Check if kreiskarte data exists.
 */
function gk_has_kreiskarte_data() {
    return file_exists( GK_DIR . '/lib/data/kreiskarte.json' );
}

/**
 * Load the geographic data from the JSON config.
 */
function gk_get_kreiskarte_data() {
    static $data = null;
    if ( $data === null ) {
        if ( ! gk_has_kreiskarte_data() ) {
            return null;
        }
        $json = file_get_contents( GK_DIR . '/lib/data/kreiskarte.json' );
        $data = json_decode( $json, true );
    }
    return $data;
}

/**
 * Get all OV zuordnung terms keyed by slug, with homepage data.
 *
 * Returns array of slug => object{ term, homepage_id, homepage_url, type }
 */
function gk_get_ov_terms_by_slug() {
    static $terms = null;
    if ( $terms !== null ) {
        return $terms;
    }

    $terms    = array();
    $ov_terms = gk_get_ov_terms();

    foreach ( $ov_terms as $term ) {
        $homepage_id  = gk_get_ov_homepage_id( $term->term_id );
        $homepage_url = $homepage_id ? get_permalink( $homepage_id ) : '';

        $terms[ $term->slug ] = (object) array(
            'term'         => $term,
            'name'         => $term->name,
            'homepage_id'  => $homepage_id,
            'homepage_url' => $homepage_url,
            'type'         => gk_get_ov_type( $term->term_id ),
        );
    }

    return $terms;
}

// Backwards compatibility alias.
function gk_get_ov_posts_by_slug() {
    return gk_get_ov_terms_by_slug();
}

/**
 * Render a simple list of all Ortsverbände (fallback when no map data exists).
 */
function gk_render_ov_list( $args = array() ) {
    $defaults = array(
        'class' => 'kreiskarte-list',
    );
    $args = wp_parse_args( $args, $defaults );

    $ov_data = gk_get_ov_terms_by_slug();

    if ( empty( $ov_data ) ) {
        return '<p class="kreiskarte-empty">Noch keine Ortsverb&auml;nde angelegt.</p>';
    }

    // Sort alphabetically by name.
    uasort( $ov_data, function ( $a, $b ) {
        return strcasecmp( $a->name, $b->name );
    } );

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $args['class'] ); ?>">
        <ul class="ov-list">
            <?php foreach ( $ov_data as $slug => $ov ) :
                $link  = $ov->homepage_url;
                $thumb = $ov->homepage_id ? get_the_post_thumbnail( $ov->homepage_id, 'thumbnail' ) : '';
            ?>
            <li class="ov-list-item">
                <?php if ( $link ) : ?>
                <a href="<?php echo esc_url( $link ); ?>">
                    <?php if ( $thumb ) : ?>
                        <span class="ov-list-thumb"><?php echo $thumb; ?></span>
                    <?php endif; ?>
                    <span class="ov-list-name"><?php echo esc_html( $ov->name ); ?></span>
                </a>
                <?php else : ?>
                    <span class="ov-list-name"><?php echo esc_html( $ov->name ); ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render the interactive SVG map.
 *
 * Each municipality polygon links to its Ortsverband page if one exists.
 * Municipalities without a zuordnung term or homepage are shown but not clickable.
 *
 * Falls back to a simple list if no map data is available.
 */
function gk_render_kreiskarte( $args = array() ) {
    $defaults = array(
        'class'     => 'kreiskarte',
        'max_width' => '700px',
    );
    $args = wp_parse_args( $args, $defaults );

    $data = gk_get_kreiskarte_data();

    // Fallback: no map data → render list.
    if ( ! $data || empty( $data['municipalities'] ) ) {
        return gk_render_ov_list( $args );
    }

    $ov_data   = gk_get_ov_terms_by_slug();
    $viewBox   = $data['_meta']['viewBox'];
    $labels    = gk_get_municipality_labels( $data );

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $args['class'] ); ?>" style="max-width: <?php echo esc_attr( $args['max_width'] ); ?>; margin: 0 auto;">
        <svg id="kreiskarte-svg" xmlns="http://www.w3.org/2000/svg"
             version="1.1" viewBox="<?php echo esc_attr( $viewBox ); ?>" role="img"
             aria-labelledby="kreiskarte-title kreiskarte-desc">
            <title id="kreiskarte-title"><?php echo esc_html( $data['_meta']['title'] ?? 'Kreiskarte' ); ?> – Gemeindekarte</title>
            <desc id="kreiskarte-desc">Interaktive Karte der Ortsverbände (<?php echo count( $data['municipalities'] ); ?> Gemeinden).</desc>

            <style>
                /*
                 * Map tokens — built from Grüne design system
                 * (Klee, Tanne, Sand, Himmel scales)
                 *
                 * WCAG AA contrast (normal text >= 4.5 : 1):
                 *   Label on OV:       #002216 / #CCE7D7  = 14 : 1
                 *   Label on OG:       #002216 / #E5F3EB  = 16 : 1
                 *   Label on Werbung:  #002216 / #FAF8F4  = 18 : 1
                 *   Label on Link:     #002216 / #fff     = 19 : 1
                 *   Hover label:       #fff    / #005538  =  9 : 1
                 *   Dark label on OV:  #F7F4ED / #1a3d30  = 10 : 1
                 *   Dark hover label:  #fff    / #008939  =  5 : 1
                 */
                :root {
                    /* Borders & interactive */
                    --kk-stroke:       var(--tanne-600, #005538);
                    --kk-hover:        var(--tanne-600, #005538);
                    --kk-focus:        #fff;

                    /* Polygon fills — green = established, neutral = not */
                    --kk-fill-ov:      var(--klee-100, #CCE7D7);
                    --kk-fill-og:      var(--klee-50,  #E5F3EB);
                    --kk-fill-werbung: var(--sand-100, #FAF8F4);
                    --kk-fill-link:    #fff;
                    --kk-fill-keine:   var(--gray-200, #E8EAEA);

                    /* District outline */
                    --kk-district:     var(--tanne-600, #005538);

                    /* Water */
                    --kk-lake:         var(--himmel-500, #3CB4E4);
                    --kk-lake-stroke:  var(--himmel-700, #0981B1);

                    /* Labels */
                    --kk-label:        var(--tanne-900, #002216);
                    --kk-label-hover:  #fff;
                }

                /* ── District outline ──────────────────────── */
                .district-shadow { fill: var(--kk-district); opacity: .12; }
                .district-fill   { fill: var(--kk-district); }

                /* ── Polygons ──────────────────────────────── */
                #kreiskarte-municipalities polygon {
                    fill: var(--kk-fill-ov);
                    stroke: var(--kk-stroke);
                    stroke-width: 1;
                    stroke-linecap: round;
                    stroke-linejoin: round;
                    vector-effect: non-scaling-stroke;
                    transition: fill .2s ease;
                    cursor: pointer;
                }
                #kreiskarte-municipalities a:hover polygon,
                #kreiskarte-municipalities a:focus polygon {
                    fill: var(--kk-hover);
                }
                #kreiskarte-municipalities a:focus-visible polygon {
                    stroke: var(--kk-focus);
                    stroke-width: 3;
                }

                /* Type variants */
                #kreiskarte-municipalities .ov-ortsgruppe polygon {
                    fill: var(--kk-fill-og);
                }
                #kreiskarte-municipalities .ov-werbung polygon {
                    fill: var(--kk-fill-werbung);
                    stroke-dasharray: 4 2;
                }
                #kreiskarte-municipalities .ov-link polygon {
                    fill: var(--kk-fill-link);
                }
                #kreiskarte-municipalities .ov-inactive polygon {
                    fill: var(--kk-fill-keine);
                    cursor: default;
                    opacity: .6;
                }

                /* ── Water ─────────────────────────────────── */
                #kreiskarte-water path {
                    fill: var(--kk-lake);
                    stroke: var(--kk-lake-stroke);
                    opacity: .7;
                }

                /* ── Labels ────────────────────────────────── */
                #kreiskarte-labels text {
                    font-family: 'PT Sans', sans-serif;
                    font-weight: 700;
                    fill: var(--kk-label);
                    text-anchor: middle;
                    dominant-baseline: central;
                    pointer-events: none;
                    transition: fill .2s, opacity .2s;
                }
                #kreiskarte-labels .kk-label--lg { font-size: 12px; }
                #kreiskarte-labels .kk-label--md { font-size: 10px; }
                #kreiskarte-labels .kk-label--sm { font-size: 8.5px; }
                #kreiskarte-labels .kk-label--hover { fill: var(--kk-label-hover); }

                /* ── Dark mode ─────────────────────────────── */
                @media (prefers-color-scheme: dark) {
                    :root {
                        --kk-stroke:       var(--klee-400,  #66B888);
                        --kk-hover:        var(--klee-600,  #008939);
                        --kk-focus:        var(--sonne-600, #FFF17A);

                        --kk-fill-ov:      #1a3d30;
                        --kk-fill-og:      #15332a;
                        --kk-fill-werbung: #112a22;
                        --kk-fill-link:    #183530;
                        --kk-fill-keine:   #0f2920;

                        --kk-district:     #0a1f18;

                        --kk-lake:         var(--himmel-700, #0981B1);
                        --kk-lake-stroke:  #066a90;

                        --kk-label:        var(--sand-200, #F7F4ED);
                        --kk-label-hover:  #fff;
                    }
                }
            </style>

            <!-- District outline -->
            <g id="kreiskarte-districts">
                <path class="district-shadow" d="<?php echo esc_attr( $data['district']['shadow'] ); ?>"/>
                <path class="district-fill" d="<?php echo esc_attr( $data['district']['fill'] ); ?>"/>
            </g>

            <!-- Municipality polygons -->
            <g id="kreiskarte-municipalities">
                <?php foreach ( $data['municipalities'] as $slug => $muni ) :
                    $type     = $muni['type'] ?? 'ov';

                    if ( $type === 'keine' ) : ?>
                    <g class="ov-inactive" aria-label="<?php echo esc_attr( $muni['name'] ); ?>">
                        <polygon points="<?php echo esc_attr( $muni['polygon'] ); ?>"/>
                        <title><?php echo esc_html( $muni['name'] ); ?></title>
                    </g>
                    <?php continue; endif;

                    // Resolve link: 'link' type uses stored URL, WP types resolve from term/page.
                    if ( $type === 'link' ) {
                        $link = ! empty( $muni['link'] ) ? $muni['link'] : '';
                    } else {
                        $ov_entry = isset( $ov_data[ $slug ] ) ? $ov_data[ $slug ] : null;
                        $link     = $ov_entry && $ov_entry->homepage_url ? $ov_entry->homepage_url : '';
                    }
                    $type_class = 'ov-' . sanitize_html_class( $type );
                ?>
                    <?php if ( $link ) : ?>
                    <a href="<?php echo esc_url( $link ); ?>" class="<?php echo $type_class; ?>" aria-label="<?php echo esc_attr( $muni['name'] ); ?>" data-ov-name="<?php echo esc_attr( $muni['name'] ); ?>" data-ov-url="<?php echo esc_url( $link ); ?>" data-ov-slug="<?php echo esc_attr( $slug ); ?>" tabindex="0"<?php if ( $type === 'link' ) echo ' target="_blank" rel="noopener noreferrer"'; ?>>
                    <?php else : ?>
                    <g class="ov-inactive <?php echo $type_class; ?>" aria-label="<?php echo esc_attr( $muni['name'] ); ?>">
                    <?php endif; ?>
                        <polygon points="<?php echo esc_attr( $muni['polygon'] ); ?>"/>
                        <title><?php echo esc_html( $muni['name'] ); ?></title>
                    <?php if ( $link ) : ?>
                    </a>
                    <?php else : ?>
                    </g>
                    <?php endif; ?>
                <?php endforeach; ?>
            </g>

            <!-- Water bodies -->
            <g id="kreiskarte-water">
                <?php foreach ( $data['water'] as $id => $path ) : ?>
                <path id="<?php echo esc_attr( $id ); ?>" d="<?php echo esc_attr( $path ); ?>" stroke-miterlimit="10"/>
                <?php endforeach; ?>
            </g>

            <!-- Labels: auto-centered via polylabel algorithm -->
            <g id="kreiskarte-labels">
                <?php foreach ( $data['municipalities'] as $slug => $muni ) :
                    $label = $labels[ $slug ] ?? null;
                    if ( ! $label ) continue;
                    $line_count = count( $label['lines'] );
                    // Shift multi-line labels up by half the total height.
                    $dy_start = $line_count > 1 ? -0.6 : 0;
                ?>
                <text class="kk-label--<?php echo esc_attr( $label['size'] ); ?>"
                      x="<?php echo esc_attr( $label['x'] ); ?>"
                      y="<?php echo esc_attr( $label['y'] ); ?>"
                      data-slug="<?php echo esc_attr( $slug ); ?>"
                      style="--kk-r: <?php echo esc_attr( $label['radius'] ); ?>">
                    <?php if ( $line_count === 1 ) : ?>
                    <tspan><?php echo esc_html( $label['lines'][0] ); ?></tspan>
                    <?php else : ?>
                    <tspan x="<?php echo esc_attr( $label['x'] ); ?>" dy="<?php echo $dy_start; ?>em"><?php echo esc_html( $label['lines'][0] ); ?></tspan>
                    <tspan x="<?php echo esc_attr( $label['x'] ); ?>" dy="1.2em"><?php echo esc_html( $label['lines'][1] ); ?></tspan>
                    <?php endif; ?>
                </text>
                <?php endforeach; ?>
            </g>

        </svg>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render the responsive Kreiskarte component.
 *
 * Desktop: SVG map.
 * Mobile:  Searchable list by default, with a toggle to show the map.
 *          Tapping a region on the mobile map opens a bottom sheet
 *          instead of navigating directly (compensates for imprecise taps).
 */
function gk_render_kreiskarte_responsive( $args = array() ) {
    $defaults = array(
        'class'     => 'kreiskarte-responsive',
        'max_width' => '700px',
    );
    $args = wp_parse_args( $args, $defaults );

    $data    = gk_get_kreiskarte_data();
    $ov_data = gk_get_ov_terms_by_slug();
    $has_map = $data && ! empty( $data['municipalities'] );

    // Build OV list data (for both list view and bottom sheet).
    $ov_items = array();
    if ( $has_map ) {
        foreach ( $data['municipalities'] as $slug => $muni ) {
            $type = $muni['type'] ?? 'ov';
            if ( $type === 'keine' ) continue;

            if ( $type === 'link' ) {
                $link = ! empty( $muni['link'] ) ? $muni['link'] : '';
            } else {
                $ov_entry = $ov_data[ $slug ] ?? null;
                $link     = $ov_entry ? $ov_entry->homepage_url : '';
            }

            $ov_items[] = array(
                'slug' => $slug,
                'name' => $muni['name'],
                'type' => $type,
                'link' => $link,
            );
        }
        usort( $ov_items, function ( $a, $b ) {
            return strcasecmp( $a['name'], $b['name'] );
        } );
    } elseif ( ! empty( $ov_data ) ) {
        // No map data — build from terms.
        uasort( $ov_data, function ( $a, $b ) {
            return strcasecmp( $a->name, $b->name );
        } );
        foreach ( $ov_data as $slug => $ov ) {
            $ov_items[] = array(
                'slug' => $slug,
                'name' => $ov->name,
                'type' => $ov->type,
                'link' => $ov->homepage_url,
            );
        }
    }

    ob_start();
    ?>
    <div class="<?php echo esc_attr( $args['class'] ); ?>">

        <!-- Search + toggle bar -->
        <div class="gk-ov-toolbar">
            <div class="gk-ov-search-wrap">
                <input type="text"
                       class="gk-ov-search"
                       placeholder="Ortsverband suchen&hellip;"
                       aria-label="Ortsverband suchen" />
                <span class="gk-ov-search-icon" aria-hidden="true">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                </span>
            </div>
            <?php if ( $has_map ) : ?>
            <button type="button" class="gk-ov-toggle gk-btn gk-btn--primary" aria-pressed="false"
                    data-label-map="Karte anzeigen" data-label-list="Liste anzeigen">
                <span class="gk-ov-toggle__icon-map" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/><line x1="8" y1="2" x2="8" y2="18"/><line x1="16" y1="6" x2="16" y2="22"/></svg>
                </span>
                <span class="gk-ov-toggle__icon-list" aria-hidden="true">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
                </span>
                <span class="gk-ov-toggle__label">Karte anzeigen</span>
            </button>
            <?php endif; ?>
        </div>

        <!-- LIST VIEW (default on mobile) -->
        <div class="gk-ov-list-view" role="list">
            <?php foreach ( $ov_items as $item ) :
                $type_label = '';
                if ( $item['type'] === 'ortsgruppe' ) $type_label = 'Ortsgruppe';
                if ( $item['type'] === 'werbung' )    $type_label = 'Im Aufbau';
                if ( $item['type'] === 'link' )       $type_label = 'Extern';
            ?>
            <a href="<?php echo $item['link'] ? esc_url( $item['link'] ) : '#'; ?>"
               class="gk-ov-chip <?php echo ! $item['link'] ? 'gk-ov-chip--inactive' : ''; ?>"
               role="listitem"
               data-ov-name="<?php echo esc_attr( $item['name'] ); ?>"
               <?php echo ! $item['link'] ? 'aria-disabled="true"' : ''; ?>
               <?php if ( $item['type'] === 'link' && $item['link'] ) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
                <span class="gk-ov-chip__arrow" aria-hidden="true">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12H3M21 12l-7-7M21 12l-7 7"/></svg>
                </span>
                <span class="gk-ov-chip__name"><?php echo esc_html( $item['name'] ); ?></span>
                <?php if ( $type_label ) : ?>
                    <span class="gk-ov-chip__badge"><?php echo esc_html( $type_label ); ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
            <p class="gk-ov-no-results" hidden>Kein Ortsverband gefunden.</p>
        </div>

        <?php if ( $has_map ) : ?>
        <!-- MAP VIEW (default on desktop, toggled on mobile) -->
        <div class="gk-ov-map-view">
            <?php echo gk_render_kreiskarte( array(
                'max_width' => $args['max_width'],
                'class'     => 'kreiskarte gk-ov-map-svg',
            ) ); ?>
        </div>

        <!-- BOTTOM SHEET (mobile only, for imprecise map taps) -->
        <div class="gk-ov-sheet" hidden aria-modal="false">
            <div class="gk-ov-sheet__backdrop"></div>
            <div class="gk-ov-sheet__panel">
                <div class="gk-ov-sheet__handle" aria-hidden="true"><span></span></div>
                <h3 class="gk-ov-sheet__title"></h3>
                <a href="#" class="gk-ov-sheet__cta gk-btn gk-btn--primary">Zur Seite &rarr;</a>
                <button type="button" class="gk-ov-sheet__close">Abbrechen</button>
            </div>
        </div>
        <?php endif; ?>

    </div>
    <?php
    return ob_get_clean();
}


/**
 * Shortcode: [kreiskarte]
 */
function gk_shortcode_kreiskarte( $atts ) {
    $atts = shortcode_atts( array(
        'class'     => 'kreiskarte',
        'max_width' => '700px',
    ), $atts, 'kreiskarte' );

    return gk_render_kreiskarte( $atts );
}
add_shortcode( 'kreiskarte', 'gk_shortcode_kreiskarte' );
