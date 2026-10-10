<?php
/**
 * OV Termine: upcoming events of one area inside its own header and menu.
 * Route: /{ov-homepage}/termine/ (see inc/ov-subpages.php).
 *
 * @package Neurg_Kreisverband
 */

$context = gk_ov_subpage_context();
$ov_term = $context['term'];
$config  = gk_ov_subpage_config( $ov_term->term_id );

get_header();

$args   = gk_scope_event_query(
    array(
		'post_type'      => 'gk_event',
		'posts_per_page' => -1,
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key -- Upcoming events are ordered by their start date.
		'meta_key'       => 'gk_event_start_date',
		'orderby'        => 'meta_value',
		'order'          => 'ASC',
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Only upcoming events.
		'meta_query'     => array(
			array(
				'key'     => 'gk_event_start_date',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'DATE',
			),
		),
    ),
    $ov_term->slug
);
$events = new WP_Query( $args );
?>

<main id="main">
<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <p class="gk-hero__kicker"><?php echo esc_html( gk_get_ov_header( $ov_term->term_id ) ); ?></p>
        <h1 class="gk-hero__title"><?php echo esc_html( $config['label_termine'] ); ?></h1>
        <div class="gk-hero__actions">
            <a href="<?php echo esc_url( gk_event_ical_url( $ov_term->slug ) ); ?>" class="gk-btn gk-btn--primary gk-btn--sm">
                <span class="fa fa-calendar" aria-hidden="true"></span> <?php esc_html_e( 'iCal-Feed abonnieren', 'neurg-kreisverband' ); ?>
            </a>
        </div>
    </div>
</section>

<section class="gk-events">
    <div class="inner">
        <?php
        get_template_part(
            'template-parts/event-list',
            null,
            array(
				'events'    => $events,
				'scope'     => $ov_term->slug,
				'show_area' => false,
            )
        );
		?>
    </div>
</section>
</main>

<?php get_footer(); ?>
