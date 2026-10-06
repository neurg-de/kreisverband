<?php
/**
 * Narrow OV editing capabilities and a dedicated homepage/rubric/menu editor.
 *
 * @package Neurg_Kreisverband
 */

/**
 * A meta capability checks both role and exact OV, without granting core admin rights.
 *
 * @param array  $caps Primitive capabilities.
 * @param string $cap Requested capability.
 * @param int    $user_id User ID.
 * @param array  $args Requested OV or category.
 * @return array
 */
function gk_ov_editor_cap( $caps, $cap, $user_id, $args ) {
    $user = get_userdata( $user_id );
    if ( 'gk_edit_ov_home' === $cap ) {
        if ( ! $user ) {
            return array( 'do_not_allow' );
        }
        $term_id = absint( $args[0] ?? 0 );
        $term    = get_term( $term_id, 'gk_zuordnung' );
        if ( ! $term instanceof WP_Term || 'kreisverband' === $term->slug || gk_is_event_only_term( $term_id ) ) {
            return array( 'do_not_allow' );
        }
        if ( user_can( $user, 'manage_options' ) ) {
            return array( 'manage_options' );
        }
        $roles = (array) $user->roles;
        return array_intersect( array( 'gk_ovadmin', 'gk_ovautor' ), $roles ) && gk_user_scope( $user ) === $term_id ? array( 'read' ) : array( 'do_not_allow' );
    }
    if ( in_array( $cap, array( 'edit_term', 'delete_term', 'assign_term' ), true ) && $user && gk_is_restricted_editor( $user ) ) {
        $term = get_term( absint( $args[0] ?? 0 ) );
        if ( $term instanceof WP_Term && 'category' === $term->taxonomy ) {
            $owner = (int) get_term_meta( $term->term_id, '_gk_category_owner', true );
            if ( 'assign_term' === $cap ) {
                if ( $owner && null !== gk_user_scope( $user ) && gk_user_scope( $user ) !== $owner ) {
                    return array( 'do_not_allow' );
                }
                return $caps;
            }
            // Core category endpoints remain closed; writes use the dedicated service.
            if ( in_array( 'gk_ovadmin', $user->roles, true ) || in_array( 'gk_ovautor', $user->roles, true ) ) {
                return array( 'do_not_allow' );
            }
        }
    }
    return $caps;
}
add_filter( 'map_meta_cap', 'gk_ov_editor_cap', 30, 4 );

/**
 * Authorize an explicit OV operation.
 *
 * @param int $term_id OV ID.
 * @return bool
 */
function gk_can_edit_ov_home( $term_id ) {
    // phpcs:ignore WordPress.WP.Capabilities.Unknown -- Object-scoped capability defined by gk_ov_editor_cap.
    return current_user_can( 'gk_edit_ov_home', $term_id );
}

/**
 * Scoped category service; creation cannot claim existing categories.
 *
 * @param int    $term_id OV ID.
 * @param string $name New label.
 * @param int    $category Existing owned category ID, or zero.
 * @return int|WP_Error
 */
function gk_ov_save_category( $term_id, $name, $category = 0 ) {
    if ( ! gk_can_edit_ov_home( $term_id ) ) {
        return new WP_Error( 'gk_forbidden', __( 'Du darfst nur Rubriken deines eigenen Bereichs bearbeiten.', 'neurg-kreisverband' ) );
    }
    $name = is_string( $name ) ? sanitize_text_field( $name ) : '';
    if ( '' === $name ) {
        return new WP_Error( 'gk_name', __( 'Bitte einen Rubriknamen eingeben.', 'neurg-kreisverband' ) );
    }
    $term = get_term( $term_id, 'gk_zuordnung' );
    if ( $category ) {
        if ( ! gk_ov_owned_category_ids( $term_id, array( $category ) ) ) {
            return new WP_Error( 'gk_forbidden', __( 'Diese Rubrik gehört nicht zu deinem Bereich.', 'neurg-kreisverband' ) );
        }
        // Keep the slug and archive URL stable on renames.
        $result = wp_update_term( $category, 'category', array( 'name' => $name ) );
    } else {
        $slug = $term->slug . '-' . sanitize_title( $name );
        if ( get_term_by( 'slug', $slug, 'category' ) ) {
            return new WP_Error( 'gk_duplicate', __( 'Diese Rubrik existiert bereits. Bitte die vorhandene Rubrik verwenden.', 'neurg-kreisverband' ) );
        }
        // A private, synchronous context authorizes only this insertion.
        $GLOBALS['gk_ov_category_write'] = (int) $term_id;
        try {
            $result = wp_insert_term( $name, 'category', array( 'slug' => $slug ) );
            if ( ! is_wp_error( $result ) ) {
                update_term_meta( $result['term_id'], '_gk_category_owner', (int) $term_id );
            }
        } finally {
            unset( $GLOBALS['gk_ov_category_write'] );
        }
    }
    return is_wp_error( $result ) ? $result : (int) $result['term_id'];
}

/**
 * Block unsupervised insertion through classic AJAX/XML-RPC/category endpoints.
 *
 * @param string|WP_Error $term Proposed term.
 * @param string          $taxonomy Taxonomy.
 * @return string|WP_Error
 */
function gk_ov_guard_category_insert( $term, $taxonomy ) {
    if ( 'category' === $taxonomy && gk_is_restricted_editor() && null !== gk_user_scope() ) {
        $scope = (int) ( $GLOBALS['gk_ov_category_write'] ?? 0 );
        if ( ! $scope || ! gk_can_edit_ov_home( $scope ) ) {
            return new WP_Error( 'gk_forbidden', __( 'Neue Rubriken des eigenen Bereichs bitte unter der Gestaltung der Bereichsstartseite anlegen.', 'neurg-kreisverband' ) );
        }
    }
    return $term;
}
add_filter( 'pre_insert_term', 'gk_ov_guard_category_insert', 10, 2 );

/**
 * Preserve old assignments, but prevent adding a foreign OV rubric even via bulk edit.
 *
 * @param int    $object_id Post ID.
 * @param mixed  $terms Submitted terms.
 * @param int[]  $tt_ids New term-taxonomy IDs.
 * @param string $taxonomy Taxonomy.
 * @param bool   $append Append flag.
 * @param int[]  $old_tt_ids Previous term-taxonomy IDs.
 */
function gk_ov_guard_category_assignment( $object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids ) {
    if ( 'category' !== $taxonomy || ! gk_is_restricted_editor() || null === gk_user_scope() ) {
        return;
    }
    $scope = gk_user_scope();
    foreach ( $tt_ids as $tt_id ) {
        $term = get_term_by( 'term_taxonomy_id', $tt_id, 'category' );
        if ( ! $term ) {
            continue;
        }
        $owner = (int) get_term_meta( $term->term_id, '_gk_category_owner', true );
        if ( $owner && $owner !== $scope && ! in_array( (int) $tt_id, array_map( 'intval', $old_tt_ids ), true ) ) {
            wp_remove_object_terms( $object_id, $term->term_id, 'category' );
        }
    }
}
add_action( 'set_object_terms', 'gk_ov_guard_category_assignment', 10, 6 );

/**
 * Hide categories owned by other OVs from restricted editors, keeping shared categories.
 *
 * @param array $args Term query args.
 * @param array $taxonomies Requested taxonomies.
 * @return array
 */
function gk_ov_category_choices( $args, $taxonomies ) {
    if ( ! in_array( 'category', $taxonomies, true ) || ! gk_is_restricted_editor() || null === gk_user_scope() || ( ! is_admin() && ! ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
        return $args;
    }
    $boundary = array(
		'relation' => 'OR',
		array(
			'key'     => '_gk_category_owner',
			'compare' => 'NOT EXISTS',
		),
		array(
			'key'     => '_gk_category_owner',
			'value'   => gk_user_scope(),
			'compare' => '=',
		),
	);
    // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Ownership boundary is required for category selection.
    $args['meta_query'] = empty( $args['meta_query'] ) ? $boundary : array(
		'relation' => 'AND',
		$args['meta_query'],
		$boundary,
	); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Owner boundary must apply to editor category choices.
    return $args;
}
add_filter( 'get_terms_args', 'gk_ov_category_choices', 20, 2 );

/**
 * Ownership meta cannot be altered by a restricted account via any metadata endpoint.
 *
 * @param mixed  $check Existing decision.
 * @param int    $object_id Category ID.
 * @param string $meta_key Key.
 * @return mixed
 */
function gk_ov_category_owner_guard( $check, $object_id, $meta_key ) {
    if ( '_gk_category_owner' === $meta_key && gk_is_restricted_editor() && ! ( ! get_term_meta( $object_id, $meta_key, true ) && (int) ( $GLOBALS['gk_ov_category_write'] ?? 0 ) === gk_user_scope() ) ) {
        return false;
    }
    return $check;
}
add_filter( 'add_term_metadata', 'gk_ov_category_owner_guard', 10, 3 );
add_filter( 'update_term_metadata', 'gk_ov_category_owner_guard', 10, 3 );
add_filter( 'delete_term_metadata', 'gk_ov_category_owner_guard', 10, 3 );

/**
 * Reject shared OV menu IDs: a restricted user may change only its uniquely assigned main menu.
 *
 * @param int $term_id OV ID.
 * @return int|WP_Error
 */
function gk_ov_editable_menu( $term_id ) {
    if ( ! gk_can_edit_ov_home( $term_id ) ) {
        return new WP_Error( 'gk_forbidden', __( 'Kein Zugriff auf dieses Bereichsmenü.', 'neurg-kreisverband' ) );
    }
    $term      = get_term( $term_id, 'gk_zuordnung' );
    $location  = 'nav-' . $term->slug;
    $locations = get_nav_menu_locations();
    $menu_id   = (int) ( $locations[ $location ] ?? 0 );
    if ( ! $menu_id || ! wp_get_nav_menu_object( $menu_id ) ) {
        return new WP_Error( 'gk_menu', __( 'Die Administration muss zuerst ein eigenes Hauptmenü für den Bereich zuweisen.', 'neurg-kreisverband' ) );
    }
    foreach ( $locations as $key => $id ) {
        if ( $key !== $location && (int) $id === $menu_id ) {
            return new WP_Error( 'gk_menu_shared', __( 'Dieses Menü wird an mehreren Stellen verwendet. Die Administration muss ein eigenes Bereichsmenü zuweisen.', 'neurg-kreisverband' ) );
        }
    }
    return $menu_id;
}

/**
 * Add/replace/remove exactly one top-level link in the own existing menu.
 *
 * @param int    $term_id OV ID.
 * @param int    $category Owned category ID for links.
 * @param int    $item Existing menu item, or zero.
 * @param string $operation add, replace, remove.
 * @return int|WP_Error
 */
function gk_ov_change_menu( $term_id, $category, $item = 0, $operation = 'add' ) {
    $menu_id = gk_ov_editable_menu( $term_id );
    if ( is_wp_error( $menu_id ) ) {
        return $menu_id;
    }
    if ( ! in_array( $operation, array( 'add', 'replace', 'remove' ), true ) ) {
        return new WP_Error( 'gk_operation', __( 'Unbekannte Menüaktion.', 'neurg-kreisverband' ) );
    }
    $items = wp_get_nav_menu_items( $menu_id );
    $found = null;
    foreach ( $items ? $items : array() as $existing ) {
        if ( (int) $existing->ID === (int) $item ) {
            $found = $existing;
        }
    }
    if ( 'add' !== $operation && ( ! $found || (int) $found->menu_item_parent ) ) {
        return new WP_Error( 'gk_item', __( 'Bitte einen obersten Menüpunkt des eigenen Bereichsmenüs wählen.', 'neurg-kreisverband' ) );
    }
    if ( 'remove' === $operation ) {
        foreach ( $items as $existing ) {
            if ( (int) $existing->menu_item_parent === (int) $item ) {
                return new WP_Error( 'gk_children', __( 'Menüpunkte mit Unterpunkten bearbeitet die Administration.', 'neurg-kreisverband' ) );
            }
        }
        return wp_delete_post( $item, true ) ? $item : new WP_Error( 'gk_remove', __( 'Menüpunkt konnte nicht entfernt werden.', 'neurg-kreisverband' ) );
    }
    $url = gk_ov_news_url( $term_id, $category );
    if ( ! $category || ! $url ) {
        return new WP_Error( 'gk_category', __( 'Bitte eine Rubrik des eigenen Bereichs mit erreichbarer Startseite wählen.', 'neurg-kreisverband' ) );
    }
    $cat = get_term( $category, 'category' );
    return wp_update_nav_menu_item(
        $menu_id,
        'replace' === $operation ? $item : 0,
        array(
            'menu-item-title'     => $cat->name,
            'menu-item-url'       => $url,
            'menu-item-type'      => 'custom',
            'menu-item-status'    => 'publish',
            'menu-item-position'  => $found && 'replace' === $operation ? $found->menu_order : 0,
            'menu-item-parent-id' => 0,
        )
    );
}

/**
 * Save only known homepage controls after an exact object-capability check.
 *
 * @param int   $term_id OV ID.
 * @param array $input Form controls.
 * @return true|WP_Error
 */
function gk_ov_save_home( $term_id, $input ) {
    if ( ! gk_can_edit_ov_home( $term_id ) ) {
        return new WP_Error( 'gk_forbidden', __( 'Kein Zugriff auf diese Bereichsstartseite.', 'neurg-kreisverband' ) );
    }
    $keys   = array_keys( gk_ov_section_labels() );
    $order  = gk_ov_valid_order( $input['section_order'] ?? array(), $keys );
    $shown  = array_intersect( is_array( $input['visible'] ?? null ) ? $input['visible'] : array(), $keys );
    $groups = array_keys( gk_ov_team_groups( $term_id ) );
    $config = array(
        'show_text'  => empty( $input['show_text'] ) ? '0' : '1',
        'show_label' => empty( $input['show_label'] ) ? '0' : '1',
        'title'      => is_string( $input['title'] ?? null ) ? sanitize_text_field( $input['title'] ) : '',
        'news_count' => max( 3, min( 20, is_scalar( $input['news_count'] ?? null ) ? (int) $input['news_count'] : 6 ) ),
        'archive'    => empty( $input['archive'] ) ? '0' : '1',
        'excluded'   => gk_ov_owned_category_ids( $term_id, $input['excluded'] ?? array() ),
        'team_order' => gk_ov_valid_order( $input['team_order'] ?? array(), $groups ),
    );
    update_term_meta( $term_id, '_gk_home_editor', $config );
    update_term_meta(
        $term_id,
        '_gk_home_sections',
        array(
			'order'   => $order,
			'visible' => array_values( $shown ),
        )
    );
    return true;
}

/** Register a dedicated editor available to OV accounts and administration. */
function gk_ov_editor_menu() {
    $scope = gk_user_scope();
    if ( current_user_can( 'manage_options' ) || ( $scope && gk_can_edit_ov_home( $scope ) ) ) {
        $hook = add_menu_page( gk_association_label( 'secondary', 'abbreviation' ) . '-Startseite gestalten', gk_association_label( 'secondary', 'abbreviation' ) . '-Startseite', 'read', 'gk-ov-home', 'gk_ov_editor_page', 'dashicons-layout', 28 );
        add_action( 'load-' . $hook, 'gk_ov_editor_preflight' );
    }
}
add_action( 'admin_menu', 'gk_ov_editor_menu', 1000 );

/** Validate writes before the admin header emits output, preserving HTTP error codes. */
function gk_ov_editor_preflight() {
    if ( 'POST' !== sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
        return;
    }
    $term_id = gk_ov_editor_term_id();
    if ( ! gk_can_edit_ov_home( $term_id ) ) {
        wp_die( esc_html__( 'Kein Zugriff auf diesen Bereich.', 'neurg-kreisverband' ), '', array( 'response' => 403 ) );
    }
    check_admin_referer( 'gk_ov_home_' . $term_id );
}

/**
 * Resolve the requested OV; unrestricted KV editorial roles are deliberately excluded.
 *
 * @return int
 */
function gk_ov_editor_term_id() {
    if ( current_user_can( 'manage_options' ) ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only scope selection; writes require the scoped nonce below.
        return isset( $_GET['ov'] ) ? absint( $_GET['ov'] ) : 0;
    }
    return (int) gk_user_scope();
}

/**
 * Render a sortable list with native keyboard-accessible buttons.
 *
 * @param string     $name Control prefix.
 * @param array      $labels Key => label in requested order.
 * @param array|null $visible Optional visibility map.
 */
function gk_ov_order_controls( $name, $labels, $visible = null ) {
    echo '<ol class="gk-ov-order">';
    foreach ( $labels as $key => $label ) {
        echo '<li draggable="true">';
        echo '<input type="hidden" name="' . esc_attr( $name ) . '[]" value="' . esc_attr( $key ) . '">';
        echo '<span>' . esc_html( $label ) . '</span> ';
        if ( null !== $visible ) {
            echo '<label><input type="checkbox" name="visible[]" value="' . esc_attr( $key ) . '" aria-label="' . esc_attr( $label . ' ' . __( 'anzeigen', 'neurg-kreisverband' ) ) . '" ' . checked( ! empty( $visible[ $key ] ), true, false ) . '> ' . esc_html__( 'anzeigen', 'neurg-kreisverband' ) . '</label> ';
        }
        echo '<button type="button" class="button gk-ov-up" aria-label="' . esc_attr( $label . ' ' . __( 'nach oben', 'neurg-kreisverband' ) ) . '">' . esc_html__( 'Auf', 'neurg-kreisverband' ) . '</button> ';
        echo '<button type="button" class="button gk-ov-down" aria-label="' . esc_attr( $label . ' ' . __( 'nach unten', 'neurg-kreisverband' ) ) . '">' . esc_html__( 'Ab', 'neurg-kreisverband' ) . '</button>';
        echo '</li>';
    }
    echo '</ol>';
}

/** Full editor, with nonce checks before every write. */
function gk_ov_editor_page() {
    $term_id = gk_ov_editor_term_id();
    if ( ! $term_id && current_user_can( 'manage_options' ) ) {
		echo '<div class="wrap"><h1>' . esc_html( gk_association_label( 'secondary', 'abbreviation' ) . '-Startseite gestalten' ) . '</h1>';
        echo '<ul>';
        foreach ( gk_get_ov_terms() as $term ) {
            echo '<li><a href="' . esc_url( admin_url( 'admin.php?page=gk-ov-home&ov=' . $term->term_id ) ) . '">' . esc_html( $term->name ) . '</a></li>';
        }
        echo '</ul></div>';
        return;
    }
    if ( ! gk_can_edit_ov_home( $term_id ) ) {
        wp_die( esc_html__( 'Kein Zugriff auf diesen Bereich.', 'neurg-kreisverband' ), '', array( 'response' => 403 ) );
    }
    $result = null;
    if ( 'POST' === sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ?? '' ) ) ) {
        check_admin_referer( 'gk_ov_home_' . $term_id );
        $action = isset( $_POST['gk_action'] ) ? sanitize_key( wp_unslash( $_POST['gk_action'] ) ) : '';
        if ( 'home' === $action ) {
            $input  = map_deep( wp_unslash( $_POST ), 'sanitize_text_field' );
            $result = gk_ov_save_home( $term_id, $input );
        } elseif ( 'category' === $action ) {
            $result = gk_ov_save_category( $term_id, sanitize_text_field( wp_unslash( $_POST['rubric_name'] ?? '' ) ), absint( $_POST['rubric_id'] ?? 0 ) );
        } elseif ( 'menu' === $action ) {
            $result = gk_ov_change_menu( $term_id, absint( $_POST['menu_category'] ?? 0 ), absint( $_POST['menu_item'] ?? 0 ), sanitize_key( wp_unslash( $_POST['menu_operation'] ?? '' ) ) );
        } elseif ( 'reset' === $action ) {
            delete_term_meta( $term_id, '_gk_home_editor' );
            delete_term_meta( $term_id, '_gk_home_sections' );
            $result = true;
        }
        if ( null !== $result ) {
            echo '<div class="notice ' . ( is_wp_error( $result ) ? 'notice-error' : 'notice-success' ) . '" role="status"><p>' . esc_html( is_wp_error( $result ) ? $result->get_error_message() : __( 'Gespeichert. Bitte die öffentliche Seite prüfen.', 'neurg-kreisverband' ) ) . '</p></div>';
        }
    }
    echo '<div class="wrap"><h1>' . esc_html( gk_association_label( 'secondary', 'abbreviation' ) . '-Startseite gestalten' ) . '</h1>';
    $term     = get_term( $term_id, 'gk_zuordnung' );
    $config   = gk_ov_home_config( $term_id );
    $sections = gk_ov_home_sections( $term_id );
    $cats     = get_terms(
        array(
			'taxonomy'   => 'category',
			'hide_empty' => false,
        )
    );
    $cats     = is_wp_error( $cats ) ? array() : array_filter( $cats, static fn( $cat ) => (int) get_term_meta( $cat->term_id, '_gk_category_owner', true ) === $term_id );
    $groups   = gk_ov_team_groups( $term_id );
    $groups   = array_replace( array_flip( gk_ov_valid_order( $config['team_order'], array_keys( $groups ) ) ), $groups );
    wp_enqueue_script( 'gk-ov-editor', GK_URI . '/lib/js/ov-editor.js', array(), GK_VERSION, true );
    ?>
    <h2><?php echo esc_html( $term->name ); ?></h2>
    <p><?php esc_html_e( 'Diese Einstellungen gelten nur für diesen Bereich. Auf/Ab oder Ziehen ändert die Reihenfolge; anschließend speichern.', 'neurg-kreisverband' ); ?></p>
    <form method="post">
        <?php wp_nonce_field( 'gk_ov_home_' . $term_id ); ?>
        <input type="hidden" name="gk_action" value="home">
        <h2><?php esc_html_e( 'Sektionen', 'neurg-kreisverband' ); ?></h2>
        <?php gk_ov_order_controls( 'section_order', array_replace( array_flip( array_keys( $sections ) ), gk_ov_section_labels() ), $sections ); ?>
        <p><?php esc_html_e( 'Seiteninhalt ist der zusätzliche Text mit Blöcken/Shortcodes aus dem Seiteneditor. Leere Team- oder Terminbereiche erscheinen erst mit passenden veröffentlichten Inhalten.', 'neurg-kreisverband' ); ?></p>
        <h2><?php esc_html_e( 'Titelbild', 'neurg-kreisverband' ); ?></h2>
        <p><label><input type="checkbox" name="show_text" value="1" <?php checked( $config['show_text'] ); ?>> <?php esc_html_e( 'Begrüßung/Titeltext anzeigen', 'neurg-kreisverband' ); ?></label></p>
        <p><label for="gk-title"><?php esc_html_e( 'Eigener Titeltext (leer: bisheriger Titel)', 'neurg-kreisverband' ); ?></label><br><input class="large-text" id="gk-title" name="title" value="<?php echo esc_attr( $config['title'] ); ?>"></p>
        <p><label><input type="checkbox" name="show_label" value="1" <?php checked( $config['show_label'] ); ?>> <?php esc_html_e( 'Bereichslabel im Titelbild anzeigen, wenn der Einstiegsmodus eines verwendet', 'neurg-kreisverband' ); ?></label></p>
        <p><?php esc_html_e( 'Im Standardmodus gibt es kein zusätzliches Typ-Label. Kopfzeile und Menü bleiben eigenständig. Das Bild bleibt im Seiteneditor beziehungsweise in den bisherigen Verband-Einstellungen pflegbar.', 'neurg-kreisverband' ); ?></p>
        <h2><?php esc_html_e( 'Aktuelles', 'neurg-kreisverband' ); ?></h2>
        <p><label for="gk-count"><?php esc_html_e( 'Anzahl der Beiträge (3 bis 20)', 'neurg-kreisverband' ); ?></label> <input type="number" id="gk-count" name="news_count" min="3" max="20" value="<?php echo esc_attr( $config['news_count'] ); ?>"></p>
        <p><label><input type="checkbox" name="archive" value="1" <?php checked( $config['archive'] || ! metadata_exists( 'term', $term_id, '_gk_home_editor' ) ); ?>> <?php esc_html_e( 'Link „Alle Beiträge“ anzeigen', 'neurg-kreisverband' ); ?></label></p>
        <fieldset><legend><?php esc_html_e( 'Diese Rubriken aus Aktuelles ausblenden (im Archiv bleiben sie sichtbar)', 'neurg-kreisverband' ); ?></legend>
        <?php foreach ( $cats as $cat ) : ?>
        <p><label><input type="checkbox" name="excluded[]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, $config['excluded'], true ) ); ?>> <?php echo esc_html( $cat->name ); ?></label></p>
        <?php endforeach; ?>
        </fieldset>
        <h2><?php esc_html_e( 'Team-Gruppen', 'neurg-kreisverband' ); ?></h2>
        <?php gk_ov_order_controls( 'team_order', $groups ); ?>
        <p><?php esc_html_e( 'Die Positionen und Funktionen der Personen innerhalb einer Gruppe bleiben erhalten. Neue Gruppen erscheinen nach den gespeicherten Gruppen.', 'neurg-kreisverband' ); ?></p>
        <?php submit_button( __( 'Startseite speichern', 'neurg-kreisverband' ) ); ?>
    </form>
    <h2><?php esc_html_e( 'Eigene Rubriken', 'neurg-kreisverband' ); ?></h2>
    <p><?php esc_html_e( 'Neue Rubriken gehören automatisch diesem Bereich. Zum Umbenennen eine eigene Rubrik wählen. Bestehende gemeinsame Rubriken übernimmt die Administration; Löschen bleibt bei ihr.', 'neurg-kreisverband' ); ?></p>
    <form method="post">
        <?php wp_nonce_field( 'gk_ov_home_' . $term_id ); ?>
        <input type="hidden" name="gk_action" value="category">
        <p><label for="gk-rubric-id"><?php esc_html_e( 'Rubrik', 'neurg-kreisverband' ); ?></label> <select id="gk-rubric-id" name="rubric_id"><option value="0"><?php esc_html_e( 'Neue Rubrik', 'neurg-kreisverband' ); ?></option>
        <?php
        foreach ( $cats as $cat ) :
			?>
            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option><?php endforeach; ?></select></p>
        <p><label for="gk-rubric-name"><?php esc_html_e( 'Rubrikname', 'neurg-kreisverband' ); ?></label> <input id="gk-rubric-name" name="rubric_name" required></p>
        <?php submit_button( __( 'Rubrik speichern', 'neurg-kreisverband' ) ); ?>
    </form>
    <h2><?php esc_html_e( 'Rubrik im Bereichsmenü', 'neurg-kreisverband' ); ?></h2>
    <?php $menu = gk_ov_editable_menu( $term_id ); ?>
    <?php if ( is_wp_error( $menu ) ) : ?>
        <p><?php echo esc_html( $menu->get_error_message() ); ?></p>
    <?php else : ?>
    <p><?php esc_html_e( 'Ersetzen oder Entfernen betrifft nur den Menülink. Die verlinkte Seite und ihre Beiträge werden nicht gelöscht. Unterpunkte bleiben beim Ersetzen erhalten.', 'neurg-kreisverband' ); ?></p>
    <form method="post">
        <?php wp_nonce_field( 'gk_ov_home_' . $term_id ); ?>
        <input type="hidden" name="gk_action" value="menu">
        <p><label for="gk-menu-op"><?php esc_html_e( 'Aktion', 'neurg-kreisverband' ); ?></label> <select name="menu_operation" id="gk-menu-op"><option value="add"><?php esc_html_e( 'Rubrik hinzufügen', 'neurg-kreisverband' ); ?></option><option value="replace"><?php esc_html_e( 'Menüpunkt durch Rubrik ersetzen', 'neurg-kreisverband' ); ?></option><option value="remove"><?php esc_html_e( 'Menüpunkt entfernen', 'neurg-kreisverband' ); ?></option></select></p>
        <p><label for="gk-menu-item"><?php esc_html_e( 'Bisheriger Menüpunkt (für Ersetzen/Entfernen)', 'neurg-kreisverband' ); ?></label> <select name="menu_item" id="gk-menu-item"><option value="0">—</option>
        <?php
        foreach ( ( wp_get_nav_menu_items( $menu ) ? wp_get_nav_menu_items( $menu ) : array() ) as $item ) :
			?>
            <?php
			if ( ! (int) $item->menu_item_parent ) :
				?>
            <option value="<?php echo esc_attr( $item->ID ); ?>"><?php echo esc_html( $item->title ); ?></option><?php endif; ?><?php endforeach; ?></select></p>
        <p><label for="gk-menu-cat"><?php esc_html_e( 'Eigene Rubrik (für Hinzufügen/Ersetzen)', 'neurg-kreisverband' ); ?></label> <select name="menu_category" id="gk-menu-cat"><option value="0">—</option>
        <?php
        foreach ( $cats as $cat ) :
			?>
            <option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option><?php endforeach; ?></select></p>
        <?php submit_button( __( 'Bereichsmenü ändern', 'neurg-kreisverband' ) ); ?>
    </form>
    <?php endif; ?>
    <h2><?php esc_html_e( 'Darstellung zurücksetzen', 'neurg-kreisverband' ); ?></h2>
    <p><?php esc_html_e( 'Setzt nur die neuen Startseitenoptionen auf das bisherige Verhalten zurück. Rubriken, Beiträge, Bilder und Menüs bleiben erhalten.', 'neurg-kreisverband' ); ?></p>
    <form method="post"><?php wp_nonce_field( 'gk_ov_home_' . $term_id ); ?><input type="hidden" name="gk_action" value="reset"><?php submit_button( __( 'Bisherige Darstellung wiederherstellen', 'neurg-kreisverband' ), 'secondary' ); ?></form>
    <p class="screen-reader-text" id="gk-ov-order-status" aria-live="polite"></p>
    </div>
    <?php
}
