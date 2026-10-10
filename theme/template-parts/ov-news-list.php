<?php
/**
 * Shared OV news cards for homepage and paginated rubric/archive views.
 *
 * @package Neurg_Kreisverband
 */

$news_query = $args['query'];
?>
<section class="gk-news">
    <div class="inner">
        <div class="gk-section-header"><h2><?php echo esc_html( $args['title'] ); ?></h2></div>
        <?php if ( $news_query->have_posts() ) : ?>
        <div class="gk-news__grid">
            <?php
            $idx = 0;
            while ( $news_query->have_posts() ) :
                $news_query->the_post();
                $featured = 0 === $idx++;
                ?>
            <article class="gk-card <?php echo $featured ? 'gk-card--featured' : ''; ?>">
                <?php if ( has_post_thumbnail() ) : ?>
                <a href="<?php the_permalink(); ?>" class="gk-card__thumb" aria-hidden="true" tabindex="-1"><?php the_post_thumbnail( $featured ? 'large' : 'listenansicht' ); ?></a>
                <?php endif; ?>
                <div class="gk-card__body">
                    <time class="gk-card__date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time>
                    <h3 class="gk-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                    <?php if ( $featured ) : ?>
                    <p class="gk-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
                    <?php endif; ?>
                </div>
            </article>
            <?php endwhile; ?>
        </div>
        <?php elseif ( ! empty( $args['empty_message'] ) ) : ?>
        <p><?php esc_html_e( 'Noch keine Beiträge in diesem Bereich.', 'neurg-kreisverband' ); ?></p>
        <?php endif; ?>
        <?php if ( ! empty( $args['archive_url'] ) ) : ?>
        <p><a class="gk-btn gk-btn--primary" href="<?php echo esc_url( $args['archive_url'] ); ?>"><?php esc_html_e( 'Alle Beiträge', 'neurg-kreisverband' ); ?></a></p>
        <?php endif; ?>
    </div>
</section>
<?php wp_reset_postdata(); ?>
