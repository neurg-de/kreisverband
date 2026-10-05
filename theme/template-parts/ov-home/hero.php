<?php
/**
 * OV homepage section: hero.
 *
 * @package Neurg_Kreisverband
 */

switch ( $landing_mode ) :

	// ── ELECTION ────────────────────────────────────────────────────────────────
	case 'election':
		$el_name   = gk_get_ov_homepage_option( $term_id, 'election_name', '' );
		$el_date   = gk_get_ov_homepage_option( $term_id, 'election_date', '' );
		$el_slogan = gk_get_ov_homepage_option( $term_id, 'election_slogan', '' );
		$el_img    = $hero_img_id;
		$days_left = $el_date ? max( 0, (int) ( ( strtotime( $el_date ) - time() ) / 86400 ) ) : null;
		?>
<section class="gk-hero gk-hero--election">
		<?php if ( $el_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $el_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <?php if ( $el_name ) : ?>
            <span class="gk-hero__kicker"><?php echo esc_html( $el_name ); ?></span>
        <?php endif; ?>
        <?php if ( null !== $days_left && $days_left > 0 ) : ?>
            <div class="gk-hero__countdown">
                <span class="gk-hero__countdown-num"><?php echo esc_html( $days_left ); ?></span>
                <span class="gk-hero__countdown-label">Tage bis zur Wahl</span>
            </div>
        <?php elseif ( 0 === $days_left ) : ?>
            <div class="gk-hero__countdown gk-hero__countdown--today">
                <span class="gk-hero__countdown-label">Heute ist Wahltag!</span>
            </div>
        <?php endif; ?>
        <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( ( $home_config['title'] ? $home_config['title'] : ( $el_slogan ? $el_slogan : $hero_title ) ) ); ?></h1>
        <div class="gk-hero__actions">
            <?php if ( $cta_label && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $cta_label ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
		<?php
        break;

	// ── CANDIDATE ───────────────────────────────────────────────────────────────
	case 'candidate':
		$c_name    = gk_get_ov_homepage_option( $term_id, 'candidate_name', '' );
		$c_role    = gk_get_ov_homepage_option( $term_id, 'candidate_role', '' );
		$c_quote   = gk_get_ov_homepage_option( $term_id, 'candidate_quote', '' );
		$c_img     = (int) gk_get_ov_homepage_option( $term_id, 'candidate_image', 0 );
		$c_cta     = gk_get_ov_homepage_option( $term_id, 'candidate_cta_label', 'Mehr erfahren' );
		$c_cta_url = gk_get_ov_homepage_option( $term_id, 'candidate_cta_url', '' );
		?>
<section class="gk-hero gk-hero--candidate">
		<?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <div class="gk-candidate">
            <?php if ( $c_img ) : ?>
                <div class="gk-candidate__photo">
                    <?php echo wp_get_attachment_image( $c_img, 'medium_large', false, array( 'class' => 'gk-candidate__img' ) ); ?>
                </div>
            <?php endif; ?>
            <div class="gk-candidate__info">
                <?php
                if ( $home_config['show_label'] ) :
					?>
                    <span class="gk-hero__kicker"><?php echo esc_html( $ov_header ); ?></span><?php endif; ?>
                <?php if ( $c_name || $home_config['title'] ) : ?>
                    <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( $home_config['title'] ? $home_config['title'] : $c_name ); ?></h1>
                <?php endif; ?>
                <?php if ( $c_role ) : ?>
                    <p class="gk-candidate__role"><?php echo esc_html( $c_role ); ?></p>
                <?php endif; ?>
                <?php if ( $c_quote ) : ?>
                    <blockquote class="gk-candidate__quote">&ldquo;<?php echo esc_html( $c_quote ); ?>&rdquo;</blockquote>
                <?php endif; ?>
                <div class="gk-hero__actions">
                    <?php if ( $c_cta && $c_cta_url ) : ?>
                        <a href="<?php echo esc_url( $c_cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $c_cta ); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
		<?php
        break;

	// ── NEWS ────────────────────────────────────────────────────────────────────
	case 'news':
		$latest   = new WP_Query(
            array_merge(
                array(
					'post_type'      => 'post',
					'posts_per_page' => 1,
                ),
                gk_ov_query_args( $ov_slug )
            )
        );
		$has_post = $latest->have_posts();
		if ( $has_post ) {
			$latest->the_post();
		}
		$post_img = $has_post && has_post_thumbnail() ? get_post_thumbnail_id() : 0;
		$bg_img   = ( $hero_img_id ? $hero_img_id : $post_img );
		?>
<section class="gk-hero gk-hero--news">
		<?php if ( $bg_img ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $bg_img, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <span class="gk-hero__kicker">Aktuell</span>
        <?php if ( ! $has_post && $home_config['title'] ) : ?>
            <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( $home_config['title'] ); ?></h1>
        <?php endif; ?>
        <?php if ( $has_post ) : ?>
            <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $home_config['title'] ? $home_config['title'] : get_the_title() ); ?></a></h1>
            <?php if ( $home_config['show_text'] ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 30 ) ); ?></p>
            <?php endif; ?>
            <div class="gk-hero__actions">
                <a href="<?php the_permalink(); ?>" class="gk-btn gk-btn--primary">Weiterlesen</a>
            </div>
        <?php endif; ?>
    </div>
</section>
		<?php
		if ( $has_post ) {
			wp_reset_postdata();
		}
        break;

	// ── FUNDRAISING ─────────────────────────────────────────────────────────────
	case 'fundraising':
		$fr_goal    = absint( gk_get_ov_homepage_option( $term_id, 'fundraising_goal', 0 ) );
		$fr_current = absint( gk_get_ov_homepage_option( $term_id, 'fundraising_current', 0 ) );
		$fr_pct     = $fr_goal > 0 ? min( 100, round( $fr_current / $fr_goal * 100 ) ) : 0;
		?>
<section class="gk-hero gk-hero--fundraising">
		<?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg"><?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?></div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <?php
        if ( $home_config['show_label'] ) :
			?>
            <span class="gk-hero__kicker"><?php echo esc_html( $ov_header ); ?></span><?php endif; ?>
        <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( ( $hero_title ? $hero_title : 'Unterstütze grüne Politik vor Ort' ) ); ?></h1>
        <?php if ( $hero_subtitle && $home_config['show_text'] ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>
        <?php if ( $fr_goal > 0 ) : ?>
            <div class="gk-fundraising-bar">
                <div class="gk-fundraising-bar__track">
                    <div class="gk-fundraising-bar__fill" style="width:<?php echo esc_html( $fr_pct ); ?>%"></div>
                </div>
                <div class="gk-fundraising-bar__stats">
                    <span class="gk-fundraising-bar__current"><?php echo number_format( $fr_current, 0, ',', '.' ); ?> &euro;</span>
                    <span class="gk-fundraising-bar__goal">Ziel: <?php echo number_format( $fr_goal, 0, ',', '.' ); ?> &euro;</span>
                </div>
            </div>
        <?php endif; ?>
        <div class="gk-hero__actions">
            <?php if ( $has_donation && $donate_url ) : ?>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $donate_cta ); ?></a>
            <?php endif; ?>
        </div>
    </div>
</section>
		<?php
        break;

	// ── MINIMAL ─────────────────────────────────────────────────────────────────
	case 'minimal':
		?>
<section class="gk-hero gk-hero--minimal">
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( $home_config['title'] ? $home_config['title'] : $ov_header ); ?></h1>
        <?php if ( $cta_label && $cta_url ) : ?>
        <div class="gk-hero__actions">
            <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary"><?php echo esc_html( $cta_label ); ?></a>
        </div>
        <?php endif; ?>
    </div>
</section>
		<?php
        break;

	// ── STANDARD (default) ──────────────────────────────────────────────────────
	default:
		?>
<section class="gk-hero">
		<?php if ( $hero_img_id ) : ?>
        <div class="gk-hero__bg">
            <?php echo wp_get_attachment_image( $hero_img_id, 'full', false, array( 'class' => 'gk-hero__img' ) ); ?>
        </div>
    <?php endif; ?>
    <div class="gk-hero__overlay"></div>
    <div class="gk-hero__content inner">
        <h1 class="gk-hero__title<?php echo $home_config['show_text'] ? '' : ' screen-reader-text'; ?>"><?php echo esc_html( $hero_title ); ?></h1>
        <?php if ( $hero_subtitle && $home_config['show_text'] ) : ?>
            <p class="gk-hero__subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
        <?php endif; ?>
        <div class="gk-hero__actions">
            <?php if ( $cta_label && $cta_url ) : ?>
                <a href="<?php echo esc_url( $cta_url ); ?>" class="gk-btn gk-btn--primary">
                    <?php echo esc_html( $cta_label ); ?>
                </a>
            <?php endif; ?>
            <?php if ( $has_donation && $donate_url ) : ?>
                <a href="<?php echo esc_url( $donate_url ); ?>" class="gk-btn gk-btn--primary">
                    <?php echo esc_html( $donate_cta ); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>
		<?php
        break;

endswitch; // landing_mode.
?>
