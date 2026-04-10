<?php
/**
 * Single Event Template
 *
 * @package Neurg_Kreisverband
 */

get_header(); ?>

<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">

    <?php while ( have_posts() ) : the_post();
        $start_date = get_post_meta( get_the_ID(), 'gk_event_start_date', true );
        $location   = get_post_meta( get_the_ID(), 'gk_event_location', true );
        $address    = get_post_meta( get_the_ID(), 'gk_event_address', true );
        $organizer  = get_post_meta( get_the_ID(), 'gk_event_organizer', true );
        $event_url  = get_post_meta( get_the_ID(), 'gk_event_url', true );
    ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <?php if ( has_post_thumbnail() ) : ?>
                <div class="titelbild">
                    <?php the_post_thumbnail( 'titelbild' ); ?>
                </div>
            <?php endif; ?>

            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>

                <div class="event-meta">
                    <?php if ( $start_date ) : ?>
                        <p class="event-date">
                            <span class="fa fa-calendar" aria-hidden="true"></span>
                            <?php echo esc_html( gk_format_event_date( get_the_ID() ) ); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ( $location || $address ) : ?>
                        <p class="event-location">
                            <span class="fa fa-map-marker" aria-hidden="true"></span>
                            <?php echo esc_html( $location ); ?>
                            <?php if ( $address && $address !== $location ) : ?>
                                <br><small><?php echo esc_html( $address ); ?></small>
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if ( $organizer ) : ?>
                        <p class="event-organizer">
                            <span class="fa fa-users" aria-hidden="true"></span>
                            <?php echo esc_html( $organizer ); ?>
                        </p>
                    <?php endif; ?>

                    <?php if ( $event_url ) : ?>
                        <p class="event-link">
                            <a href="<?php echo esc_url( $event_url ); ?>" target="_blank" rel="noopener noreferrer">Weitere Informationen</a>
                        </p>
                    <?php endif; ?>

                    <p class="event-ical">
                        <a href="<?php the_permalink(); ?>?ical=1" title="Termin herunterladen">
                            <span class="fa fa-download" aria-hidden="true"></span> iCal
                        </a>
                    </p>
                </div>
            </header>

            <div class="entry-content inner">
                <?php the_content(); ?>
            </div>
        </article>

    <?php endwhile; ?>

    </main>
</div>

<?php get_sidebar(); ?>
<?php get_footer(); ?>
