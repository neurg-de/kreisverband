<?php
$post_id  = get_the_ID();
$amt      = get_post_meta( $post_id, 'kr8mb_pers_pos_amt', true );
$email    = get_post_meta( $post_id, 'kr8mb_pers_contact_email', true );
$www      = get_post_meta( $post_id, 'kr8mb_pers_contact_www', true );
$shortbio = get_post_meta( $post_id, 'kr8mb_pers_excerpt', true );

$contact = array(
    'facebook' => get_post_meta( $post_id, 'kr8mb_pers_contact_facebook', true ),
    'twitter'  => get_post_meta( $post_id, 'kr8mb_pers_contact_twitter', true ),
    'insta'    => get_post_meta( $post_id, 'kr8mb_pers_contact_insta', true ),
    'tiktok'   => get_post_meta( $post_id, 'kr8mb_pers_contact_tiktok', true ),
    'threads'  => get_post_meta( $post_id, 'kr8mb_pers_contact_threads', true ),
    'mastodon' => get_post_meta( $post_id, 'kr8mb_pers_contact_mastodon', true ),
);
$social_links = gk_build_social_links( $contact );
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'clearfix single-person' ); ?> role="article">

    <?php if ( has_post_thumbnail() ) : ?>
        <div class="person-image"><?php the_post_thumbnail( 'medium' ); ?></div>
    <?php endif; ?>

    <header class="article-header">
        <h1 class="entry-title"><?php the_title(); ?></h1>
        <?php if ( $amt ) : ?><p class="funktion"><?php echo esc_html( $amt ); ?></p><?php endif; ?>
    </header>

    <section class="entry-content clearfix">
        <?php if ( $shortbio ) : ?><p class="short"><?php echo esc_html( $shortbio ); ?></p><?php endif; ?>
        <?php the_content(); ?>
    </section>

    <footer class="person-contact">
        <?php if ( $www ) : ?><p><a href="<?php echo esc_url( $www ); ?>" target="_blank"><i class="fa fa-home"></i> Website</a></p><?php endif; ?>
        <?php if ( $email ) : ?><p><a href="mailto:<?php echo esc_attr( $email ); ?>"><i class="fas fa-envelope"></i> E-Mail</a></p><?php endif; ?>
        <?php gk_social_links_bar( $social_links ); ?>
    </footer>
</article>
