<?php
/**
 * Shared explicit affiliation badge for person cards.
 *
 * @package Neurg_Kreisverband
 */

$affiliation = gk_person_affiliation_label( $args['person_id'] ?? get_the_ID() );
if ( $affiliation ) :
    ?>
    <span class="gk-person-affiliation"><?php echo esc_html( $affiliation ); ?></span>
<?php endif; ?>
