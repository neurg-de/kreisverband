<?php
/**
 * Validated, paginated OV news/rubric archive.
 *
 * @package Neurg_Kreisverband
 */

$context = gk_ov_news_context();
get_header();
?>
<div id="content" class="home-gk ov-homepage"><main id="main">
    <div class="inner">
        <h1><?php echo esc_html( $context['category'] ? $context['category']->name : __( 'Aktuelles', 'neurg-kreisverband' ) ); ?></h1>
        <p><a href="<?php echo esc_url( get_permalink( gk_get_ov_public_homepage_id( $context['term']->term_id ) ) ); ?>"><?php echo esc_html( $context['term']->name ); ?></a></p>
    </div>
    <?php
    global $wp_query;
    get_template_part(
        'template-parts/ov-news-list',
        null,
        array(
			'query'         => $wp_query,
			'title'         => __( 'Beiträge', 'neurg-kreisverband' ),
			'empty_message' => true,
        )
    );
    $archive_page = max( 1, (int) get_query_var( 'paged' ) );
    $base_url     = gk_ov_news_url( $context['term']->term_id, $context['category'] ? $context['category']->term_id : 0 );
    $base         = str_replace( '999999999', '%#%', add_query_arg( 'paged', 999999999, $base_url ) );
    $links        = paginate_links(
        array(
			'base'      => $base,
			'format'    => '',
			'total'     => $wp_query->max_num_pages,
			'current'   => $archive_page,
			'type'      => 'list',
			'prev_text' => __( 'Zurück', 'neurg-kreisverband' ),
			'next_text' => __( 'Weiter', 'neurg-kreisverband' ),
        )
    );
    if ( $links ) {
        echo '<nav class="inner" aria-label="' . esc_attr__( 'Beitragsseiten', 'neurg-kreisverband' ) . '">' . wp_kses_post( $links ) . '</nav>';
    }
    ?>
</main></div>
<?php get_footer(); ?>
