<?php
/**
 * OV news section.
 *
 * @package Neurg_Kreisverband
 */

$ov_posts = new WP_Query( gk_ov_news_args( $term_id ) );
if ( $ov_posts->have_posts() || $home_config['archive'] ) {
	get_template_part(
        'template-parts/ov-news-list',
        null,
        array(
			'query'       => $ov_posts,
			'title'       => __( 'Aktuelles', 'neurg-kreisverband' ),
			'archive_url' => $home_config['archive'] ? gk_ov_news_url( $term_id ) : '',
        )
	);
}
