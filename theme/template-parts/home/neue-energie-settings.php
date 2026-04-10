<?php
/**
 * Settings for the Neue Energie homepage variant.
 *
 * Provides render and sanitize callbacks for the Verband settings page.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Landing mode definitions.
 *
 * Each mode changes the Hero + Intro sections above the Kreiskarte.
 * The rest of the page (map, news, events, engage) stays the same.
 */
function gk_get_landing_modes() {
    return array(
        'standard' => array(
            'label' => 'Willkommen',
            'icon'  => '&#x1F3E0;',
            'desc'  => 'Allzweck-Startseite: Hero-Bild, Titel, Untertitel und Intro-Karten. Ideal für den täglichen Betrieb.',
        ),
        'election' => array(
            'label' => 'Wahlkampf',
            'icon'  => '&#x1F5F3;',
            'desc'  => 'Wahlkampf-Modus mit Countdown, Slogan und Aufruf zum Mitmachen. Am besten in den Wochen vor einer Wahl.',
        ),
        'candidate' => array(
            'label' => 'Kandidat:in',
            'icon'  => '&#x1F9D1;',
            'desc'  => 'Stellt eine Kandidat:in in den Mittelpunkt: großes Foto, Name, Zitat. Für Personalisierungskampagnen.',
        ),
        'news' => array(
            'label' => 'Aktuelles',
            'icon'  => '&#x1F4F0;',
            'desc'  => 'Zeigt den neuesten Beitrag prominent an. Wenn es eine wichtige Nachricht oder Stellungnahme gibt.',
        ),
        'fundraising' => array(
            'label' => 'Spendenaktion',
            'icon'  => '&#x1F49A;',
            'desc'  => 'Spendenaufruf mit Fortschrittsbalken und Ziel. Für Fundraising-Kampagnen und Spendenaktionen.',
        ),
        'minimal' => array(
            'label' => 'Direkt zum OV',
            'icon'  => '&#x1F4CD;',
            'desc'  => 'Minimaler Hero, schneller Einstieg zur Kreiskarte. Wenn der Fokus auf den Ortsverbänden liegt.',
        ),
    );
}

/**
 * Render settings fields for the Neue Energie variant.
 *
 * @param array $s Current saved settings for this variant.
 */
function gk_home_neue_energie_render( $s ) {
    $prefix = 'gk_homepage[variants][neue-energie]';
    $modes  = gk_get_landing_modes();
    $active = $s['landing_mode'] ?? 'standard';
    ?>
    <h3>Landing-Modus</h3>
    <p class="description" style="margin-bottom:1rem;">Bestimmt, wie die Startseite oberhalb der Kreiskarte aussieht. Wähle den Modus, der zu eurer aktuellen Situation passt.</p>

    <div class="gk-landing-modes" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:12px; margin-bottom:2rem;">
        <?php foreach ( $modes as $slug => $mode ) : ?>
        <label class="gk-landing-mode-card" style="display:block; padding:16px; border:2px solid <?php echo $active === $slug ? '#005538' : '#ddd'; ?>; border-radius:12px; cursor:pointer; background:<?php echo $active === $slug ? '#f0faf5' : '#fff'; ?>; transition:border-color .2s;">
            <input type="radio" name="<?php echo $prefix; ?>[landing_mode]"
                   value="<?php echo esc_attr( $slug ); ?>"
                   <?php checked( $active, $slug ); ?>
                   style="margin-right:8px;" />
            <span style="font-size:1.3em; vertical-align:middle;"><?php echo $mode['icon']; ?></span>
            <strong style="font-size:1.05em;"><?php echo esc_html( $mode['label'] ); ?></strong>
            <p class="description" style="margin:6px 0 0 26px;"><?php echo esc_html( $mode['desc'] ); ?></p>
        </label>
        <?php endforeach; ?>
    </div>

    <!-- ── Standard mode settings ─────────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="standard" <?php if ( $active !== 'standard' ) echo 'style="display:none"'; ?>>
        <h3>Hero-Bereich</h3>
        <table class="form-table">
            <tr>
                <th><label>Hero-Titel</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[hero_title]"
                           value="<?php echo esc_attr( $s['hero_title'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Gemeinsam für einen grünen Landkreis" />
                    <p class="description">Leer = Seitentitel wird verwendet.</p>
                </td>
            </tr>
            <tr>
                <th><label>Hero-Untertitel</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[hero_subtitle]"
                           value="<?php echo esc_attr( $s['hero_subtitle'] ?? '' ); ?>"
                           class="large-text" />
                </td>
            </tr>
            <tr>
                <th>Hero-Bild</th>
                <td>
                    <?php $img_id = absint( $s['hero_image'] ?? 0 ); ?>
                    <div class="gk-media-preview" data-target="ne-hero-image">
                        <?php if ( $img_id ) echo wp_get_attachment_image( $img_id, 'medium' ); ?>
                    </div>
                    <input type="hidden" class="gk-media-value" id="ne-hero-image"
                           name="<?php echo $prefix; ?>[hero_image]"
                           value="<?php echo esc_attr( $img_id ); ?>" />
                    <button type="button" class="button gk-media-pick" data-target="ne-hero-image">Bild wählen</button>
                    <?php if ( $img_id ) : ?>
                        <button type="button" class="button gk-media-remove" data-target="ne-hero-image">Entfernen</button>
                    <?php endif; ?>
                    <p class="description">Vollbild-Hintergrund. Leer = Beitragsbild der Startseite.</p>
                </td>
            </tr>
            <tr>
                <th><label>Primärer CTA</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[cta_label]"
                           value="<?php echo esc_attr( $s['cta_label'] ?? '' ); ?>"
                           style="width:200px"
                           placeholder="z.B. Mitmachen" />
                    <input type="url" name="<?php echo $prefix; ?>[cta_url]"
                           value="<?php echo esc_attr( $s['cta_url'] ?? '' ); ?>"
                           style="width:300px"
                           placeholder="https://..." />
                    <p class="description">Gelber Button im Hero. Leer = nicht angezeigt.</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Election mode settings ─────────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="election" <?php if ( $active !== 'election' ) echo 'style="display:none"'; ?>>
        <h3>Wahlkampf-Einstellungen</h3>
        <table class="form-table">
            <tr>
                <th><label>Wahl-Name</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[election_name]"
                           value="<?php echo esc_attr( $s['election_name'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Bundestagswahl 2025" />
                </td>
            </tr>
            <tr>
                <th><label>Wahltag</label></th>
                <td>
                    <input type="date" name="<?php echo $prefix; ?>[election_date]"
                           value="<?php echo esc_attr( $s['election_date'] ?? '' ); ?>" />
                    <p class="description">Für den Countdown. Wird nach dem Wahltag automatisch ausgeblendet.</p>
                </td>
            </tr>
            <tr>
                <th><label>Slogan</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[election_slogan]"
                           value="<?php echo esc_attr( $s['election_slogan'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Jede Stimme zählt für unseren Landkreis!" />
                </td>
            </tr>
            <tr>
                <th>Hero-Bild</th>
                <td>
                    <?php $eimg = absint( $s['election_image'] ?? $s['hero_image'] ?? 0 ); ?>
                    <div class="gk-media-preview" data-target="ne-election-image">
                        <?php if ( $eimg ) echo wp_get_attachment_image( $eimg, 'medium' ); ?>
                    </div>
                    <input type="hidden" class="gk-media-value" id="ne-election-image"
                           name="<?php echo $prefix; ?>[election_image]"
                           value="<?php echo esc_attr( $eimg ); ?>" />
                    <button type="button" class="button gk-media-pick" data-target="ne-election-image">Bild wählen</button>
                    <?php if ( $eimg ) : ?>
                        <button type="button" class="button gk-media-remove" data-target="ne-election-image">Entfernen</button>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><label>CTA-Text</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[election_cta_label]"
                           value="<?php echo esc_attr( $s['election_cta_label'] ?? 'Wahlprogramm lesen' ); ?>"
                           style="width:200px" />
                    <input type="url" name="<?php echo $prefix; ?>[election_cta_url]"
                           value="<?php echo esc_attr( $s['election_cta_url'] ?? '' ); ?>"
                           style="width:300px"
                           placeholder="https://..." />
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Candidate mode settings ────────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="candidate" <?php if ( $active !== 'candidate' ) echo 'style="display:none"'; ?>>
        <h3>Kandidat:in-Einstellungen</h3>
        <table class="form-table">
            <tr>
                <th><label>Name</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[candidate_name]"
                           value="<?php echo esc_attr( $s['candidate_name'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Maria Musterfrau" />
                </td>
            </tr>
            <tr>
                <th><label>Amt / Rolle</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[candidate_role]"
                           value="<?php echo esc_attr( $s['candidate_role'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Direktkandidatin für den Wahlkreis Starnberg" />
                </td>
            </tr>
            <tr>
                <th><label>Zitat</label></th>
                <td>
                    <textarea name="<?php echo $prefix; ?>[candidate_quote]"
                              rows="3" class="large-text"
                              placeholder="z.B. Für einen Landkreis, der Klimaschutz lebt."><?php echo esc_textarea( $s['candidate_quote'] ?? '' ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th>Foto</th>
                <td>
                    <?php $cimg = absint( $s['candidate_image'] ?? 0 ); ?>
                    <div class="gk-media-preview" data-target="ne-candidate-image">
                        <?php if ( $cimg ) echo wp_get_attachment_image( $cimg, 'medium' ); ?>
                    </div>
                    <input type="hidden" class="gk-media-value" id="ne-candidate-image"
                           name="<?php echo $prefix; ?>[candidate_image]"
                           value="<?php echo esc_attr( $cimg ); ?>" />
                    <button type="button" class="button gk-media-pick" data-target="ne-candidate-image">Bild wählen</button>
                    <?php if ( $cimg ) : ?>
                        <button type="button" class="button gk-media-remove" data-target="ne-candidate-image">Entfernen</button>
                    <?php endif; ?>
                    <p class="description">Hochformat empfohlen. Wird neben dem Zitat angezeigt.</p>
                </td>
            </tr>
            <tr>
                <th><label>CTA-Text</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[candidate_cta_label]"
                           value="<?php echo esc_attr( $s['candidate_cta_label'] ?? 'Mehr erfahren' ); ?>"
                           style="width:200px" />
                    <input type="url" name="<?php echo $prefix; ?>[candidate_cta_url]"
                           value="<?php echo esc_attr( $s['candidate_cta_url'] ?? '' ); ?>"
                           style="width:300px"
                           placeholder="https://..." />
                </td>
            </tr>
        </table>
    </div>

    <!-- ── News mode settings ─────────────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="news" <?php if ( $active !== 'news' ) echo 'style="display:none"'; ?>>
        <h3>Aktuelles-Einstellungen</h3>
        <table class="form-table">
            <tr>
                <th><label>Überschrift</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[news_heading]"
                           value="<?php echo esc_attr( $s['news_heading'] ?? 'Aktuell' ); ?>"
                           style="width:200px" />
                    <p class="description">Kleine Überschrift über dem Beitrag. Der neueste Beitrag wird automatisch geladen.</p>
                </td>
            </tr>
            <tr>
                <th>Hero-Bild</th>
                <td>
                    <?php $nimg = absint( $s['news_image'] ?? $s['hero_image'] ?? 0 ); ?>
                    <div class="gk-media-preview" data-target="ne-news-image">
                        <?php if ( $nimg ) echo wp_get_attachment_image( $nimg, 'medium' ); ?>
                    </div>
                    <input type="hidden" class="gk-media-value" id="ne-news-image"
                           name="<?php echo $prefix; ?>[news_image]"
                           value="<?php echo esc_attr( $nimg ); ?>" />
                    <button type="button" class="button gk-media-pick" data-target="ne-news-image">Bild wählen</button>
                    <?php if ( $nimg ) : ?>
                        <button type="button" class="button gk-media-remove" data-target="ne-news-image">Entfernen</button>
                    <?php endif; ?>
                    <p class="description">Hintergrundbild. Leer = Beitragsbild des neuesten Beitrags.</p>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Fundraising mode settings ──────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="fundraising" <?php if ( $active !== 'fundraising' ) echo 'style="display:none"'; ?>>
        <h3>Spendenaktion-Einstellungen</h3>
        <table class="form-table">
            <tr>
                <th><label>Kampagnen-Titel</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[fundraising_title]"
                           value="<?php echo esc_attr( $s['fundraising_title'] ?? '' ); ?>"
                           class="large-text"
                           placeholder="z.B. Gemeinsam für den Wahlkampf 2025" />
                </td>
            </tr>
            <tr>
                <th><label>Beschreibung</label></th>
                <td>
                    <textarea name="<?php echo $prefix; ?>[fundraising_text]"
                              rows="3" class="large-text"
                              placeholder="z.B. Mit deiner Spende ermöglichst du Plakate, Flyer und Veranstaltungen..."><?php echo esc_textarea( $s['fundraising_text'] ?? '' ); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><label>Spendenziel (&euro;)</label></th>
                <td>
                    <input type="number" name="<?php echo $prefix; ?>[fundraising_goal]"
                           value="<?php echo esc_attr( $s['fundraising_goal'] ?? '' ); ?>"
                           min="0" step="100" style="width:120px"
                           placeholder="5000" />
                </td>
            </tr>
            <tr>
                <th><label>Aktueller Stand (&euro;)</label></th>
                <td>
                    <input type="number" name="<?php echo $prefix; ?>[fundraising_current]"
                           value="<?php echo esc_attr( $s['fundraising_current'] ?? '' ); ?>"
                           min="0" step="10" style="width:120px"
                           placeholder="0" />
                    <p class="description">Manuell pflegen oder per Shortcode aus Twingle aktualisieren.</p>
                </td>
            </tr>
            <tr>
                <th>Hero-Bild</th>
                <td>
                    <?php $fimg = absint( $s['fundraising_image'] ?? $s['hero_image'] ?? 0 ); ?>
                    <div class="gk-media-preview" data-target="ne-fundraising-image">
                        <?php if ( $fimg ) echo wp_get_attachment_image( $fimg, 'medium' ); ?>
                    </div>
                    <input type="hidden" class="gk-media-value" id="ne-fundraising-image"
                           name="<?php echo $prefix; ?>[fundraising_image]"
                           value="<?php echo esc_attr( $fimg ); ?>" />
                    <button type="button" class="button gk-media-pick" data-target="ne-fundraising-image">Bild wählen</button>
                    <?php if ( $fimg ) : ?>
                        <button type="button" class="button gk-media-remove" data-target="ne-fundraising-image">Entfernen</button>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <!-- ── Minimal mode settings ─────────────────────────────────── -->
    <div class="gk-mode-settings" data-mode="minimal" <?php if ( $active !== 'minimal' ) echo 'style="display:none"'; ?>>
        <p class="description" style="padding:1rem 0;">Minimaler Modus: Zeigt einen Titel und einen Pfeil zur Kreiskarte.</p>
        <table class="form-table">
            <tr>
                <th><label>Titel</label></th>
                <td>
                    <input type="text" name="<?php echo $prefix; ?>[minimal_title]"
                           value="<?php echo esc_attr( $s['minimal_title'] ?? '' ); ?>"
                           class="regular-text"
                           placeholder="<?php echo esc_attr( gk_get_kv_info( 'name' ) ?: 'Kreisverband' ); ?>" />
                    <p class="description">Leer lassen, um den Namen aus der Ersteinrichtung zu verwenden.</p>
                </td>
            </tr>
        </table>
    </div>

    <h3>Sektionen</h3>
    <table class="form-table">
        <tr>
            <th>Kreiskarte</th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo $prefix; ?>[show_kreiskarte]" value="1"
                           <?php checked( $s['show_kreiskarte'] ?? true ); ?> />
                    Kreiskarte prominent anzeigen
                </label>
                <?php if ( ! function_exists( 'gk_has_kreiskarte_data' ) || ! gk_has_kreiskarte_data() ) : ?>
                    <p class="description" style="color:#b32d2e;">
                        Noch keine Kreiskarte vorhanden.
                        <a href="<?php echo admin_url( 'admin.php?page=kreiskarte-generator' ); ?>">Jetzt erstellen &rarr;</a>
                    </p>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <th>Aktuelles</th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo $prefix; ?>[show_aktuelles]" value="1"
                           <?php checked( $s['show_aktuelles'] ?? true ); ?> />
                    Aktuelle Beiträge als Karten-Grid
                </label>
                &nbsp;&mdash;&nbsp;
                <label>
                    <input type="number" name="<?php echo $prefix; ?>[aktuelles_count]"
                           value="<?php echo esc_attr( $s['aktuelles_count'] ?? 6 ); ?>"
                           min="1" max="12" style="width:60px" />
                    Beiträge
</label>
            </td>
        </tr>
        <tr>
            <th>Termine</th>
            <td>
                <label>
                    <input type="checkbox" name="<?php echo $prefix; ?>[show_termine]" value="1"
                           <?php checked( $s['show_termine'] ?? true ); ?> />
                    Termine als horizontale Timeline
                </label>
                &nbsp;&mdash;&nbsp;
                <label>
                    <input type="number" name="<?php echo $prefix; ?>[termine_count]"
                           value="<?php echo esc_attr( $s['termine_count'] ?? 5 ); ?>"
                           min="1" max="20" style="width:60px" />
                    Termine
                </label>
            </td>
        </tr>
    </table>

    <!-- Toggle mode-specific settings panels -->
    <script>
    jQuery(function($) {
        $('input[name$="[landing_mode]"]').on('change', function() {
            var mode = $(this).val();
            $('.gk-mode-settings').hide();
            $('.gk-mode-settings[data-mode="' + mode + '"]').show();
            // Update card borders
            $('.gk-landing-mode-card').css({ 'border-color': '#ddd', 'background': '#fff' });
            $(this).closest('.gk-landing-mode-card').css({ 'border-color': '#005538', 'background': '#f0faf5' });
        });
    });
    </script>
    <?php
}

/**
 * Sanitize settings for the Neue Energie variant.
 *
 * @param array $input Raw POST data for this variant.
 * @return array Sanitized settings.
 */
function gk_home_neue_energie_sanitize( $input ) {
    if ( ! is_array( $input ) ) return array();

    $valid_modes = array_keys( gk_get_landing_modes() );
    $mode = in_array( $input['landing_mode'] ?? '', $valid_modes, true )
        ? $input['landing_mode'] : 'standard';

    return array(
        // Mode selector
        'landing_mode'       => $mode,

        // Standard mode
        'hero_title'         => sanitize_text_field( $input['hero_title'] ?? '' ),
        'hero_subtitle'      => sanitize_text_field( $input['hero_subtitle'] ?? '' ),
        'hero_image'         => absint( $input['hero_image'] ?? 0 ),
        'cta_label'          => sanitize_text_field( $input['cta_label'] ?? '' ),
        'cta_url'            => esc_url_raw( $input['cta_url'] ?? '' ),

        // Election mode
        'election_name'      => sanitize_text_field( $input['election_name'] ?? '' ),
        'election_date'      => preg_match( '/^\d{4}-\d{2}-\d{2}$/', $input['election_date'] ?? '' ) ? $input['election_date'] : '',
        'election_slogan'    => sanitize_text_field( $input['election_slogan'] ?? '' ),
        'election_image'     => absint( $input['election_image'] ?? 0 ),
        'election_cta_label' => sanitize_text_field( $input['election_cta_label'] ?? 'Wahlprogramm lesen' ),
        'election_cta_url'   => esc_url_raw( $input['election_cta_url'] ?? '' ),

        // Candidate mode
        'candidate_name'      => sanitize_text_field( $input['candidate_name'] ?? '' ),
        'candidate_role'      => sanitize_text_field( $input['candidate_role'] ?? '' ),
        'candidate_quote'     => sanitize_textarea_field( $input['candidate_quote'] ?? '' ),
        'candidate_image'     => absint( $input['candidate_image'] ?? 0 ),
        'candidate_cta_label' => sanitize_text_field( $input['candidate_cta_label'] ?? 'Mehr erfahren' ),
        'candidate_cta_url'   => esc_url_raw( $input['candidate_cta_url'] ?? '' ),

        // News mode
        'news_heading'       => sanitize_text_field( $input['news_heading'] ?? 'Aktuell' ),
        'news_image'         => absint( $input['news_image'] ?? 0 ),

        // Fundraising mode
        'fundraising_title'   => sanitize_text_field( $input['fundraising_title'] ?? '' ),
        'fundraising_text'    => sanitize_textarea_field( $input['fundraising_text'] ?? '' ),
        'fundraising_goal'    => absint( $input['fundraising_goal'] ?? 0 ),
        'fundraising_current' => absint( $input['fundraising_current'] ?? 0 ),
        'fundraising_image'   => absint( $input['fundraising_image'] ?? 0 ),

        // Sections (shared across all modes)
        'show_kreiskarte'    => ! empty( $input['show_kreiskarte'] ),
        'show_aktuelles'     => ! empty( $input['show_aktuelles'] ),
        'aktuelles_count'    => min( 12, max( 1, absint( $input['aktuelles_count'] ?? 6 ) ) ),
        'show_termine'       => ! empty( $input['show_termine'] ),
        'termine_count'      => min( 20, max( 1, absint( $input['termine_count'] ?? 5 ) ) ),
    );
}
