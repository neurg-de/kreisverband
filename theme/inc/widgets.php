<?php
/**
 * Custom Widgets: Social Media Links, Teaser Article
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// ── Social Media Widget ──────────────────────────────────────────────────────

class GK_Social_Media_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'gk_socialmedia',
            __( 'Social Media Links', 'neurg-kreisverband' ),
            array( 'description' => 'Links zu Profilen in Sozialen Netzwerken.' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        if ( ! empty( $instance['title'] ) ) {
            echo '<h3 class="widgettitle">' . esc_html( $instance['title'] ) . '</h3>';
        }

        // Map widget fields to canonical platform keys for URL normalization.
        $widget_platforms = array(
            'instagram' => 'instagram',
            'facebook'  => 'facebook',
            'x'         => 'x',
            'tiktok'    => 'tiktok',
            'threads'   => 'threads',
            'bluesky'   => 'bluesky',
            'mastodon'  => 'mastodon',
            'youtube'   => 'youtube',
        );

        $links = array();
        foreach ( $widget_platforms as $field => $platform ) {
            $value = $instance[ $field ] ?? '';
            if ( ! empty( $value ) ) {
                $url = gk_normalize_social_url( $platform, $value );
                if ( $url ) {
                    $links[ $platform ] = $url;
                }
            }
        }

        // RSS is not a social platform — handle separately.
        if ( ! empty( $instance['rss'] ) ) {
            $links['rss'] = $instance['rss'];
        }

        if ( ! empty( $links ) ) {
            $platforms = gk_social_platforms();
            echo '<ul class="sociallinks">';
            foreach ( $links as $platform => $url ) {
                if ( $platform === 'rss' ) {
                    echo '<li><a href="' . esc_url( $url ) . '" title="RSS" target="_blank" rel="noopener"><span class="fas fa-rss"></span><span class="screen-reader-text">RSS</span></a></li>';
                } elseif ( isset( $platforms[ $platform ] ) ) {
                    $def = $platforms[ $platform ];
                    echo '<li><a href="' . esc_url( $url ) . '" title="' . esc_attr( $def['label'] ) . '" target="_blank" rel="noopener"><span class="' . esc_attr( $def['icon'] ) . '"></span><span class="screen-reader-text">' . esc_html( $def['label'] ) . '</span></a></li>';
                }
            }
            echo '</ul>';
        }

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $fields   = array( 'title', 'instagram', 'facebook', 'x', 'tiktok', 'threads', 'bluesky', 'mastodon', 'youtube', 'rss' );
        foreach ( $fields as $field ) {
            $instance[ $field ] = sanitize_text_field( $new_instance[ $field ] );
        }
        return $instance;
    }

    public function form( $instance ) {
        $fields = array(
            'title'     => 'Titel',
            'instagram' => 'Instagram',
            'facebook'  => 'Facebook',
            'x'         => 'X',
            'tiktok'    => 'TikTok',
            'threads'   => 'Threads',
            'bluesky'   => 'Bluesky',
            'mastodon'  => 'Mastodon',
            'youtube'   => 'YouTube',
            'rss'       => 'RSS-Feed URL',
        );

        foreach ( $fields as $key => $label ) {
            $value = isset( $instance[ $key ] ) ? esc_attr( $instance[ $key ] ) : '';
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $label ); ?>:</label>
                <input class="widefat" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo $value; ?>" />
            </p>
            <?php
        }
    }
}


// ── Teaser Article Widget ────────────────────────────────────────────────────

class GK_Teaser_Widget extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'gk_teaserarticle',
            'Teaser',
            array( 'description' => 'Artikel-Teaser mit Bild' )
        );
    }

    public function widget( $args, $instance ) {
        echo $args['before_widget'];

        $title    = apply_filters( 'widget_title', $instance['title'] );
        $url      = isset( $instance['url'] ) ? $instance['url'] : '';
        $subhead  = isset( $instance['subhead'] ) ? $instance['subhead'] : '';
        $desc     = isset( $instance['desc'] ) ? $instance['desc'] : '';
        $img      = isset( $instance['image_url'] ) ? $instance['image_url'] : '';
        $nodesc   = ! empty( $instance['show_desc'] );

        if ( $nodesc ) echo '<div class="nodesc">';

        if ( ! empty( $img ) ) {
            if ( ! empty( $url ) ) echo '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '" class="gk-post-list__image">';
            echo '<img src="' . esc_url( $img ) . '" alt="' . esc_attr( $title ) . '" class="wp-post-image">';
            if ( ! empty( $url ) ) echo '</a>';
        }

        if ( ! empty( $subhead ) ) {
            echo '<p class="subhead">' . esc_html( $subhead ) . '</p>';
        }

        if ( ! empty( $title ) ) {
            echo $args['before_title'];
            if ( ! empty( $url ) ) echo '<a href="' . esc_url( $url ) . '" title="' . esc_attr( $title ) . '">';
            echo esc_html( $title );
            if ( ! empty( $url ) ) echo '</a>';
            echo $args['after_title'];
        }

        if ( ! empty( $desc ) ) {
            echo '<p>' . esc_html( $desc ) . '</p>';
        }

        if ( $nodesc ) echo '</div>';

        echo $args['after_widget'];
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $text_fields = array( 'title', 'url', 'subhead', 'desc', 'image_id', 'image_size', 'image_url' );
        foreach ( $text_fields as $field ) {
            $instance[ $field ] = isset( $new_instance[ $field ] ) ? strip_tags( $new_instance[ $field ] ) : '';
        }
        $instance['show_desc'] = isset( $new_instance['show_desc'] ) ? $new_instance['show_desc'] : '';
        return $instance;
    }

    public function form( $instance ) {
        $fields = array(
            'title'   => 'Titel',
            'url'     => 'URL',
            'subhead' => 'Dachzeile',
            'desc'    => 'Beschreibung',
        );

        foreach ( $fields as $key => $label ) {
            $value = isset( $instance[ $key ] ) ? esc_attr( $instance[ $key ] ) : '';
            ?>
            <p>
                <label for="<?php echo $this->get_field_id( $key ); ?>"><?php echo esc_html( $label ); ?>:</label>
                <?php if ( $key === 'desc' ) : ?>
                    <textarea class="widefat" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>"><?php echo $value; ?></textarea>
                <?php else : ?>
                    <input class="widefat" id="<?php echo $this->get_field_id( $key ); ?>" name="<?php echo $this->get_field_name( $key ); ?>" type="text" value="<?php echo $value; ?>" />
                <?php endif; ?>
            </p>
            <?php
        }

        $image_url = isset( $instance['image_url'] ) ? $instance['image_url'] : '';
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'image_url' ); ?>">Bild-URL:</label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'image_url' ); ?>" name="<?php echo $this->get_field_name( 'image_url' ); ?>" type="text" value="<?php echo esc_attr( $image_url ); ?>" />
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( isset( $instance['show_desc'] ) ? $instance['show_desc'] : '', 'on' ); ?> id="<?php echo $this->get_field_id( 'show_desc' ); ?>" name="<?php echo $this->get_field_name( 'show_desc' ); ?>" />
            <label for="<?php echo $this->get_field_id( 'show_desc' ); ?>">Überschrift in Desktop-Version ausblenden.</label>
        </p>
        <?php
    }
}


// ── Register Widgets ─────────────────────────────────────────────────────────

add_action( 'widgets_init', function() {
    register_widget( 'GK_Teaser_Widget' );
    register_widget( 'GK_Social_Media_Widget' );
} );
