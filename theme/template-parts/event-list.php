<?php
/**
 * Upcoming events grouped by month (shared by the Termine page and OV Termine pages).
 *
 * @package Neurg_Kreisverband
 *
 * @var array $args {
 *     @type WP_Query $events Event query.
 *     @type string   $scope     Area slug for the iCal link ('' = all).
 *     @type bool     $show_area Show the area badge on each event.
 *     @type string   $empty_text Message when no events match.
 * }
 */

$events            = $args['events'];
$current_zuordnung = $args['scope'] ?? '';
$show_area         = $args['show_area'] ?? true;
$empty_text        = $args['empty_text'] ?? '';
?>
<?php
if ( $events->have_posts() ) :
	$current_month = '';
	$track_open    = false;

	while ( $events->have_posts() ) :
		$events->the_post();
		$start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
		$start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
		$location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
		$month_key  = date_i18n( 'F Y', strtotime( $start_date ) );

		// Month group header.
		if ( $month_key !== $current_month ) :
			if ( $track_open ) {
				echo '</div>';
			}
			$current_month = $month_key;
			?>
                    <h2 class="gk-events__month-header"><?php echo esc_html( $month_key ); ?></h2>
                    <div class="gk-events__track">
					<?php
                    $track_open = true;
                endif;

		// Build meta parts.
		$meta_parts     = array();
		$formatted_date = gk_format_event_date( get_the_ID() );
		if ( $formatted_date ) {
			$meta_parts[] = esc_html( $formatted_date );
		}
		if ( $location ) {
			$meta_parts[] = esc_html( $location );
		}

		$zuordnung = wp_get_post_terms( get_the_ID(), 'gk_zuordnung', array( 'fields' => 'names' ) );
		?>
                <a href="<?php the_permalink(); ?>" class="gk-event">
                    <div class="gk-event__date">
                        <span class="gk-event__day"><?php echo esc_html( date_i18n( 'j', strtotime( $start_date ) ) ); ?></span>
                        <span class="gk-event__month"><?php echo esc_html( date_i18n( 'M', strtotime( $start_date ) ) ); ?></span>
                    </div>
                    <div class="gk-event__info">
                        <h3 class="gk-event__title"><?php the_title(); ?></h3>
                        <p class="gk-event__meta">
					<?php echo esc_html( implode( ' · ', array_map( 'wp_specialchars_decode', $meta_parts ) ) ); ?>
					<?php if ( $show_area && ! is_wp_error( $zuordnung ) && ! empty( $zuordnung ) ) : ?>
                                <span class="gk-badge"><?php echo esc_html( $zuordnung[0] ); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="gk-event__arrow" aria-hidden="true">&rsaquo;</span>
                </a>
				<?php
            endwhile;

	if ( $track_open ) {
		echo '</div>';
	}
	?>

<?php else : ?>
            <div class="gk-events__empty">
                <p><?php echo esc_html( $empty_text ? $empty_text : 'Aktuell keine kommenden Termine.' ); ?></p>
                <a href="<?php echo esc_url( gk_event_ical_url( $current_zuordnung ) ); ?>" class="gk-btn gk-btn--primary gk-btn--sm">
                    iCal abonnieren
                </a>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>
