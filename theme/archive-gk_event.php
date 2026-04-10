<?php
/**
 * Event Archive Template — Upcoming events list
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<section id="content"><div class="inner gk-layout clearfix">
    <div id="main" class="gk-layout__main first clearfix" role="main">

        <header class="page-header" id="termine">
            <h1 class="page-title">Termine</h1>
            <p class="event-ical-feed">
                <a href="<?php echo esc_url( home_url( '/termine/ical/' ) ); ?>" title="Alle Termine als iCal abonnieren">
                    <span class="fa fa-calendar" aria-hidden="true"></span> iCal-Feed abonnieren
                </a>
            </p>
        </header>

        <?php
        $zuordnung_terms = get_terms( array(
            'taxonomy'   => 'gk_zuordnung',
            'hide_empty' => true,
            'orderby'    => 'name',
        ) );
        $current_zuordnung = isset( $_GET['zuordnung'] ) ? sanitize_text_field( $_GET['zuordnung'] ) : '';
        ?>
        <?php if ( ! is_wp_error( $zuordnung_terms ) && count( $zuordnung_terms ) > 1 ) : ?>
        <form class="termine-filter" method="get" action="<?php echo esc_url( home_url( '/termine/' ) ); ?>">
            <label for="zuordnung">Zuordnung:</label>
            <select name="zuordnung" id="zuordnung" onchange="this.form.submit()">
                <option value="">Alle</option>
                <?php foreach ( $zuordnung_terms as $term ) : ?>
                    <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected( $current_zuordnung, $term->slug ); ?>>
                        <?php echo esc_html( $term->name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit">Filtern</button></noscript>
        </form>
        <?php endif; ?>

        <?php if ( have_posts() ) : ?>

            <div class="gk-event-list">
            <?php while ( have_posts() ) : the_post();
                $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
                $start_time = get_post_meta( get_the_ID(), 'gk_event_start_time', true );
                $all_day    = get_post_meta( get_the_ID(), 'gk_event_all_day', true );
                $end_time   = get_post_meta( get_the_ID(), 'gk_event_end_time', true );
                $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
            ?>
                <article class="gk-event-item clearfix">
                    <div class="gk-event-item__date">
                        <span class="gk-date__day"><?php echo esc_html( date_i18n( 'd', strtotime( $start_date ) ) ); ?></span>
                        <span class="gk-date__month"><?php echo esc_html( date_i18n( 'M', strtotime( $start_date ) ) ); ?></span>
                        <span class="gk-date__year"><?php echo esc_html( date_i18n( 'Y', strtotime( $start_date ) ) ); ?></span>
                    </div>
                    <div class="gk-event-item__info">
                        <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                        <?php
                        $zuordnung = wp_get_post_terms( get_the_ID(), 'gk_zuordnung', array( 'fields' => 'names' ) );
                        if ( ! is_wp_error( $zuordnung ) && ! empty( $zuordnung ) ) :
                        ?>
                            <span class="gk-badge"><?php echo esc_html( $zuordnung[0] ); ?></span>
                        <?php endif; ?>
                        <p class="gk-event-item__meta">
                            <?php if ( $all_day === '1' ) : ?>
                                Ganztägig
                            <?php elseif ( $start_time ) : ?>
                                <?php echo esc_html( $start_time ); ?> Uhr
                                <?php if ( $end_time ) echo '&ndash; ' . esc_html( $end_time ) . ' Uhr'; ?>
                            <?php endif; ?>
                            <?php if ( $location ) : ?>
                                <span class="gk-event-item__location">&bull; <?php echo esc_html( $location ); ?></span>
                            <?php endif; ?>
                        </p>
                        <?php if ( has_excerpt() ) : ?>
                            <p class="gk-event-item__desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endwhile; ?>
            </div>

            <?php the_posts_pagination( array(
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ) ); ?>

        <?php else : ?>
            <p class="keine-termine">Aktuell keine kommenden Termine.</p>
        <?php endif; ?>

    </div>

    <?php get_sidebar(); ?>
</div></section>

<?php get_footer(); ?>
