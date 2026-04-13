<?php
/**
 * OV Team Overview
 *
 * Displays all persons belonging to an Ortsverband, grouped by Abteilung.
 * Route: {ov-slug}/team/
 *
 * @package Neurg_Kreisverband
 */

get_header();

$ov_slug   = sanitize_title( get_query_var( 'gk_ov_context' ) );
$ov_term   = gk_get_ov_term( $ov_slug );
$term_id   = $ov_term ? $ov_term->term_id : 0;
$ov_header = $ov_term ? gk_get_ov_header( $term_id ) : '';
$homepage_id = $ov_term ? gk_get_ov_homepage_id( $term_id ) : 0;
$ov_home     = $homepage_id ? get_permalink( $homepage_id ) : home_url( '/' );

// Query all persons for this OV.
$ov_persons = new WP_Query( array(
    'post_type'      => 'person',
    'posts_per_page' => -1,
    'orderby'        => 'menu_order title',
    'order'          => 'ASC',
    'tax_query'      => array( array(
        'taxonomy' => 'gk_zuordnung',
        'field'    => 'slug',
        'terms'    => $ov_slug,
    ) ),
) );

// Group by abteilung.
$all_persons = array();
$abt_groups  = array();
$abt_labels  = array();
$ungrouped   = array();

while ( $ov_persons->have_posts() ) : $ov_persons->the_post();
    $person = array(
        'id'        => get_the_ID(),
        'title'     => get_the_title(),
        'permalink' => get_permalink(),
        'funktion'  => get_post_meta( get_the_ID(), 'kr8mb_pers_position_funktion', true ),
        'thumb'     => has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'medium' ) : '',
    );
    $idx = count( $all_persons );
    $all_persons[] = $person;

    $terms = get_the_terms( get_the_ID(), 'abteilung' );
    if ( $terms && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $t ) {
            $abt_labels[ $t->slug ] = $t->name;
            $abt_groups[ $t->slug ][] = $idx;
        }
    } else {
        $ungrouped[] = $idx;
    }
endwhile;
wp_reset_postdata();

// Sort abteilungen alphabetically.
uksort( $abt_groups, function( $a, $b ) use ( $abt_labels ) {
    return strcasecmp( $abt_labels[ $a ], $abt_labels[ $b ] );
});
?>

<section id="content" class="gk-ov-team-page">
<div class="inner">

    <div class="gk-section-header">
        <p class="gk-section-header__context">
            <a href="<?php echo esc_url( $ov_home ); ?>"><?php echo esc_html( $ov_header ); ?></a>
        </p>
        <h1>Unser Team</h1>
    </div>

    <?php if ( ! empty( $all_persons ) ) :
        foreach ( $abt_groups as $slug => $indices ) : ?>
    <div class="gk-ov-team-page__section">
        <h2><?php echo esc_html( $abt_labels[ $slug ] ); ?></h2>
        <div class="gk-team__grid">
            <?php foreach ( $indices as $i ) : $p = $all_persons[ $i ]; ?>
            <a href="<?php echo esc_url( $p['permalink'] ); ?>" class="gk-team__card">
                <div class="gk-team__photo">
                    <?php if ( $p['thumb'] ) : echo $p['thumb']; else : ?>
                    <svg class="gk-team__placeholder" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="78" r="36" fill="currentColor" opacity=".25"/><ellipse cx="100" cy="176" rx="56" ry="46" fill="currentColor" opacity=".18"/></svg>
                    <?php endif; ?>
                </div>
                <h3 class="gk-team__name"><?php echo esc_html( $p['title'] ); ?></h3>
                <?php if ( $p['funktion'] ) : ?>
                    <p class="gk-team__role"><?php echo esc_html( $p['funktion'] ); ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
        <?php endforeach;

        if ( ! empty( $ungrouped ) ) : ?>
    <div class="gk-ov-team-page__section">
        <h2>Weitere Mitglieder</h2>
        <div class="gk-team__grid">
            <?php foreach ( $ungrouped as $i ) : $p = $all_persons[ $i ]; ?>
            <a href="<?php echo esc_url( $p['permalink'] ); ?>" class="gk-team__card">
                <div class="gk-team__photo">
                    <?php if ( $p['thumb'] ) : echo $p['thumb']; else : ?>
                    <svg class="gk-team__placeholder" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="78" r="36" fill="currentColor" opacity=".25"/><ellipse cx="100" cy="176" rx="56" ry="46" fill="currentColor" opacity=".18"/></svg>
                    <?php endif; ?>
                </div>
                <h3 class="gk-team__name"><?php echo esc_html( $p['title'] ); ?></h3>
                <?php if ( $p['funktion'] ) : ?>
                    <p class="gk-team__role"><?php echo esc_html( $p['funktion'] ); ?></p>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
        <?php endif;
    else : ?>
        <p>Keine Mitglieder gefunden.</p>
    <?php endif; ?>

</div>
</section>

<?php get_footer(); ?>
