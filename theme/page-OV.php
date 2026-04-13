<?php
/**
 * Template Name: OV-Startseite
 *
 * Homepage for an Ortsverband / Ortsgruppe.
 *
 * Sections (all toggleable via admin settings):
 *  1. Hero (6 modes — same as KV homepage)
 *  2. Page content (from WP editor — optional)
 *  3. Team (person grid)
 *  4. Aktuelles (news card grid)
 *  5. Termine (event track)
 *  6. Contact (address, phone, email, social)
 *  7. Engagement CTAs
 *
 * @package Neurg_Kreisverband
 */

get_header();

global $post;
$zuordnung = wp_get_object_terms( $post->ID, 'gk_zuordnung', array( 'fields' => 'all' ) );
$ov_slug   = ! empty( $zuordnung ) ? $zuordnung[0]->slug : '';
$ov_term   = ! empty( $zuordnung ) ? $zuordnung[0] : null;
$ov_type   = $ov_term ? gk_get_ov_type( $ov_term->term_id ) : 'ov';
$contact   = $ov_term ? gk_get_ov_contact( $ov_term->term_id ) : array();
$term_id   = $ov_term ? $ov_term->term_id : 0;

// Homepage settings.
$landing_mode  = gk_get_ov_homepage_option( $term_id, 'landing_mode', 'standard' );
$hero_title    = gk_get_ov_homepage_option( $term_id, 'hero_title' );
$hero_subtitle = gk_get_ov_homepage_option( $term_id, 'hero_subtitle' );
$hero_img_id   = (int) gk_get_ov_homepage_option( $term_id, 'hero_image', 0 );
$cta_label     = gk_get_ov_homepage_option( $term_id, 'cta_label' );
$cta_url       = gk_get_ov_homepage_option( $term_id, 'cta_url' );

$show_team     = gk_get_ov_homepage_option( $term_id, 'show_team', '1' ) === '1';
$show_news     = gk_get_ov_homepage_option( $term_id, 'show_aktuelles', '1' ) === '1';
$show_events   = gk_get_ov_homepage_option( $term_id, 'show_termine', '1' ) === '1';
$show_contact  = gk_get_ov_homepage_option( $term_id, 'show_contact', '1' ) === '1';
$show_engage   = gk_get_ov_homepage_option( $term_id, 'show_engage', '1' ) === '1';

$ov_header = gk_get_ov_header( $term_id );

$donation     = function_exists( 'gk_get_donation_config' ) ? gk_get_donation_config() : array();
$has_donation = ! empty( $donation['enabled'] );
$donate_url   = gk_get_donate_page_url( $donation );
$donate_cta   = $donation['cta_text'] ?? 'Jetzt spenden';

// Fallbacks from the page itself.
while ( have_posts() ) : the_post();
    if ( ! $hero_title )    $hero_title    = get_the_title();
    if ( ! $hero_subtitle ) $hero_subtitle = get_the_excerpt();
    if ( ! $hero_img_id && has_post_thumbnail() ) $hero_img_id = get_post_thumbnail_id();
endwhile;

// ════════════════════════════════════════════════════════════════════════════
//  WERBUNG — Dedicated engagement landing page (exits early)
// ════════════════════════════════════════════════════════════════════════════
if ( $ov_type === 'werbung' ) :
    $gemeinde = $ov_term ? $ov_term->name : '';
    // Strip "Grüne in " or "OV " prefix for cleaner display.
    $gemeinde_clean = preg_replace( '/^(Gr(ü|ue)ne in |OV |Ortsverband |Ortsgruppe )/u', '', $gemeinde );
    $contact_email  = $contact['email'] ?? '';
    $kv_info        = get_option( 'gk_kv_info', array() );
    $kv_email       = $kv_info['email'] ?? '';
    $email_target   = $contact_email ?: $kv_email;
    get_template_part( 'template-parts/ov-werbung', null, array(
        'gemeinde'     => $gemeinde_clean,
        'hero_img_id'  => $hero_img_id,
        'hero_title'   => $hero_title,
        'hero_subtitle'=> $hero_subtitle,
        'email'        => $email_target,
        'contact'      => $contact,
        'ov_header'    => $ov_header,
    ) );
    get_footer();
    return;
endif;
?>

<div id="primary" class="content-area">
<main id="main" class="site-main" role="main">
<div class="home-gk ov-homepage ov-type-<?php echo esc_attr( $ov_type ); ?>">

<?php
// ════════════════════════════════════════════════════════════════════════════
//  1. HERO — Rendered per landing mode
// ════════════════════════════════════════════════════════════════════════════

switch ( $landing_mode ) :

// ── ELECTION ────────────────────────────────────────────────────────────────
case 'election':
    $el_name     = gk_get_ov_homepage_option( $term_id, 'election_name', '' );
    $el_date     = gk_get_ov_homepage_option( $term_id, 'election_date', '' );
    $el_slogan   = gk_get_ov_homepage_option( $term_id, 'election_slogan', '' );
    $el_img      = $hero_img_id;
    $days_left   = $el_date ? max( 0, (int) ( ( strtotime( $el_date ) - time() ) / 86400 ) ) : null;
?>
<section class="gk-hero gk-hero--election">
    <?php if ( $el_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $el_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <?php if ( $el_name ) : ?>
            <span class="gk-hero__kicker"><?php echo esc_html( $el_name ); ?></span>
        <?php endif; ?>
        <?php if ( $days_left !== null && $days_left > 0 ) : ?>
            <div class="gk-hero__countdown">
                <span class="gk-hero__countdown-num"><?php echo $days_left; ?></span>
                <span class="gk-hero__countdown-label">Tage bis zur Wahl</span>
            </div>
        <?php elseif ( $days_left === 0 ) : ?>
            <div class="gk-hero__countdown gk-hero__countdown--today">
                <span class="gk-hero__countdown-label">Heute ist Wahltag!</span>
            </div>
        <?php endif; ?>
        <h1 class="gk-hero__title"><?php echo esc_html( $el_slogan ?: $hero_title ); ?></h1>
        <div class="gk-hero__actions">
            <?php if ( $cta_label && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $cta_label ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php break;

// ── CANDIDATE ───────────────────────────────────────────────────────────────
case 'candidate':
    $c_name    = gk_get_ov_homepage_option( $term_id, 'candidate_name', '' );
    $c_role    = gk_get_ov_homepage_option( $term_id, 'candidate_role', '' );
    $c_quote   = gk_get_ov_homepage_option( $term_id, 'candidate_quote', '' );
    $c_img     = (int) gk_get_ov_homepage_option( $term_id, 'candidate_image', 0 );
    $c_cta     = gk_get_ov_homepage_option( $term_id, 'candidate_cta_label', 'Mehr erfahren' );
    $c_cta_url = gk_get_ov_homepage_option( $term_id, 'candidate_cta_url', '' );
?>
<section class="gk-hero gk-hero--candidate">
    <?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <div class="gk-candidate">
            <?php if ( $c_img ) : ?>
                <div class="gk-candidate__photo">
                    <?php echo wp_get_attachment_image( $c_img, 'medium_large', false, array( 'class' => 'gk-candidate__img' ) ); ?>
                </div>
            <?php endif; ?>
            <div class="gk-candidate__info">
                <span class="gk-hero__kicker"><?php echo esc_html( $ov_header ); ?></span>
                <?php if ( $c_name ) : ?>
                    <h1 class="gk-hero__title"><?php echo esc_html( $c_name ); ?></h1>
                <?php endif; ?>
                <?php if ( $c_role ) : ?>
                    <p class="gk-candidate__role"><?php echo esc_html( $c_role ); ?></p>
                <?php endif; ?>
                <?php if ( $c_quote ) : ?>
                    <blockquote class="gk-candidate__quote">&ldquo;<?php echo esc_html( $c_quote ); ?>&rdquo;</blockquote>
                <?php endif; ?>
                <div class="gk-hero__actions">
                    <?php if ( $c_cta && $c_cta_url ) : ?>
                        <a href="<?php echo esc_url( $c_cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $c_cta ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php break;

// ── NEWS ────────────────────────────────────────────────────────────────────
case 'news':
    $latest   = new WP_Query( array_merge( array(
        'post_type'      => 'post',
        'posts_per_page' => 1,
    ), gk_ov_query_args( $ov_slug ) ) );
    $has_post = $latest->have_posts();
    if ( $has_post ) $latest->the_post();
    $post_img = $has_post && has_post_thumbnail() ? get_post_thumbnail_id() : 0;
    $bg_img   = $hero_img_id ?: $post_img;
?>
<section class="gk-hero gk-hero--news">
    <?php if ( $bg_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $bg_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker">Aktuell</span>
        <?php if ( $has_post ) : ?>
            <h1 class="gk-hero__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
            <p class="gk-hero__subtitle"><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
            <div class="gk-hero__actions">
                <a href="<?php the_permalink(); ?>" class="gk-btn gk-btn--primary">Weiterlesen</a>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php
    if ( $has_post ) wp_reset_postdata();
    break;

// ── FUNDRAISING ─────────────────────────────────────────────────────────────
case 'fundraising':
    $fr_goal    = absint( gk_get_ov_homepage_option( $term_id, 'fundraising_goal', 0 ) );
    $fr_current = absint( gk_get_ov_homepage_option( $term_id, 'fundraising_current', 0 ) );
    $fr_pct     = $fr_goal > 0 ? min( 100, round( $fr_current / $fr_goal * 100 ) ) : 0;
?>
<section class="gk-hero gk-hero--fundraising">
    <?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker"><?php echo esc_html( $ov_header ); ?></span>
        <h1 class="gk-hero__title"><?php echo esc_html( $hero_title ?: 'Unterstütze grüne Politik vor Ort' ); ?></h1>
        <?php if ( $hero_subtitle ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>
        <?php if ( $fr_goal > 0 ) : ?>
            <div class="gk-fundraising-bar">
                <div class="gk-fundraising-bar__track">
                    <div class="gk-fundraising-bar__fill" style="width:<?php echo $fr_pct; ?>%"></div>
                </div>
                <div class="gk-fundraising-bar__stats">
                    <span class="gk-fundraising-bar__current"><?php echo number_format( $fr_current, 0, ',', '.' ); ?> &euro;</span>
                    <span class="gk-fundraising-bar__goal">Ziel: <?php echo number_format( $fr_goal, 0, ',', '.' ); ?> &euro;</span>
                </div>
            </div>
        <?php endif; ?>
        <div class="gk-hero__actions">
            <?php if ( $has_donation && $donate_url ) : ?>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $donate_cta ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php break;

// ── MINIMAL ─────────────────────────────────────────────────────────────────
case 'minimal':
?>
<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title"><?php echo esc_html( $ov_header ); ?></h1>
        <?php if ( $cta_label && $cta_url ) : ?>
        <div class="gk-hero__actions">
            <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $cta_label ); ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php break;

// ── STANDARD (default) ──────────────────────────────────────────────────────
default:
?>
<section class="gk-hero">
    <?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg">
            <?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?>
        </div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title"><?php echo esc_html( $hero_title ); ?></h1>
        <?php if ( $hero_subtitle ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>
        <div class="gk-hero__actions">
            <?php if ( $cta_label && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary">
                    <?php echo esc_html( $cta_label ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $has_donation && $donate_url ) : ?>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary">
                    <?php echo esc_html( $donate_cta ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php break;

endswitch; // landing_mode ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  2. PAGE CONTENT — From WP editor (optional)
// ════════════════════════════════════════════════════════════════════════════
rewind_posts();
while ( have_posts() ) : the_post();
    if ( trim( strip_tags( get_the_content() ) ) ) : ?>
<section class="ov-content">
    <div class="inner">
        <div class="ov-content__body entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
    <?php endif;
endwhile; ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  3. TEAM — Tabbed by Abteilung, auto-rotating
// ════════════════════════════════════════════════════════════════════════════
if ( $show_team ) :
    $ov_persons = new WP_Query( array(
        'post_type'      => 'person',
        'posts_per_page' => 60,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => $ov_slug,
        ) ),
    ) );

    if ( $ov_persons->have_posts() ) :
        // Collect persons and group by abteilung
        $all_persons  = array();
        $abt_groups   = array(); // slug => array of person indices
        $abt_labels   = array(); // slug => name
        while ( $ov_persons->have_posts() ) : $ov_persons->the_post();
            $terms = get_the_terms( get_the_ID(), 'abteilung' );
            if ( ! $terms || is_wp_error( $terms ) ) {
                continue; // Only show persons assigned to an Abteilung
            }

            $person = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'permalink' => get_permalink(),
                'funktion'  => get_post_meta( get_the_ID(), 'kr8mb_pers_position_funktion', true ),
                'thumb'     => has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'medium' ) : '',
            );
            $idx = count( $all_persons );
            $all_persons[] = $person;

            foreach ( $terms as $t ) {
                $abt_labels[ $t->slug ] = $t->name;
                $abt_groups[ $t->slug ][] = $idx;
            }
        endwhile;
        wp_reset_postdata();

        // Build ordered tab list (abteilungen only, no "Alle")
        $tabs = array();
        if ( ! empty( $abt_groups ) ) {
            uksort( $abt_groups, function( $a, $b ) use ( $abt_labels ) {
                return strcasecmp( $abt_labels[ $a ], $abt_labels[ $b ] );
            });
            foreach ( $abt_groups as $slug => $indices ) {
                $tabs[ $slug ] = $abt_labels[ $slug ];
            }
        }
        $has_tabs = count( $tabs ) > 1;
        $first_slug = $has_tabs ? array_key_first( $tabs ) : '';
    ?>
<section class="gk-team" id="ov-team">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Unser Team</h2>
        </div>
        <?php if ( $has_tabs ) : ?>
        <nav class="gk-team__tabs" role="tablist" aria-label="Team-Abteilungen">
            <?php foreach ( $tabs as $slug => $label ) : ?>
            <button class="gk-team__tab<?php echo $slug === $first_slug ? ' is-active' : ''; ?>"
                    role="tab"
                    aria-selected="<?php echo $slug === $first_slug ? 'true' : 'false'; ?>"
                    aria-controls="team-panel-<?php echo esc_attr( $slug ); ?>"
                    data-tab="<?php echo esc_attr( $slug ); ?>">
                <?php echo esc_html( $label ); ?>
            </button>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <?php if ( $has_tabs ) :
            foreach ( $tabs as $slug => $label ) :
                $indices = $abt_groups[ $slug ];
        ?>
        <div class="gk-team__panel<?php echo $slug === $first_slug ? ' is-active' : ''; ?>"
             id="team-panel-<?php echo esc_attr( $slug ); ?>"
             role="tabpanel">
            <div class="gk-team__grid">
                <?php foreach ( $indices as $i ) : $p = $all_persons[ $i ]; ?>
                <a href="<?php echo esc_url( $p['permalink'] ); ?>" class="gk-team__card">
                    <div class="gk-team__photo">
                        <?php if ( $p['thumb'] ) : echo $p['thumb']; else : ?>
                        <svg class="gk-team__placeholder" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="78" r="36" fill="currentColor" opacity=".25"/><ellipse cx="100" cy="176" rx="56" ry="46" fill="currentColor" opacity=".18"/></svg>
                        <?php endif; ?>
                    </div>
                    <h3 class="gk-team__name"><?php echo esc_html( $p['title'] ); ?></h3>
                    <?php if ( $p['funktion'] ) : ?>
                        <p class="gk-team__role"><?php echo esc_html( $p['funktion'] ); ?></p>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach;
        else : ?>
        <div class="gk-team__grid">
            <?php foreach ( $all_persons as $p ) : ?>
            <a href="<?php echo esc_url( $p['permalink'] ); ?>" class="gk-team__card">
                <div class="gk-team__photo">
                    <?php if ( $p['thumb'] ) : echo $p['thumb']; else : ?>
                    <svg class="gk-team__placeholder" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="78" r="36" fill="currentColor" opacity=".25"/><ellipse cx="100" cy="176" rx="56" ry="46" fill="currentColor" opacity=".18"/></svg>
                    <?php endif; ?>
                </div>
                <h3 class="gk-team__name"><?php echo esc_html( $p['title'] ); ?></h3>
                <?php if ( $p['funktion'] ) : ?>
                    <p class="gk-team__role"><?php echo esc_html( $p['funktion'] ); ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="gk-team__footer">
            <a href="<?php echo esc_url( gk_ov_team_url( $ov_slug ) ); ?>" class="gk-btn gk-btn--primary">Alle anzeigen</a>
        </div>
    </div>
</section>
    <?php endif;
endif; ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  4. AKTUELLES — News card grid
// ════════════════════════════════════════════════════════════════════════════
if ( $show_news ) :
    $ov_posts = new WP_Query( array(
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => $ov_slug,
        ) ),
    ) );

    if ( $ov_posts->have_posts() ) : ?>
<section class="gk-news">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Aktuelles</h2>
        </div>
        <div class="gk-news__grid">
            <?php
            $idx = 0;
            while ( $ov_posts->have_posts() ) : $ov_posts->the_post();
                $is_featured = ( $idx === 0 );
                $thumb_size  = $is_featured ? 'large' : 'listenansicht';
            ?>
            <article class="gk-card <?php echo $is_featured ? 'gk-card--featured' : ''; ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="gk-card__thumb">
                        <?php the_post_thumbnail( $thumb_size ); ?>
                    </a>
                <?php endif; ?>
                <div class="gk-card__body">
                    <time class="gk-card__date" datetime="<?php echo get_the_date( 'c' ); ?>">
                        <?php echo get_the_date(); ?>
                    </time>
                    <h3 class="gk-card__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </h3>
                    <?php if ( $is_featured ) : ?>
                        <p class="gk-card__excerpt"><?php echo wp_trim_words( get_the_excerpt(), 30 ); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php
                $idx++;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
    <?php endif;
endif; ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  5. TERMINE — Event track
// ════════════════════════════════════════════════════════════════════════════
if ( $show_events ) :
    $ov_events = new WP_Query( array(
        'post_type'      => 'gk_event',
        'posts_per_page' => 5,
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array( array(
            'key'     => 'gk_event_start_date',
            'value'   => current_time( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ) ),
        'tax_query'      => array( array(
            'taxonomy' => 'gk_zuordnung',
            'field'    => 'slug',
            'terms'    => $ov_slug,
        ) ),
    ) );

    if ( $ov_events->have_posts() ) : ?>
<section class="gk-events">
    <div class="inner">
        <div class="gk-section-header">
            <h2>N&auml;chste Termine</h2>
        </div>
        <div class="gk-events__track">
            <?php while ( $ov_events->have_posts() ) : $ov_events->the_post();
                $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
                $start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
                $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
            ?>
            <a href="<?php the_permalink(); ?>" class="gk-event">
                <div class="gk-event__date">
                    <span class="gk-event__day"><?php echo esc_html( date_i18n( 'j', strtotime( $start_date ) ) ); ?></span>
                    <span class="gk-event__month"><?php echo esc_html( date_i18n( 'M', strtotime( $start_date ) ) ); ?></span>
                </div>
                <div class="gk-event__info">
                    <h3 class="gk-event__title"><?php the_title(); ?></h3>
                    <?php if ( $start_time || $location ) : ?>
                    <p class="gk-event__meta">
                        <?php if ( $start_time ) echo esc_html( $start_time ) . ' Uhr'; ?>
                        <?php if ( $start_time && $location ) echo ' &middot; '; ?>
                        <?php if ( $location ) echo esc_html( $location ); ?>
                    </p>
                    <?php endif; ?>
                </div>
                <span class="gk-event__arrow" aria-hidden="true">&rarr;</span>
            </a>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
    </div>
</section>
    <?php endif;
endif; ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  6. CONTACT — From term meta
// ════════════════════════════════════════════════════════════════════════════
$ov_social_links = $show_contact && $ov_term ? gk_build_social_links( $contact ) : array();
$has_contact = $show_contact && $ov_term && (
    ! empty( $contact['anschrift'] ) || ! empty( $contact['email'] ) ||
    ! empty( $contact['telefon'] ) || ! empty( $contact['www'] ) ||
    ! empty( $ov_social_links )
);

if ( $has_contact ) : ?>
<section class="gk-contact">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Kontakt</h2>
        </div>
        <div class="gk-contact__card">
            <?php if ( ! empty( $contact['anschrift'] ) || ! empty( $contact['telefon'] ) || ! empty( $contact['email'] ) || ! empty( $contact['www'] ) ) : ?>
            <div class="gk-contact__info">
                <h3><?php echo esc_html( $ov_term->name ); ?></h3>
                <address class="gk-contact__details">
                    <?php if ( ! empty( $contact['anschrift'] ) ) : ?>
                    <div class="gk-contact__item">
                        <i class="fas fa-location-dot" aria-hidden="true"></i>
                        <span><?php echo nl2br( esc_html( $contact['anschrift'] ) ); ?></span>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['telefon'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^+0-9]/', '', $contact['telefon'] ) ); ?>">
                            <i class="fas fa-phone" aria-hidden="true"></i>
                            <span><?php echo esc_html( $contact['telefon'] ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>">
                            <i class="fas fa-envelope" aria-hidden="true"></i>
                            <span><?php echo esc_html( $contact['email'] ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                    <?php if ( ! empty( $contact['www'] ) ) : ?>
                    <div class="gk-contact__item">
                        <a href="<?php echo esc_url( $contact['www'] ); ?>" target="_blank" rel="noopener">
                            <i class="fas fa-globe" aria-hidden="true"></i>
                            <span><?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', rtrim( $contact['www'], '/' ) ) ); ?></span>
                        </a>
                    </div>
                    <?php endif; ?>
                </address>
            </div>
            <?php endif; ?>

            <?php if ( ! empty( $ov_social_links ) ) : ?>
            <div class="gk-contact__social">
                <h4 class="gk-contact__social-heading">Folge uns</h4>
                <?php gk_social_links_bar( $ov_social_links ); ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>


<?php
// ════════════════════════════════════════════════════════════════════════════
//  7. ENGAGEMENT — CTAs
// ════════════════════════════════════════════════════════════════════════════
if ( $show_engage ) : ?>
<section class="gk-engage">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Werde aktiv</h2>
            <p>Es gibt viele Wege, gr&uuml;ne Politik vor Ort mitzugestalten.</p>
        </div>
        <div class="gk-engage__grid">
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3>Mitglied werden</h3>
                <p>Tritt den Gr&uuml;nen bei und gestalte Politik von der Basis aus mit.</p>
                <a href="https://www.gruene.de/mitglied-werden" class="gk-btn gk-btn--primary" target="_blank" rel="noopener">
                    Mitglied werden &rarr;
                </a>
            </div>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>Komm vorbei</h3>
                <p>Unsere Treffen stehen allen offen. Lerne uns kennen &mdash; ganz unverbindlich.</p>
                <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" class="gk-btn gk-btn--primary">Kontakt aufnehmen</a>
                <?php endif; ?>
            </div>
            <?php if ( $has_donation && $donate_url ) : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <h3>Spenden</h3>
                <p>Deine Spende erm&ouml;glicht politische Arbeit vor Ort. Jeder Euro z&auml;hlt.</p>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $donate_cta ); ?> &rarr;</a>
            </div>
            <?php else : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <h3>Schreib uns</h3>
                <p>Fragen, Ideen, Kritik? Wir freuen uns &uuml;ber jede Nachricht.</p>
                <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" class="gk-btn gk-btn--primary">E-Mail schreiben</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

</div><!-- .home-gk.ov-homepage -->
</main>
</div>


<script>
(function() {
    var section = document.getElementById('ov-team');
    if (!section) return;
    var tabs = section.querySelectorAll('.gk-team__tab');
    if (tabs.length < 2) return;

    var panels = section.querySelectorAll('.gk-team__panel');
    var current = 0;
    var interval = null;
    var userClicked = false;

    function activate(index) {
        tabs[current].classList.remove('is-active');
        tabs[current].setAttribute('aria-selected', 'false');
        panels[current].classList.remove('is-active');
        current = index;
        tabs[current].classList.add('is-active');
        tabs[current].setAttribute('aria-selected', 'true');
        panels[current].classList.add('is-active');
    }

    function next() {
        activate((current + 1) % tabs.length);
    }

    function startRotation() {
        if (interval) clearInterval(interval);
        interval = setInterval(next, 5000);
    }

    tabs.forEach(function(tab, i) {
        tab.addEventListener('click', function() {
            userClicked = true;
            if (interval) { clearInterval(interval); interval = null; }
            activate(i);
        });
    });

    // Only auto-rotate when visible
    var observer = new IntersectionObserver(function(entries) {
        if (entries[0].isIntersecting && !userClicked) {
            startRotation();
        } else if (interval) {
            clearInterval(interval);
            interval = null;
        }
    }, { threshold: 0.2 });
    observer.observe(section);
})();
</script>

<?php get_footer(); ?>
