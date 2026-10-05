<?php // phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- Existing template filename is required for WordPress routing and saved page templates.
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
$ov_slug = gk_get_post_zuordnung_slug( $post->ID );
$ov_term = gk_get_ov_term( $ov_slug );
$ov_type = $ov_term ? gk_get_ov_type( $ov_term->term_id ) : 'ov';
$contact = $ov_term ? gk_get_ov_contact( $ov_term->term_id ) : array();
$term_id = $ov_term ? $ov_term->term_id : 0;

// Homepage settings.
$landing_mode  = gk_get_ov_homepage_option( $term_id, 'landing_mode', 'standard' );
$hero_title    = gk_get_ov_homepage_option( $term_id, 'hero_title' );
$hero_subtitle = gk_get_ov_homepage_option( $term_id, 'hero_subtitle' );
$hero_img_id   = (int) gk_get_ov_homepage_option( $term_id, 'hero_image', 0 );
$cta_label     = gk_get_ov_homepage_option( $term_id, 'cta_label' );
$cta_url       = gk_get_ov_homepage_option( $term_id, 'cta_url' );

$show_team    = '1' === gk_get_ov_homepage_option( $term_id, 'show_team', '1' );
$show_news    = '1' === gk_get_ov_homepage_option( $term_id, 'show_aktuelles', '1' );
$show_events  = '1' === gk_get_ov_homepage_option( $term_id, 'show_termine', '1' );
$show_contact = '1' === gk_get_ov_homepage_option( $term_id, 'show_contact', '1' );
$show_engage  = '1' === gk_get_ov_homepage_option( $term_id, 'show_engage', '1' );

$ov_header = gk_get_ov_header( $term_id );

$donation     = function_exists( 'gk_get_donation_config' ) ? gk_get_donation_config() : array();
$has_donation = ! empty( $donation['enabled'] );
$donate_url   = gk_get_donate_page_url( $donation );
$donate_cta   = $donation['cta_text'] ?? 'Jetzt spenden';

// Fallbacks from the page itself.
while ( have_posts() ) :
	the_post();
    if ( ! $hero_title ) {
		$hero_title = get_the_title();
    }
    if ( ! $hero_img_id && has_post_thumbnail() ) {
		$hero_img_id = get_post_thumbnail_id();
    }
endwhile;

// ════════════════════════════════════════════════════════════════════════════
// WERBUNG — Dedicated engagement landing page (exits early)
// ════════════════════════════════════════════════════════════════════════════
if ( 'werbung' === $ov_type ) :
    $gemeinde = $ov_term ? $ov_term->name : '';
    // Strip "Grüne in " or "OV " prefix for cleaner display.
    $gemeinde_clean = preg_replace( '/^(Gr(ü|ue)ne in |OV |Ortsverband |Ortsgruppe )/u', '', $gemeinde );
    $contact_email  = $contact['email'] ?? '';
    $kv_info        = get_option( 'gk_kv_info', array() );
    $kv_email       = $kv_info['email'] ?? '';
    $email_target   = ( $contact_email ? $contact_email : $kv_email );
    get_template_part(
        'template-parts/ov-werbung',
        null,
        array(
			'gemeinde'      => $gemeinde_clean,
			'hero_img_id'   => $hero_img_id,
			'hero_title'    => $hero_title,
			'hero_subtitle' => $hero_subtitle,
			'email'         => $email_target,
			'contact'       => $contact,
			'ov_header'     => $ov_header,
        )
    );
    get_footer();
    return;
endif;
?>

<div id="primary" class="content-area">
<main id="main" class="site-main" role="main">
<div class="home-gk ov-homepage ov-type-<?php echo esc_attr( $ov_type ); ?>">

<?php
// ════════════════════════════════════════════════════════════════════════════
// 1. HERO — Rendered per landing mode
// ════════════════════════════════════════════════════════════════════════════

switch ( $landing_mode ) :

	// ── ELECTION ────────────────────────────────────────────────────────────────
	case 'election':
		$el_name   = gk_get_ov_homepage_option( $term_id, 'election_name', '' );
		$el_date   = gk_get_ov_homepage_option( $term_id, 'election_date', '' );
		$el_slogan = gk_get_ov_homepage_option( $term_id, 'election_slogan', '' );
		$el_img    = $hero_img_id;
		$days_left = $el_date ? max( 0, (int) ( ( strtotime( $el_date ) - time() ) / 86400 ) ) : null;
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
        <?php if ( null !== $days_left && $days_left > 0 ) : ?>
            <div class="gk-hero__countdown">
                <span class="gk-hero__countdown-num"><?php echo esc_html( $days_left ); ?></span>
                <span class="gk-hero__countdown-label">Tage bis zur Wahl</span>
            </div>
        <?php elseif ( 0 === $days_left ) : ?>
            <div class="gk-hero__countdown gk-hero__countdown--today">
                <span class="gk-hero__countdown-label">Heute ist Wahltag!</span>
            </div>
        <?php endif; ?>
        <h1 class="gk-hero__title"><?php echo esc_html( ( $el_slogan ? $el_slogan : $hero_title ) ); ?></h1>
        <div class="gk-hero__actions">
            <?php if ( $cta_label && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $cta_label ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
		<?php
        break;

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
		<?php
        break;

	// ── NEWS ────────────────────────────────────────────────────────────────────
	case 'news':
		$latest   = new WP_Query(
            array_merge(
                array(
					'post_type'      => 'post',
					'posts_per_page' => 1,
                ),
                gk_ov_query_args( $ov_slug )
            )
        );
		$has_post = $latest->have_posts();
		if ( $has_post ) {
			$latest->the_post();
		}
		$post_img = $has_post && has_post_thumbnail() ? get_post_thumbnail_id() : 0;
		$bg_img   = ( $hero_img_id ? $hero_img_id : $post_img );
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
            <p class="gk-hero__subtitle"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
            <div class="gk-hero__actions">
                <a href="<?php the_permalink(); ?>" class="gk-btn gk-btn--primary">Weiterlesen</a>
            </div>
        <?php endif; ?>
    </div>
</section>
		<?php
		if ( $has_post ) {
			wp_reset_postdata();
		}
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
        <h1 class="gk-hero__title"><?php echo esc_html( ( $hero_title ? $hero_title : 'Unterstütze grüne Politik vor Ort' ) ); ?></h1>
        <?php if ( $hero_subtitle ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>
        <?php if ( $fr_goal > 0 ) : ?>
            <div class="gk-fundraising-bar">
                <div class="gk-fundraising-bar__track">
                    <div class="gk-fundraising-bar__fill" style="width:<?php echo esc_html( $fr_pct ); ?>%"></div>
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
		<?php
        break;

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
		<?php
        break;

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
        break;

endswitch; // landing_mode.
?>


<?php
// ════════════════════════════════════════════════════════════════════════════
// 2. PAGE CONTENT — From WP editor (optional)
// ════════════════════════════════════════════════════════════════════════════
rewind_posts();
while ( have_posts() ) :
	the_post();
    if ( trim( wp_strip_all_tags( get_the_content() ) ) ) :
		?>
<section class="ov-content">
    <div class="inner">
        <div class="ov-content__body entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
		<?php
    endif;
endwhile;
?>


<?php
// ════════════════════════════════════════════════════════════════════════════
// 3. TEAM — Tabbed by Abteilung, auto-rotating
// ════════════════════════════════════════════════════════════════════════════
if ( $show_team ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the shared renderer.
    echo gk_render_team_carousel( array( 'zuordnung' => $ov_slug ) );
}
?>


<?php
// ════════════════════════════════════════════════════════════════════════════
// 4. AKTUELLES — News card grid
// ════════════════════════════════════════════════════════════════════════════
if ( $show_news ) :
    $ov_posts = new WP_Query(
        array(
			'post_type'      => 'post',
			'posts_per_page' => 6,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
			'tax_query'      => array(
				array(
					'taxonomy' => 'gk_zuordnung',
					'field'    => 'slug',
					'terms'    => $ov_slug,
				),
			),
        )
    );

    if ( $ov_posts->have_posts() ) :
		?>
<section class="gk-news">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Aktuelles</h2>
        </div>
        <div class="gk-news__grid">
            <?php
            $idx = 0;
            while ( $ov_posts->have_posts() ) :
				$ov_posts->the_post();
                $is_featured = ( 0 === $idx );
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
                        <p class="gk-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                    <?php endif; ?>
                </div>
            </article>
				<?php
                ++$idx;
            endwhile;
            wp_reset_postdata();
            ?>
        </div>
    </div>
</section>
		<?php
    endif;
endif;
?>


<?php
// ════════════════════════════════════════════════════════════════════════════
// 5. TERMINE — Event track
// ════════════════════════════════════════════════════════════════════════════
if ( $show_events ) :
    $ov_events = new WP_Query(
        array(
			'post_type'      => 'gk_event',
			'post_status'    => 'publish',
			'has_password'   => false,
			'posts_per_page' => 5,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
			'meta_key'       => 'gk_event_start_date',
			'orderby'        => 'meta_value',
			'order'          => 'ASC',
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
			'meta_query'     => array(
				array(
					'key'     => 'gk_event_start_date',
					'value'   => current_time( 'Y-m-d' ),
					'compare' => '>=',
					'type'    => 'DATE',
				),
			),
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Required taxonomy/date constraints preserve the configured content scope; WordPress caches these queries.
			'tax_query'      => array(
				array(
					'taxonomy' => 'gk_zuordnung',
					'field'    => 'slug',
					'terms'    => $ov_slug,
				),
			),
        )
    );

    if ( $ov_events->have_posts() ) :
		?>
<section class="gk-events">
    <div class="inner">
        <div class="gk-section-header">
            <h2>N&auml;chste Termine</h2>
            <a class="gk-btn gk-btn--sm" href="<?php echo esc_url( gk_event_ical_url( $ov_slug ) ); ?>"><?php esc_html_e( 'OV-Termine als iCal abonnieren', 'neurg-kreisverband' ); ?></a>
        </div>
        <div class="gk-events__track">
            <?php
            while ( $ov_events->have_posts() ) :
				$ov_events->the_post();
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
                        <?php
                        if ( $start_time ) {
							echo esc_html( $start_time ) . ' Uhr';}
						?>
                        <?php
                        if ( $start_time && $location ) {
							echo ' &middot; ';}
						?>
                        <?php
                        if ( $location ) {
							echo esc_html( $location );}
						?>
                    </p>
                    <?php endif; ?>
                </div>
                <span class="gk-event__arrow" aria-hidden="true">&rarr;</span>
            </a>
				<?php
            endwhile;
			wp_reset_postdata();
			?>
        </div>
    </div>
</section>
		<?php
    endif;
endif;
?>


<?php
// ════════════════════════════════════════════════════════════════════════════
// 6. CONTACT — From term meta
// ════════════════════════════════════════════════════════════════════════════
$ov_social_links = $show_contact && $ov_term ? gk_build_social_links( $contact ) : array();
$has_contact     = $show_contact && $ov_term && (
    ! empty( $contact['anschrift'] ) || ! empty( $contact['email'] ) ||
    ! empty( $contact['telefon'] ) || ! empty( $contact['www'] ) ||
    ! empty( $ov_social_links )
);

if ( $has_contact ) :
	?>
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
// 7. ENGAGEMENT — CTAs
// ════════════════════════════════════════════════════════════════════════════
if ( $show_engage ) :
	?>
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



<?php get_footer(); ?>
