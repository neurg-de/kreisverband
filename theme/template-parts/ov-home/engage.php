<?php
/**
 * OV homepage section: engage.
 *
 * @package Neurg_Kreisverband
 */

if ( $show_engage ) :
	?>
<section class="gk-engage">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Werde aktiv</h2>
            <p>Es gibt viele Wege, gr&uuml;ne Politik vor Ort mitzugestalten.</p>
        </div>
        <div class="gk-engage__grid">
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
                </div>
                <h3>Mitglied werden</h3>
                <p>Tritt den Gr&uuml;nen bei und gestalte Politik von der Basis aus mit.</p>
                <a href="https://www.gruene.de/mitglied-werden" class="gk-btn gk-btn--primary" target="_blank" rel="noopener">
                    Mitglied werden &rarr;
                </a>
            </div>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </div>
                <h3>Komm vorbei</h3>
                <p>Unsere Treffen stehen allen offen. Lerne uns kennen &mdash; ganz unverbindlich.</p>
                <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" class="gk-btn gk-btn--primary">Kontakt aufnehmen</a>
                <?php endif; ?>
            </div>
            <?php if ( $has_donation && $donate_url ) : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                </div>
                <h3>Spenden</h3>
                <p>Deine Spende erm&ouml;glicht politische Arbeit vor Ort. Jeder Euro z&auml;hlt.</p>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $donate_cta ); ?> &rarr;</a>
            </div>
            <?php else : ?>
            <div class="gk-engage__card">
                <div class="gk-engage__icon-wrap">
                    <svg class="gk-engage__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                </div>
                <h3>Schreib uns</h3>
                <p>Fragen, Ideen, Kritik? Wir freuen uns &uuml;ber jede Nachricht.</p>
                <?php if ( ! empty( $contact['email'] ) ) : ?>
                    <a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>" class="gk-btn gk-btn--primary">E-Mail schreiben</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>
