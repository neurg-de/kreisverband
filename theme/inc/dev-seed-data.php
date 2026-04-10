<?php
/**
 * Dev Seed Data
 *
 * Seeds a complete demo dataset for local development:
 * pages, posts, persons, events, taxonomies, menus, and options.
 * Only active when WP_DEBUG is true.
 *
 * Usage:
 *   wp eval 'gk_seed_all();'
 *   Or visit: /wp-admin/?gk_seed=1 (as admin)
 *   Or: make seed (via WP-CLI in Docker)
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Master Seed Function ───────────────────────────────────────────────────

/**
 * Seed all demo data. Idempotent — safe to run multiple times.
 *
 * @return array Summary of seeded items.
 */
function gk_seed_all() {
    $result = array();

    $result['taxonomies'] = gk_seed_taxonomies();
    $result['pages']      = gk_seed_pages();
    $result['posts']      = gk_seed_posts();
    $result['persons']    = gk_seed_persons();
    $result['events']     = gk_seed_events();
    $result['menus']      = gk_seed_menus();
    $result['social']     = gk_seed_social_data();
    $result['options']    = gk_seed_options();

    flush_rewrite_rules();

    return $result;
}


// ── Taxonomies ─────────────────────────────────────────────────────────────

function gk_seed_taxonomies() {
    $seeded = array();

    // Abteilungen
    $abteilungen = array(
        'kreisvorstand'   => 'Kreisvorstand',
        'stadtrat'        => 'Stadtratsfraktion',
        'kreistag'        => 'Kreistagsfraktion',
        'ortsvorstand'    => 'Ortsvorstand',
        'gruene-jugend'   => 'GRÜNE Jugend',
        'ag-energie'      => 'AG Energie & Klima',
    );

    foreach ( $abteilungen as $slug => $name ) {
        if ( ! term_exists( $slug, 'abteilung' ) ) {
            wp_insert_term( $name, 'abteilung', array( 'slug' => $slug ) );
            $seeded[] = "Abteilung: $name";
        }
    }

    // Zuordnungen (KV is created automatically, add OVs)
    $ovs = array(
        'ov-musterstadt'  => array( 'name' => 'OV Musterstadt',  'header' => 'Musterstadt' ),
        'ov-neuburg'      => array( 'name' => 'OV Neuburg',      'header' => 'Neuburg' ),
        'ov-schrobenhausen' => array( 'name' => 'OV Schrobenhausen', 'header' => 'Schrobenhausen' ),
    );

    foreach ( $ovs as $slug => $data ) {
        if ( ! term_exists( $slug, 'gk_zuordnung' ) ) {
            $result = wp_insert_term( $data['name'], 'gk_zuordnung', array(
                'slug'        => $slug,
                'description' => 'Ortsverband ' . $data['header'],
            ) );
            if ( ! is_wp_error( $result ) ) {
                update_term_meta( $result['term_id'], '_gk_ov_type', 'ov' );
                update_term_meta( $result['term_id'], '_gk_ov_header', $data['header'] );
                $seeded[] = 'Zuordnung: ' . $data['name'];
            }
        }
    }

    // Event categories
    $event_cats = array(
        'sitzung'        => 'Sitzung',
        'veranstaltung'  => 'Veranstaltung',
        'aktion'         => 'Aktion',
        'parteitag'      => 'Parteitag',
        'stammtisch'     => 'Stammtisch',
    );

    foreach ( $event_cats as $slug => $name ) {
        if ( ! term_exists( $slug, 'event_kategorie' ) ) {
            wp_insert_term( $name, 'event_kategorie', array( 'slug' => $slug ) );
            $seeded[] = "Event-Kategorie: $name";
        }
    }

    // Post categories
    $categories = array(
        'presse'         => 'Pressemitteilungen',
        'aktuelles'      => 'Aktuelles',
        'kommunalpolitik' => 'Kommunalpolitik',
        'umwelt-klima'   => 'Umwelt & Klima',
    );

    foreach ( $categories as $slug => $name ) {
        if ( ! term_exists( $slug, 'category' ) ) {
            wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
            $seeded[] = "Kategorie: $name";
        }
    }

    return $seeded;
}


// ── Pages ──────────────────────────────────────────────────────────────────

function gk_seed_pages() {
    $seeded = array();

    $pages = array(
        array(
            'title'    => 'Startseite',
            'slug'     => 'startseite',
            'template' => 'page-home.php',
            'content'  => 'Willkommen beim GRÜNEN Kreisverband Musterkreis.',
        ),
        array(
            'title'    => 'Blog',
            'slug'     => 'blog',
            'template' => 'page-blog.php',
            'content'  => '',
        ),
        array(
            'title'    => 'Termine',
            'slug'     => 'termine',
            'template' => 'page-termine.php',
            'content'  => '[termine anzahl="20"]',
        ),
        array(
            'title'    => 'Impressum',
            'slug'     => 'impressum',
            'template' => '',
            'content'  => "<h2>Angaben gemäß § 5 TMG</h2>\n<p>BÜNDNIS 90/DIE GRÜNEN Kreisverband Musterkreis<br>\nMusterstraße 1<br>\n12345 Musterstadt</p>\n\n<h2>Kontakt</h2>\n<p>Telefon: 01234 567890<br>\nE-Mail: info@gruene-musterkreis.de</p>\n\n<h2>Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>\n<p>Maria Musterfrau<br>\nMusterstraße 1<br>\n12345 Musterstadt</p>",
        ),
        array(
            'title'    => 'Datenschutz',
            'slug'     => 'datenschutz',
            'template' => '',
            'content'  => "<h2>Datenschutzerklärung</h2>\n<p>Wir nehmen den Schutz Ihrer persönlichen Daten ernst. Diese Datenschutzerklärung informiert Sie über Art, Umfang und Zweck der Verarbeitung personenbezogener Daten auf unserer Website.</p>\n\n<h3>Verantwortliche Stelle</h3>\n<p>BÜNDNIS 90/DIE GRÜNEN Kreisverband Musterkreis<br>\nMusterstraße 1, 12345 Musterstadt<br>\ninfo@gruene-musterkreis.de</p>\n\n<h3>Hosting</h3>\n<p>Unsere Website wird bei einem deutschen Hosting-Anbieter betrieben. Die Server stehen in Deutschland.</p>",
        ),
        array(
            'title'    => 'Über uns',
            'slug'     => 'ueber-uns',
            'template' => 'page-fullpage.php',
            'content'  => "<p>Die GRÜNEN im Kreisverband Musterkreis setzen sich für eine nachhaltige, gerechte und weltoffene Kommune ein. Seit unserer Gründung engagieren wir uns vor Ort für Umweltschutz, soziale Gerechtigkeit und eine lebendige Demokratie.</p>\n\n<h2>Unsere Schwerpunkte</h2>\n<ul>\n<li>Klimaschutz und Energiewende vor Ort</li>\n<li>Nachhaltige Mobilität</li>\n<li>Bezahlbares Wohnen</li>\n<li>Bildung und Chancengleichheit</li>\n<li>Integration und Vielfalt</li>\n</ul>",
        ),
        array(
            'title'    => 'Kreisvorstand',
            'slug'     => 'kreisvorstand',
            'template' => '',
            'content'  => "[vorstand abteilung=\"kreisvorstand\"]",
        ),
        array(
            'title'    => 'Kreistagsfraktion',
            'slug'     => 'kreistagsfraktion',
            'template' => '',
            'content'  => "[team abteilung=\"kreistag\"]",
        ),
        array(
            'title'    => 'Ortsverbände',
            'slug'     => 'ortsverbaende',
            'template' => '',
            'content'  => "[gliederungen]",
        ),
        array(
            'title'    => 'Spenden',
            'slug'     => 'spenden',
            'template' => '',
            'content'  => "<h2>Unterstützen Sie uns!</h2>\n<p>Ihre Spende hilft uns, GRÜNE Politik vor Ort zu gestalten.</p>\n\n<h3>Bankverbindung</h3>\n<p>BÜNDNIS 90/DIE GRÜNEN KV Musterkreis<br>\nIBAN: DE12 3456 7890 1234 5678 90<br>\nBIC: DEUTDEDBXXX<br>\nVerwendungszweck: Spende KV</p>",
        ),
        array(
            'title'    => 'Kontakt',
            'slug'     => 'kontakt',
            'template' => '',
            'content'  => "<h2>Kontakt</h2>\n<p>Schreiben Sie uns – wir freuen uns auf Ihre Nachricht!</p>\n\n[kontaktformular]",
        ),
        array(
            'title'    => 'Kitchen Sink',
            'slug'     => 'kitchen-sink',
            'template' => 'page-kitchen-sink.php',
            'content'  => 'Designsystem-Referenz mit allen UI-Komponenten.',
        ),
    );

    foreach ( $pages as $page ) {
        if ( get_page_by_path( $page['slug'] ) ) {
            continue;
        }

        $page_id = wp_insert_post( array(
            'post_type'    => 'page',
            'post_title'   => $page['title'],
            'post_name'    => $page['slug'],
            'post_content' => $page['content'],
            'post_status'  => 'publish',
        ) );

        if ( $page_id && ! is_wp_error( $page_id ) ) {
            if ( ! empty( $page['template'] ) ) {
                update_post_meta( $page_id, '_wp_page_template', $page['template'] );
            }
            $seeded[] = $page['title'];
        }
    }

    // Set front page and posts page.
    $front = get_page_by_path( 'startseite' );
    $blog  = get_page_by_path( 'blog' );
    if ( $front ) {
        update_option( 'show_on_front', 'page' );
        update_option( 'page_on_front', $front->ID );
    }
    if ( $blog ) {
        update_option( 'page_for_posts', $blog->ID );
    }

    return $seeded;
}


// ── Blog Posts ──────────────────────────────────────────────────────────────

function gk_seed_posts() {
    $seeded = array();

    $posts = array(
        array(
            'title'    => 'GRÜNE fordern mehr Radwege im Kreisgebiet',
            'content'  => '<p>Der Kreisverband fordert den Ausbau des Radwegenetzes im gesamten Kreisgebiet. „Sichere Radwege sind die Grundlage für eine echte Verkehrswende", so unsere Sprecherin Maria Musterfrau.</p>' . "\n\n" . '<p>In einem Antrag an den Kreistag fordern wir die Erstellung eines kreisweiten Radverkehrskonzepts mit konkreten Ausbauplänen bis 2028.</p>',
            'category' => 'presse',
            'date'     => '-3 days',
        ),
        array(
            'title'    => 'Erfolgreiche Baumpflanzaktion am Stadtpark',
            'content'  => "<p>Gemeinsam mit engagierten Bürgerinnen und Bürgern haben wir am vergangenen Samstag 50 neue Bäume im Stadtpark gepflanzt. Ein tolles Zeichen für den Klimaschutz vor Ort!</p>\n\n<p>Vielen Dank an alle Helferinnen und Helfer, die trotz des kühlen Wetters mit angepackt haben.</p>",
            'category' => 'aktuelles',
            'date'     => '-7 days',
        ),
        array(
            'title'    => 'Kreistag beschließt Klimaschutzkonzept',
            'content'  => "<p>Nach langen Verhandlungen hat der Kreistag unser Klimaschutzkonzept verabschiedet. Damit verpflichtet sich der Landkreis, bis 2035 klimaneutral zu werden.</p>\n\n<p>Das Konzept umfasst Maßnahmen in den Bereichen Energie, Verkehr, Gebäude und Landwirtschaft. Ein großer Erfolg für die GRÜNE Kreistagsfraktion!</p>",
            'category' => 'kommunalpolitik',
            'date'     => '-14 days',
        ),
        array(
            'title'    => 'Einladung zum Grünen Stammtisch',
            'content'  => "<p>Jeden ersten Donnerstag im Monat laden wir zum Grünen Stammtisch ein. In lockerer Atmosphäre diskutieren wir aktuelle Themen und lernen uns kennen.</p>\n\n<p>Der nächste Stammtisch findet im Gasthaus Zum Löwen statt. Neue Gesichter sind herzlich willkommen!</p>",
            'category' => 'aktuelles',
            'date'     => '-21 days',
        ),
        array(
            'title'    => 'PM: Landkreis muss bei Solarenergie vorangehen',
            'content'  => '<p>In einer Pressemitteilung fordert die GRÜNE Kreistagsfraktion den Landkreis auf, alle geeigneten kreiseigenen Dächer mit Photovoltaikanlagen auszustatten.</p>' . "\n\n" . '<p>„Wir können nicht Klimaschutz predigen und gleichzeitig unsere eigenen Gebäude ohne Solaranlagen lassen", kritisiert Fraktionsvorsitzende Claudia Sonnenschein.</p>',
            'category' => 'presse',
            'date'     => '-30 days',
        ),
        array(
            'title'    => 'Besuch der Biogasanlage in Oberdorf',
            'content'  => "<p>Die AG Energie & Klima besuchte die neue Biogasanlage in Oberdorf. Betreiber Hans Feldmann zeigte uns, wie aus landwirtschaftlichen Reststoffen Strom und Wärme für 200 Haushalte erzeugt wird.</p>\n\n<p>Ein gelungenes Beispiel für dezentrale Energieversorgung im ländlichen Raum.</p>",
            'category' => 'umwelt-klima',
            'date'     => '-45 days',
        ),
    );

    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );

    foreach ( $posts as $post_data ) {
        $existing = get_posts( array( 'title' => $post_data['title'], 'post_type' => 'post', 'posts_per_page' => 1 ) );
        $existing = ! empty( $existing ) ? $existing[0] : null;
        if ( $existing ) {
            continue;
        }

        $post_id = wp_insert_post( array(
            'post_type'    => 'post',
            'post_title'   => $post_data['title'],
            'post_content' => $post_data['content'],
            'post_status'  => 'publish',
            'post_date'    => date( 'Y-m-d H:i:s', strtotime( $post_data['date'] ) ),
        ) );

        if ( $post_id && ! is_wp_error( $post_id ) ) {
            $cat = get_term_by( 'slug', $post_data['category'], 'category' );
            if ( $cat ) {
                wp_set_post_categories( $post_id, array( $cat->term_id ) );
            }
            if ( $kv_term ) {
                wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
            }
            $seeded[] = $post_data['title'];
        }
    }

    return $seeded;
}


// ── Persons ────────────────────────────────────────────────────────────────

function gk_seed_persons() {
    $seeded = array();

    $persons = array(
        array(
            'name'        => 'Maria Musterfrau',
            'content'     => 'Kreisvorstandssprecherin seit 2022. Engagiert für Klimaschutz und soziale Gerechtigkeit.',
            'abteilungen' => array( 'kreisvorstand' ),
            'abt_meta'    => array( 'kreisvorstand' => array( 'position' => '1', 'function' => 'Sprecherin', 'hidden' => false ) ),
            'contact'     => array(
                'kr8mb_pers_contact_email'    => 'maria@gruene-musterkreis.de',
                'kr8mb_pers_contact_insta'    => 'maria.musterfrau',
                'kr8mb_pers_contact_facebook' => 'https://www.facebook.com/maria.musterfrau',
            ),
        ),
        array(
            'name'        => 'Thomas Grünberg',
            'content'     => 'Kreisvorstandssprecher und Kreisrat. Schwerpunkte: Verkehrswende und ÖPNV.',
            'abteilungen' => array( 'kreisvorstand', 'kreistag' ),
            'abt_meta'    => array(
                'kreisvorstand' => array( 'position' => '2', 'function' => 'Sprecher', 'hidden' => false ),
                'kreistag'      => array( 'position' => '1', 'function' => 'Fraktionsvorsitzender', 'hidden' => false ),
            ),
            'contact'     => array(
                'kr8mb_pers_contact_email'   => 'thomas@gruene-musterkreis.de',
                'kr8mb_pers_contact_twitter' => 'ThomasGruenberg',
            ),
        ),
        array(
            'name'        => 'Claudia Sonnenschein',
            'content'     => 'Kreisrätin und Energieexpertin. Setzt sich für den Ausbau erneuerbarer Energien ein.',
            'abteilungen' => array( 'kreistag', 'ag-energie' ),
            'abt_meta'    => array(
                'kreistag'   => array( 'position' => '2', 'function' => 'Stv. Fraktionsvorsitzende', 'hidden' => false ),
                'ag-energie' => array( 'position' => '1', 'function' => 'Sprecherin', 'hidden' => false ),
            ),
            'contact'     => array(
                'kr8mb_pers_contact_email'    => 'claudia@gruene-musterkreis.de',
                'kr8mb_pers_contact_mastodon' => 'https://gruene.social/@claudia',
            ),
        ),
        array(
            'name'        => 'Stefan Waldmann',
            'content'     => 'Schatzmeister des Kreisverbands. Im Beruf Steuerberater.',
            'abteilungen' => array( 'kreisvorstand' ),
            'abt_meta'    => array( 'kreisvorstand' => array( 'position' => '3', 'function' => 'Schatzmeister', 'hidden' => false ) ),
            'contact'     => array(
                'kr8mb_pers_contact_email' => 'stefan@gruene-musterkreis.de',
            ),
        ),
        array(
            'name'        => 'Lisa Blumenfeld',
            'content'     => 'Beisitzerin im Kreisvorstand. Aktiv in der GRÜNEN Jugend.',
            'abteilungen' => array( 'kreisvorstand', 'gruene-jugend' ),
            'abt_meta'    => array(
                'kreisvorstand' => array( 'position' => '4', 'function' => 'Beisitzerin', 'hidden' => false ),
                'gruene-jugend' => array( 'position' => '1', 'function' => 'Sprecherin GJ', 'hidden' => false ),
            ),
            'contact'     => array(
                'kr8mb_pers_contact_insta'  => 'lisa.blumenfeld',
                'kr8mb_pers_contact_tiktok' => '@lisa.blumenfeld',
            ),
        ),
        array(
            'name'        => 'Hans Bergmann',
            'content'     => 'Kreisrat seit 2020. Experte für Naturschutz und Landwirtschaft.',
            'abteilungen' => array( 'kreistag' ),
            'abt_meta'    => array( 'kreistag' => array( 'position' => '3', 'function' => '', 'hidden' => false ) ),
            'contact'     => array(
                'kr8mb_pers_contact_email' => 'hans@gruene-musterkreis.de',
                'kr8mb_pers_contact_www'   => 'https://hans-bergmann.example.de',
            ),
        ),
        array(
            'name'        => 'Anna Wiesengrund',
            'content'     => 'Stadträtin in Musterstadt. Schwerpunkt: Bildung und Soziales.',
            'abteilungen' => array( 'stadtrat' ),
            'abt_meta'    => array( 'stadtrat' => array( 'position' => '1', 'function' => 'Fraktionsvorsitzende', 'hidden' => false ) ),
            'contact'     => array(
                'kr8mb_pers_contact_email'    => 'anna@gruene-musterstadt.de',
                'kr8mb_pers_contact_threads'  => 'anna.wiesengrund',
            ),
            'zuordnung'   => 'ov-musterstadt',
        ),
        array(
            'name'        => 'Peter Lichtblick',
            'content'     => 'Sprecher des OV Neuburg. Kümmert sich um Stadtentwicklung und Wohnen.',
            'abteilungen' => array( 'ortsvorstand' ),
            'abt_meta'    => array( 'ortsvorstand' => array( 'position' => '1', 'function' => 'Sprecher', 'hidden' => false ) ),
            'contact'     => array(
                'kr8mb_pers_contact_email' => 'peter@gruene-neuburg.de',
            ),
            'zuordnung'   => 'ov-neuburg',
        ),
    );

    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );

    foreach ( $persons as $p ) {
        $existing = get_posts( array( 'title' => $p['name'], 'post_type' => 'person', 'posts_per_page' => 1 ) );
        $existing = ! empty( $existing ) ? $existing[0] : null;
        if ( $existing ) {
            continue;
        }

        $post_id = wp_insert_post( array(
            'post_type'    => 'person',
            'post_title'   => $p['name'],
            'post_content' => $p['content'],
            'post_status'  => 'publish',
        ) );

        if ( ! $post_id || is_wp_error( $post_id ) ) {
            continue;
        }

        // Abteilungen
        if ( ! empty( $p['abteilungen'] ) ) {
            $term_ids = array();
            foreach ( $p['abteilungen'] as $slug ) {
                $term = get_term_by( 'slug', $slug, 'abteilung' );
                if ( $term ) {
                    $term_ids[] = $term->term_id;
                }
            }
            if ( $term_ids ) {
                wp_set_object_terms( $post_id, $term_ids, 'abteilung' );
            }
        }

        // Per-abteilung meta
        if ( ! empty( $p['abt_meta'] ) ) {
            update_post_meta( $post_id, '_gk_abteilung_meta', $p['abt_meta'] );
        }

        // Contact meta
        if ( ! empty( $p['contact'] ) ) {
            foreach ( $p['contact'] as $key => $value ) {
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Zuordnung
        $zuordnung_slug = $p['zuordnung'] ?? 'kreisverband';
        $zuordnung_term = get_term_by( 'slug', $zuordnung_slug, 'gk_zuordnung' );
        if ( $zuordnung_term ) {
            wp_set_object_terms( $post_id, array( (int) $zuordnung_term->term_id ), 'gk_zuordnung' );
        } elseif ( $kv_term ) {
            wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
        }

        $seeded[] = $p['name'];
    }

    return $seeded;
}


// ── Events ─────────────────────────────────────────────────────────────────

function gk_seed_events() {
    $seeded = array();

    $events = array(
        array(
            'title'      => 'Kreismitgliederversammlung',
            'content'    => 'Ordentliche Kreismitgliederversammlung mit Vorstandswahlen und Antragsberatung.',
            'start_date' => date( 'Y-m-d', strtotime( '+14 days' ) ),
            'start_time' => '19:00',
            'end_time'   => '22:00',
            'location'   => 'Bürgerhaus Musterstadt',
            'address'    => 'Hauptstraße 10, 12345 Musterstadt',
            'organizer'  => 'KV Musterkreis',
            'kategorie'  => 'parteitag',
        ),
        array(
            'title'      => 'Grüner Stammtisch',
            'content'    => 'Offener Stammtisch – alle Interessierten sind willkommen!',
            'start_date' => date( 'Y-m-d', strtotime( '+7 days' ) ),
            'start_time' => '19:30',
            'end_time'   => '22:00',
            'location'   => 'Gasthaus Zum Löwen',
            'address'    => 'Marktplatz 5, 12345 Musterstadt',
            'organizer'  => 'KV Musterkreis',
            'kategorie'  => 'stammtisch',
        ),
        array(
            'title'      => 'Kreistagssitzung',
            'content'    => 'Öffentliche Sitzung des Kreistags. Auf der Tagesordnung: Haushaltsberatung.',
            'start_date' => date( 'Y-m-d', strtotime( '+21 days' ) ),
            'start_time' => '14:00',
            'end_time'   => '18:00',
            'location'   => 'Landratsamt',
            'address'    => 'Landkreisstraße 1, 12345 Musterstadt',
            'organizer'  => 'Landkreis Musterkreis',
            'kategorie'  => 'sitzung',
        ),
        array(
            'title'      => 'Radltour durch den Landkreis',
            'content'    => 'Gemeinsame Radtour entlang der geplanten Radschnellwege. Ca. 30 km, flach.',
            'start_date' => date( 'Y-m-d', strtotime( '+30 days' ) ),
            'start_time' => '10:00',
            'end_time'   => '15:00',
            'location'   => 'Bahnhof Musterstadt',
            'address'    => 'Bahnhofsplatz 1, 12345 Musterstadt',
            'organizer'  => 'AG Verkehr',
            'kategorie'  => 'aktion',
        ),
        array(
            'title'      => 'Klausurwochenende Kreisvorstand',
            'content'    => 'Strategieklausur des Kreisvorstands zur Vorbereitung der Kommunalwahl.',
            'start_date' => date( 'Y-m-d', strtotime( '+45 days' ) ),
            'end_date'   => date( 'Y-m-d', strtotime( '+46 days' ) ),
            'all_day'    => true,
            'location'   => 'Tagungshaus Waldfrieden',
            'address'    => 'Waldweg 12, 12346 Oberdorf',
            'organizer'  => 'Kreisvorstand',
            'kategorie'  => 'sitzung',
        ),
        array(
            'title'      => 'OV Musterstadt: Vorstandssitzung',
            'content'    => 'Interne Vorstandssitzung des OV Musterstadt.',
            'start_date' => date( 'Y-m-d', strtotime( '+10 days' ) ),
            'start_time' => '20:00',
            'end_time'   => '21:30',
            'location'   => 'GRÜNES Büro Musterstadt',
            'address'    => 'Grüne Gasse 3, 12345 Musterstadt',
            'organizer'  => 'OV Musterstadt',
            'kategorie'  => 'sitzung',
            'zuordnung'  => 'ov-musterstadt',
        ),
        array(
            'title'      => 'Infostand auf dem Wochenmarkt',
            'content'    => 'Kommt vorbei! Wir informieren über unsere Arbeit im Kreistag und sammeln Unterschriften.',
            'start_date' => date( 'Y-m-d', strtotime( '+5 days' ) ),
            'start_time' => '09:00',
            'end_time'   => '13:00',
            'location'   => 'Wochenmarkt Musterstadt',
            'address'    => 'Marktplatz, 12345 Musterstadt',
            'organizer'  => 'KV Musterkreis',
            'kategorie'  => 'aktion',
        ),
    );

    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );

    foreach ( $events as $event ) {
        $existing = get_posts( array( 'title' => $event['title'], 'post_type' => 'gk_event', 'posts_per_page' => 1 ) );
        $existing = ! empty( $existing ) ? $existing[0] : null;
        if ( $existing ) {
            continue;
        }

        $post_id = wp_insert_post( array(
            'post_type'    => 'gk_event',
            'post_title'   => $event['title'],
            'post_content' => $event['content'],
            'post_status'  => 'publish',
            'post_excerpt' => wp_trim_words( $event['content'], 20 ),
        ) );

        if ( ! $post_id || is_wp_error( $post_id ) ) {
            continue;
        }

        // Event meta
        update_post_meta( $post_id, 'gk_event_start_date', $event['start_date'] );
        if ( ! empty( $event['start_time'] ) ) {
            update_post_meta( $post_id, 'gk_event_start_time', $event['start_time'] );
        }
        if ( ! empty( $event['end_date'] ) ) {
            update_post_meta( $post_id, 'gk_event_end_date', $event['end_date'] );
        }
        if ( ! empty( $event['end_time'] ) ) {
            update_post_meta( $post_id, 'gk_event_end_time', $event['end_time'] );
        }
        if ( ! empty( $event['all_day'] ) ) {
            update_post_meta( $post_id, 'gk_event_all_day', '1' );
        }
        if ( ! empty( $event['location'] ) ) {
            update_post_meta( $post_id, 'gk_event_location', $event['location'] );
        }
        if ( ! empty( $event['address'] ) ) {
            update_post_meta( $post_id, 'gk_event_address', $event['address'] );
        }
        if ( ! empty( $event['organizer'] ) ) {
            update_post_meta( $post_id, 'gk_event_organizer', $event['organizer'] );
        }

        // Event category
        if ( ! empty( $event['kategorie'] ) ) {
            $cat = get_term_by( 'slug', $event['kategorie'], 'event_kategorie' );
            if ( $cat ) {
                wp_set_object_terms( $post_id, array( $cat->term_id ), 'event_kategorie' );
            }
        }

        // Zuordnung
        $zuordnung_slug = $event['zuordnung'] ?? '';
        if ( $zuordnung_slug ) {
            $zuordnung_term = get_term_by( 'slug', $zuordnung_slug, 'gk_zuordnung' );
            if ( $zuordnung_term ) {
                wp_set_object_terms( $post_id, array( (int) $zuordnung_term->term_id ), 'gk_zuordnung' );
            }
        } elseif ( $kv_term ) {
            wp_set_object_terms( $post_id, array( (int) $kv_term->term_id ), 'gk_zuordnung' );
        }

        $seeded[] = $event['title'];
    }

    return $seeded;
}


// ── Navigation Menus ───────────────────────────────────────────────────────

function gk_seed_menus() {
    $seeded = array();

    // Main menu
    $main_menu_name = 'Hauptmenü (Demo)';
    $main_menu = wp_get_nav_menu_object( $main_menu_name );
    if ( ! $main_menu ) {
        $main_menu_id = wp_create_nav_menu( $main_menu_name );

        $pages_in_menu = array(
            'ueber-uns'         => 'Über uns',
            'kreisvorstand'     => 'Kreisvorstand',
            'kreistagsfraktion' => 'Kreistagsfraktion',
            'ortsverbaende'     => 'Ortsverbände',
            'termine'           => 'Termine',
            'blog'              => 'Aktuelles',
            'kontakt'           => 'Kontakt',
        );

        $position = 0;
        foreach ( $pages_in_menu as $slug => $label ) {
            $page = get_page_by_path( $slug );
            if ( $page ) {
                wp_update_nav_menu_item( $main_menu_id, 0, array(
                    'menu-item-title'     => $label,
                    'menu-item-object-id' => $page->ID,
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-position'  => ++$position,
                ) );
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['nav-main']   = $main_menu_id;
        $locations['nav-mobile'] = $main_menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        $seeded[] = $main_menu_name;
    }

    // Footer menu
    $footer_menu_name = 'Fußleiste (Demo)';
    $footer_menu = wp_get_nav_menu_object( $footer_menu_name );
    if ( ! $footer_menu ) {
        $footer_menu_id = wp_create_nav_menu( $footer_menu_name );

        $footer_pages = array(
            'impressum'   => 'Impressum',
            'datenschutz' => 'Datenschutz',
            'kontakt'     => 'Kontakt',
        );

        $position = 0;
        foreach ( $footer_pages as $slug => $label ) {
            $page = get_page_by_path( $slug );
            if ( $page ) {
                wp_update_nav_menu_item( $footer_menu_id, 0, array(
                    'menu-item-title'     => $label,
                    'menu-item-object-id' => $page->ID,
                    'menu-item-object'    => 'page',
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                    'menu-item-position'  => ++$position,
                ) );
            }
        }

        $locations = get_theme_mod( 'nav_menu_locations', array() );
        $locations['nav-footer'] = $footer_menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );

        $seeded[] = $footer_menu_name;
    }

    return $seeded;
}


// ── Social / Contact Data ──────────────────────────────────────────────────

function gk_seed_social_data() {
    if ( ! taxonomy_exists( 'gk_zuordnung' ) ) {
        return array( 'error' => 'Taxonomy gk_zuordnung not registered.' );
    }

    $seeded = array();

    // ── Kreisverband ────────────────────────────────────────────────────────
    $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    if ( ! $kv_term ) {
        gk_ensure_zuordnung_defaults();
        $kv_term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    }

    if ( $kv_term ) {
        $kv_social = array(
            '_gk_contact_facebook' => 'https://www.facebook.com/GrueneMusterkreis',
            '_gk_contact_insta'    => 'gruene.musterkreis',
            '_gk_contact_twitter'  => 'GrueneMK',
            '_gk_contact_tiktok'   => '@gruene.musterkreis',
            '_gk_contact_threads'  => 'gruene.musterkreis',
            '_gk_contact_mastodon' => 'https://gruene.social/@musterkreis',
            '_gk_contact_www'      => 'https://gruene-musterkreis.de',
            '_gk_contact_email'    => 'info@gruene-musterkreis.de',
            '_gk_contact_telefon'  => '01234 567890',
            '_gk_contact_anschrift' => "GRÜNES Büro\nMusterstraße 1\n12345 Musterstadt",
        );
        foreach ( $kv_social as $key => $value ) {
            update_term_meta( $kv_term->term_id, $key, $value );
        }

        $kv_info = get_option( 'gk_kv_info', array() );
        $kv_info = array_merge( $kv_info, array(
            'name'              => 'KV Musterkreis',
            'social_instagram'  => 'gruene.musterkreis',
            'social_facebook'   => 'https://www.facebook.com/GrueneMusterkreis',
            'social_x'          => 'GrueneMK',
            'social_tiktok'     => '@gruene.musterkreis',
            'social_threads'    => 'gruene.musterkreis',
            'social_bluesky'    => 'gruene-musterkreis.bsky.social',
            'social_mastodon'   => 'https://gruene.social/@musterkreis',
            'social_youtube'    => 'https://www.youtube.com/@GrueneMusterkreis',
        ) );
        update_option( 'gk_kv_info', $kv_info );

        $seeded[] = 'Kreisverband';
    }

    // ── OV social data ──────────────────────────────────────────────────────
    $ov_term = get_term_by( 'slug', 'ov-musterstadt', 'gk_zuordnung' );
    if ( $ov_term ) {
        $ov_social = array(
            '_gk_contact_facebook' => 'GrueneMusterstadt',
            '_gk_contact_insta'    => 'gruene.musterstadt',
            '_gk_contact_www'      => 'https://gruene-musterstadt.de',
            '_gk_contact_email'    => 'info@gruene-musterstadt.de',
        );
        foreach ( $ov_social as $key => $value ) {
            update_term_meta( $ov_term->term_id, $key, $value );
        }
        $seeded[] = 'OV Musterstadt';
    }

    return array( 'seeded' => $seeded );
}


// ── Site Options ───────────────────────────────────────────────────────────

function gk_seed_options() {
    $seeded = array();

    // General settings
    update_option( 'blogname', 'GRÜNE Musterkreis' );
    update_option( 'blogdescription', 'BÜNDNIS 90/DIE GRÜNEN Kreisverband Musterkreis' );
    update_option( 'date_format', 'j. F Y' );
    update_option( 'time_format', 'H:i' );
    update_option( 'timezone_string', 'Europe/Berlin' );
    update_option( 'WPLANG', 'de_DE' );
    update_option( 'posts_per_page', 10 );

    // Permalink structure
    update_option( 'permalink_structure', '/%postname%/' );
    $seeded[] = 'Site options';

    return $seeded;
}


// ── Admin Trigger ──────────────────────────────────────────────────────────

add_action( 'admin_init', function () {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Full seed
    if ( isset( $_GET['gk_seed'] ) ) {
        $result = gk_seed_all();
        $summary = array();
        foreach ( $result as $section => $items ) {
            if ( is_array( $items ) && ! empty( $items ) ) {
                $count = isset( $items['seeded'] ) ? count( $items['seeded'] ) : count( $items );
                $summary[] = "$section: $count";
            }
        }
        $text = implode( ', ', $summary );
        add_action( 'admin_notices', function () use ( $text ) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>Seed-Daten erstellt:</strong> ' . esc_html( $text );
            echo '</p></div>';
        } );
        return;
    }

    // Social-only seed (backwards compat)
    if ( isset( $_GET['gk_seed_social'] ) ) {
        $result = gk_seed_social_data();
        $names  = implode( ', ', $result['seeded'] ?? array() );
        add_action( 'admin_notices', function () use ( $names ) {
            echo '<div class="notice notice-success is-dismissible"><p>';
            echo '<strong>Social-Daten geseedet:</strong> ' . esc_html( $names );
            echo '</p></div>';
        } );
    }
} );
