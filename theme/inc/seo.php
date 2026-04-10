<?php
/**
 * Built-in SEO
 *
 * Lightweight SEO without a plugin:
 *   - Meta descriptions from excerpts
 *   - Open Graph meta tags (Facebook, Twitter cards) with image dimensions
 *   - Canonical URLs (all page types)
 *   - Robots meta (noindex for search, thin archives)
 *   - Clean <title> tag
 *   - JSON-LD structured data (Organization, Article, Event, BreadcrumbList, WebSite)
 *   - Lazy loading for images
 *   - Resource hints (preconnect, dns-prefetch)
 *   - Pagination rel links (prev/next)
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Open Graph, Twitter Cards & Meta ───────────────────────────────────────

function gk_seo_meta_tags() {
    $kv_name = function_exists( 'gk_kv_name' ) ? gk_kv_name() : get_bloginfo( 'name' );
    $site_name = $kv_name ?: get_bloginfo( 'name' );

    // Defaults
    $og_title       = get_bloginfo( 'name' );
    $og_description = get_bloginfo( 'description' );
    $og_url         = home_url( '/' );
    $og_type        = 'website';
    $og_image       = '';
    $og_image_w     = 0;
    $og_image_h     = 0;

    if ( is_singular() ) {
        global $post;
        $og_title = get_the_title();
        $og_url   = get_permalink();
        $og_type  = is_singular( 'post' ) ? 'article' : 'website';

        // Description from excerpt or content
        if ( has_excerpt( $post->ID ) ) {
            $og_description = get_the_excerpt();
        } else {
            $og_description = wp_trim_words( strip_shortcodes( $post->post_content ), 30, '...' );
        }

        // Featured image with dimensions
        if ( has_post_thumbnail( $post->ID ) ) {
            $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
            if ( $img ) {
                $og_image   = $img[0];
                $og_image_w = $img[1];
                $og_image_h = $img[2];
            }
        }

        // Fallback: custom logo or default theme image
        if ( ! $og_image ) {
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            if ( $custom_logo_id ) {
                $logo_img = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                if ( $logo_img ) {
                    $og_image   = $logo_img[0];
                    $og_image_w = $logo_img[1];
                    $og_image_h = $logo_img[2];
                }
            }
        }
        if ( ! $og_image && defined( 'GK_IMAGE_DIR' ) && file_exists( get_template_directory() . '/lib/images/og-default.png' ) ) {
            $og_image = GK_IMAGE_DIR . 'og-default.png';
        }
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        $og_title = $term->name;
        $og_url   = get_term_link( $term );
        if ( $term->description ) {
            $og_description = $term->description;
        }
    } elseif ( is_author() ) {
        $author = get_queried_object();
        $og_title = $author->display_name;
        $og_url   = get_author_posts_url( $author->ID );
    } elseif ( is_post_type_archive() ) {
        $og_title = post_type_archive_title( '', false );
        $og_url   = get_post_type_archive_link( get_queried_object()->name );
    }

    $og_description = wp_strip_all_tags( $og_description );
    $og_description = mb_substr( $og_description, 0, 160 );

    // Output
    echo "\n<!-- Neurg Kreisverband SEO -->\n";

    // Meta description
    echo '<meta name="description" content="' . esc_attr( $og_description ) . '" />' . "\n";

    // Robots
    if ( is_search() ) {
        echo '<meta name="robots" content="noindex, follow" />' . "\n";
    } elseif ( is_paged() && ( is_category() || is_tag() || is_tax() || is_archive() ) ) {
        echo '<meta name="robots" content="noindex, follow" />' . "\n";
    }

    // Open Graph
    echo '<meta property="og:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url( $og_url ) . '" />' . "\n";
    echo '<meta property="og:type" content="' . esc_attr( $og_type ) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr( $site_name ) . '" />' . "\n";
    echo '<meta property="og:locale" content="de_DE" />' . "\n";

    if ( $og_image ) {
        echo '<meta property="og:image" content="' . esc_url( $og_image ) . '" />' . "\n";
        if ( $og_image_w && $og_image_h ) {
            echo '<meta property="og:image:width" content="' . (int) $og_image_w . '" />' . "\n";
            echo '<meta property="og:image:height" content="' . (int) $og_image_h . '" />' . "\n";
        }
        $og_image_alt = is_singular() ? get_the_title() : $og_title;
        echo '<meta property="og:image:alt" content="' . esc_attr( $og_image_alt ) . '" />' . "\n";
    }

    // Article-specific OG tags
    if ( is_singular( 'post' ) ) {
        global $post;
        $article_author = function_exists( 'gk_kv_name' ) ? gk_kv_name() : get_bloginfo( 'name' );
        echo '<meta property="article:author" content="' . esc_attr( $article_author ) . '" />' . "\n";
        echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '" />' . "\n";
        echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '" />' . "\n";
        $tags = get_the_tags();
        if ( $tags ) {
            foreach ( $tags as $tag ) {
                echo '<meta property="article:tag" content="' . esc_attr( $tag->name ) . '" />' . "\n";
            }
        }
        $cats = get_the_category();
        if ( $cats ) {
            echo '<meta property="article:section" content="' . esc_attr( $cats[0]->name ) . '" />' . "\n";
        }
    }

    // Twitter Card
    echo '<meta name="twitter:card" content="' . ( $og_image ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr( $og_title ) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr( $og_description ) . '" />' . "\n";
    if ( $og_image ) {
        echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '" />' . "\n";
    }

    // Twitter site handle from KV social settings
    $kv_info    = function_exists( 'gk_get_kv_info' ) ? gk_get_kv_info() : array();
    $twitter_handle = '';
    if ( ! empty( $kv_info['social_x'] ) ) {
        $twitter_handle = '@' . ltrim( $kv_info['social_x'], '@' );
    } elseif ( ! empty( $kv_info['social_twitter'] ) ) {
        $twitter_handle = '@' . ltrim( $kv_info['social_twitter'], '@' );
    }
    if ( $twitter_handle ) {
        echo '<meta name="twitter:site" content="' . esc_attr( $twitter_handle ) . '" />' . "\n";
    }

    // Canonical URL — all page types
    $canonical = '';
    if ( is_singular() ) {
        $canonical = get_permalink();
    } elseif ( is_front_page() ) {
        $canonical = home_url( '/' );
    } elseif ( is_category() || is_tag() || is_tax() ) {
        $canonical = get_term_link( get_queried_object() );
    } elseif ( is_post_type_archive() ) {
        $canonical = get_post_type_archive_link( get_queried_object()->name );
    } elseif ( is_author() ) {
        $canonical = get_author_posts_url( get_queried_object_id() );
    }
    if ( $canonical && ! is_wp_error( $canonical ) ) {
        echo '<link rel="canonical" href="' . esc_url( $canonical ) . '" />' . "\n";
    }

    // Pagination rel links
    if ( is_archive() || is_home() || is_search() ) {
        global $wp_query;
        $paged = max( 1, get_query_var( 'paged' ) );
        $max   = $wp_query->max_num_pages;
        if ( $paged > 1 ) {
            echo '<link rel="prev" href="' . esc_url( get_pagenum_link( $paged - 1 ) ) . '" />' . "\n";
        }
        if ( $paged < $max ) {
            echo '<link rel="next" href="' . esc_url( get_pagenum_link( $paged + 1 ) ) . '" />' . "\n";
        }
    }

    echo "<!-- /Neurg Kreisverband SEO -->\n";
}
add_action( 'wp_head', 'gk_seo_meta_tags', 1 );


// ── JSON-LD: Organization (front page) ─────────────────────────────────────

function gk_seo_jsonld_organization() {
    if ( ! is_front_page() ) return;

    $kv      = function_exists( 'gk_get_kv_info' ) ? gk_get_kv_info() : array();
    $name    = ! empty( $kv['name'] ) ? $kv['name'] : get_bloginfo( 'name' );
    $email   = ! empty( $kv['email'] ) ? $kv['email'] : '';
    $phone   = ! empty( $kv['phone'] ) ? $kv['phone'] : '';
    $url     = ! empty( $kv['website'] ) ? $kv['website'] : home_url( '/' );
    $address = ! empty( $kv['address'] ) ? $kv['address'] : '';

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $name,
        'url'      => $url,
    );

    if ( $email ) $schema['email'] = $email;
    if ( $phone ) $schema['telephone'] = $phone;

    // Address from setup wizard (free-text, output as one block)
    if ( $address ) {
        $schema['address'] = array(
            '@type'          => 'PostalAddress',
            'streetAddress'  => $address,
            'addressCountry' => 'DE',
        );
    }

    // Social profiles
    $same_as = array();
    if ( ! empty( $kv['social_facebook'] ) )  $same_as[] = $kv['social_facebook'];
    if ( ! empty( $kv['social_instagram'] ) ) $same_as[] = 'https://instagram.com/' . $kv['social_instagram'];
    if ( ! empty( $kv['social_mastodon'] ) )  $same_as[] = $kv['social_mastodon'];
    if ( ! empty( $kv['social_youtube'] ) )   $same_as[] = $kv['social_youtube'];
    if ( ! empty( $kv['social_bluesky'] ) )   $same_as[] = 'https://bsky.app/profile/' . $kv['social_bluesky'];
    if ( ! empty( $same_as ) ) $schema['sameAs'] = $same_as;

    // Logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
        if ( $logo_url ) $schema['logo'] = $logo_url;
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_organization', 2 );


// ── JSON-LD: WebSite + SearchAction (front page) ──────────────────────────

function gk_seo_jsonld_website() {
    if ( ! is_front_page() ) return;

    $kv_name = function_exists( 'gk_kv_name' ) ? gk_kv_name() : '';
    $name    = $kv_name ?: get_bloginfo( 'name' );

    $schema = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'WebSite',
        'name'          => $name,
        'url'           => home_url( '/' ),
        'inLanguage'    => 'de-DE',
        'potentialAction' => array(
            '@type'       => 'SearchAction',
            'target'      => array(
                '@type'        => 'EntryPoint',
                'urlTemplate'  => home_url( '/?s={search_term_string}' ),
            ),
            'query-input' => 'required name=search_term_string',
        ),
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_website', 2 );


// ── JSON-LD: Article (single posts) ────────────────────────────────────────

function gk_seo_jsonld_article() {
    if ( ! is_singular( 'post' ) ) return;

    global $post;

    $schema = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => get_the_title(),
        'url'           => get_permalink(),
        'datePublished' => get_the_date( 'c' ),
        'dateModified'  => get_the_modified_date( 'c' ),
        'inLanguage'    => 'de-DE',
        'mainEntityOfPage' => array(
            '@type' => 'WebPage',
            '@id'   => get_permalink(),
        ),
    );

    // Description
    if ( has_excerpt( $post->ID ) ) {
        $schema['description'] = wp_strip_all_tags( get_the_excerpt() );
    } else {
        $schema['description'] = wp_strip_all_tags( wp_trim_words( strip_shortcodes( $post->post_content ), 30, '...' ) );
    }

    // Author as the organization
    $kv_name = function_exists( 'gk_kv_name' ) ? gk_kv_name() : get_bloginfo( 'name' );
    $schema['author'] = array(
        '@type' => 'Organization',
        'name'  => $kv_name ?: get_bloginfo( 'name' ),
        'url'   => home_url( '/' ),
    );
    $schema['publisher'] = $schema['author'];

    // Publisher logo
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $logo_url = wp_get_attachment_image_url( $custom_logo_id, 'full' );
        if ( $logo_url ) {
            $schema['publisher']['logo'] = array(
                '@type' => 'ImageObject',
                'url'   => $logo_url,
            );
        }
    }

    // Featured image
    if ( has_post_thumbnail( $post->ID ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
        if ( $img ) {
            $schema['image'] = array(
                '@type'  => 'ImageObject',
                'url'    => $img[0],
                'width'  => $img[1],
                'height' => $img[2],
            );
        }
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_article', 3 );


// ── JSON-LD: Event ─────────────────────────────────────────────────────────

function gk_seo_jsonld_event() {
    if ( ! is_singular( 'gk_event' ) ) return;

    global $post;
    $start_date = get_post_meta( $post->ID, 'gk_event_start_date', true );
    $start_time = get_post_meta( $post->ID, 'gk_event_start_time', true );
    $end_date   = get_post_meta( $post->ID, 'gk_event_end_date', true );
    $end_time   = get_post_meta( $post->ID, 'gk_event_end_time', true );
    $location   = get_post_meta( $post->ID, 'gk_event_location', true );
    $address    = get_post_meta( $post->ID, 'gk_event_address', true );

    if ( ! $start_date ) return;

    $schema = array(
        '@context'  => 'https://schema.org',
        '@type'     => 'Event',
        'name'      => get_the_title(),
        'startDate' => $start_date . ( $start_time ? 'T' . $start_time : '' ),
        'url'       => get_permalink(),
    );

    if ( $end_date ) {
        $schema['endDate'] = $end_date . ( $end_time ? 'T' . $end_time : '' );
    }

    if ( has_excerpt() ) {
        $schema['description'] = wp_strip_all_tags( get_the_excerpt() );
    }

    // Featured image
    if ( has_post_thumbnail( $post->ID ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
        if ( $img ) {
            $schema['image'] = $img[0];
        }
    }

    if ( $location || $address ) {
        $schema['location'] = array(
            '@type'   => 'Place',
            'name'    => $location ?: $address,
        );
        if ( $address ) {
            $schema['location']['address'] = array(
                '@type'          => 'PostalAddress',
                'streetAddress'  => $address,
                'addressCountry' => 'DE',
            );
        }
    }

    $kv_name = function_exists( 'gk_kv_name' ) ? gk_kv_name() : get_bloginfo( 'name' );
    if ( $kv_name ) {
        $schema['organizer'] = array(
            '@type' => 'Organization',
            'name'  => $kv_name,
            'url'   => home_url( '/' ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_event', 3 );


// ── JSON-LD: Person (single person CPT) ───────────────────────────────────

function gk_seo_jsonld_person() {
    if ( ! is_singular( 'person' ) ) return;

    global $post;

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => 'Person',
        'name'     => get_the_title(),
        'url'      => get_permalink(),
    );

    // Description from excerpt or content
    if ( has_excerpt( $post->ID ) ) {
        $schema['description'] = wp_strip_all_tags( get_the_excerpt() );
    } else {
        $desc = wp_strip_all_tags( wp_trim_words( strip_shortcodes( $post->post_content ), 30, '...' ) );
        if ( $desc ) {
            $schema['description'] = $desc;
        }
    }

    // Featured image
    if ( has_post_thumbnail( $post->ID ) ) {
        $img = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
        if ( $img ) {
            $schema['image'] = $img[0];
        }
    }

    // Job title from post meta
    $job_title = get_post_meta( $post->ID, 'gk_person_job_title', true );
    if ( ! $job_title ) {
        $job_title = get_post_meta( $post->ID, 'job_title', true );
    }
    if ( $job_title ) {
        $schema['jobTitle'] = $job_title;
    }

    // Affiliation: the organization
    $kv_name = function_exists( 'gk_kv_name' ) ? gk_kv_name() : get_bloginfo( 'name' );
    if ( $kv_name ) {
        $schema['affiliation'] = array(
            '@type' => 'Organization',
            'name'  => $kv_name,
            'url'   => home_url( '/' ),
        );
    }

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_person', 3 );


// ── JSON-LD: BreadcrumbList ────────────────────────────────────────────────

function gk_seo_jsonld_breadcrumbs() {
    if ( is_front_page() || is_admin() ) return;

    $items = array();
    $pos   = 1;

    // Home is always first
    $items[] = array(
        '@type'    => 'ListItem',
        'position' => $pos++,
        'name'     => 'Start',
        'item'     => home_url( '/' ),
    );

    if ( is_singular( 'post' ) ) {
        $cats = get_the_category();
        if ( $cats ) {
            $cat = $cats[0];
            // Walk up the category hierarchy
            $ancestors = array();
            $current   = $cat;
            while ( $current->parent ) {
                $parent = get_category( $current->parent );
                if ( ! $parent || is_wp_error( $parent ) ) break;
                $ancestors[] = $parent;
                $current     = $parent;
            }
            foreach ( array_reverse( $ancestors ) as $a ) {
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $a->name,
                    'item'     => get_category_link( $a->term_id ),
                );
            }
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => $cat->name,
                'item'     => get_category_link( $cat->term_id ),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        );

    } elseif ( is_singular( 'gk_event' ) ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Termine',
            'item'     => get_post_type_archive_link( 'gk_event' ),
        );
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        );

    } elseif ( is_singular( 'person' ) ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        );

    } elseif ( is_singular( 'page' ) ) {
        global $post;
        $ancestors = get_post_ancestors( $post );
        foreach ( array_reverse( $ancestors ) as $ancestor_id ) {
            $items[] = array(
                '@type'    => 'ListItem',
                'position' => $pos++,
                'name'     => get_the_title( $ancestor_id ),
                'item'     => get_permalink( $ancestor_id ),
            );
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => get_the_title(),
        );

    } elseif ( is_category() || is_tag() || is_tax() ) {
        $term = get_queried_object();
        if ( is_category() && $term->parent ) {
            $ancestors = array();
            $current   = $term;
            while ( $current->parent ) {
                $parent = get_category( $current->parent );
                if ( ! $parent || is_wp_error( $parent ) ) break;
                $ancestors[] = $parent;
                $current     = $parent;
            }
            foreach ( array_reverse( $ancestors ) as $a ) {
                $items[] = array(
                    '@type'    => 'ListItem',
                    'position' => $pos++,
                    'name'     => $a->name,
                    'item'     => get_category_link( $a->term_id ),
                );
            }
        }
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => $term->name,
        );

    } elseif ( is_post_type_archive() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => post_type_archive_title( '', false ),
        );

    } elseif ( is_search() ) {
        $items[] = array(
            '@type'    => 'ListItem',
            'position' => $pos++,
            'name'     => 'Suche: ' . get_search_query(),
        );
    }

    if ( count( $items ) < 2 ) return;

    $schema = array(
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    );

    echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . '</script>' . "\n";
}
add_action( 'wp_head', 'gk_seo_jsonld_breadcrumbs', 4 );


// ── Clean Title Tag ────────────────────────────────────────────────────────

function gk_seo_document_title_parts( $title ) {
    $kv_short = function_exists( 'gk_kv_short_name' ) ? gk_kv_short_name() : '';
    if ( $kv_short ) {
        $title['site'] = $kv_short;
    }
    return $title;
}
add_filter( 'document_title_parts', 'gk_seo_document_title_parts' );


// ── Lazy Loading ───────────────────────────────────────────────────────────

function gk_seo_lazy_load_images( $attr, $attachment, $size ) {
    if ( ! is_admin() ) {
        if ( ! isset( $attr['loading'] ) ) {
            $attr['loading'] = 'lazy';
        }
        if ( ! isset( $attr['decoding'] ) ) {
            $attr['decoding'] = 'async';
        }
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'gk_seo_lazy_load_images', 10, 3 );


// ── Resource Hints ─────────────────────────────────────────────────────────

function gk_seo_resource_hints( $urls, $relation_type ) {
    if ( 'dns-prefetch' === $relation_type ) {
        $urls[] = 'cdnjs.cloudflare.com';
    }
    if ( 'preconnect' === $relation_type ) {
        $urls[] = array(
            'href'        => 'https://cdnjs.cloudflare.com',
            'crossorigin' => 'anonymous',
        );
    }
    return $urls;
}
add_filter( 'wp_resource_hints', 'gk_seo_resource_hints', 10, 2 );


// ── Cleanup WP Head ────────────────────────────────────────────────────────

// Remove WP version meta tag
remove_action( 'wp_head', 'wp_generator' );

// Remove WordPress canonical (we output our own)
remove_action( 'wp_head', 'rel_canonical' );

// Remove shortlink
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
