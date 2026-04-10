<!doctype html>
<html <?php language_attributes(); ?> class="no-js">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="HandheldFriendly" content="True">
    <meta name="MobileOptimized" content="320">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="theme-color" content="<?php echo esc_attr( apply_filters( 'gk_theme_color', '#0A321E' ) ); ?>">

    <link rel="icon" href="<?php echo esc_url( GK_URI . '/favicon.ico' ); ?>">
    <?php if ( file_exists( get_template_directory() . '/apple-touch-icon.png' ) ) : ?>
        <link rel="apple-touch-icon" href="<?php echo esc_url( GK_URI . '/apple-touch-icon.png' ); ?>">
    <?php endif; ?>

    <?php wp_head(); ?>
</head>
<?php
    // Determine OV context via zuordnung taxonomy.
    global $post;
    $gk_ov_slug = isset( $post ) ? gk_get_post_zuordnung_slug( $post->ID ) : '';
    $gk_is_ov   = $gk_ov_slug !== '' && $gk_ov_slug !== 'kreisverband';

    if ( $gk_is_ov ) {
        $gk_ov_term   = get_term_by( 'slug', $gk_ov_slug, 'gk_zuordnung' );
        $homepage_id   = $gk_ov_term ? gk_get_ov_homepage_id( $gk_ov_term->term_id ) : 0;
        $gk_ov_home    = $homepage_id ? get_permalink( $homepage_id ) : home_url( '/' );
        $gk_ov_header  = $gk_ov_term ? gk_get_ov_header( $gk_ov_term->term_id ) : '';
    }
?>
<body <?php body_class( $gk_is_ov ? 'gk-ov-context' : '' ); ?>>
    
    <nav class="gk-skip-links" aria-label="Sprungmarken"><ul>
        <li><a href="#content" class="gk-skip-link">Direkt zum Inhalt</a></li>
        <li><a href="#hauptmenue" class="gk-skip-link">Zur Navigation</a></li>
        <li><a href="#sidebar1" class="gk-skip-link">Seitenleiste</a></li>
        <li><a href="#footer" class="gk-skip-link">Fussbereich</a></li>
    </ul></nav>
    <style>
        .gk-skip-links { position: absolute; }
        .gk-skip-links ul { list-style: none; margin: 0; padding: 0; }
        .gk-skip-link {
            position: absolute;
            left: -9999px;
            top: auto;
            width: 1px;
            height: 1px;
            overflow: hidden;
            z-index: 100000;
        }
        .gk-skip-link:focus {
            position: fixed;
            top: 6px;
            left: 6px;
            width: auto;
            height: auto;
            padding: 12px 24px;
            background: #0A321E;
            color: #fff;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,.3);
            outline: 2px solid #fff;
        }
    </style>

    <!-- header -->
    <header id="header">
        <div class="sitetitle">
            <h2><?php bloginfo( 'name' ); ?></h2>
            <h2><?php bloginfo( 'description' ); ?></h2>
        </div>

        <!-- mobile header -->
        <section class="header-mobile">
            <?php if ( $gk_is_ov ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kv-back" title="Zurück zum Kreisverband">KV</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( $gk_is_ov ? $gk_ov_home : home_url( '/' ) ); ?>" title="Zur Startseite" class="logolink">
                <?php if ( file_exists( GK_DIR . '/lib/images/logo_small.png' ) ) : ?>
                    <img src="<?php echo esc_url( GK_IMAGE_DIR . 'logo_small.png' ); ?>" width="500" height="500" alt="<?php bloginfo( 'name' ); ?>" />
                <?php endif; ?>
                <h2><?php echo $gk_is_ov ? esc_html( $gk_ov_header ) : get_bloginfo( 'name' ); ?></h2>
            </a>
            <a class="switch-menu" href="#nav-mobile" role="button" aria-expanded="false" aria-label="Menü öffnen"><span class="fa fa-bars" aria-hidden="true"></span><span class="hidden">Menu</span></a>
        </section>
    </header>

    <!-- mobile menu -->
    <div id="nav-mobile">
        <div class="nav-mobile-view">
            <a class="switch-menu" href="#header" role="button" aria-expanded="true" aria-label="Menü schliessen"><span class="fa fa-times" aria-hidden="true"></span>Menu schliessen</a>
            <div class="logo">
                <?php if ( $gk_is_ov ) : ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kv-back" title="Zurück zum Kreisverband">KV</a>
                <?php endif; ?>
                <a href="<?php echo esc_url( $gk_is_ov ? $gk_ov_home : home_url( '/' ) ); ?>" title="Zur Startseite" class="logolink">
                    <?php if ( file_exists( GK_DIR . '/lib/images/logo_small.png' ) ) : ?>
                        <img src="<?php echo esc_url( GK_IMAGE_DIR . 'logo_small.png' ); ?>" width="500" height="500" alt="<?php bloginfo( 'name' ); ?>" />
                    <?php endif; ?>
                    <h2><?php echo $gk_is_ov ? esc_html( $gk_ov_header ) : get_bloginfo( 'name' ); ?></h2>
                </a>
                <span class="clearfix"></span>
            </div>

            <?php get_search_form(); ?>

            <?php if ( $gk_is_ov ) : ?>
                <nav role="navigation" class="ov"><?php gk_ov_navi( $gk_ov_slug ); ?></nav>
            <?php endif; ?>

            <nav role="navigation" class="kv"><h6 class="unsichtbar">Hauptmenue:</h6><?php gk_nav_mobile(); ?></nav>
        </div>
        <div class="mobile-overlay"></div>
    </div>

    <!-- fixed desktop menu -->
    <div class="nav-wrap" id="nav-flyin"><div class="inner">
        <?php if ( $gk_is_ov ) : ?>
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kv-back" title="Zurück zum Kreisverband">KV</a>
        <?php endif; ?>
        <div class="logo-desktop">
            <a href="<?php echo esc_url( $gk_is_ov ? $gk_ov_home : home_url( '/' ) ); ?>" title="Zur Startseite">
                <?php if ( file_exists( GK_DIR . '/lib/images/logo_small.png' ) ) : ?>
                    <img src="<?php echo esc_url( GK_IMAGE_DIR . 'logo_small.png' ); ?>" width="500" height="500" alt="<?php bloginfo( 'name' ); ?>" />
                <?php endif; ?>
                <h2><?php echo $gk_is_ov ? esc_html( $gk_ov_header ) : get_bloginfo( 'name' ); ?></h2>
            </a>
        </div>
        <nav role="navigation" class="nav-main"><h6 class="unsichtbar">Hauptmenue:</h6>
            <?php if ( $gk_is_ov ) { gk_nav_ov( $gk_ov_slug ); } ?>
            <?php gk_nav_main(); ?>
        </nav>
    </div></div>

    <!-- normal desktop menu -->
    <div class="nav-wrap inner" id="nav-desktop">
        <nav role="navigation" class="nav-main" id="hauptmenue"><h6 class="unsichtbar">Hauptmenue:</h6>
            <?php if ( $gk_is_ov ) : ?>
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="kv-back" title="Zurück zum Kreisverband">KV</a>
            <?php endif; ?>
            <a href="<?php echo esc_url( $gk_is_ov ? $gk_ov_home : home_url( '/' ) ); ?>" title="Zur Startseite" class="logolink">
                <?php if ( file_exists( GK_DIR . '/lib/images/logo_small.png' ) ) : ?>
                    <img src="<?php echo esc_url( GK_IMAGE_DIR . 'logo_small.png' ); ?>" width="500" height="500" alt="<?php bloginfo( 'name' ); ?>" />
                <?php endif; ?>
                <h2><?php echo $gk_is_ov ? esc_html( $gk_ov_header ) : get_bloginfo( 'name' ); ?></h2>
            </a>
            <?php if ( $gk_is_ov ) { gk_nav_ov( $gk_ov_slug ); } ?>
            <?php gk_nav_main(); ?>
        </nav>
    </div>

    <!-- search desktop (below nav) -->
    <div class="search-desktop" id="suche"><div class="inner">
        <?php get_search_form(); ?>
        <a href="#header" aria-label="Suche schliessen"><i class="fa fa-times" aria-hidden="true"></i> Suche schliessen</a>
    </div></div>

    <?php gk_context_social_bar(); ?>
<!-- end header -->
