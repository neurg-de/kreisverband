<?php
/**
 * Template for the "Termine" page — shows all upcoming events.
 *
 * Automatically matched by WordPress for the page with slug "termine".
 *
 * @package Neurg_Kreisverband
 */

get_header();

$zuordnung_terms   = get_terms( array(
    'taxonomy'   => 'gk_zuordnung',
    'hide_empty' => true,
    'orderby'    => 'name',
) );
$current_zuordnung = isset( $_GET['zuordnung'] ) ? sanitize_text_field( $_GET['zuordnung'] ) : '';

$args = array(
    'post_type'      => 'gk_event',
    'posts_per_page' => -1,
    'meta_key'       => 'gk_event_start_date',
    'orderby'        => 'meta_value',
    'order'          => 'ASC',
    'meta_query'     => array( array(
        'key'     => 'gk_event_start_date',
        'value'   => current_time( 'Y-m-d' ),
        'compare' => '>=',
        'type'    => 'DATE',
    ) ),
);

if ( $current_zuordnung ) {
    $args['tax_query'] = array( array(
        'taxonomy' => 'gk_zuordnung',
        'field'    => 'slug',
        'terms'    => $current_zuordnung,
    ) );
}

$events = new WP_Query( $args );
?>

<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title">Termine</h1>
        <div class="gk-hero__actions">
            <a href="<?php echo esc_url( home_url( '/termine/ical/' ) ); ?>" class="gk-btn gk-btn--primary gk-btn--sm">
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
            <?php foreach ( $zuordnung_terms as $term ) : ?>
                <a class="gk-btn gk-btn--filter gk-btn--sm<?php echo ( $current_zuordnung === $term->slug ) ? ' is-active' : ''; ?>"
                   href="<?php echo esc_url( add_query_arg( 'zuordnung', $term->slug, get_permalink() ) ); ?>">
                    <?php echo esc_html( $term->name ); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if ( $events->have_posts() ) :
            $current_month = '';
            $track_open    = false;

            while ( $events->have_posts() ) : $events->the_post();
                $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
                $start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
                $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
                $month_key  = date_i18n( 'F Y', strtotime( $start_date ) );

                // Month group header
                if ( $month_key !== $current_month ) :
                    if ( $track_open ) echo '</div>';
                    $current_month = $month_key;
                ?>
                    <h2 class="gk-events__month-header"><?php echo esc_html( $month_key ); ?></h2>
                    <div class="gk-events__track">
                <?php
                    $track_open = true;
                endif;

                // Build meta parts
                $meta_parts = array();
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
                            <?php echo implode( ' &middot; ', $meta_parts ); ?>
                            <?php if ( ! is_wp_error( $zuordnung ) && ! empty( $zuordnung ) ) : ?>
                                <span class="gk-badge"><?php echo esc_html( $zuordnung[0] ); ?></span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <span class="gk-event__arrow" aria-hidden="true">&rsaquo;</span>
                </a>
            <?php endwhile;

            if ( $track_open ) echo '</div>';
        ?>

        <?php else : ?>
            <div class="gk-events__empty">
                <p>Aktuell keine kommenden Termine.</p>
                <a href="<?php echo esc_url( home_url( '/termine/ical/' ) ); ?>" class="gk-btn gk-btn--primary gk-btn--sm">
                    iCal abonnieren
                </a>
            </div>
        <?php endif; ?>

        <?php wp_reset_postdata(); ?>

    </div>
</section>

<?php get_footer(); ?>
