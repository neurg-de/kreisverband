<?php
/**
 * Shortcodes for displaying persons, Ortsverbände, and UI elements
 *
 * Shortcodes:
 *   [mandate]              - Persons with mandates, sorted by list position
 *   [landesliste]          - Persons for state/district lists
 *   [vorstand]             - Executive board members
 *   [glv]                  - Group leadership (Gemeinderat etc.)
 *   [team]                 - Team members
 *   [abteilung]            - Filterable person grid with OV tabs
 *   [kontakt]              - Contact information display
 *   [gliederungen]         - Ortsverband list (Kreisverbandsebene)
 *   [arbeitsgemeinschaften] - Working groups (LAGen)
 *   [unterseiten]          - Child page navigation with thumbnails
 *   [tabs] [tab]           - Responsive tabs
 *   [parallax]             - Parallax section
 *   [icon]                 - FontAwesome icon
 *   [abstand]              - Spacer
 *   [infobox]              - Info box
 *   [box]                  - Colored box
 *   [spendenbalken]        - Donation progress bar
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Enable excerpt support for pages
add_post_type_support( 'page', 'excerpt' );


// ── Person Shortcodes ────────────────────────────────────────────────────────

/**
 * Helper: run a person query and render a template part in a section wrapper.
 */
function gk_person_shortcode( $atts, $template_part, $section_class, $sort_key = 'kr8mb_pers_pos_sortierung', $variant = 'team' ) {
    $atts = shortcode_atts( array(
        'person'    => '',
        'abteilung' => '',
    ), $atts );

    global $wp_query, $post;
    $temp     = $wp_query;
    $wp_query = new WP_Query();

    if ( ! empty( $atts['person'] ) ) {
        $person_ids = explode( ',', $atts['person'] );
        $args = array(
            'post_type'  => 'person',
            'post__in'   => $person_ids,
            'order'      => 'ASC',
            'orderby'    => 'meta_value',
            'meta_key'   => $sort_key,
            'abteilung'  => $atts['slug'],
        );
    } else {
        $args = array(
            'post_type'      => 'person',
            'order'          => 'ASC',
            'orderby'        => 'meta_value',
            'meta_key'       => $sort_key,
            'abteilung'      => $atts['slug'],
            'posts_per_page' => -1,
        );
    }

    $wp_query->query( $args );
    ob_start();
    ?>
    <section class="<?php echo esc_attr( $section_class ); ?> clearfix">
    <?php if ( $wp_query->have_posts() ) : ?>
        <?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); ?>
            <?php get_template_part( 'template-parts/' . $template_part, null, array( 'variant' => $variant ) ); ?>
        <?php endwhile; ?>
    <?php else : ?>
        <p class="gk-empty-state">Keine Personen gefunden.</p>
    <?php endif; ?>
    </section>
    <?php
    $wp_query = $temp;
    wp_reset_postdata();
    return ob_get_clean();
}


function gk_shortcode_mandate( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--mandate', 'kr8mb_pers_pos_listenplatz', 'mandat' );
}
add_shortcode( 'mandate', 'gk_shortcode_mandate' );


function gk_shortcode_landesliste( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--landesliste', 'kr8mb_pers_pos_listenplatz', 'landesliste' );
}
add_shortcode( 'landesliste', 'gk_shortcode_landesliste' );


function gk_shortcode_vorstand( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--vorstand', 'kr8mb_pers_pos_sortierung', 'vorstand' );
}
add_shortcode( 'vorstand', 'gk_shortcode_vorstand' );


function gk_shortcode_glv( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--glv', 'kr8mb_pers_pos_sortierung', 'glv' );
}
add_shortcode( 'glv', 'gk_shortcode_glv' );


function gk_shortcode_team( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--team', 'kr8mb_pers_pos_sortierung', 'team' );
}
add_shortcode( 'team', 'gk_shortcode_team' );


function gk_shortcode_kontakt( $atts ) {
    return gk_person_shortcode( $atts, 'content-person', 'gk-person-list gk-person-list--kontakt', 'kr8mb_pers_pos_listenplatz', 'kontakt' );
}
add_shortcode( 'kontakt', 'gk_shortcode_kontakt' );


/**
 * [abteilung slug=stadtratsliste] - Filterable person grid with OV tabs
 *
 * Queries ALL persons in the given abteilung across every Zuordnung.
 * Renders filter tabs so users can narrow by Ortsverband.
 * The "Alle" (KV) tab shows everyone; each OV tab shows only its members.
 *
 * Attributes:
 *   slug  — abteilung taxonomy term slug (required)
 *   typ   — legacy: sort/display type (deprecated, kept for back-compat)
 */
function gk_shortcode_abteilung( $atts ) {
    $atts = shortcode_atts( array(
        'slug'       => '',
        'abteilung'  => '',  // legacy alias for slug
        'typ'        => '',  // legacy: ignored when _gk_abteilung_meta exists
        'limit'      => 0,   // 0 = show all
        'zuordnung'  => '',  // filter by gk_zuordnung slug (empty = show all)
    ), $atts );

    // Support legacy attribute name
    if ( empty( $atts['slug'] ) && ! empty( $atts['abteilung'] ) ) {
        $atts['slug'] = $atts['abteilung'];
    }

    $abt_slug = $atts['slug'];
    if ( empty( $abt_slug ) ) {
        return '';
    }

    // Query persons in this abteilung, optionally filtered by zuordnung
    $zuordnung = $atts['zuordnung'];
    $query_args = array(
        'post_type'      => 'person',
        'abteilung'      => $abt_slug,
        'posts_per_page' => -1,
    );
    if ( $zuordnung && $zuordnung !== 'kreisverband' ) {
        $query_args['tax_query'] = array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => $zuordnung,
        ) );
    }
    $query = new WP_Query( $query_args );

    if ( ! $query->have_posts() ) {
        return '';
    }

    // Build per-person data and sort by position (new meta), fallback to old fields
    $typ      = $atts['typ'];
    $persons  = array();
    $ov_counts = array();
    $ov_tabs   = array();

    foreach ( $query->posts as $p ) {
        $pid = $p->ID;

        // Read new per-abteilung meta
        $abt_meta = gk_get_abteilung_meta_for( $pid, $abt_slug );
        $position = $abt_meta['position'];
        $function = $abt_meta['function'];
        $hidden   = $abt_meta['hidden'];

        // Fallback to old fields if new meta is empty (not yet migrated)
        if ( $position === '' && $function === '' ) {
            $position = gk_legacy_position( $pid, $typ );
            $function = gk_legacy_function( $pid, $typ );
        }

        // Zuordnung (OV)
        $ov_slug = gk_get_post_zuordnung_slug( $pid ) ?: 'kreisverband';
        if ( $ov_slug !== 'kreisverband' ) {
            $ov_counts[ $ov_slug ] = ( $ov_counts[ $ov_slug ] ?? 0 ) + 1;
        }

        $persons[] = array(
            'post'     => $p,
            'position' => $position,
            'function' => $function,
            'hidden'   => $hidden,
            'ov_slug'  => $ov_slug,
        );
    }

    // Sort: persons with position first (numeric ASC), then alphabetically by title
    usort( $persons, function( $a, $b ) {
        $a_pos = $a['position'];
        $b_pos = $b['position'];
        $a_has = $a_pos !== '';
        $b_has = $b_pos !== '';

        if ( $a_has && ! $b_has ) return -1;
        if ( ! $a_has && $b_has ) return 1;
        if ( $a_has && $b_has ) {
            $cmp = intval( $a_pos ) - intval( $b_pos );
            if ( $cmp !== 0 ) return $cmp;
        }
        return strcasecmp( $a['post']->post_title, $b['post']->post_title );
    } );

    // Apply limit
    $limit = intval( $atts['limit'] );
    if ( $limit > 0 ) {
        $persons = array_slice( $persons, 0, $limit );
        // Rebuild OV counts from limited set
        $ov_counts = array();
        foreach ( $persons as $item ) {
            $ov = $item['ov_slug'];
            if ( $ov !== 'kreisverband' ) {
                $ov_counts[ $ov ] = ( $ov_counts[ $ov ] ?? 0 ) + 1;
            }
        }
    }

    // Build OV tab labels
    foreach ( $ov_counts as $slug => $count ) {
        $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
        if ( $term ) {
            $ov_tabs[ $slug ] = $term->name;
        }
    }
    asort( $ov_tabs );

    // Resolve abteilung term for heading
    $abt          = get_term_by( 'slug', $abt_slug, 'abteilung' );
    $show_filters = count( $ov_tabs ) > 0 && ! $zuordnung;
    $total        = count( $persons );

    ob_start();
    ?>
    <section class="gk-abteilung">
        <?php if ( $abt ) : ?>
        <div class="gk-abteilung__header">
            <h3><?php echo esc_html( $abt->name ); ?></h3>
            <?php if ( $abt->description ) : ?>
                <p><?php echo esc_html( $abt->description ); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ( $show_filters ) : ?>
        <div class="gk-abteilung__filters" role="tablist" aria-label="<?php esc_attr_e( 'Nach Ortsverband filtern', 'flavor' ); ?>">
            <button class="gk-btn--filter gk-btn--sm is-active" role="tab" aria-selected="true" data-filter="all">
                Alle <span class="gk-abteilung__count"><?php echo $total; ?></span>
            </button>
            <?php foreach ( $ov_tabs as $slug => $name ) : ?>
            <button class="gk-btn--filter gk-btn--sm" role="tab" aria-selected="false" data-filter="<?php echo esc_attr( $slug ); ?>">
                <?php echo esc_html( $name ); ?>
                <span class="gk-abteilung__count"><?php echo esc_html( $ov_counts[ $slug ] ); ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="gk-abteilung__grid" aria-live="polite">
        <?php foreach ( $persons as $item ) :
            $pid       = $item['post']->ID;
            $permalink = esc_url( get_permalink( $pid ) );
            $title     = esc_html( get_the_title( $pid ) );
            $position  = ltrim( $item['position'], '0' );
            $function  = $item['function'];
            $hidden    = $item['hidden'];
            $ov_slug   = $item['ov_slug'];

            $ov_label = '';
            if ( $ov_slug !== 'kreisverband' && isset( $ov_tabs[ $ov_slug ] ) ) {
                $ov_label = $ov_tabs[ $ov_slug ];
            }
        ?>
            <a class="gk-abteilung__card" href="<?php echo $permalink; ?>" data-ov="<?php echo esc_attr( $ov_slug ); ?>">
                <div class="gk-abteilung__photo">
                    <?php if ( has_post_thumbnail( $pid ) ) : ?>
                        <?php echo get_the_post_thumbnail( $pid, 'medium', array( 'alt' => $title ) ); ?>
                    <?php else : ?>
                        <img src="<?php echo esc_url( GK_IMAGE_DIR . 'platzhalter.png' ); ?>" alt="Kein Foto vorhanden" />
                    <?php endif; ?>
                </div>
                <?php if ( $position && ! $hidden ) : ?>
                    <span class="gk-abteilung__position"><?php echo esc_html( $position ); ?></span>
                <?php endif; ?>
                <h4 class="gk-abteilung__name"><?php echo $title; ?></h4>
                <?php if ( $function ) : ?>
                    <p class="gk-abteilung__role"><?php echo esc_html( $function ); ?></p>
                <?php endif; ?>
                <?php if ( $ov_label ) : ?>
                    <p class="gk-abteilung__ov"><?php echo esc_html( $ov_label ); ?></p>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
        </div>

        <p class="gk-abteilung__empty" hidden>Keine Personen gefunden.</p>
    </section>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * Legacy fallback: read position from old meta fields based on typ.
 */
function gk_legacy_position( $pid, $typ ) {
    $map = array(
        'ovvorstand'        => 'kr8mb_pers_sortierung1',
        'gemeinderat'       => 'kr8mb_pers_sortierung2',
        'stadtrat'          => 'kr8mb_pers_sortierung2',
        'gemeinderatsliste' => 'kr8mb_pers_pos_listenplatz',
        'stadtratsliste'    => 'kr8mb_pers_pos_listenplatz',
    );
    $key = $map[ $typ ] ?? 'kr8mb_pers_pos_sortierung';
    return get_post_meta( $pid, $key, true ) ?: '';
}

/**
 * Legacy fallback: read function/role from old meta fields based on typ.
 */
function gk_legacy_function( $pid, $typ ) {
    if ( $typ === 'ovvorstand' ) {
        return get_post_meta( $pid, 'kr8mb_pers_funktion1', true );
    }
    if ( in_array( $typ, array( 'ba', 'stadtrat', 'gemeinderat' ), true ) ) {
        return get_post_meta( $pid, 'kr8mb_pers_funktion2', true );
    }
    return get_post_meta( $pid, 'kr8mb_pers_pos_amt', true );
}
add_shortcode( 'abteilung', 'gk_shortcode_abteilung' );
add_shortcode( 'personenliste', 'gk_shortcode_abteilung' ); // legacy alias


// ── Gliederung Shortcodes (now using zuordnung terms) ────────────────────────

/**
 * [gliederungen] — List OV zuordnung terms with contact info.
 */
function gk_shortcode_gliederungen( $atts ) {
    $atts = shortcode_atts( array(
        'type' => '', // filter by _gk_ov_type
    ), $atts );

    $ov_terms = gk_get_ov_terms();
    if ( empty( $ov_terms ) ) return '';

    ob_start();
    ?>
    <section class="gliederungen-list kve-list clearfix">
    <?php foreach ( $ov_terms as $term ) :
        $type = gk_get_ov_type( $term->term_id );
        if ( ! empty( $atts['type'] ) && $type !== $atts['type'] ) continue;
        $contact = gk_get_ov_contact( $term->term_id );
        $homepage_id = gk_get_ov_homepage_id( $term->term_id );
    ?>
    <article class="clearfix">
        <?php if ( $homepage_id && has_post_thumbnail( $homepage_id ) ) : ?>
            <?php echo get_the_post_thumbnail( $homepage_id, 'thumbnail' ); ?>
        <?php endif; ?>
        <header class="article-header">
            <h2><?php if ( ! empty( $contact['www'] ) ) : ?><a href="<?php echo esc_url( $contact['www'] ); ?>"><?php endif; ?><?php echo esc_html( $term->name ); ?><?php if ( ! empty( $contact['www'] ) ) : ?></a><?php endif; ?></h2>
        </header>
        <section class="entry-content">
            <p class="contact">
                <?php if ( ! empty( $contact['www'] ) ) : ?><a href="<?php echo esc_url( $contact['www'] ); ?>" title="Website"><i class="fa fa-globe"></i></a><?php endif; ?>
                <?php if ( ! empty( $contact['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" title="E-Mail"><i class="fa fa-envelope"></i></a><?php endif; ?>
            </p>
            <?php gk_social_links_bar( gk_build_social_links( $contact ), array( 'class' => 'gk-social-links--inline' ) ); ?>
        </section>
    </article>
    <?php endforeach; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'gliederungen', 'gk_shortcode_gliederungen' );


/**
 * [arbeitsgemeinschaften] — List OV terms as working groups (LAGen).
 */
function gk_shortcode_arbeitsgemeinschaften( $atts ) {
    $atts = shortcode_atts( array(
        'type' => '',
    ), $atts );

    $ov_terms = gk_get_ov_terms();
    if ( empty( $ov_terms ) ) return '';

    ob_start();
    ?>
    <section class="gliederungen-list lagen-list clearfix">
    <?php foreach ( $ov_terms as $term ) :
        $type = gk_get_ov_type( $term->term_id );
        if ( ! empty( $atts['type'] ) && $type !== $atts['type'] ) continue;
        $contact = gk_get_ov_contact( $term->term_id );
        $homepage_id = gk_get_ov_homepage_id( $term->term_id );
        $homepage_url = $homepage_id ? get_permalink( $homepage_id ) : '';
    ?>
    <article class="clearfix">
        <?php if ( $homepage_id && has_post_thumbnail( $homepage_id ) ) : ?>
            <?php echo get_the_post_thumbnail( $homepage_id, 'thumbnail' ); ?>
        <?php endif; ?>
        <header class="article-header">
            <h2><?php if ( $homepage_url ) : ?><a href="<?php echo esc_url( $homepage_url ); ?>"><?php endif; ?><?php echo esc_html( $term->name ); ?><?php if ( $homepage_url ) : ?></a><?php endif; ?></h2>
        </header>
        <section class="entry-content">
            <p class="contact">
                <?php if ( ! empty( $contact['www'] ) ) : ?><a href="<?php echo esc_url( $contact['www'] ); ?>" title="Website"><i class="fa fa-globe"></i></a><?php endif; ?>
                <?php if ( ! empty( $contact['email'] ) ) : ?><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" title="E-Mail"><i class="fa fa-envelope"></i></a><?php endif; ?>
            </p>
            <?php gk_social_links_bar( gk_build_social_links( $contact ), array( 'class' => 'gk-social-links--inline' ) ); ?>
        </section>
    </article>
    <?php endforeach; ?>
    </section>
    <?php
    return ob_get_clean();
}
add_shortcode( 'arbeitsgemeinschaften', 'gk_shortcode_arbeitsgemeinschaften' );


// ── UI Shortcodes ────────────────────────────────────────────────────────────

/**
 * [unterseiten] - Child page listing with thumbnails
 */
function gk_shortcode_unterseiten() {
    $the_id  = get_the_ID();
    $pages   = get_pages( array(
        'child_of'    => $the_id,
        'title_li'    => '',
        'parent'      => $the_id,
        'sort_order'  => 'ASC',
        'sort_column' => 'menu_order',
    ) );

    $children = '';
    foreach ( $pages as $page ) {
        $thumb     = get_the_post_thumbnail( $page->ID, 'thumbnail' );
        $children .= '<li>';
        $children .= '<a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . $thumb . '</a>';
        $children .= '<p><a href="' . esc_url( get_permalink( $page->ID ) ) . '">' . esc_html( $page->post_title ) . '</a>';
        $children .= '<span>' . esc_html( $page->post_excerpt ) . '</span></p>';
        $children .= '</li>';
    }

    return '<nav><ul class="sitemap sitemap-thumb">' . $children . '</ul></nav>';
}
add_shortcode( 'unterseiten', 'gk_shortcode_unterseiten' );


/**
 * [tabs] [tab title="..."] - Responsive tabs
 */
function gk_shortcode_tabs( $atts, $content = null ) {
    return '<div class="responsive-tabs content-tabs">' . do_shortcode( $content ) . '</div>'
        . '<script>jQuery(document).ready(function() { if(typeof RESPONSIVEUI !== "undefined") RESPONSIVEUI.responsiveTabs(); })</script>';
}
add_shortcode( 'tabs', 'gk_shortcode_tabs' );

function gk_shortcode_tab( $atts, $content = null ) {
    $atts = shortcode_atts( array( 'title' => 'Titel' ), $atts );
    return '<h2>' . esc_html( $atts['title'] ) . '</h2><div>' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'tab', 'gk_shortcode_tab' );


/**
 * [parallax] - Parallax/full-width section break
 */
function gk_shortcode_parallax( $atts ) {
    $atts = shortcode_atts( array(
        'vollbild'         => '',
        'hintergrundfarbe' => '',
        'schriftfarbe'     => '',
        'id'               => '',
        'bgscroll'         => '',
        'hintergrundbild'  => '',
    ), $atts );

    $class1 = $atts['vollbild'] !== '' ? 'fullpage' : 'textblock ';
    $class2 = $atts['hintergrundbild'] !== '' ? 'parallax' : '';
    $class3 = $atts['bgscroll'] !== '' ? 'bgscroll' : '';

    $style = '';
    if ( $atts['hintergrundfarbe'] ) $style .= 'background-color:#' . esc_attr( $atts['hintergrundfarbe'] ) . ';';
    if ( $atts['schriftfarbe'] )     $style .= 'color:#' . esc_attr( $atts['schriftfarbe'] ) . ';';
    if ( $atts['hintergrundbild'] )  $style .= 'background-image:url(' . esc_url( $atts['hintergrundbild'] ) . ');';

    return '</div></article><article id="' . esc_attr( $atts['id'] ) . '" class="' . esc_attr( trim( "$class1 $class2 $class3" ) ) . '" style="' . $style . '"><div class="inner">';
}
add_shortcode( 'parallax', 'gk_shortcode_parallax' );


/**
 * [icon symbol="fa-rocket" groesse="fa-4x"]
 */
function gk_shortcode_icon( $atts ) {
    $atts = shortcode_atts( array(
        'symbol'  => 'fa-rocket',
        'groesse' => 'fa-4x',
    ), $atts );
    return '<p class="icon"><span class="fa ' . esc_attr( $atts['symbol'] ) . ' ' . esc_attr( $atts['groesse'] ) . '"></span></p>';
}
add_shortcode( 'icon', 'gk_shortcode_icon' );


/**
 * [abstand] - Vertical spacer
 */
function gk_shortcode_abstand() {
    return '<div class="abstand"></div>';
}
add_shortcode( 'abstand', 'gk_shortcode_abstand' );


/**
 * [infobox title="..."]...[/infobox]
 */
function gk_shortcode_infobox( $atts, $content = null ) {
    $atts = shortcode_atts( array( 'title' => 'Infobox' ), $atts );
    return '<div class="infobox"><h3>' . esc_html( $atts['title'] ) . '</h3>' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'infobox', 'gk_shortcode_infobox' );


/**
 * [box]...[/box] - Colored box
 */
function gk_shortcode_box( $atts, $content = null ) {
    return '<div class="colorbox">' . do_shortcode( $content ) . '</div>';
}
add_shortcode( 'box', 'gk_shortcode_box' );


/**
 * [spendenbalken ziel="1000" stand="450"] - Donation progress bar
 */
function gk_shortcode_spendenbalken( $atts ) {
    $atts = shortcode_atts( array(
        'ziel'  => '0',
        'stand' => '0',
    ), $atts );

    $ziel  = floatval( $atts['ziel'] );
    $stand = floatval( $atts['stand'] );
    if ( $ziel <= 0 ) return '';

    $prozent   = $stand / $ziel * 100;
    $ziel_fmt  = number_format( $ziel, 0, ',', '.' );
    $stand_fmt = number_format( $stand, 0, ',', '.' );
    $label     = $stand_fmt . '&euro; von ' . $ziel_fmt . '&euro;';

    if ( $prozent < 50 ) {
        return '<div class="spendenbalken"><div class="fortschritt" style="width:' . esc_attr( $prozent ) . '%"></div>' . $label . '</div>';
    }
    return '<div class="spendenbalken"><div class="fortschritt" style="width:' . esc_attr( $prozent ) . '%">' . $label . '</div></div>';
}
add_shortcode( 'spendenbalken', 'gk_shortcode_spendenbalken' );


// ── Content filter: fix shortcode wrapping in paragraphs ─────────────────────

add_filter( 'the_content', 'gk_fix_shortcode_paragraphs' );
function gk_fix_shortcode_paragraphs( $content ) {
    $block = join( '|', array( 'col', 'tabs', 'tab' ) );
    $content = preg_replace( "/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/", '[$2$3]', $content );
    $content = preg_replace( "/(<p>)?\[\/($block)](<\/p>|<br \/>)?/", '[/$2]', $content );
    return $content;
}
