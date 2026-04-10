<?php
/**
 * Meta Boxes for Person and Page post types
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'add_meta_boxes', 'gk_add_meta_boxes' );
add_action( 'save_post', 'gk_save_person_contact' );
add_action( 'save_post', 'gk_save_page_meta' );


function gk_add_meta_boxes() {
    global $post;

    // Person meta boxes
    add_meta_box( 'gk_pers_contact', 'Kontaktdaten', 'gk_person_contact_cb', 'person', 'normal', 'high' );

    // Page-specific meta boxes
    if ( ! empty( $post ) ) {
        $template = get_post_meta( $post->ID, '_wp_page_template', true );
        if ( in_array( $template, array( 'page-landingpage.php', 'page-landingpage-small.php' ), true ) ) {
            add_meta_box( 'gk_page_themen', 'Kategorien', 'gk_page_themen_cb', 'page', 'normal', '' );
        }
        if ( $template === 'page-story.php' ) {
            add_meta_box( 'gk_page_story', 'Inhalt', 'gk_page_story_cb', 'page', 'normal', '' );
        }
    }
}


// ── Person: Contact ──────────────────────────────────────────────────────────

function gk_person_contact_cb( $post ) {
    $values    = get_post_custom( $post->ID );
    $get       = fn( $key ) => isset( $values[ $key ] ) ? esc_attr( $values[ $key ][0] ) : '';
    $www       = $get( 'kr8mb_pers_contact_www' );
    $email     = $get( 'kr8mb_pers_contact_email' );
    $facebook  = $get( 'kr8mb_pers_contact_facebook' );
    $twitter   = $get( 'kr8mb_pers_contact_twitter' );
    $mastodon  = $get( 'kr8mb_pers_contact_mastodon' );
    $insta     = $get( 'kr8mb_pers_contact_insta' );
    $tiktok    = $get( 'kr8mb_pers_contact_tiktok' );
    $threads   = $get( 'kr8mb_pers_contact_threads' );
    $anschrift = isset( $values['kr8mb_pers_contact_anschrift'] ) ? esc_html( $values['kr8mb_pers_contact_anschrift'][0] ) : '';
    $telefon   = isset( $values['kr8mb_pers_contact_telefon'] ) ? esc_html( $values['kr8mb_pers_contact_telefon'][0] ) : '';

    $hint = 'URL oder Username';

    wp_nonce_field( 'gk_meta_box_nonce', 'gk_meta_box_nonce' );
    ?>
    <table class="form-table"><tbody>
    <tr>
        <th scope="row"><label for="kr8mb_pers_contact_www">Website</label></th>
        <td><input type="text" name="kr8mb_pers_contact_www" id="kr8mb_pers_contact_www" value="<?php echo $www; ?>" class="regular-text" /><br><span class="description">z.B. https://domain.de</span></td>
        <th scope="row"><label for="kr8mb_pers_contact_email">E-Mail</label></th>
        <td><input type="text" name="kr8mb_pers_contact_email" id="kr8mb_pers_contact_email" value="<?php echo $email; ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th scope="row"><label for="kr8mb_pers_contact_insta">Instagram</label></th>
        <td><input type="text" name="kr8mb_pers_contact_insta" id="kr8mb_pers_contact_insta" value="<?php echo $insta; ?>" class="regular-text" /><br><span class="description"><?php echo $hint; ?></span></td>
        <th scope="row"><label for="kr8mb_pers_contact_facebook">Facebook</label></th>
        <td><input type="text" name="kr8mb_pers_contact_facebook" id="kr8mb_pers_contact_facebook" value="<?php echo $facebook; ?>" class="regular-text" /><br><span class="description"><?php echo $hint; ?></span></td>
    </tr>
    <tr>
        <th scope="row"><label for="kr8mb_pers_contact_twitter">X</label></th>
        <td><input type="text" name="kr8mb_pers_contact_twitter" id="kr8mb_pers_contact_twitter" value="<?php echo $twitter; ?>" class="regular-text" /><br><span class="description"><?php echo $hint; ?></span></td>
        <th scope="row"><label for="kr8mb_pers_contact_tiktok">TikTok</label></th>
        <td><input type="text" name="kr8mb_pers_contact_tiktok" id="kr8mb_pers_contact_tiktok" value="<?php echo $tiktok; ?>" class="regular-text" /><br><span class="description"><?php echo $hint; ?></span></td>
    </tr>
    <tr>
        <th scope="row"><label for="kr8mb_pers_contact_threads">Threads</label></th>
        <td><input type="text" name="kr8mb_pers_contact_threads" id="kr8mb_pers_contact_threads" value="<?php echo $threads; ?>" class="regular-text" /><br><span class="description"><?php echo $hint; ?></span></td>
        <th scope="row"><label for="kr8mb_pers_contact_mastodon">Mastodon</label></th>
        <td><input type="text" name="kr8mb_pers_contact_mastodon" id="kr8mb_pers_contact_mastodon" value="<?php echo $mastodon; ?>" class="regular-text" /><br><span class="description">URL oder @user@instanz</span></td>
    </tr>
    <tr>
        <th scope="row"><label for="kr8mb_pers_contact_anschrift">Anschrift</label></th>
        <td><textarea name="kr8mb_pers_contact_anschrift" id="kr8mb_pers_contact_anschrift" rows="3" cols="40"><?php echo $anschrift; ?></textarea></td>
        <th scope="row"><label for="kr8mb_pers_contact_telefon">Telefon</label></th>
        <td><input type="text" name="kr8mb_pers_contact_telefon" id="kr8mb_pers_contact_telefon" value="<?php echo $telefon; ?>" class="regular-text" /></td>
    </tr>
    </tbody></table>
    <?php
}

function gk_save_person_contact( $post_id ) {
    if ( ! gk_can_save_meta( $post_id ) ) return;

    $text_fields = array(
        'kr8mb_pers_contact_www',
        'kr8mb_pers_contact_email',
        'kr8mb_pers_contact_facebook',
        'kr8mb_pers_contact_twitter',
        'kr8mb_pers_contact_mastodon',
        'kr8mb_pers_contact_insta',
        'kr8mb_pers_contact_tiktok',
        'kr8mb_pers_contact_threads',
        'kr8mb_pers_contact_telefon',
    );

    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }

    if ( isset( $_POST['kr8mb_pers_contact_anschrift'] ) ) {
        update_post_meta( $post_id, 'kr8mb_pers_contact_anschrift', sanitize_textarea_field( $_POST['kr8mb_pers_contact_anschrift'] ) );
    }
}


// ── Page: Themen & Story ─────────────────────────────────────────────────────

function gk_page_themen_cb( $post ) {
    $values   = get_post_custom( $post->ID );
    $themenid = isset( $values['kr8mb_page_themen_id'] ) ? esc_attr( $values['kr8mb_page_themen_id'][0] ) : '';
    $formatid = isset( $values['kr8mb_page_format_id'] ) ? esc_attr( $values['kr8mb_page_format_id'][0] ) : '';

    wp_nonce_field( 'gk_meta_box_nonce', 'gk_meta_box_nonce' );
    ?>
    <table class="form-table"><tbody>
    <tr>
        <th scope="row"><label for="kr8mb_page_themen_id">Thema (Schlagwort)</label></th>
        <td><input type="text" name="kr8mb_page_themen_id" id="kr8mb_page_themen_id" value="<?php echo $themenid; ?>" class="regular-text" /><br><span class="description">Slug der Schlagworte, Komma-getrennt, z.B. "umwelt,klima"</span></td>
        <th scope="row"><label for="kr8mb_page_format_id">Format (Kategorie)</label></th>
        <td><input type="text" name="kr8mb_page_format_id" id="kr8mb_page_format_id" value="<?php echo $formatid; ?>" class="regular-text" /><br><span class="description">Slug der Kategorien, Komma-getrennt</span></td>
    </tr>
    </tbody></table>
    <?php
}

function gk_page_story_cb( $post ) {
    $values = get_post_custom( $post->ID );
    $vz     = isset( $values['kr8mb_page_story_vz'] ) ? esc_attr( $values['kr8mb_page_story_vz'][0] ) : '';

    wp_nonce_field( 'gk_meta_box_nonce', 'gk_meta_box_nonce' );
    ?>
    <table class="form-table"><tbody>
    <tr>
        <th scope="row"><label for="kr8mb_page_story_vz">Menüpunkte</label></th>
        <td><textarea name="kr8mb_page_story_vz" id="kr8mb_page_story_vz" rows="5" cols="50"><?php echo $vz; ?></textarea><br><span class="description">Menüelemente für das Inhaltsverzeichnis (li a Format)</span></td>
    </tr>
    </tbody></table>
    <?php
}

function gk_save_page_meta( $post_id ) {
    if ( ! gk_can_save_meta( $post_id ) ) return;

    $fields = array( 'kr8mb_page_themen_id', 'kr8mb_page_format_id', 'kr8mb_page_story_vz' );
    foreach ( $fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, $field, sanitize_text_field( $_POST[ $field ] ) );
        }
    }
}


// ── Helper ───────────────────────────────────────────────────────────────────

function gk_can_save_meta( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return false;
    if ( ! isset( $_POST['gk_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['gk_meta_box_nonce'], 'gk_meta_box_nonce' ) ) return false;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return false;
    return true;
}
