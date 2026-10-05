<?php
/**
 * OV homepage section: team.
 *
 * @package Neurg_Kreisverband
 */

if ( $show_team ) {
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by the shared renderer.
    echo gk_render_team_carousel(
        array(
			'zuordnung'   => $ov_slug,
			'group_order' => $home_config['team_order'],
        )
    );
}
