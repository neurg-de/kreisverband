<?php
/**
 * Sidebar template
 *
 * @package Neurg_Kreisverband
 */

global $post;
$gk_ov_slug = isset( $post ) ? gk_get_post_zuordnung_slug( $post->ID ) : '';
$gk_is_ov   = $gk_ov_slug !== '' && $gk_ov_slug !== 'kreisverband';
?>

<div id="sidebar1" class="sidebar gk-layout__sidebar last clearfix" role="complementary">
    <ul>
        <?php dynamic_sidebar( 'infospalte' ); ?>
    </ul>

    <?php if ( $gk_is_ov ) :
        $ov_term = get_term_by( 'slug', $gk_ov_slug, 'gk_zuordnung' );
        if ( $ov_term ) :
            $contact = gk_get_ov_contact( $ov_term->term_id );
    ?>
        <div id="sidebar_ovkontakt" class="sidebar gk-layout__sidebar last clearfix" role="complementary">
            <h3 class="widgettitle">Kontakt</h3>
            <h4><?php echo esc_html( $ov_term->name ); ?></h4>
            <?php if ( ! empty( $contact['anschrift'] ) ) : ?>
                <p><?php echo wpautop( esc_html( $contact['anschrift'] ) ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $contact['telefon'] ) ) : ?>
                <p><?php echo esc_html( $contact['telefon'] ); ?></p>
            <?php endif; ?>
            <?php if ( ! empty( $contact['email'] ) ) : ?>
                <p><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><i class="fas fa-envelope"></i> <?php echo esc_html( $contact['email'] ); ?></a></p>
            <?php endif; ?>
            <?php if ( ! empty( $contact['www'] ) ) : ?>
                <p><a href="<?php echo esc_url( $contact['www'] ); ?>"><i class="fas fa-globe"></i> <?php echo esc_html( str_replace( array( 'https://www.', 'http://www.' ), '', $contact['www'] ) ); ?></a></p>
            <?php endif; ?>
        </div>
    <?php endif; endif; ?>
</div>
