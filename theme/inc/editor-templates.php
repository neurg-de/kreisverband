<?php
/**
 * Discoverable editorial layouts without global design permissions.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Register reusable, unsynced starting points in the block inserter. */
function gk_register_editor_patterns() {
    register_block_pattern_category( 'gk-content', array( 'label' => __( 'Verband: Inhalte', 'neurg-kreisverband' ) ) );
    $patterns = array(
        'introduction'  => array(
            'title'   => __( 'Vorstellung mit Bild und Text', 'neurg-kreisverband' ),
            'content' => '<!-- wp:columns --><div class="wp-block-columns"><!-- wp:column --><div class="wp-block-column"><!-- wp:image /--></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:heading --><h2 class="wp-block-heading">Wir vor Ort</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Hier stellen wir unseren Ortsverband und unsere Arbeit vor.</p><!-- /wp:paragraph --></div><!-- /wp:column --></div><!-- /wp:columns -->',
        ),
        'priorities'    => array(
            'title'   => __( 'Unsere Themen', 'neurg-kreisverband' ),
            'content' => '<!-- wp:heading --><h2 class="wp-block-heading">Unsere Themen</h2><!-- /wp:heading --><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Dafür setzen wir uns ein</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Beschreibe hier ein konkretes Vorhaben und warum es für die Menschen vor Ort wichtig ist.</p><!-- /wp:paragraph -->',
        ),
        'participation' => array(
            'title'   => __( 'Mitmachen und Kontakt', 'neurg-kreisverband' ),
            'content' => '<!-- wp:heading --><h2 class="wp-block-heading">Mach mit!</h2><!-- /wp:heading --><!-- wp:paragraph --><p>Du möchtest vor Ort etwas bewegen? Hier ist Platz für unsere nächsten Treffen und die passende Kontaktmöglichkeit.</p><!-- /wp:paragraph -->',
        ),
    );
    foreach ( $patterns as $name => $pattern ) {
        register_block_pattern( 'gk/' . $name, array_merge( $pattern, array( 'categories' => array( 'gk-content' ) ) ) );
    }
}
add_action( 'init', 'gk_register_editor_patterns' );

/** Make layout guidance accessible alongside the pages an OV can edit. */
function gk_register_editor_templates_page() {
    add_submenu_page( 'edit.php?post_type=page', __( 'Vorlagen & Gestaltung', 'neurg-kreisverband' ), __( 'Vorlagen & Gestaltung', 'neurg-kreisverband' ), 'edit_pages', 'gk-editor-templates', 'gk_render_editor_templates_page' );
}
add_action( 'admin_menu', 'gk_register_editor_templates_page', 30 );

/** Render guidance without exposing a global theme settings screen. */
function gk_render_editor_templates_page() {
    if ( ! current_user_can( 'edit_pages' ) ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'neurg-kreisverband' ), 403 );
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Vorlagen & Gestaltung', 'neurg-kreisverband' ); ?></h1>
        <p><?php esc_html_e( 'Öffne eine Seite deines Verbands. Über das Plus-Zeichen im Editor findest du unter „Vorlagen“ die Kategorie „Verband: Inhalte“. Du kannst diese Bausteine einfügen und anschließend Texte und Bilder bearbeiten.', 'neurg-kreisverband' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Vorstellung mit Bild und Text', 'neurg-kreisverband' ); ?></li>
            <li><?php esc_html_e( 'Unsere Themen', 'neurg-kreisverband' ); ?></li>
            <li><?php esc_html_e( 'Mitmachen und Kontakt', 'neurg-kreisverband' ); ?></li>
        </ul>
        <p><?php esc_html_e( 'Die Seitenvorlage wählst du rechts in den Seiteneinstellungen unter „Template“. Für die OV-Startseite nutzt du „OV-Startseite“, für weitere OV-Seiten „OV-Unterseite“. Die Inhalte bleiben deinem Verband zugeordnet.', 'neurg-kreisverband' ); ?></p>
        <p><?php esc_html_e( 'Unter „Verband“ kannst du als OV-Admin außerdem die Darstellung deiner Startseite, Kontaktangaben und die sichtbaren Inhaltsbereiche einstellen. Bilder lädst du über „Medien“ oder direkt über einen Bildblock hoch.', 'neurg-kreisverband' ); ?></p>
        <p><a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=page' ) ); ?>"><?php esc_html_e( 'Seiten bearbeiten', 'neurg-kreisverband' ); ?></a></p>
    </div>
    <?php
}
