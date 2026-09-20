<?php
/**
 * Minimal administrator-only role overview.
 *
 * @package Neurg_Kreisverband
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** Administrator gate, kept separate from editorial OV management. */
function gk_can_manage_editorial_roles() {
    return current_user_can( 'manage_options' ) && current_user_can( 'list_users' ) && current_user_can( 'promote_users' );
}

/**
 * Validated role change, shared by the form handler and regression tests.
 *
 * @param int    $user_id Target user ID.
 * @param string $role Requested theme role.
 * @param string $nonce Per-object security nonce.
 */
function gk_change_editorial_role( $user_id, $role, $nonce ) {
    if ( ! gk_can_manage_editorial_roles() || ! current_user_can( 'promote_user', $user_id ) ) {
        return new WP_Error( 'gk_role_forbidden', __( 'Keine Berechtigung zur Rollenänderung.', 'neurg-kreisverband' ) );
    }
    if ( ! wp_verify_nonce( $nonce, 'gk_role_change_' . $user_id ) ) {
        return new WP_Error( 'gk_role_nonce', __( 'Sicherheitsprüfung fehlgeschlagen. Bitte die Übersicht neu laden.', 'neurg-kreisverband' ) );
    }
    $user = get_userdata( $user_id );
    if ( ! $user || get_current_user_id() === $user_id || user_can( $user, 'manage_options' ) || ! in_array( $role, gk_editorial_roles(), true ) || ! array_intersect( $user->roles, gk_editorial_roles() ) ) {
        return new WP_Error( 'gk_role_invalid', __( 'Hier können nur andere bestehende Theme-Redaktionskonten geändert werden.', 'neurg-kreisverband' ) );
    }
    // Preserve the explicitly designated office account and its scope.
    if ( 'geschaeftsstelle' === $user->user_login && 'gk_kvautor_ov' !== $role ) {
        return new WP_Error( 'gk_office_role', __( 'Die Geschäftsstelle behält KV-Autor (mit OV-Zugang).', 'neurg-kreisverband' ) );
    }
    if ( in_array( $role, array( 'gk_ovadmin', 'gk_ovautor' ), true ) ) {
        $term = get_term_by( 'slug', $user->user_login, 'gk_zuordnung' );
        if ( ! $term || 'kreisverband' === $term->slug ) {
            return new WP_Error( 'gk_role_scope', __( 'Für eine OV-Rolle muss der Benutzername einem bestehenden OV-Zuordnungskürzel entsprechen.', 'neurg-kreisverband' ) );
        }
    }
    $user->set_role( $role );
    return true;
}

/** Register under Users, never under the editorial OV menus. */
function gk_register_role_overview() {
    add_users_page( __( 'Theme-Rollen', 'neurg-kreisverband' ), __( 'Theme-Rollen', 'neurg-kreisverband' ), 'promote_users', 'gk-role-overview', 'gk_render_role_overview' );
}
add_action( 'admin_menu', 'gk_register_role_overview' );

/** Show only login, theme role and responsibility; no email or credential fields. */
function gk_render_role_overview() {
    if ( ! gk_can_manage_editorial_roles() ) {
        wp_die( esc_html__( 'Keine Berechtigung.', 'neurg-kreisverband' ), '', array( 'response' => 403 ) );
    }
    echo '<div class="wrap"><h1>' . esc_html__( 'Theme-Rollen und Zuständigkeiten', 'neurg-kreisverband' ) . '</h1>';
    echo '<p>' . esc_html__( 'KV-Autor: eigene KV-Inhalte. KV-Autor (mit OV-Zugang): Inhalte aller Bereiche. OV-Admin: Inhalte des eigenen OVs. OV-Autor: eigene Beiträge, Personen und Termine im eigenen OV. Eingeschränkte Rollen dürfen den Papierkorb verwenden, aber nicht dauerhaft löschen. OV-Zuordnungen folgen dem Benutzernamen, der dem OV-Kürzel entsprechen muss.', 'neurg-kreisverband' ) . '</p>';
    // phpcs:disable WordPress.Security.NonceVerification.Missing -- gk_change_editorial_role verifies the per-user nonce before mutation.
    if ( isset( $_POST['gk_role_user'], $_POST['gk_role'], $_POST['gk_role_nonce'] ) ) {
        $result = gk_change_editorial_role( absint( $_POST['gk_role_user'] ), sanitize_key( wp_unslash( $_POST['gk_role'] ) ), sanitize_text_field( wp_unslash( $_POST['gk_role_nonce'] ) ) );
        echo '<div class="notice ' . ( is_wp_error( $result ) ? 'notice-error' : 'notice-success' ) . '"><p>' . esc_html( is_wp_error( $result ) ? $result->get_error_message() : __( 'Rolle gespeichert.', 'neurg-kreisverband' ) ) . '</p></div>';
    }
    // phpcs:enable WordPress.Security.NonceVerification.Missing
    $roles = wp_roles()->roles;
    echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Benutzername', 'neurg-kreisverband' ) . '</th><th>' . esc_html__( 'Theme-Rolle', 'neurg-kreisverband' ) . '</th><th>' . esc_html__( 'Zuständigkeit', 'neurg-kreisverband' ) . '</th><th>' . esc_html__( 'Rolle ändern', 'neurg-kreisverband' ) . '</th></tr></thead><tbody>';
    foreach ( get_users(
        array(
			'role__in' => array_merge( gk_editorial_roles(), array( 'administrator' ) ),
			'orderby'  => 'login',
        )
    ) as $user ) {
        $names = array();
        foreach ( $user->roles as $role ) {
            $names[] = $roles[ $role ]['name'] ?? $role;
        }
        $scope = gk_user_scope( $user );
        $term  = $scope ? get_term( $scope, 'gk_zuordnung' ) : null;
        $label = null === $scope ? __( 'KV und alle OVs', 'neurg-kreisverband' ) : ( $term && ! is_wp_error( $term ) ? $term->name : __( 'Keine gültige OV-Zuordnung – Zugriff gesperrt', 'neurg-kreisverband' ) );
        echo '<tr><td>' . esc_html( $user->user_login ) . '</td><td>' . esc_html( implode( ', ', $names ) ) . '</td><td>' . esc_html( $label ) . '</td><td>';
        if ( get_current_user_id() !== $user->ID && ! user_can( $user, 'manage_options' ) ) {
            echo '<form method="post"><input type="hidden" name="gk_role_user" value="' . esc_attr( $user->ID ) . '">';
            wp_nonce_field( 'gk_role_change_' . $user->ID, 'gk_role_nonce' );
            echo '<label class="screen-reader-text" for="gk-role-' . esc_attr( $user->ID ) . '">' . esc_html__( 'Neue Theme-Rolle', 'neurg-kreisverband' ) . '</label><select id="gk-role-' . esc_attr( $user->ID ) . '" name="gk_role">';
            foreach ( gk_editorial_roles() as $role ) {
                echo '<option value="' . esc_attr( $role ) . '" ' . selected( in_array( $role, $user->roles, true ), true, false ) . '>' . esc_html( $roles[ $role ]['name'] ) . '</option>';
            }
            echo '</select> ';
            submit_button( __( 'Rolle speichern', 'neurg-kreisverband' ), 'secondary', 'submit', false );
            echo '</form>';
        }
        echo '</td></tr>';
    }
    echo '</tbody></table></div>';
}
