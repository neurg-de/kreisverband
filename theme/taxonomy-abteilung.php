<?php
/**
 * Abteilung Taxonomy Archive
 *
 * Displays persons belonging to an abteilung, scoped by zuordnung:
 *   /abteilung/{slug}              → KV persons
 *   /{ov-slug}/abteilung/{slug}    → OV persons
 *
 * @package Neurg_Kreisverband
 */

get_header();

$term       = get_queried_object();
$ov_context = get_query_var( 'gk_ov_context' );
$ov_term    = $ov_context ? gk_get_ov_term( $ov_context ) : false;

// Context label for the heading.
$context_label = $ov_term ? $ov_term->name : '';
?>

<section id="content">
<div class="inner">

    <div class="gk-section-header">
        <h1><?php echo esc_html( $term->name ); ?></h1>
        <?php if ( $context_label ) : ?>
            <p class="gk-section-header__context"><?php echo esc_html( $context_label ); ?></p>
        <?php endif; ?>
        <?php if ( $term->description ) : ?>
            <p><?php echo esc_html( $term->description ); ?></p>
        <?php endif; ?>
    </div>

    <?php if ( have_posts() ) : ?>
    <div class="gk-team__grid">
        <?php while ( have_posts() ) : the_post();
            $person = array(
                'id'        => get_the_ID(),
                'title'     => get_the_title(),
                'permalink' => get_permalink(),
                'amt'       => get_post_meta( get_the_ID(), 'kr8mb_pers_pos_amt', true ),
                'thumb'     => has_post_thumbnail() ? get_the_post_thumbnail( get_the_ID(), 'medium' ) : '',
            );
        ?>
        <a href="<?php echo esc_url( $person['permalink'] ); ?>" class="gk-team__card">
            <div class="gk-team__photo">
                <?php if ( $person['thumb'] ) : echo $person['thumb']; else : ?>
                <svg class="gk-team__placeholder" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><circle cx="100" cy="78" r="36" fill="currentColor" opacity=".25"/><ellipse cx="100" cy="176" rx="56" ry="46" fill="currentColor" opacity=".18"/></svg>
                <?php endif; ?>
            </div>
            <h3 class="gk-team__name"><?php echo esc_html( $person['title'] ); ?></h3>
            <?php if ( $person['amt'] ) : ?>
                <p class="gk-team__role"><?php echo esc_html( $person['amt'] ); ?></p>
            <?php endif; ?>
        </a>
        <?php endwhile; ?>
    </div>
    <?php else : ?>
        <p>Keine Personen gefunden.</p>
    <?php endif; ?>

</div>
</section>

<?php get_footer(); ?>
