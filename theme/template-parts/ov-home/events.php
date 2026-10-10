<?php
/**
 * OV homepage section: events.
 *
 * @package Neurg_Kreisverband
 */

if ( $show_events ) :
    $ov_events = new WP_Query(
        array(
			'post_type'      => 'gk_event',
			'post_status'    => 'publish',
			'has_password'   => false,
			'posts_per_page' => isset( $events_limit ) ? (int) $events_limit : 5,
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
            <a class="gk-btn gk-btn--sm" href="<?php echo esc_url( gk_event_ical_url( $ov_slug ) ); ?>"><?php esc_html_e( 'Termine dieses Bereichs als iCal abonnieren', 'neurg-kreisverband' ); ?></a>
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
        <?php $ov_termine_url = $ov_term ? gk_ov_subpage_url( $ov_term->term_id, 'termine' ) : ''; ?>
        <?php if ( $ov_termine_url ) : ?>
        <p class="gk-events__more"><a href="<?php echo esc_url( $ov_termine_url ); ?>" class="gk-btn gk-btn--primary"><?php esc_html_e( 'Alle Termine', 'neurg-kreisverband' ); ?></a></p>
        <?php endif; ?>
    </div>
</section>
		<?php
    endif;
endif;
?>
