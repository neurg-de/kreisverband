<?php
/**
 * Single Event Template
 *
 * Designed for scannability: a user landing on this page needs
 * WHEN / WHERE / WHAT within two seconds, then easy calendar actions.
 *
 * @package Neurg_Kreisverband
 */

get_header();

while ( have_posts() ) : the_post();
    $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
    $start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
    $end_date   = get_post_meta( get_the_ID(), 'gk_event_end_date', true );
    $end_time   = get_post_meta( get_the_ID(), 'gk_event_end_time', true );
    $all_day    = get_post_meta( get_the_ID(), 'gk_event_all_day', true );
    $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
    $address    = get_post_meta( get_the_ID(), 'gk_event_address', true );
    $organizer  = get_post_meta( get_the_ID(), 'gk_event_organizer', true );
    $event_url  = get_post_meta( get_the_ID(), 'gk_event_url', true );

    $zuordnung      = wp_get_post_terms( get_the_ID(), 'gk_zuordnung', array( 'fields' => 'names' ) );
    $zuordnung_name = ( ! is_wp_error( $zuordnung ) && ! empty( $zuordnung ) ) ? $zuordnung[0] : '';
    $event_cats     = wp_get_post_terms( get_the_ID(), 'event_kategorie', array( 'fields' => 'names' ) );
    $event_cat      = ( ! is_wp_error( $event_cats ) && ! empty( $event_cats ) ) ? $event_cats[0] : '';

    // Build Google Calendar URL
    $gcal_url = '';
    if ( $start_date ) {
        $gcal_base = 'https://calendar.google.com/calendar/render?action=TEMPLATE';
        $gcal_base .= '&text=' . rawurlencode( get_the_title() );

        if ( $all_day === '1' ) {
            $gcal_start = str_replace( '-', '', $start_date );
            $gcal_end   = $end_date ? str_replace( '-', '', date( 'Y-m-d', strtotime( $end_date . ' +1 day' ) ) ) : $gcal_start;
            $gcal_base .= '&dates=' . $gcal_start . '/' . $gcal_end;
        } else {
            $gcal_start = str_replace( '-', '', $start_date ) . 'T' . str_replace( ':', '', $start_time ?: '0000' ) . '00';
            $gcal_end_date = $end_date ?: $start_date;
            $gcal_end_time = $end_time ?: $start_time ?: '0000';
            $gcal_end   = str_replace( '-', '', $gcal_end_date ) . 'T' . str_replace( ':', '', $gcal_end_time ) . '00';
            $gcal_base .= '&dates=' . $gcal_start . '/' . $gcal_end;
        }

        $loc_parts = array_filter( array( $location, $address ) );
        if ( $loc_parts ) {
            $gcal_base .= '&location=' . rawurlencode( implode( ', ', $loc_parts ) );
        }
        if ( get_the_content() ) {
            $gcal_base .= '&details=' . rawurlencode( wp_trim_words( wp_strip_all_tags( get_the_content() ), 40 ) );
        }
        $gcal_url = $gcal_base;
    }

    // Is this event in the past?
    $is_past = $start_date && $start_date < current_time( 'Y-m-d' );

    // Termine page for back link
    $termine_page = get_page_by_path( 'termine' );
    $termine_url  = $termine_page ? get_permalink( $termine_page ) : home_url( '/termine/' );
?>

<!-- Back navigation -->
<div class="gk-event-single__back">
    <div class="inner">
        <a href="<?php echo esc_url( $termine_url ); ?>" class="gk-event-single__back-link">
            <span class="fa fa-arrow-left" aria-hidden="true"></span> Alle Termine
        </a>
    </div>
</div>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'gk-event-single' ); ?>>

    <!-- Header: Date badge + Title + Quick meta -->
    <header class="gk-event-single__header">
        <div class="inner">
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="gk-event-single__hero-img">
                    <?php the_post_thumbnail( 'titelbild' ); ?>
                </div>
            <?php endif; ?>

            <div class="gk-event-single__header-content">
                <?php if ( $start_date ) : ?>
                <div class="gk-event-single__date-badge" aria-label="<?php echo esc_attr( date_i18n( 'j. F Y', strtotime( $start_date ) ) ); ?>">
                    <span class="gk-event-single__date-weekday"><?php echo esc_html( date_i18n( 'D', strtotime( $start_date ) ) ); ?></span>
                    <span class="gk-event-single__date-day"><?php echo esc_html( date_i18n( 'j', strtotime( $start_date ) ) ); ?></span>
                    <span class="gk-event-single__date-month"><?php echo esc_html( date_i18n( 'M Y', strtotime( $start_date ) ) ); ?></span>
                </div>
                <?php endif; ?>

                <div class="gk-event-single__title-group">
                    <?php if ( $event_cat || $zuordnung_name ) : ?>
                        <div class="gk-event-single__badges">
                            <?php if ( $event_cat ) : ?>
                                <span class="gk-badge"><?php echo esc_html( $event_cat ); ?></span>
                            <?php endif; ?>
                            <?php if ( $zuordnung_name ) : ?>
                                <span class="gk-badge"><?php echo esc_html( $zuordnung_name ); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="gk-event-single__title"><?php the_title(); ?></h1>

                    <?php if ( $is_past ) : ?>
                        <p class="gk-event-single__past-notice">Dieser Termin liegt in der Vergangenheit.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </header>

    <!-- Detail cards: structured info for quick scanning -->
    <div class="gk-event-single__details">
        <div class="inner">
            <div class="gk-event-single__detail-grid">

                <!-- When -->
                <div class="gk-event-single__detail-card">
                    <div class="gk-event-single__detail-icon" aria-hidden="true">
                        <span class="fa fa-clock"></span>
                    </div>
                    <div class="gk-event-single__detail-body">
                        <h3 class="gk-event-single__detail-label">Wann</h3>
                        <p class="gk-event-single__detail-value">
                            <?php echo esc_html( gk_format_event_date( get_the_ID() ) ); ?>
                        </p>
                    </div>
                </div>

                <!-- Where -->
                <?php if ( $location || $address ) : ?>
                <div class="gk-event-single__detail-card">
                    <div class="gk-event-single__detail-icon" aria-hidden="true">
                        <span class="fa fa-map-marker-alt"></span>
                    </div>
                    <div class="gk-event-single__detail-body">
                        <h3 class="gk-event-single__detail-label">Wo</h3>
                        <p class="gk-event-single__detail-value">
                            <?php if ( $location ) echo esc_html( $location ); ?>
                            <?php if ( $address && $address !== $location ) : ?>
                                <br><span class="gk-event-single__address"><?php echo esc_html( $address ); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php if ( $address ) : ?>
                            <a href="https://maps.google.com/?q=<?php echo rawurlencode( $address ); ?>" target="_blank" rel="noopener noreferrer" class="gk-event-single__map-link">
                                <span class="fa fa-external-link-alt" aria-hidden="true"></span> Route planen
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Who -->
                <?php if ( $organizer ) : ?>
                <div class="gk-event-single__detail-card">
                    <div class="gk-event-single__detail-icon" aria-hidden="true">
                        <span class="fa fa-users"></span>
                    </div>
                    <div class="gk-event-single__detail-body">
                        <h3 class="gk-event-single__detail-label">Veranstalter</h3>
                        <p class="gk-event-single__detail-value"><?php echo esc_html( $organizer ); ?></p>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Actions: Calendar buttons -->
            <div class="gk-event-single__actions">
                <a href="<?php the_permalink(); ?>?ical=1" class="gk-btn gk-btn--primary gk-btn--sm">
                    <span class="fa fa-download" aria-hidden="true"></span> In Kalender speichern
                </a>
                <?php if ( $gcal_url ) : ?>
                    <a href="<?php echo esc_url( $gcal_url ); ?>" target="_blank" rel="noopener noreferrer" class="gk-btn gk-btn--filter gk-btn--sm">
                        <span class="fa fa-calendar-plus" aria-hidden="true"></span> Google Kalender
                    </a>
                <?php endif; ?>
                <?php if ( $event_url ) : ?>
                    <a href="<?php echo esc_url( $event_url ); ?>" target="_blank" rel="noopener noreferrer" class="gk-btn gk-btn--filter gk-btn--sm">
                        <span class="fa fa-external-link-alt" aria-hidden="true"></span> Weitere Infos
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Content -->
    <?php if ( get_the_content() ) : ?>
    <div class="gk-event-single__content">
        <div class="inner">
            <div class="gk-event-single__body">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

</article>

<?php endwhile; ?>

<?php get_footer(); ?>
