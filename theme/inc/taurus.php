<?php
/**
 * The Taurus Integration
 *
 * Compliance badge widget, Customizer settings, and sponsor credit
 * for The Taurus (thetaurus.com) — EU Regulation 2024/900 compliance platform.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Helpers ────────────────────────────────────────────────────────────────────

/**
 * Get the site language as a two-letter code.
 *
 * @return string 'de' or 'en'
 */
function gk_taurus_get_lang() {
    $locale = get_locale();
    if ( function_exists( 'pll_current_language' ) ) {
        $locale = pll_current_language( 'locale' );
    } elseif ( defined( 'ICL_LANGUAGE_CODE' ) ) {
        $locale = ICL_LANGUAGE_CODE;
    }
    return ( str_starts_with( $locale, 'de' ) ) ? 'de' : 'en';
}

/**
 * Get a Taurus theme option.
 *
 * @param string $key     Option suffix (slug, show_credit, show_badge, badge_lang).
 * @param mixed  $default Default value.
 * @return mixed
 */
function gk_taurus_option( $key, $default = '' ) {
    return get_theme_mod( 'gk_taurus_' . $key, $default );
}

/**
 * Determine the effective badge language.
 *
 * @return string 'de' or 'en'
 */
function gk_taurus_badge_lang() {
    $setting = gk_taurus_option( 'badge_lang', 'auto' );
    if ( $setting === 'auto' ) {
        return gk_taurus_get_lang();
    }
    return $setting;
}


// ── Customizer ─────────────────────────────────────────────────────────────────

add_action( 'customize_register', 'gk_taurus_customizer' );

/**
 * Register The Taurus section and controls in the Customizer.
 */
function gk_taurus_customizer( $wp_customize ) {

    // Section
    $wp_customize->add_section( 'gk_taurus', array(
        'title'    => 'The Taurus',
        'priority' => 190,
    ) );

    // ── Profile slug ───────────────────────────────────────────────────────────
    $wp_customize->add_setting( 'gk_taurus_slug', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_title',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'gk_taurus_slug', array(
        'label'       => __( 'Profil-Slug', 'neurg-kreisverband' ),
        'description' => __( 'Ihr Organisations-Slug auf thetaurus.com (z.B. gruene-kv-freiburg)', 'neurg-kreisverband' ),
        'section'     => 'gk_taurus',
        'type'        => 'text',
    ) );

    // ── Show The Taurus in footer ─────────────────────────────────────────────
    $wp_customize->add_setting( 'gk_taurus_show_footer', array(
        'default'           => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'gk_taurus_show_footer', array(
        'label'       => __( 'The Taurus im Footer anzeigen', 'neurg-kreisverband' ),
        'description' => __( 'Zeigt das Compliance-Badge (mit Profil-Slug) oder den Sponsor-Hinweis (ohne Slug) im Footer an.', 'neurg-kreisverband' ),
        'section'     => 'gk_taurus',
        'type'        => 'checkbox',
    ) );

    // ── Badge language ─────────────────────────────────────────────────────────
    $wp_customize->add_setting( 'gk_taurus_badge_lang', array(
        'default'           => 'auto',
        'sanitize_callback' => 'gk_taurus_sanitize_lang',
        'transport'         => 'postMessage',
    ) );
    $wp_customize->add_control( 'gk_taurus_badge_lang', array(
        'label'   => __( 'Badge-Sprache', 'neurg-kreisverband' ),
        'section' => 'gk_taurus',
        'type'    => 'radio',
        'choices' => array(
            'auto' => __( 'Automatisch (Website-Sprache)', 'neurg-kreisverband' ),
            'de'   => 'Deutsch',
            'en'   => 'English',
        ),
    ) );
}

/**
 * Sanitize badge language option.
 */
function gk_taurus_sanitize_lang( $value ) {
    return in_array( $value, array( 'auto', 'de', 'en' ), true ) ? $value : 'auto';
}


// ── Footer output ──────────────────────────────────────────────────────────────

/**
 * Render The Taurus element in the footer.
 *
 * With slug: shows the live compliance badge.
 * Without slug: shows the subtle sponsor credit line.
 * Controlled by a single toggle (gk_taurus_show_footer).
 */
function gk_taurus_render_footer_taurus() {
    if ( ! gk_taurus_option( 'show_footer', true ) ) {
        return;
    }

    $slug = gk_taurus_option( 'slug', '' );
    $lang = gk_taurus_badge_lang();

    if ( $slug ) {
        // Live badge — replaces the sponsor credit
        $badge_url   = 'https://thetaurus.com/profile/org/' . rawurlencode( $slug ) . '/badge?lang=' . $lang;
        $profile_url = 'https://thetaurus.com/' . rawurlencode( $slug ) . '?utm_source=neurg&utm_medium=widget&utm_campaign=badge';
        $alt         = ( $lang === 'de' ) ? 'Compliance-Status auf The Taurus' : 'Compliance status on The Taurus';
        $title       = ( $lang === 'de' ) ? 'Compliance-Profil auf The Taurus ansehen' : 'View compliance profile on The Taurus';

        printf(
            '<div class="neurg-taurus-badge"><a href="%s" target="_blank" rel="noopener" title="%s"><img src="%s" alt="%s" loading="lazy" width="200" height="46" /></a></div>',
            esc_url( $profile_url ),
            esc_attr( $title ),
            esc_url( $badge_url ),
            esc_attr( $alt )
        );
    } else {
        // Subtle sponsor credit
        $site_lang = gk_taurus_get_lang();

        if ( $site_lang === 'de' ) {
            $url  = 'https://thetaurus.com/de?utm_source=neurg&utm_medium=theme&utm_campaign=footer';
            $text = 'Gesponsert von <a href="%s" target="_blank" rel="noopener">The Taurus</a> — Compliance für politische Werbung';
        } else {
            $url  = 'https://thetaurus.com?utm_source=neurg&utm_medium=theme&utm_campaign=footer';
            $text = 'Sponsored by <a href="%s" target="_blank" rel="noopener">The Taurus</a> — Compliance for political advertising';
        }

        printf( '<p class="neurg-sponsor-credit">' . $text . '</p>', esc_url( $url ) );
    }
}


// ── Dashboard widget ───────────────────────────────────────────────────────────

add_action( 'wp_dashboard_setup', 'gk_taurus_dashboard_widget' );

function gk_taurus_dashboard_widget() {
    // Only show when no slug is configured yet — once connected, the card disappears.
    if ( gk_taurus_option( 'slug', '' ) ) {
        return;
    }

    wp_add_dashboard_widget(
        'gk_taurus_promo',
        'The Taurus — Compliance für politische Werbung',
        'gk_taurus_dashboard_widget_cb'
    );
}

function gk_taurus_dashboard_widget_cb() {
    $customize_url = admin_url( 'customize.php?autofocus[section]=gk_taurus' );
    ?>
    <style>
        #gk_taurus_promo .inside { padding: 0 !important; }
        .gk-taurus-card { padding: 16px 12px; }
        .gk-taurus-card p { margin: 0 0 12px; color: #50575e; }
        .gk-taurus-card strong { color: #1d2327; }
        .gk-taurus-card-steps { margin: 0 0 16px; padding: 0 0 0 20px; }
        .gk-taurus-card-steps li { margin-bottom: 6px; color: #50575e; }
        .gk-taurus-card-steps code { background: #f0f0f1; padding: 2px 6px; border-radius: 3px; font-size: 12px; }
        .gk-taurus-card-actions { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
        .gk-taurus-card-actions .button-primary { background: #005e3a; border-color: #005e3a; }
        .gk-taurus-card-actions .button-primary:hover { background: #004d30; border-color: #004d30; }
        .gk-taurus-card-promo { display: inline-block; margin-top: 12px; padding: 6px 12px; background: #f0f6f3; border-left: 3px solid #005e3a; font-size: 13px; color: #1d2327; }
    </style>
    <div class="gk-taurus-card">
        <p>
            <strong>EU-Verordnung 2024/900</strong> verpflichtet politische Organisationen zur Transparenz bei Werbung.
            Mit The Taurus erfüllen Sie die Anforderungen in wenigen Minuten.
        </p>
        <ol class="gk-taurus-card-steps">
            <li>Kostenlos registrieren mit Promo-Code <code>NEURG</code></li>
            <li>Organisation anlegen und Profil-Slug festlegen</li>
            <li>Slug hier im Theme eintragen — fertig</li>
        </ol>
        <div class="gk-taurus-card-actions">
            <a href="https://thetaurus.com/de/register?promo=NEURG&utm_source=neurg&utm_medium=admin&utm_campaign=dashboard"
               class="button button-primary" target="_blank" rel="noopener">
                Kostenlos registrieren
            </a>
            <a href="<?php echo esc_url( $customize_url ); ?>" class="button">
                Slug eintragen
            </a>
        </div>
        <div class="gk-taurus-card-promo">
            Als Neurg-Theme-Nutzer erhalten Sie <strong>6 Monate kostenlosen Zugang</strong>.
        </div>
    </div>
    <?php
}


// ── Widget ─────────────────────────────────────────────────────────────────────

add_action( 'widgets_init', 'gk_taurus_register_widget' );

function gk_taurus_register_widget() {
    register_widget( 'GK_Taurus_Badge_Widget' );
}

/**
 * The Taurus Compliance Badge Widget
 */
class GK_Taurus_Badge_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'gk_taurus_badge',
            'The Taurus Compliance Badge',
            array(
                'description' => __( 'Zeigt den Compliance-Status Ihrer Organisation auf The Taurus an (EU Reg. 2024/900).', 'neurg-kreisverband' ),
            )
        );
    }

    public function widget( $args, $instance ) {
        $slug = ! empty( $instance['slug'] ) ? $instance['slug'] : gk_taurus_option( 'slug', '' );
        $lang = ! empty( $instance['lang'] ) ? $instance['lang'] : 'auto';

        if ( $lang === 'auto' ) {
            $lang = gk_taurus_get_lang();
        }

        echo $args['before_widget'];

        if ( $slug ) {
            $badge_url   = 'https://thetaurus.com/profile/org/' . rawurlencode( $slug ) . '/badge?lang=' . $lang;
            $profile_url = 'https://thetaurus.com/' . rawurlencode( $slug ) . '?utm_source=neurg&utm_medium=widget&utm_campaign=badge';
            $alt         = ( $lang === 'de' ) ? 'Compliance-Status auf The Taurus' : 'Compliance status on The Taurus';
            $title       = ( $lang === 'de' ) ? 'Compliance-Profil auf The Taurus ansehen' : 'View compliance profile on The Taurus';

            printf(
                '<div class="neurg-taurus-badge"><a href="%s" target="_blank" rel="noopener" title="%s"><img src="%s" alt="%s" loading="lazy" width="200" height="46" /></a></div>',
                esc_url( $profile_url ),
                esc_attr( $title ),
                esc_url( $badge_url ),
                esc_attr( $alt )
            );
        } else {
            if ( $lang === 'de' ) {
                $cta_text   = 'Compliance für politische Werbung sicherstellen';
                $btn_text   = 'Kostenlos starten';
                $note_text  = 'Promo-Code NEURG für kostenlosen Zugang';
                $signup_url = 'https://thetaurus.com/de/register?promo=NEURG&utm_source=neurg&utm_medium=widget&utm_campaign=signup-cta';
            } else {
                $cta_text   = 'Ensure compliance for political advertising';
                $btn_text   = 'Get started free';
                $note_text  = 'Use promo code NEURG for free access';
                $signup_url = 'https://thetaurus.com/en/register?promo=NEURG&utm_source=neurg&utm_medium=widget&utm_campaign=signup-cta';
            }

            printf(
                '<div class="neurg-taurus-cta"><p>%s</p><a href="%s" class="neurg-taurus-cta-button" target="_blank" rel="noopener">%s</a><span class="neurg-taurus-cta-note">%s</span></div>',
                esc_html( $cta_text ),
                esc_url( $signup_url ),
                esc_html( $btn_text ),
                esc_html( $note_text )
            );
        }

        echo $args['after_widget'];
    }

    public function form( $instance ) {
        $slug = ! empty( $instance['slug'] ) ? $instance['slug'] : '';
        $lang = ! empty( $instance['lang'] ) ? $instance['lang'] : 'auto';
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>">
                <?php esc_html_e( 'Profil-Slug:', 'neurg-kreisverband' ); ?>
            </label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'slug' ) ); ?>"
                   name="<?php echo esc_attr( $this->get_field_name( 'slug' ) ); ?>"
                   type="text" value="<?php echo esc_attr( $slug ); ?>"
                   placeholder="gruene-kv-freiburg" />
            <small><?php esc_html_e( 'Leer lassen, um den Slug aus dem Customizer zu verwenden.', 'neurg-kreisverband' ); ?></small>
        </p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'lang' ) ); ?>">
                <?php esc_html_e( 'Sprache:', 'neurg-kreisverband' ); ?>
            </label>
            <select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'lang' ) ); ?>"
                    name="<?php echo esc_attr( $this->get_field_name( 'lang' ) ); ?>">
                <option value="auto" <?php selected( $lang, 'auto' ); ?>><?php esc_html_e( 'Automatisch', 'neurg-kreisverband' ); ?></option>
                <option value="de" <?php selected( $lang, 'de' ); ?>>Deutsch</option>
                <option value="en" <?php selected( $lang, 'en' ); ?>>English</option>
            </select>
        </p>
        <p class="description">
            <?php esc_html_e( 'Platzieren Sie dieses Widget im Footer oder auf Ihrer Impressum-Seite. Es zeigt Ihren Live-Compliance-Status für EU-Verordnung 2024/900 und verlinkt zu Ihrem öffentlichen Profil auf The Taurus.', 'neurg-kreisverband' ); ?>
        </p>
        <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance         = array();
        $instance['slug'] = sanitize_title( $new_instance['slug'] ?? '' );
        $instance['lang'] = in_array( $new_instance['lang'] ?? '', array( 'auto', 'de', 'en' ), true )
            ? $new_instance['lang']
            : 'auto';
        return $instance;
    }
}


// ── Gutenberg Block ────────────────────────────────────────────────────────────

add_action( 'init', 'gk_taurus_register_block' );

function gk_taurus_register_block() {
    if ( ! function_exists( 'register_block_type' ) ) {
        return;
    }

    register_block_type( 'neurg/taurus-badge', array(
        'api_version'     => 3,
        'editor_script'   => 'neurg-taurus-badge-editor',
        'render_callback' => 'gk_taurus_block_render',
        'attributes'      => array(
            'slug' => array(
                'type'    => 'string',
                'default' => '',
            ),
            'lang' => array(
                'type'    => 'string',
                'default' => 'auto',
            ),
        ),
        'category'        => 'widgets',
    ) );
}

add_action( 'enqueue_block_editor_assets', 'gk_taurus_block_editor_assets' );

function gk_taurus_block_editor_assets() {
    wp_register_script(
        'neurg-taurus-badge-editor',
        GK_URI . '/lib/js/block-taurus-badge.js',
        array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
        GK_VERSION,
        true
    );
}

/**
 * Render callback for the Gutenberg block.
 */
function gk_taurus_block_render( $attributes ) {
    $slug = ! empty( $attributes['slug'] ) ? $attributes['slug'] : gk_taurus_option( 'slug', '' );
    $lang = ! empty( $attributes['lang'] ) ? $attributes['lang'] : 'auto';

    if ( $lang === 'auto' ) {
        $lang = gk_taurus_get_lang();
    }

    ob_start();

    if ( $slug ) {
        $badge_url   = 'https://thetaurus.com/profile/org/' . rawurlencode( $slug ) . '/badge?lang=' . $lang;
        $profile_url = 'https://thetaurus.com/' . rawurlencode( $slug ) . '?utm_source=neurg&utm_medium=widget&utm_campaign=badge';
        $alt         = ( $lang === 'de' ) ? 'Compliance-Status auf The Taurus' : 'Compliance status on The Taurus';
        $title       = ( $lang === 'de' ) ? 'Compliance-Profil auf The Taurus ansehen' : 'View compliance profile on The Taurus';

        printf(
            '<div class="neurg-taurus-badge"><a href="%s" target="_blank" rel="noopener" title="%s"><img src="%s" alt="%s" loading="lazy" width="200" height="46" /></a></div>',
            esc_url( $profile_url ),
            esc_attr( $title ),
            esc_url( $badge_url ),
            esc_attr( $alt )
        );
    } else {
        if ( $lang === 'de' ) {
            $cta_text   = 'Compliance für politische Werbung sicherstellen';
            $btn_text   = 'Kostenlos starten';
            $note_text  = 'Promo-Code NEURG für kostenlosen Zugang';
            $signup_url = 'https://thetaurus.com/de/register?promo=NEURG&utm_source=neurg&utm_medium=widget&utm_campaign=signup-cta';
        } else {
            $cta_text   = 'Ensure compliance for political advertising';
            $btn_text   = 'Get started free';
            $note_text  = 'Use promo code NEURG for free access';
            $signup_url = 'https://thetaurus.com/en/register?promo=NEURG&utm_source=neurg&utm_medium=widget&utm_campaign=signup-cta';
        }

        printf(
            '<div class="neurg-taurus-cta"><p>%s</p><a href="%s" class="neurg-taurus-cta-button" target="_blank" rel="noopener">%s</a><span class="neurg-taurus-cta-note">%s</span></div>',
            esc_html( $cta_text ),
            esc_url( $signup_url ),
            esc_html( $btn_text ),
            esc_html( $note_text )
        );
    }

    return ob_get_clean();
}
