<?php
/**
 * Homepage Variant: Neue Energie
 *
 * A bold, modern layout built around the Kreiskarte.
 * Designed to engage both existing members and politically
 * interested citizens who want to understand what a
 * Kreisverband does and how to get involved.
 *
 * Variant:       neue-energie
 * Variant Name:  Neurg Energie
 * Variant Desc:  Modernes Layout mit prominenter Kreiskarte, Engagement-Bereich und Karten-Grid.
 * Variant Thumb: home/neue-energie.png
 *
 * @package Neurg_Kreisverband
 */

// ── Data ────────────────────────────────────────────────────────────────────

$landing_mode  = gk_homepage_option( 'landing_mode', 'standard' );
$hero_title    = gk_homepage_option( 'hero_title' );
$hero_subtitle = gk_homepage_option( 'hero_subtitle' );
$hero_img_id   = gk_homepage_option( 'hero_image', 0 );
$cta_label     = gk_homepage_option( 'cta_label' );
$cta_url       = gk_homepage_option( 'cta_url' );

$donation      = function_exists( 'gk_get_donation_config' ) ? gk_get_donation_config() : array();
$has_donation   = ! empty( $donation['enabled'] );
$donate_url     = gk_get_donate_page_url( $donation );
$donate_cta     = $donation['cta_text'] ?? 'Jetzt spenden';

$kv_info = get_option( 'gk_kv_info', array() );
$kv_name = $kv_info['full_name'] ?? 'Kreisverband';

while ( have_posts() ) : the_post();
    if ( ! $hero_title )    $hero_title    = get_the_title();
    if ( ! $hero_subtitle ) $hero_subtitle = get_the_excerpt();
    if ( ! $hero_img_id && has_post_thumbnail() ) $hero_img_id = get_post_thumbnail_id();
endwhile;
?>

<div class="home-gk home-gk--<?php echo esc_attr( $landing_mode ); ?>">

<?php
// ════════════════════════════════════════════════════════════════════════════
//  HERO + INTRO — Rendered per landing mode
// ════════════════════════════════════════════════════════════════════════════

switch ( $landing_mode ) :

// ── ELECTION ────────────────────────────────────────────────────────────────
case 'election':
    $el_name     = gk_homepage_option( 'election_name', '' );
    $el_date     = gk_homepage_option( 'election_date', '' );
    $el_slogan   = gk_homepage_option( 'election_slogan', '' );
    $el_img      = gk_homepage_option( 'election_image', $hero_img_id );
    $el_cta      = gk_homepage_option( 'election_cta_label', 'Wahlprogramm lesen' );
    $el_cta_url  = gk_homepage_option( 'election_cta_url', '' );
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
            <?php if ( $el_cta && $el_cta_url ) : ?>
                <a href="<?php echo esc_url( $el_cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $el_cta ); ?></a>
            <?php endif; ?>
            <a href="#ovs" class="gk-btn gk-btn--primary">Ortsverband finden</a>
        </div>
    </div>
</section>
<?php break;

// ── CANDIDATE ───────────────────────────────────────────────────────────────
case 'candidate':
    $c_name    = gk_homepage_option( 'candidate_name', '' );
    $c_role    = gk_homepage_option( 'candidate_role', '' );
    $c_quote   = gk_homepage_option( 'candidate_quote', '' );
    $c_img     = gk_homepage_option( 'candidate_image', 0 );
    $c_cta     = gk_homepage_option( 'candidate_cta_label', 'Mehr erfahren' );
    $c_cta_url = gk_homepage_option( 'candidate_cta_url', '' );
?>
<section class="gk-hero gk-hero--candidate">
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <div class="gk-candidate">
            <?php if ( $c_img ) : ?>
                <div class="gk-candidate__photo">
                    <?php echo wp_get_attachment_image( $c_img, 'medium_large', false, array( 'class' => 'gk-candidate__img' ) ); ?>
                </div>
            <?php endif; ?>
            <div class="gk-candidate__info">
                <span class="gk-hero__kicker"><?php echo esc_html( $kv_name ); ?></span>
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
    $n_heading = gk_homepage_option( 'news_heading', 'Aktuell' );
    $n_img     = gk_homepage_option( 'news_image', $hero_img_id );
    $latest    = new WP_Query( array_merge( array(
        'post_type'      => 'post',
        'posts_per_page' => 1,
    ), function_exists( 'gk_kv_query_args' ) ? gk_kv_query_args() : array() ) );
    $has_post  = $latest->have_posts();
    if ( $has_post ) $latest->the_post();
    $post_img  = $has_post && has_post_thumbnail() ? get_post_thumbnail_id() : 0;
    $bg_img    = $n_img ?: $post_img;
?>
<section class="gk-hero gk-hero--news">
    <?php if ( $bg_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $bg_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker"><?php echo esc_html( $n_heading ); ?></span>
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
    $fr_title   = gk_homepage_option( 'fundraising_title', '' );
    $fr_text    = gk_homepage_option( 'fundraising_text', '' );
    $fr_goal    = absint( gk_homepage_option( 'fundraising_goal', 0 ) );
    $fr_current = absint( gk_homepage_option( 'fundraising_current', 0 ) );
    $fr_img     = gk_homepage_option( 'fundraising_image', $hero_img_id );
    $fr_pct     = $fr_goal > 0 ? min( 100, round( $fr_current / $fr_goal * 100 ) ) : 0;
?>
<section class="gk-hero gk-hero--fundraising">
    <?php if ( $fr_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $fr_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker"><?php echo esc_html( $kv_name ); ?></span>
        <h1 class="gk-hero__title"><?php echo esc_html( $fr_title ?: 'Unterstütze grüne Politik vor Ort' ); ?></h1>
        <?php if ( $fr_text ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $fr_text ); ?></p>
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
        <h1 class="gk-hero__title"><?php echo esc_html( $kv_name ); ?></h1>
        <div class="gk-hero__actions">
            <a href="#ovs" class="gk-btn gk-btn--primary">Ortsverband finden &darr;</a>
        </div>
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

<?php
rewind_posts();
while ( have_posts() ) : the_post();
    if ( trim( strip_tags( get_the_content() ) ) ) : ?>
<section class="gk-intro">
    <div class="inner">
        <div class="gk-intro__custom entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
    <?php endif;
endwhile; ?>
<?php break;

endswitch; // landing_mode ?>

<!-- ╔═══════════════════════════════════════════════════════════════╗
     ║  3. KREISKARTE — The centrepiece                            ║
     ╚═══════════════════════════════════════════════════════════════╝ -->
<?php if ( gk_homepage_option( 'show_kreiskarte', true ) ) : ?>
<section class="gk-map" id="ovs">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Finde deinen Ortsverband</h2>
            <p>Wähle deinen Ort, um mehr über die Grünen in deiner Nähe zu erfahren.</p>
        </div>
        <?php echo gk_render_kreiskarte_responsive( array( 'max_width' => '100%' ) ); ?>
    </div>
</section>
<?php endif; ?>

<!-- ╔═══════════════════════════════════════════════════════════════╗
     ║  4. AKTUELLES — Card grid with featured first post          ║
     ╚═══════════════════════════════════════════════════════════════╝ -->
<?php if ( gk_homepage_option( 'show_aktuelles', true ) ) :
    $count       = intval( gk_homepage_option( 'aktuelles_count', 6 ) );
    $news        = new WP_Query( array_merge( array(
        'post_type'      => 'post',
        'posts_per_page' => $count,
    ), gk_kv_query_args() ) );

    if ( $news->have_posts() ) : ?>
<section class="gk-news">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Aktuelles</h2>
        </div>
        <div class="gk-news__grid">
            <?php
            $idx = 0;
            while ( $news->have_posts() ) : $news->the_post();
                $is_featured = ( $idx === 0 );
                $thumb_size  = $is_featured ? 'large' : 'listenansicht';
            ?>
            <article class="gk-card <?php echo $is_featured ? 'gk-card--featured' : ''; ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                    <a href="<?php the_permalink(); ?>" class="gk-card__thumb">
                        <?php the_post_thumbnail( $thumb_size ); ?>
                    </a>
                <?php elseif ( $is_featured ) : ?>
                    <a href="<?php the_permalink(); ?>" class="gk-card__thumb gk-card__thumb--placeholder"></a>
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
        <div class="gk-news__more">
            <?php
                $posts_page_id = get_option( 'page_for_posts' );
                $news_url      = $posts_page_id ? get_permalink( $posts_page_id ) : '';
                if ( ! $news_url ) {
                    $news_url = home_url( '/aktuelles/' );
                }
            ?>
            <a href="<?php echo esc_url( $news_url ); ?>" class="gk-btn gk-btn--primary">
                Alle Nachrichten &rarr;
            </a>
        </div>
    </div>
</section>
    <?php endif;
endif; ?>

<!-- ╔═══════════════════════════════════════════════════════════════╗
     ║  5. TERMINE — Horizontal timeline                           ║
     ╚═══════════════════════════════════════════════════════════════╝ -->
<?php if ( gk_homepage_option( 'show_termine', true ) ) :
    $events = new WP_Query( array(
        'post_type'      => 'gk_event',
        'posts_per_page' => intval( gk_homepage_option( 'termine_count', 5 ) ),
        'meta_key'       => 'gk_event_start_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => array( array(
            'key'     => 'gk_event_start_date',
            'value'   => current_time( 'Y-m-d' ),
            'compare' => '>=',
            'type'    => 'DATE',
        ) ),
    ) );

    if ( $events->have_posts() ) : ?>
<section class="gk-events">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Nächste Termine</h2>
        </div>
        <div class="gk-events__track">
            <?php while ( $events->have_posts() ) : $events->the_post();
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
        <div class="gk-events__more">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'gk_event' ) ?: '/termine' ); ?>" class="gk-btn gk-btn--primary">
                Alle Termine &rarr;
            </a>
        </div>
    </div>
</section>
    <?php endif;
endif; ?>

<!-- ╔═══════════════════════════════════════════════════════════════╗
     ║  6. ENGAGEMENT — Three paths to get involved                ║
     ╚═══════════════════════════════════════════════════════════════╝ -->
<section class="gk-engage">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Werde aktiv</h2>
            <p>Es gibt viele Wege, grüne Politik in deinem Landkreis mitzugestalten.</p>
        </div>
        <div class="gk-engage__grid">
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3>Mitglied werden</h3>
                <p>Tritt den Grünen bei und gestalte Politik von der Basis aus mit. Du entscheidest, wie viel Zeit du einbringst.</p>
                <a href="https://www.gruene.de/mitglied-werden" class="gk-btn gk-btn--primary" target="_blank" rel="noopener">
                    Mitglied werden &rarr;
                </a>
            </div>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>Komm vorbei</h3>
                <p>Unsere Treffen und Veranstaltungen stehen allen offen. Lerne uns kennen &mdash; ganz unverbindlich.</p>
                <a href="#ovs" class="gk-btn gk-btn--primary">
                    Ortsverband finden
                </a>
            </div>
            <?php if ( $has_donation && $donate_url ) : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <h3>Spenden</h3>
                <p>Deine Spende ermöglicht Wahlkämpfe, Veranstaltungen und politische Arbeit vor Ort. Jeder Euro zählt.</p>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary">
                    <?php echo esc_html( $donate_cta ); ?> &rarr;
                </a>
            </div>
            <?php else : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <h3>Schreib uns</h3>
                <p>Fragen, Ideen, Kritik? Wir freuen uns über jede Nachricht und antworten so schnell wie möglich.</p>
                <a href="/kontakt" class="gk-btn gk-btn--primary">
                    Kontakt aufnehmen
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ╔═══════════════════════════════════════════════════════════════╗
     ║  7. WIDGET AREAS                                            ║
     ╚═══════════════════════════════════════════════════════════════╝ -->
<?php if ( is_active_sidebar( 'homeone' ) ) : ?>
<section class="gk-widgets gk-widgets--one">
    <div class="inner">
        <?php dynamic_sidebar( 'homeone' ); ?>
    </div>
</section>
<?php endif; ?>

<?php if ( is_active_sidebar( 'hometwo' ) ) : ?>
<section class="gk-widgets gk-widgets--two">
    <div class="inner">
        <?php dynamic_sidebar( 'hometwo' ); ?>
    </div>
</section>
<?php endif; ?>

<!-- Sticky OV prompt (visible while above the Kreiskarte section) -->
<div class="gk-sticky-ov" id="gk-sticky-ov" hidden>
    <a href="#ovs" class="gk-sticky-ov__link">
        <span class="gk-sticky-ov__text">Suchst du deinen Ortsverband?</span>
        <svg class="gk-sticky-ov__icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
    </a>
</div>

</div><!-- .home-gk -->
