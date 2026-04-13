<?php
/**
 * Ortsverband (OV) System
 *
 * OVs are managed as gk_zuordnung taxonomy terms.
 * Each term can have term meta for type, homepage, and contact data.
 * The Kreisverband is the default term; all other terms are OVs.
 *
 * Term meta keys:
 *   _gk_ov_type         — 'ov', 'ortsgruppe', 'werbung'
 *   _gk_homepage_id     — page ID for the OV's homepage
 *   _gk_contact_www     — website URL
 *   _gk_contact_email   — email
 *   _gk_contact_facebook
 *   _gk_contact_twitter  — X (formerly Twitter)
 *   _gk_contact_tiktok
 *   _gk_contact_threads
 *   _gk_contact_mastodon
 *   _gk_contact_insta
 *   _gk_contact_telefon
 *   _gk_contact_anschrift
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── OV Term Helpers ─────────────────────────────────────────────────────────

/**
 * Get all OV zuordnung terms (everything except Kreisverband).
 *
 * @param array $args Additional get_terms args.
 * @return WP_Term[]
 */
function gk_get_ov_terms( $args = array() ) {
    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );

    $defaults = array(
        'taxonomy'   => 'gk_zuordnung',
        'hide_empty' => false,
        'exclude'    => $kv_term ? array( $kv_term->term_id ) : array(),
        'orderby'    => 'name',
    );

    $terms = get_terms( wp_parse_args( $args, $defaults ) );
    return is_wp_error( $terms ) ? array() : $terms;
}

/**
 * Get OV term by slug.
 *
 * @param string $slug
 * @return WP_Term|false
 */
function gk_get_ov_term( $slug ) {
    $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
    if ( ! $term || $term->slug === 'kreisverband' ) {
        return false;
    }
    return $term;
}

/**
 * Get the zuordnung slug for a given post.
 *
 * @param int|null $post_id
 * @return string The zuordnung slug, or empty string.
 */
function gk_get_post_zuordnung_slug( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }
    $terms = wp_get_post_terms( $post_id, 'gk_zuordnung', array( 'fields' => 'slugs' ) );
    if ( is_wp_error( $terms ) || empty( $terms ) ) {
        return '';
    }
    return $terms[0];
}

/**
 * Check if a post belongs to an Ortsverband (not Kreisverband).
 *
 * @param int|null $post_id
 * @return bool
 */
function gk_post_is_ov( $post_id = null ) {
    $slug = gk_get_post_zuordnung_slug( $post_id );
    return $slug !== '' && $slug !== 'kreisverband';
}

/**
 * Get all contact data for an OV term.
 *
 * @param int $term_id
 * @return array Associative array of contact fields.
 */
function gk_get_ov_contact( $term_id ) {
    $fields = array( 'www', 'email', 'facebook', 'twitter', 'tiktok', 'threads', 'mastodon', 'insta', 'telefon', 'anschrift' );
    $contact = array();
    foreach ( $fields as $field ) {
        $contact[ $field ] = get_term_meta( $term_id, '_gk_contact_' . $field, true );
    }
    return $contact;
}

/**
 * Get the homepage page ID for an OV term.
 *
 * @param int $term_id
 * @return int Page ID or 0.
 */
function gk_get_ov_homepage_id( $term_id ) {
    return (int) get_term_meta( $term_id, '_gk_homepage_id', true );
}

/**
 * Get the impressum or datenschutz page ID for a zuordnung slug.
 *
 * Checks the zuordnung term meta first, falls back to global KV info.
 *
 * @param string $zuordnung_slug The zuordnung slug.
 * @param string $type           'impressum' or 'datenschutz'.
 * @return int Page ID or 0.
 */
function gk_get_zuordnung_legal_page( $zuordnung_slug, $type ) {
    $term = get_term_by( 'slug', $zuordnung_slug, 'gk_zuordnung' );
    if ( $term && ! is_wp_error( $term ) ) {
        $page_id = (int) get_term_meta( $term->term_id, '_gk_' . $type . '_page', true );
        if ( $page_id && get_post_status( $page_id ) === 'publish' ) {
            return $page_id;
        }
    }
    // Fall back to KV-level setting.
    return (int) gk_get_kv_info( $type . '_page' );
}

/**
 * Get OV type for a term.
 *
 * @param int $term_id
 * @return string 'ov', 'ortsgruppe', or 'werbung'.
 */
function gk_get_ov_type( $term_id ) {
    return get_term_meta( $term_id, '_gk_ov_type', true ) ?: 'ov';
}

/**
 * Get the header text for an OV term.
 *
 * @param int $term_id
 * @return string Header text, or term name as fallback.
 */
function gk_get_ov_header( $term_id ) {
    $header = get_term_meta( $term_id, '_gk_ov_header', true );
    if ( $header ) {
        return $header;
    }
    $term = get_term( $term_id, 'gk_zuordnung' );
    return $term ? $term->name : '';
}

/**
 * Get all OV homepage settings as an array.
 *
 * @param int $term_id
 * @return array
 */
function gk_get_ov_homepage_options( $term_id ) {
    static $cache = array();
    if ( ! isset( $cache[ $term_id ] ) ) {
        $cache[ $term_id ] = get_term_meta( $term_id, '_gk_ov_homepage', true ) ?: array();
    }
    return $cache[ $term_id ];
}

/**
 * Get a single OV homepage setting.
 *
 * @param int    $term_id
 * @param string $key
 * @param mixed  $default
 * @return mixed
 */
function gk_get_ov_homepage_option( $term_id, $key, $default = '' ) {
    $options = gk_get_ov_homepage_options( $term_id );
    $value   = $options[ $key ] ?? '';
    return $value !== '' ? $value : $default;
}


// ── Context Social Bar ──────────────────────────────────────────────────────

/**
 * Render a social media bar for the current KV or OV context.
 *
 * Desktop: floating vertical strip on the right edge.
 * Mobile:  compact horizontal row at the top of the page.
 */
function gk_context_social_bar() {
    global $post;

    $slug = isset( $post ) ? gk_get_post_zuordnung_slug( $post->ID ) : '';
    if ( empty( $slug ) ) {
        $slug = 'kreisverband';
    }

    $term = get_term_by( 'slug', $slug, 'gk_zuordnung' );
    if ( ! $term ) {
        return;
    }

    $contact   = gk_get_ov_contact( $term->term_id );
    $links     = gk_build_social_links( $contact );
    $platforms = gk_social_platforms();

    if ( empty( $links ) ) {
        return;
    }
    ?>
    <nav class="gk-social-bar" aria-label="Soziale Netzwerke">
        <ul>
        <?php foreach ( $links as $platform => $url ) :
            $def = $platforms[ $platform ] ?? null;
            if ( ! $def ) continue;
        ?>
            <li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener" title="<?php echo esc_attr( $def['label'] ); ?>"><i class="<?php echo esc_attr( $def['icon'] ); ?>" aria-hidden="true"></i><span class="screen-reader-text"><?php echo esc_html( $def['label'] ); ?></span></a></li>
        <?php endforeach; ?>
        </ul>
    </nav>
    <?php
}


// ── Abteilung × Zuordnung Scoping ──────────────────────────────────────────

/**
 * Register gk_ov_context query var for OV-scoped abteilung archives.
 */
function gk_register_ov_context_query_var( $vars ) {
    $vars[] = 'gk_ov_context';
    $vars[] = 'gk_ov_team';
    return $vars;
}
add_filter( 'query_vars', 'gk_register_ov_context_query_var' );

/**
 * Add rewrite rules for OV-scoped abteilung archives.
 * Pattern: {ov-slug}/abteilung/{abteilung-slug}
 */
function gk_abteilung_ov_rewrite_rules() {
    add_rewrite_rule(
        '^([^/]+)/abteilung/([^/]+)/page/([0-9]+)/?$',
        'index.php?abteilung=$matches[2]&gk_ov_context=$matches[1]&paged=$matches[3]',
        'top'
    );
    add_rewrite_rule(
        '^([^/]+)/abteilung/([^/]+)/?$',
        'index.php?abteilung=$matches[2]&gk_ov_context=$matches[1]',
        'top'
    );
    // OV team overview: {ov-slug}/team/
    add_rewrite_rule(
        '^([^/]+)/team/?$',
        'index.php?gk_ov_team=1&gk_ov_context=$matches[1]',
        'top'
    );
}
add_action( 'init', 'gk_abteilung_ov_rewrite_rules' );

/**
 * Scope abteilung taxonomy archives by zuordnung.
 *
 * - /abteilung/{slug}              → persons with gk_zuordnung=kreisverband
 * - /{ov-slug}/abteilung/{slug}    → persons with gk_zuordnung={ov-slug}
 */
function gk_abteilung_scope_by_zuordnung( $query ) {
    if ( is_admin() || ! $query->is_main_query() || ! $query->is_tax( 'abteilung' ) ) {
        return;
    }

    $ov_context     = get_query_var( 'gk_ov_context' );
    $zuordnung_slug = $ov_context ? sanitize_title( $ov_context ) : 'kreisverband';

    // Validate that the zuordnung term exists.
    $term = get_term_by( 'slug', $zuordnung_slug, 'gk_zuordnung' );
    if ( ! $term && $ov_context ) {
        $query->set_404();
        return;
    }

    $tax_query = $query->get( 'tax_query' ) ?: array();
    $tax_query[] = array(
        'taxonomy' => 'gk_zuordnung',
        'field'    => 'slug',
        'terms'    => $zuordnung_slug,
    );
    $query->set( 'tax_query', $tax_query );
    $query->set( 'posts_per_page', -1 );
    $query->set( 'orderby', 'menu_order title' );
    $query->set( 'order', 'ASC' );
}
add_action( 'pre_get_posts', 'gk_abteilung_scope_by_zuordnung' );

/**
 * Get the URL for an abteilung archive scoped to a zuordnung.
 *
 * @param WP_Term|string $abteilung      Abteilung term or slug.
 * @param string         $zuordnung_slug Zuordnung slug (default: 'kreisverband').
 * @return string URL.
 */
function gk_abteilung_url( $abteilung, $zuordnung_slug = 'kreisverband' ) {
    if ( is_string( $abteilung ) ) {
        $abteilung = get_term_by( 'slug', $abteilung, 'abteilung' );
    }
    if ( ! $abteilung ) {
        return '';
    }

    if ( ! $zuordnung_slug || $zuordnung_slug === 'kreisverband' ) {
        return get_term_link( $abteilung );
    }

    return home_url( $zuordnung_slug . '/abteilung/' . $abteilung->slug . '/' );
}


// ── OV Navigation ────────────────────────────────────────────────────────────

/**
 * Render OV page navigation for an OV identified by zuordnung slug.
 *
 * @param string $ov_slug The OV zuordnung term slug.
 */
function gk_ov_navi( $ov_slug ) {
    if ( empty( $ov_slug ) || $ov_slug === 'kreisverband' ) {
        return;
    }

    // Get all page IDs with this zuordnung.
    $pages = get_posts( array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => $ov_slug,
        ) ),
    ) );

    if ( empty( $pages ) ) {
        return;
    }

    echo '<ul class="ovnavi">' . "\n";
    wp_list_pages( array(
        'include'  => implode( ',', $pages ),
        'title_li' => '',
    ) );
    echo "</ul>\n";
}

// Backwards compatibility
function OVnavi( $author_id ) {
    // Legacy calls pass author_id — try to resolve to zuordnung slug.
    $ov_slug = '';
    $user = get_user_by( 'id', $author_id );
    if ( $user ) {
        $ov_slug = $user->user_login;
    }
    gk_ov_navi( $ov_slug );
}


// ── OV Template Routing ──────────────────────────────────────────────────────

/**
 * Route event posts by OV to the OV event template.
 */
function gk_ov_event_template( $template ) {
    global $post;
    if ( ! is_singular( 'event' ) || ! $post ) return $template;

    if ( gk_post_is_ov( $post->ID ) ) {
        $ov_template = locate_template( 'single-event-OV.php' );
        if ( $ov_template ) {
            add_filter( 'body_class', 'gk_ov_body_class' );
            return $ov_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'gk_ov_event_template' );


/**
 * Route person posts by OV to the OV single template.
 *
 * Disabled: person posts now always use single-person.php (full-width profile).
 * OV context is shown via badges in the profile header instead of sidebar nav.
 */
// function gk_ov_person_template( $template ) {
//     global $post;
//     if ( ! is_singular( 'person' ) || ! $post ) return $template;
//
//     if ( gk_post_is_ov( $post->ID ) ) {
//         $ov_template = locate_template( 'single-OV.php' );
//         if ( $ov_template ) {
//             add_filter( 'body_class', 'gk_ov_body_class' );
//             return $ov_template;
//         }
//     }
//     return $template;
// }
// add_filter( 'single_template', 'gk_ov_person_template' );


/**
 * Route regular posts by OV to the OV single template.
 */
function gk_ov_single_template( $template ) {
    global $post;
    if ( ! is_singular( 'post' ) || ! $post ) return $template;

    if ( gk_post_is_ov( $post->ID ) ) {
        $ov_template = locate_template( 'single-OV.php' );
        if ( $ov_template ) {
            add_filter( 'body_class', 'gk_ov_body_class' );
            return $ov_template;
        }
    }
    return $template;
}
add_filter( 'single_template', 'gk_ov_single_template' );


/**
 * Route child pages of OV top-level pages to the OV subpage template.
 */
function gk_ov_child_template( $template ) {
    global $post;
    if ( ! $post || ! $post->post_parent ) return $template;

    $ancestors = get_post_ancestors( $post->ID );
    if ( empty( $ancestors ) ) return $template;

    $top_parent = get_post( end( $ancestors ) );
    if ( ! $top_parent ) return $template;

    if ( gk_post_is_ov( $top_parent->ID ) ) {
        $ov_template = locate_template( 'page-OVsubpages.php' );
        if ( $ov_template ) {
            add_filter( 'body_class', 'gk_ov_body_class' );
            return $ov_template;
        }
    }
    return $template;
}
add_filter( 'template_include', 'gk_ov_child_template' );


/**
 * Route {ov-slug}/team/ to the OV team template.
 */
function gk_ov_team_template( $template ) {
    if ( ! get_query_var( 'gk_ov_team' ) ) {
        return $template;
    }

    $ov_slug = sanitize_title( get_query_var( 'gk_ov_context' ) );
    $ov_term = $ov_slug ? gk_get_ov_term( $ov_slug ) : false;
    if ( ! $ov_term ) {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        return get_404_template();
    }

    $ov_template = locate_template( 'page-OV-team.php' );
    if ( $ov_template ) {
        add_filter( 'body_class', 'gk_ov_body_class' );
        return $ov_template;
    }
    return $template;
}
add_filter( 'template_include', 'gk_ov_team_template' );


/**
 * Get the URL for an OV's team overview page.
 *
 * @param string $ov_slug Zuordnung slug.
 * @return string URL.
 */
function gk_ov_team_url( $ov_slug ) {
    return home_url( $ov_slug . '/team/' );
}


/**
 * Add OV body class for styling.
 */
function gk_ov_body_class( $classes ) {
    $classes[] = 'page-template-page-OVsubpages';
    return $classes;
}


// ── OV Listing Shortcode ────────────────────────────────────────────────────

/**
 * [ortsverband_liste] — Render a list of all OVs from zuordnung terms.
 */
function gk_shortcode_ortsverband_liste( $atts ) {
    $atts = shortcode_atts( array(
        'mode' => '', // filter by type: 'ov', 'ortsgruppe', 'werbung'
    ), $atts );

    $ov_terms = gk_get_ov_terms();
    if ( empty( $ov_terms ) ) {
        return '';
    }

    ob_start();
    ?>
    <ul class="ov-liste">
    <?php foreach ( $ov_terms as $term ) :
        $type = gk_get_ov_type( $term->term_id );
        if ( ! empty( $atts['mode'] ) && $type !== $atts['mode'] ) continue;

        $homepage_id = gk_get_ov_homepage_id( $term->term_id );
        $link = $homepage_id ? get_permalink( $homepage_id ) : '';
    ?>
        <li class="ov-liste-item ov-mode-<?php echo esc_attr( $type ); ?>">
        <?php if ( $type === 'full' || ( $link && $type !== 'werbung' ) ) : ?>
            <a href="<?php echo esc_url( $link ); ?>">
                <?php echo esc_html( $term->name ); ?>
            </a>
        <?php elseif ( $type === 'werbung' ) : ?>
            <span class="ov-placeholder">
                <?php echo esc_html( $term->name ); ?>
                <em><?php echo esc_html( apply_filters( 'gk_ov_placeholder_text', 'Im Aufbau' ) ); ?></em>
            </span>
        <?php else : ?>
            <?php echo esc_html( $term->name ); ?>
        <?php endif; ?>
        </li>
    <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}
add_shortcode( 'ortsverband_liste', 'gk_shortcode_ortsverband_liste' );


// ── Home Query Filter ────────────────────────────────────────────────────────

/**
 * Exclude OV posts from the main blog feed.
 * Only show posts with gk_zuordnung=kreisverband on the home page.
 */
function gk_exclude_ov_from_home( $query ) {
    if ( ! $query->is_main_query() || ! $query->is_home() || is_admin() ) {
        return;
    }

    $excluded_cats = apply_filters( 'gk_home_excluded_categories', array() );
    if ( ! empty( $excluded_cats ) ) {
        $query->set( 'category__not_in', $excluded_cats );
    }

    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    if ( $kv_term ) {
        $tax_query = $query->get( 'tax_query' ) ?: array();
        $tax_query[] = array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => 'kreisverband',
        );
        $query->set( 'tax_query', $tax_query );
    }
}
add_action( 'pre_get_posts', 'gk_exclude_ov_from_home' );


// ── Zuordnung Helpers ───────────────────────────────────────────────────────

/**
 * Build query args to filter for KV-only posts.
 */
function gk_kv_query_args() {
    return array(
        'tax_query' => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => 'kreisverband',
        ) ),
    );
}

/**
 * Build query args to filter for a specific OV's posts.
 *
 * @param string $ov_slug The OV slug (zuordnung term slug).
 * @return array WP_Query args.
 */
function gk_ov_query_args( $ov_slug ) {
    return array(
        'tax_query' => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => sanitize_title( $ov_slug ),
        ) ),
    );
}


// ── Zuordnung Migration ─────────────────────────────────────────────────────

/**
 * One-time migration: assign gk_zuordnung to existing posts based on author.
 *
 * Run via admin notice or WP-CLI: wp eval 'gk_migrate_zuordnung();'
 */
function gk_migrate_zuordnung() {
    if ( ! taxonomy_exists( 'gk_zuordnung' ) ) {
        return array( 'error' => 'Taxonomy gk_zuordnung not registered.' );
    }

    gk_ensure_zuordnung_defaults();
    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    if ( ! $kv_term ) {
        return array( 'error' => 'Could not create Kreisverband term.' );
    }

    // Discover OV users: users with login starting with "ov-"
    $ov_users = get_users( array(
        'search'         => 'ov-*',
        'search_columns' => array( 'user_login' ),
        'fields'         => array( 'ID', 'user_login', 'display_name' ),
    ) );

    // Build a map: user_id -> term for OV users
    $ov_user_term_map = array();
    foreach ( $ov_users as $user ) {
        $term_name = $user->display_name ?: $user->user_login;
        $term_slug = sanitize_title( $user->user_login );

        $existing = get_term_by( 'slug', $term_slug, 'gk_zuordnung' );
        if ( ! $existing ) {
            $result = wp_insert_term( $term_name, 'gk_zuordnung', array(
                'slug'        => $term_slug,
                'description' => 'Beitraege von ' . $term_name,
            ) );
            if ( ! is_wp_error( $result ) ) {
                $ov_user_term_map[ (int) $user->ID ] = (int) $result['term_id'];
            }
        } else {
            $ov_user_term_map[ (int) $user->ID ] = (int) $existing->term_id;
        }
    }

    // Process all content types.
    $post_types = array( 'post', 'page', 'person', 'gk_event', 'attachment' );
    $migrated   = 0;
    $skipped    = 0;

    foreach ( $post_types as $pt ) {
        $posts = get_posts( array(
            'post_type'      => $pt,
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ) );

        foreach ( $posts as $post_id ) {
            $existing_terms = wp_get_object_terms( $post_id, 'gk_zuordnung', array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $existing_terms ) && ! empty( $existing_terms ) ) {
                $skipped++;
                continue;
            }

            $post      = get_post( $post_id );
            $author_id = (int) $post->post_author;

            if ( isset( $ov_user_term_map[ $author_id ] ) ) {
                wp_set_object_terms( $post_id, array( $ov_user_term_map[ $author_id ] ), 'gk_zuordnung' );
            } else {
                wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
            }
            $migrated++;
        }
    }

    update_option( 'gk_zuordnung_migrated', true );

    return array(
        'migrated' => $migrated,
        'skipped'  => $skipped,
        'ov_terms' => count( $ov_user_term_map ),
    );
}


/**
 * Show admin notice if migration hasn't run yet.
 */
function gk_zuordnung_migration_notice() {
    if ( get_option( 'gk_zuordnung_migrated' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['gk_run_zuordnung_migration'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'gk_zuordnung_migrate' ) ) {
        $result = gk_migrate_zuordnung();
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo '<strong>Zuordnung-Migration abgeschlossen:</strong> ';
        echo esc_html( $result['migrated'] ) . ' Beiträge zugeordnet, ';
        echo esc_html( $result['skipped'] ) . ' übersprungen, ';
        echo esc_html( $result['ov_terms'] ) . ' OV-Begriffe erstellt.';
        echo '</p></div>';
        return;
    }

    $url = wp_nonce_url( admin_url( '?gk_run_zuordnung_migration=1' ), 'gk_zuordnung_migrate' );
    echo '<div class="notice notice-warning"><p>';
    echo '<strong>Kreisverband-Theme:</strong> Bestehende Beiträge müssen dem Kreisverband oder einem Ortsverband zugeordnet werden. ';
    echo '<a href="' . esc_url( $url ) . '" class="button button-primary">Jetzt zuordnen</a>';
    echo '</p></div>';
}
add_action( 'admin_notices', 'gk_zuordnung_migration_notice' );


/**
 * Show admin notice to create nav menus for all zuordnungen.
 */
function gk_nav_menu_migration_notice() {
    if ( get_option( 'gk_nav_menus_migrated' ) ) {
        return;
    }
    // Only show after zuordnung migration is done.
    if ( ! get_option( 'gk_zuordnung_migrated' ) ) {
        return;
    }
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['gk_run_nav_menu_migration'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'gk_nav_menu_migrate' ) ) {
        $result = gk_migrate_create_nav_menus();
        update_option( 'gk_nav_menus_migrated', true );
        echo '<div class="notice notice-success is-dismissible"><p>';
        echo '<strong>Men&uuml;-Migration abgeschlossen:</strong> ';
        echo esc_html( $result['created'] ?? 0 ) . ' Men&uuml;s erstellt, ';
        echo esc_html( $result['skipped'] ?? 0 ) . ' &uuml;bersprungen.';
        echo '</p></div>';
        return;
    }

    $url = wp_nonce_url( admin_url( '?gk_run_nav_menu_migration=1' ), 'gk_nav_menu_migrate' );
    echo '<div class="notice notice-warning"><p>';
    echo '<strong>Kreisverband-Theme:</strong> F&uuml;r jeden Bereich kann automatisch ein Hauptmen&uuml; aus den vorhandenen Seiten erstellt werden. ';
    echo '<a href="' . esc_url( $url ) . '" class="button button-primary">Men&uuml;s jetzt erstellen</a>';
    echo '</p></div>';
}
add_action( 'admin_notices', 'gk_nav_menu_migration_notice' );


// ── Navigation Menu Helper ──────────────────────────────────────────────────

/**
 * One-time migration: create a header nav menu for each zuordnung term.
 *
 * For each zuordnung that has no menu assigned to its header location yet,
 * creates a menu containing all published pages (excluding the startpage)
 * as a hierarchical tree matching the page parent structure.
 *
 * Run via WP-CLI: wp eval 'gk_migrate_create_nav_menus();'
 */
function gk_migrate_create_nav_menus() {
    $terms = get_terms( array(
        'taxonomy'   => 'gk_zuordnung',
        'hide_empty' => false,
    ) );

    if ( is_wp_error( $terms ) ) {
        return array( 'error' => 'Could not load zuordnung terms.' );
    }

    $created   = 0;
    $skipped   = 0;
    $locations = get_nav_menu_locations();

    foreach ( $terms as $term ) {
        $location    = 'nav-' . $term->slug;
        $homepage_id = (int) get_term_meta( $term->term_id, '_gk_homepage_id', true );

        // Skip if the location already has a menu.
        if ( ! empty( $locations[ $location ] ) ) {
            $skipped++;
            continue;
        }

        // Get all published pages for this zuordnung.
        $pages = get_posts( array(
            'post_type'      => 'page',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'tax_query'      => array( array(
                'taxonomy' => 'gk_zuordnung',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ) ),
        ) );

        if ( empty( $pages ) ) continue;

        // Collect page IDs (without the homepage).
        $page_ids  = array();
        $by_parent = array();

        foreach ( $pages as $page ) {
            if ( $page->ID === $homepage_id ) continue;
            $page_ids[] = $page->ID;
        }

        if ( empty( $page_ids ) ) continue;

        // Group pages by effective parent.
        // If a page's parent is the homepage or outside this zuordnung, treat it as top-level (parent = 0).
        foreach ( $pages as $page ) {
            if ( $page->ID === $homepage_id ) continue;

            $parent = $page->post_parent;
            if ( $parent === $homepage_id || ( $parent && ! in_array( $parent, $page_ids, true ) ) ) {
                $parent = 0;
            }
            $by_parent[ $parent ][] = $page;
        }

        $top_level = $by_parent[0] ?? array();
        if ( empty( $top_level ) ) continue;

        // Don't overwrite an existing menu with the same name.
        $menu_name     = $term->name;
        $existing_menu = wp_get_nav_menu_object( $menu_name );
        if ( $existing_menu ) {
            $locations[ $location ] = $existing_menu->term_id;
            $skipped++;
            continue;
        }

        // Create the nav menu and add pages recursively.
        $menu_id = wp_create_nav_menu( $menu_name );
        if ( is_wp_error( $menu_id ) ) continue;

        $position = 0;
        gk_add_pages_to_nav_menu( $menu_id, $top_level, $by_parent, 0, $position );

        $locations[ $location ] = $menu_id;
        $created++;
    }

    set_theme_mod( 'nav_menu_locations', $locations );

    return array( 'created' => $created, 'skipped' => $skipped );
}

/**
 * Recursively add pages as items to a nav menu, preserving hierarchy.
 *
 * @param int        $menu_id        Nav menu term ID.
 * @param WP_Post[]  $pages          Pages to add at this level.
 * @param array      $by_parent      Map of parent_id => child pages.
 * @param int        $parent_item_id Parent menu item ID (0 for top-level).
 * @param int        $position       Running position counter (by reference).
 */
function gk_add_pages_to_nav_menu( $menu_id, $pages, &$by_parent, $parent_item_id, &$position ) {
    foreach ( $pages as $page ) {
        $position++;
        $item_id = wp_update_nav_menu_item( $menu_id, 0, array(
            'menu-item-object-id'  => $page->ID,
            'menu-item-object'     => 'page',
            'menu-item-type'       => 'post_type',
            'menu-item-parent-id'  => $parent_item_id,
            'menu-item-status'     => 'publish',
            'menu-item-position'   => $position,
        ) );

        if ( ! is_wp_error( $item_id ) && ! empty( $by_parent[ $page->ID ] ) ) {
            gk_add_pages_to_nav_menu( $menu_id, $by_parent[ $page->ID ], $by_parent, $item_id, $position );
        }
    }
}


// ── Media Library Restriction ────────────────────────────────────────────────

/**
 * Limit non-admin users to only see their own media uploads.
 */
function gk_limit_media_library( $query ) {
    $user_id = get_current_user_id();
    if ( $user_id && ! current_user_can( 'activate_plugins' ) && ! current_user_can( 'edit_others_posts' ) ) {
        $query['author'] = $user_id;
    }
    return $query;
}
add_filter( 'ajax_query_attachments_args', 'gk_limit_media_library' );
