<?php
/**
 * Progressively enhanced team: a scrollable row, expandable to all departments.
 *
 * @package Neurg_Kreisverband
 */

$team_id = $args['id'];
$groups  = $args['groups'];
?>
<section class="gk-team gk-team--carousel" data-team-carousel aria-labelledby="<?php echo esc_attr( $team_id ); ?>-heading">
    <div class="inner">
        <div class="gk-section-header">
            <h2 id="<?php echo esc_attr( $team_id ); ?>-heading"><?php echo esc_html( $args['title'] ); ?></h2>
        </div>
        <div class="gk-team__tabs" role="tablist" aria-label="Team-Abteilungen" hidden>
            <?php foreach ( $groups as $slug => $group ) : ?>
                <button type="button" class="gk-team__tab" id="<?php echo esc_attr( $team_id . '-tab-' . $slug ); ?>" role="tab" aria-selected="false" aria-controls="<?php echo esc_attr( $team_id . '-panel-' . $slug ); ?>" tabindex="-1">
                    <?php echo esc_html( $group['label'] ); ?>
                    <span class="gk-team__count"><?php echo esc_html( count( $group['people'] ) ); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <div id="<?php echo esc_attr( $team_id ); ?>-panels" class="gk-team__panels">
            <?php foreach ( $groups as $slug => $group ) : ?>
                <div class="gk-team__panel" id="<?php echo esc_attr( $team_id . '-panel-' . $slug ); ?>" aria-labelledby="<?php echo esc_attr( $team_id . '-label-' . $slug ); ?>">
                    <h3 class="gk-team__group-title" id="<?php echo esc_attr( $team_id . '-label-' . $slug ); ?>"><?php echo esc_html( $group['label'] ); ?></h3>
                    <div class="gk-team__track" tabindex="0" aria-label="<?php echo esc_attr( $group['label'] ); ?>">
                        <?php foreach ( $group['people'] as $person ) : ?>
                            <a class="gk-team__card" href="<?php echo esc_url( get_permalink( $person['id'] ) ); ?>">
                                <div class="gk-team__photo">
                                    <?php if ( has_post_thumbnail( $person['id'] ) ) : ?>
                                        <?php echo get_the_post_thumbnail( $person['id'], 'medium', array( 'alt' => '' ) ); ?>
                                    <?php else : ?>
                                        <span class="gk-team__placeholder" aria-hidden="true"><?php echo esc_html( gk_person_initials( $person['name'] ) ); ?></span>
                                    <?php endif; ?>
                                </div>
                                <h4 class="gk-team__name"><?php echo esc_html( $person['name'] ); ?></h4>
                                <p class="gk-team__role"><?php echo esc_html( $person['role'] ); ?>
                                    <?php get_template_part( 'template-parts/person-affiliation', null, array( 'person_id' => $person['id'] ) ); ?>
                                </p>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="gk-team__controls" hidden>
            <div class="gk-team__navigation">
                <button type="button" class="gk-team__tab" data-team-prev aria-label="Vorherige Mitglieder">←</button>
                <span class="gk-team__status" aria-live="off"></span>
                <button type="button" class="gk-team__tab" data-team-next aria-label="Nächste Mitglieder">→</button>
                <button type="button" class="gk-team__tab" data-team-play aria-pressed="false">Automatik pausieren</button>
            </div>
            <button type="button" class="gk-btn gk-btn--primary" data-team-expand aria-expanded="false" aria-controls="<?php echo esc_attr( $team_id ); ?>-panels">Alle anzeigen</button>
        </div>
    </div>
</section>
