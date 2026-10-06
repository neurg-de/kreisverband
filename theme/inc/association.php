<?php
/**
 * Configurable names for the two editorial levels; legacy IDs remain stable.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Available terminology, without assigning a fixed hierarchy to a name. */
function gk_association_types() {
    return array(
        'kreisverband'    => array(
			'singular'     => 'Kreisverband',
			'plural'       => 'Kreisverbände',
			'abbreviation' => 'KV',
		),
        'stadtverband'    => array(
			'singular'     => 'Stadtverband',
			'plural'       => 'Stadtverbände',
			'abbreviation' => 'SV',
		),
        'bezirksverband'  => array(
			'singular'     => 'Bezirksverband',
			'plural'       => 'Bezirksverbände',
			'abbreviation' => 'BV',
		),
        'regionsverband'  => array(
			'singular'     => 'Regionsverband',
			'plural'       => 'Regionsverbände',
			'abbreviation' => 'RV',
		),
        'regionalverband' => array(
			'singular'     => 'Regionalverband',
			'plural'       => 'Regionalverbände',
			'abbreviation' => 'RV',
		),
        'landesverband'   => array(
			'singular'     => 'Landesverband',
			'plural'       => 'Landesverbände',
			'abbreviation' => 'LV',
		),
        'bundesverband'   => array(
			'singular'     => 'Bundesverband',
			'plural'       => 'Bundesverbände',
			'abbreviation' => 'Bund',
		),
        'ortsverband'     => array(
			'singular'     => 'Ortsverband',
			'plural'       => 'Ortsverbände',
			'abbreviation' => 'OV',
		),
        'ortsgruppe'      => array(
			'singular'     => 'Ortsgruppe',
			'plural'       => 'Ortsgruppen',
			'abbreviation' => 'OG',
		),
        'stadtteilgruppe' => array(
			'singular'     => 'Stadtteilgruppe',
			'plural'       => 'Stadtteilgruppen',
			'abbreviation' => 'STG',
		),
        'regionalgruppe'  => array(
			'singular'     => 'Regionalgruppe',
			'plural'       => 'Regionalgruppen',
			'abbreviation' => 'RG',
		),
        'bezirksgruppe'   => array(
			'singular'     => 'Bezirksgruppe',
			'plural'       => 'Bezirksgruppen',
			'abbreviation' => 'BG',
		),
        'gemeindeverband' => array(
			'singular'     => 'Gemeindeverband',
			'plural'       => 'Gemeindeverbände',
			'abbreviation' => 'GV',
		),
        'ortsverein'      => array(
			'singular'     => 'Ortsverein',
			'plural'       => 'Ortsvereine',
			'abbreviation' => 'OV',
		),
        'unterbezirk'     => array(
			'singular'     => 'Unterbezirk',
			'plural'       => 'Unterbezirke',
			'abbreviation' => 'UB',
		),
    );
}

/**
 * Validate an explicitly selected level, including custom terminology.
 *
 * @param array  $info Setup settings.
 * @param string $level Primary or secondary.
 * @return array|null Valid display forms, or null for incomplete configuration.
 */
function gk_association_level( $info, $level ) {
    $type  = $info[ $level . '_type' ] ?? '';
    $types = gk_association_types();
    if ( ! is_string( $type ) ) {
        return null;
    }
    if ( isset( $types[ $type ] ) ) {
        $forms  = $types[ $type ];
        $prefix = $info[ $level . '_prefix' ] ?? '';
        if ( is_string( $prefix ) && '' !== trim( $prefix ) ) {
            $forms['abbreviation'] = sanitize_text_field( $prefix );
        }
        return $forms;
    }
    if ( 'custom' !== $type ) {
        return null;
    }
    $forms = array();
    foreach ( array( 'singular', 'plural', 'abbreviation' ) as $form ) {
        $value = $info[ $level . '_' . $form ] ?? '';
        if ( ! is_string( $value ) || '' === trim( sanitize_text_field( $value ) ) ) {
            return null;
        }
        $forms[ $form ] = sanitize_text_field( $value );
    }
    $prefix = $info[ $level . '_prefix' ] ?? '';
    if ( is_string( $prefix ) && '' !== trim( sanitize_text_field( $prefix ) ) ) {
        $forms['abbreviation'] = sanitize_text_field( $prefix );
    }
    return $forms;
}

/**
 * Read display terminology; unconfigured installations retain KV/OV wording.
 *
 * @param string $level Primary or secondary.
 * @param string $form Singular, plural or abbreviation.
 * @return string Display label.
 */
function gk_association_label( $level = 'primary', $form = 'singular' ) {
    $info  = get_option( 'gk_kv_info', array() );
    $label = gk_association_level( is_array( $info ) ? $info : array(), $level );
    if ( null === $label ) {
        $types = gk_association_types();
        $label = $types[ 'secondary' === $level ? 'ortsverband' : 'kreisverband' ];
    }
    return $label[ $form ] ?? $label['singular'];
}

/**
 * Sanitize both levels as one unit, preserving valid prior choices on errors.
 *
 * @param array $input Submitted setup settings.
 * @return array Validated structure fields.
 */
function gk_sanitize_association_structure( $input ) {
    $clean = array();
    foreach ( array( 'primary', 'secondary' ) as $level ) {
        $forms = gk_association_level( $input, $level );
        if ( null === $forms ) {
            add_settings_error( 'gk_kv_info', 'association_structure', __( 'Bitte die Bezeichnungen beider Ebenen auswählen. Bei einer eigenen Bezeichnung sind Einzahl, Mehrzahl und Kürzel erforderlich.', 'neurg-kreisverband' ) );
            $old = get_option( 'gk_kv_info', array() );
            return is_array( $old ) ? array_intersect_key( $old, array_flip( array( 'primary_type', 'primary_singular', 'primary_plural', 'primary_abbreviation', 'secondary_type', 'secondary_singular', 'secondary_plural', 'secondary_abbreviation', 'primary_prefix', 'secondary_prefix', 'secondary_url_prefix' ) ) ) : array();
        }
        $clean[ $level . '_type' ]   = $input[ $level . '_type' ];
        $clean[ $level . '_prefix' ] = is_string( $input[ $level . '_prefix' ] ?? null ) ? sanitize_text_field( $input[ $level . '_prefix' ] ) : '';
        if ( 'custom' === $input[ $level . '_type' ] ) {
            $forms['abbreviation'] = sanitize_text_field( $input[ $level . '_abbreviation' ] );
        }
        foreach ( $forms as $form => $value ) {
            $clean[ $level . '_' . $form ] = $value;
        }
    }
    $prefix                        = $input['secondary_url_prefix'] ?? '';
    $clean['secondary_url_prefix'] = is_string( $prefix ) ? sanitize_title( $prefix ) : '';
    return $clean;
}

/** Refresh only the root term's display name after setup changes. */
function gk_sync_association_name() {
    $term = get_term_by( 'slug', 'kreisverband', 'gk_zuordnung' );
    $name = gk_association_label();
    if ( $term && $term->name !== $name ) {
        wp_update_term( $term->term_id, 'gk_zuordnung', array( 'name' => $name ) );
    }
}
add_action( 'update_option_gk_kv_info', 'gk_sync_association_name' );
add_action( 'add_option_gk_kv_info', 'gk_sync_association_name' );

/**
 * Translate historical role names at display time, without changing rights.
 *
 * @param string $translation Translated string.
 * @param string $text Original string.
 * @param string $context Translation context.
 * @return string Display name.
 */
function gk_association_role_label( $translation, $text, $context ) {
    if ( 'User role' !== $context ) {
        return $translation;
    }
    $primary   = gk_association_label( 'primary', 'abbreviation' );
    $secondary = gk_association_label( 'secondary', 'abbreviation' );
    $labels    = array(
        'KV-Autor'                 => $primary . '-Autor',
        'KV-Autor (mit OV-Zugang)' => $primary . '-Autor (mit ' . $secondary . '-Zugang)',
        'OV-Admin'                 => $secondary . '-Admin',
        'OV-Autor'                 => $secondary . '-Autor',
    );
    return $labels[ $text ] ?? $translation;
}
add_filter( 'gettext_with_context', 'gk_association_role_label', 10, 3 );

/**
 * Display configured names for the existing page templates.
 *
 * @param array $templates Page template names keyed by file.
 * @return array Template names.
 */
function gk_association_page_templates( $templates ) {
    foreach ( array(
		'page-OV.php'      => '-Startseite',
		'page-ov-info.php' => '-Unterseite',
		'page-ov-news.php' => '-Aktuelles',
	) as $file => $suffix ) {
        if ( isset( $templates[ $file ] ) ) {
            $templates[ $file ] = gk_association_label( 'secondary', 'abbreviation' ) . $suffix;
        }
    }
    return $templates;
}
add_filter( 'theme_page_templates', 'gk_association_page_templates' );

/**
 * Required terminology selectors at the start of the setup form.
 *
 * @param array $info Current setup settings.
 */
function gk_render_association_setup( $info ) {
    ?>
    <table class="form-table">
        <?php
        foreach ( array(
			'primary'   => 'Hauptverband',
			'secondary' => 'Untergliederungen',
		) as $level => $title ) :
			?>
        <tr>
            <th><label for="gk-<?php echo esc_attr( $level ); ?>-type"><?php echo esc_html( $title ); ?> *</label></th>
            <td>
                <select id="gk-<?php echo esc_attr( $level ); ?>-type" name="gk_kv_info[<?php echo esc_attr( $level ); ?>_type]" class="gk-association-type" data-level="<?php echo esc_attr( $level ); ?>" required>
                    <option value="">— Bezeichnung auswählen —</option>
                    <?php foreach ( gk_association_types() as $value => $forms ) : ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $info[ $level . '_type' ] ?? '', $value ); ?>><?php echo esc_html( $forms[ 'secondary' === $level ? 'plural' : 'singular' ] ); ?></option>
                    <?php endforeach; ?>
                    <option value="custom" <?php selected( $info[ $level . '_type' ] ?? '', 'custom' ); ?>>Eigene Bezeichnung</option>
                </select>
                <div id="gk-<?php echo esc_attr( $level ); ?>-custom">
                    <p class="description">Bei „Eigene Bezeichnung“ alle drei Felder ausfüllen.</p>
                    <?php
                    foreach ( array(
						'singular'     => 'Einzahl',
						'plural'       => 'Mehrzahl',
						'abbreviation' => 'Kürzel',
					) as $form => $label ) :
						?>
                    <p><label for="gk-<?php echo esc_attr( $level . '-' . $form ); ?>"><?php echo esc_html( $label ); ?></label><br>
                    <input id="gk-<?php echo esc_attr( $level . '-' . $form ); ?>" name="gk_kv_info[<?php echo esc_attr( $level . '_' . $form ); ?>]" value="<?php echo esc_attr( $info[ $level . '_' . $form ] ?? '' ); ?>" type="text" class="regular-text"></p>
                    <?php endforeach; ?>
                </div>
                <p><label for="gk-<?php echo esc_attr( $level ); ?>-prefix">Anzeigepräfix / Kürzel</label><br>
                <input id="gk-<?php echo esc_attr( $level ); ?>-prefix" name="gk_kv_info[<?php echo esc_attr( $level ); ?>_prefix]" value="<?php echo esc_attr( $info[ $level . '_prefix' ] ?? '' ); ?>" type="text" class="regular-text">
                <br><span class="description">Optional, z.B. „STG“. Leer verwendet das Kürzel der gewählten Bezeichnung. Gilt für Navigation, Rollenanzeigen und neue automatisch angelegte Namen.</span></p>
            </td>
        </tr>
        <?php endforeach; ?>
        <tr><th><label for="gk-secondary-url-prefix">URL-Präfix neuer Untergliederungen</label></th>
            <td><input id="gk-secondary-url-prefix" name="gk_kv_info[secondary_url_prefix]" value="<?php echo esc_attr( $info['secondary_url_prefix'] ?? '' ); ?>" type="text" class="regular-text">
            <p class="description">Optional, z.B. „stadtteil“ für „stadtteil-musterort“. Leer verwendet das Anzeigepräfix in URL-Schreibweise. Bestehende Slugs und Kontozuordnungen bleiben erhalten.</p></td></tr>
    </table>
    <script>
    document.querySelectorAll('.gk-association-type').forEach(function(select) {
        var fields = document.getElementById('gk-' + select.dataset.level + '-custom');
        function update() {
            var custom = select.value === 'custom';
            fields.hidden = !custom;
            fields.querySelectorAll('input').forEach(function(input) { input.required = custom; });
        }
        select.addEventListener('change', update);
        update();
    });
    </script>
    <?php
}

/** URL prefix for new entries; never rename persisted scopes or accounts. */
function gk_association_url_prefix() {
    $info   = get_option( 'gk_kv_info', array() );
    $prefix = is_array( $info ) && is_string( $info['secondary_url_prefix'] ?? null ) ? sanitize_title( $info['secondary_url_prefix'] ) : '';
    if ( ! $prefix ) {
        $prefix = sanitize_title( gk_association_label( 'secondary', 'abbreviation' ) );
    }
    return $prefix ? $prefix : 'verband';
}

/**
 * Generate a prefixed slug when the entry has no explicitly supplied URL slug.
 *
 * @param string $name New entry name.
 * @return string Slug for a new assignment.
 */
function gk_association_new_slug( $name ) {
    $prefix = gk_association_url_prefix();
    $slug   = sanitize_title( $name );
    return str_starts_with( $slug, $prefix . '-' ) ? $slug : $prefix . '-' . $slug;
}
