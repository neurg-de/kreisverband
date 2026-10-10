<?php
/**
 * Template for the "Termine" page — shows all upcoming events.
 *
 * Automatically matched by WordPress for the page with slug "termine".
 *
 * @package Neurg_Kreisverband
 */

get_header();

$zuordnung_terms   = get_terms(
    array(
		'taxonomy'   => 'gk_zuordnung',
		'hide_empty' => true,
		'orderby'    => 'name',
    )
);
$current_zuordnung = gk_event_scope();

$args = array(
    'post_type'      => 'gk_event',
    'posts_per_page' => -1,
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
);

$args = gk_scope_event_query( $args, $current_zuordnung );

$events = new WP_Query( $args );
?>

<main id="main">
<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title">Termine</h1>
        <div class="gk-hero__actions">
            <a href="<?php echo esc_url( gk_event_ical_url( $current_zuordnung ) ); ?>" class="gk-btn gk-btn--primary gk-btn--sm">
                <span class="fa fa-calendar" aria-hidden="true"></span> iCal-Feed abonnieren
            </a>
        </div>
    </div>
</section>

<section class="gk-events">
    <div class="inner">

        <?php if ( ! is_wp_error( $zuordnung_terms ) && count( $zuordnung_terms ) > 1 ) : ?>
        <div class="gk-events__toolbar">
            <a class="gk-btn gk-btn--filter gk-btn--sm<?php echo $current_zuordnung ? '' : ' is-active'; ?>"
                href="<?php echo esc_url( get_permalink() ); ?>">Alle</a>
            <?php foreach ( $zuordnung_terms as $area_term ) : ?>
                <a class="gk-btn gk-btn--filter gk-btn--sm<?php echo ( $current_zuordnung === $area_term->slug ) ? ' is-active' : ''; ?>"
                    href="<?php echo esc_url( add_query_arg( 'zuordnung', $area_term->slug, get_permalink() ) ); ?>">
                    <?php echo esc_html( $area_term->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php
        get_template_part(
            'template-parts/event-list',
            null,
            array(
				'events' => $events,
				'scope'  => $current_zuordnung,
            )
        );
		?>

    </div>
</section>
</main>

<?php get_footer(); ?>
