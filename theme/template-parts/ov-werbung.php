<?php
/**
 * Template Part: OV Werbung (Engagement Landing Page)
 *
 * A focused, conversion-optimized page for municipalities without
 * an active Ortsverband. The goal: get visitors excited about
 * starting a local Green party chapter and make contact easy.
 *
 * @package Neurg_Kreisverband
 */

$gemeinde     = $args['gemeinde'] ?? '';
$hero_img_id  = $args['hero_img_id'] ?? 0;
$hero_title   = $args['hero_title'] ?? '';
$hero_subtitle= $args['hero_subtitle'] ?? '';
$email        = $args['email'] ?? '';
$contact      = $args['contact'] ?? array();
$ov_header    = $args['ov_header'] ?? '';

if ( ! $hero_title ) {
    $hero_title = 'Gr&uuml;ne Politik f&uuml;r ' . esc_html( $gemeinde );
}
if ( ! $hero_subtitle ) {
    $hero_subtitle = 'Engagiere dich vor Ort &mdash; gemeinsam k&ouml;nnen wir etwas bewegen.';
}
?>

<div id="primary" class="content-area">
<main id="main" class="site-main" role="main">
<div class="home-gk ov-werbung">

<!-- Hero -->
<section class="gk-hero gk-hero--werbung">
    <?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker">B&Uuml;NDNIS 90/DIE GR&Uuml;NEN</span>
        <h1 class="gk-hero__title"><?php echo $hero_title; ?></h1>
        <p class="gk-hero__subtitle"><?php echo $hero_subtitle; ?></p>
        <div class="gk-hero__actions">
            <?php if ( $email ) : ?>
                <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( 'Interesse an Grüner Politik in ' . $gemeinde ); ?>" class="gk-btn gk-btn--primary">
                    Jetzt Kontakt aufnehmen &rarr;
                </a>
            <?php endif; ?>
            <a href="#warum" class="gk-btn gk-btn--outline">Mehr erfahren &darr;</a>
        </div>
    </div>
</section>

<!-- Why: Three reasons to get involved -->
<section class="ov-wb-why" id="warum">
    <div class="inner">
        <div class="gk-section-header">
            <h2>Warum gr&uuml;ne Politik in <?php echo esc_html( $gemeinde ); ?>?</h2>
            <p>Vor Ort entscheidet sich, wie wir leben &mdash; von der Verkehrswende bis zum Klimaschutz.</p>
        </div>
        <div class="ov-wb-why__grid">
            <div class="ov-wb-why__card">
                <div class="ov-wb-why__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <h3>Klimaschutz beginnt lokal</h3>
                <p>Ob Radwege, Solaranlagen oder Gr&uuml;nfl&auml;chen &mdash; die wichtigsten Entscheidungen werden in deiner Gemeinde getroffen.</p>
            </div>
            <div class="ov-wb-why__card">
                <div class="ov-wb-why__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <h3>Gemeinsam st&auml;rker</h3>
                <p>Allein ist Politik frustrierend. Als Gruppe seid ihr sichtbar, k&ouml;nnt Antr&auml;ge stellen und euch gegenseitig motivieren.</p>
            </div>
            <div class="ov-wb-why__card">
                <div class="ov-wb-why__icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <h3>Deine Stimme z&auml;hlt</h3>
                <p>In der Kommunalpolitik bewegt eine einzelne Stimme mehr als auf jeder anderen Ebene. Nutze diese Chance.</p>
            </div>
        </div>
    </div>
</section>

<!-- How: Steps to get started -->
<section class="ov-wb-steps">
    <div class="inner">
        <div class="gk-section-header gk-section-header--light">
            <h2>So geht&rsquo;s los</h2>
            <p>Einen Ortsverband gr&uuml;nden ist einfacher als du denkst.</p>
        </div>
        <div class="ov-wb-steps__grid">
            <div class="ov-wb-steps__step">
                <span class="ov-wb-steps__num">1</span>
                <h3>Melde dich bei uns</h3>
                <p>Schreib uns eine kurze Nachricht. Wir freuen uns &uuml;ber jede:n, der/die sich engagieren m&ouml;chte.</p>
            </div>
            <div class="ov-wb-steps__step">
                <span class="ov-wb-steps__num">2</span>
                <h3>Lerne Gleichgesinnte kennen</h3>
                <p>Wir vernetzen dich mit anderen Interessierten in <?php echo esc_html( $gemeinde ); ?> und im Kreisverband.</p>
            </div>
            <div class="ov-wb-steps__step">
                <span class="ov-wb-steps__num">3</span>
                <h3>Werde aktiv</h3>
                <p>Ob als Ortsgruppe, Stammtisch oder fester Ortsverband &mdash; ihr bestimmt das Tempo und die Form.</p>
            </div>
        </div>
    </div>
</section>

<!-- Page Content (optional) -->
<?php
rewind_posts();
while ( have_posts() ) : the_post();
    if ( trim( get_the_content() ) ) : ?>
<section class="ov-wb-content">
    <div class="inner">
        <div class="ov-wb-content__body entry-content">
            <?php the_content(); ?>
        </div>
    </div>
</section>
    <?php endif;
endwhile; ?>

<!-- Final CTA -->
<section class="ov-wb-cta">
    <div class="inner">
        <div class="ov-wb-cta__box">
            <h2>Interesse geweckt?</h2>
            <p>Egal ob du schon Mitglied bist oder einfach neugierig &mdash; melde dich bei uns. Gemeinsam machen wir <?php echo esc_html( $gemeinde ); ?> gr&uuml;ner.</p>
            <div class="ov-wb-cta__actions">
                <?php if ( $email ) : ?>
                    <a href="mailto:<?php echo esc_attr( $email ); ?>?subject=<?php echo rawurlencode( 'Interesse an Grüner Politik in ' . $gemeinde ); ?>" class="gk-btn gk-btn--primary gk-btn--lg">
                        <i class="fas fa-envelope"></i> E-Mail schreiben
                    </a>
                <?php endif; ?>
                <a href="https://www.gruene.de/mitglied-werden" class="gk-btn gk-btn--primary gk-btn--lg ov-werbung__btn-alt" target="_blank" rel="noopener">
                    Mitglied werden &rarr;
                </a>
            </div>
            <?php if ( ! empty( $contact['telefon'] ) ) : ?>
                <p class="ov-wb-cta__phone">Oder ruf uns an: <strong><?php echo esc_html( $contact['telefon'] ); ?></strong></p>
            <?php endif; ?>
        </div>
    </div>
</section>

</div><!-- .ov-werbung -->
</main>
</div>

<style>
/* ── Werbung Variables ────────────────────────────────────────── */
.ov-werbung {
    --gk-werbung-green:       var(--gk-green, #005538);
    --gk-werbung-green-light: var(--gk-green-light, #D5EEE6);
    --gk-werbung-green-dark:  var(--gk-green-dark, #002216);
    --gk-werbung-sand:        var(--gk-sand, #F5F1E9);
    --gk-werbung-sand-dark:   var(--gk-sand-dark, #EFE8DB);
    --gk-werbung-yellow:      var(--gk-yellow, #FFF17A);
    --gk-werbung-white:       #fff;
    --gk-werbung-text-on-dark: #fff;
    --gk-werbung-text:        var(--gk-text, #002216);
    --gk-werbung-text-light:  var(--gk-text-light, #5F9885);
    --gk-werbung-radius:      8px;
    --gk-werbung-radius-lg:   16px;
    --gk-werbung-shadow:      0 2px 12px rgba(0,0,0,.08);
    --gk-werbung-shadow-hover:0 6px 24px rgba(0,0,0,.12);
    color: var(--gk-werbung-text);
}
.ov-werbung *, .ov-werbung *::before, .ov-werbung *::after { box-sizing: border-box; }

/* ── Shared from ne-* ─────────────────────────────────────────── */
.ov-werbung .gk-btn {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .75rem 1.75rem;
    font-family: var(--font-heading); font-weight: 700; font-size: .95rem;
    line-height: 1.3; text-decoration: none; border-radius: 50px;
    transition: all .2s ease; cursor: pointer;
    border: 2px solid transparent; text-align: center;
}
.ov-werbung .gk-btn--lg { padding: 1rem 2.25rem; font-size: 1.05rem; }
.ov-werbung .gk-btn--primary { background: var(--gk-werbung-yellow); color: var(--gk-werbung-green-dark); border-color: var(--gk-werbung-yellow); }
.ov-werbung .gk-btn--primary:hover { background: #ffe94a; border-color: #ffe94a; }
.ov-werbung .ov-werbung__btn-alt { background: var(--gk-werbung-green); color: var(--gk-werbung-text-on-dark); border-color: var(--gk-werbung-green); }
.ov-werbung .ov-werbung__btn-alt:hover { background: var(--gk-werbung-green-dark); border-color: var(--gk-werbung-green-dark); }
.ov-werbung .gk-btn--outline { background: transparent; color: var(--gk-werbung-text-on-dark); border-color: var(--gk-werbung-text-on-dark); }
.ov-werbung .gk-btn--outline:hover { background: var(--gk-werbung-white); color: var(--gk-werbung-green-dark); }

.ov-werbung .gk-section-header { text-align: center; margin-bottom: 2.5rem; }
.ov-werbung .gk-section-header h2 {
    font-family: var(--font-heading); font-size: clamp(1.75rem, 4vw, 2.5rem);
    font-weight: 700; margin: 0 0 .5rem; color: var(--gk-werbung-green-dark);
}
.ov-werbung .gk-section-header p {
    font-size: 1.1rem; color: var(--gk-werbung-text-light);
    max-width: 600px; margin: 0 auto; line-height: 1.6;
}
.ov-werbung .gk-section-header--light h2 { color: var(--gk-werbung-text-on-dark); }
.ov-werbung .gk-section-header--light p { color: rgba(255,255,255,.8); }

/* ── Hero ─────────────────────────────────────────────────────── */
.ov-werbung .gk-hero {
    position: relative; min-height: 80vh;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden; background: var(--gk-werbung-green-dark);
}
.ov-werbung .gk-hero__bg { position: absolute; inset: 0; }
.ov-werbung .gk-hero__img { width: 100%; height: 100%; object-fit: cover; }
.ov-werbung .gk-hero__overlay {
    position: absolute; inset: 0; z-index: 1;
    background: linear-gradient(to top, rgba(0,0,0,.65) 0%, rgba(0,0,0,.3) 50%, rgba(0,0,0,.15) 100%);
}
.ov-werbung .gk-hero__content {
    position: relative; z-index: 2; text-align: center;
    padding-top: 5rem; padding-bottom: 5rem; max-width: 800px;
}
.ov-werbung .gk-hero__kicker {
    display: inline-block; font-family: var(--font-heading);
    font-size: .85rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .08em;
    color: var(--gk-werbung-yellow); margin-bottom: .75rem;
}
.ov-werbung .gk-hero__title {
    font-family: var(--font-heading); font-size: clamp(2.2rem, 7vw, 4rem);
    font-weight: 700; color: var(--gk-werbung-text-on-dark);
    margin: 0 0 1rem; line-height: 1.1;
    text-shadow: 0 2px 20px rgba(0,0,0,.3);
}
.ov-werbung .gk-hero__subtitle {
    font-size: clamp(1.05rem, 2.5vw, 1.4rem);
    color: rgba(255,255,255,.9);
    margin: 0 0 2.5rem; line-height: 1.5;
    max-width: 600px; margin-left: auto; margin-right: auto;
}
.ov-werbung .gk-hero__actions {
    display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
}

/* ── Why Section ──────────────────────────────────────────────── */
.ov-wb-why { padding: 5rem 0; background: var(--gk-werbung-white); }
.ov-wb-why__grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 2rem;
}
.ov-wb-why__card {
    background: var(--gk-werbung-sand); border-radius: var(--gk-werbung-radius-lg);
    padding: 2.5rem 2rem; text-align: center;
    transition: box-shadow .2s, transform .2s;
}
.ov-wb-why__card:hover {
    box-shadow: var(--gk-werbung-shadow-hover); transform: translateY(-4px);
}
.ov-wb-why__icon {
    width: 56px; height: 56px; margin: 0 auto 1.25rem;
    background: var(--gk-werbung-green); border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    color: var(--gk-werbung-text-on-dark);
}
.ov-wb-why__icon svg { width: 28px; height: 28px; }
.ov-wb-why__card h3 {
    font-family: var(--font-heading); font-size: 1.2rem;
    font-weight: 700; margin: 0 0 .75rem; color: var(--gk-werbung-green-dark);
}
.ov-wb-why__card p {
    font-size: .95rem; line-height: 1.6; color: var(--gk-werbung-text-light); margin: 0;
}

/* ── Steps Section ────────────────────────────────────────────── */
.ov-wb-steps { padding: 5rem 0; background: var(--gk-werbung-green); color: var(--gk-werbung-text-on-dark); }
.ov-wb-steps__grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 2rem;
    counter-reset: step;
}
.ov-wb-steps__step {
    text-align: center; padding: 2rem 1.5rem;
    background: rgba(255,255,255,.08); border-radius: var(--gk-werbung-radius-lg);
    border: 1px solid rgba(255,255,255,.12);
}
.ov-wb-steps__num {
    display: inline-flex; align-items: center; justify-content: center;
    width: 48px; height: 48px; border-radius: 50%;
    background: var(--gk-werbung-yellow); color: var(--gk-werbung-green-dark);
    font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700;
    margin-bottom: 1.25rem;
}
.ov-wb-steps__step h3 {
    font-family: var(--font-heading); font-size: 1.15rem;
    font-weight: 700; margin: 0 0 .75rem; color: var(--gk-werbung-text-on-dark);
}
.ov-wb-steps__step p {
    font-size: .95rem; line-height: 1.6; color: rgba(255,255,255,.85); margin: 0;
}

/* ── Content Section ──────────────────────────────────────────── */
.ov-wb-content { padding: 4rem 0; background: var(--gk-werbung-sand); }
.ov-wb-content__body { max-width: 800px; margin: 0 auto; font-size: 1.1rem; line-height: 1.7; }

/* ── Final CTA ────────────────────────────────────────────────── */
.ov-wb-cta { padding: 5rem 0; background: var(--gk-werbung-sand); }
.ov-wb-cta__box {
    max-width: 700px; margin: 0 auto; text-align: center;
    background: var(--gk-werbung-white); border-radius: var(--gk-werbung-radius-lg);
    padding: 3rem 2.5rem; box-shadow: var(--gk-werbung-shadow-hover);
}
.ov-wb-cta__box h2 {
    font-family: var(--font-heading); font-size: clamp(1.5rem, 4vw, 2rem);
    font-weight: 700; margin: 0 0 1rem; color: var(--gk-werbung-green-dark);
}
.ov-wb-cta__box > p {
    font-size: 1.05rem; line-height: 1.6; color: var(--gk-werbung-text-light);
    margin: 0 0 2rem; max-width: 500px; margin-left: auto; margin-right: auto;
}
.ov-wb-cta__actions {
    display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-bottom: 1.5rem;
}
.ov-wb-cta__phone {
    font-size: .95rem; color: var(--gk-werbung-text-light); margin: 0;
}
.ov-wb-cta__phone strong { color: var(--gk-werbung-green-dark); }

/* ── Responsive ───────────────────────────────────────────────── */
@media (max-width: 639px) {
    .ov-werbung .gk-hero { min-height: 70vh; }
    .ov-werbung .gk-hero__content { padding-top: 3rem; padding-bottom: 3rem; }
    .ov-werbung .gk-hero__actions { flex-direction: column; align-items: center; }
    .ov-werbung .gk-btn { width: 100%; max-width: 300px; }
    .ov-wb-why { padding: 3rem 0; }
    .ov-wb-steps { padding: 3rem 0; }
    .ov-wb-cta { padding: 3rem 0; }
    .ov-wb-cta__box { padding: 2rem 1.5rem; }
    .ov-wb-cta__actions { flex-direction: column; align-items: center; }
}

/* ── Dark Mode ────────────────────────────────────────────────── */
@media (prefers-color-scheme: dark) {
    .ov-werbung {
        --gk-werbung-green: var(--tanne-400); --gk-werbung-green-light: var(--tanne-800);
        --gk-werbung-green-dark: var(--sand-200); --gk-werbung-sand: var(--tanne-900);
        --gk-werbung-yellow: var(--sonne-600); --gk-werbung-white: var(--tanne-800);
        --gk-werbung-text-on-dark: var(--sand-200); --gk-werbung-text: var(--sand-200);
        --gk-werbung-text-light: var(--tanne-300);
        --gk-werbung-shadow: 0 2px 12px rgba(0,0,0,.3);
        --gk-werbung-shadow-hover: 0 6px 24px rgba(0,0,0,.4);
    }
    .ov-werbung .gk-hero { background: var(--tanne-900); }
    .ov-werbung .gk-btn--primary, .ov-werbung .gk-btn--primary:hover { color: var(--tanne-900); }
    .ov-wb-why__icon { background: var(--tanne-500); }
    .ov-wb-steps { background: var(--tanne-700); }
    .ov-wb-cta__box { background: var(--tanne-700); box-shadow: none; }
}

/* ── Reduced motion ───────────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .ov-werbung .gk-btn, .ov-wb-why__card { transition: none; }
}
</style>
