/**
 * Event Sidebar Panel – Termin-Details in the Block Editor document sidebar.
 *
 * Replaces the classic meta box (which ends up at the very bottom of the page)
 * with a PluginDocumentSettingPanel so event fields are immediately visible.
 */
( function () {
    var el              = wp.element.createElement;
    var Fragment        = wp.element.Fragment;
    var registerPlugin  = wp.plugins.registerPlugin;
    var PluginDocumentSettingPanel = wp.editPost.PluginDocumentSettingPanel;
    var useSelect       = wp.data.useSelect;
    var useDispatch     = wp.data.useDispatch;
    var TextControl     = wp.components.TextControl;
    var CheckboxControl = wp.components.CheckboxControl;

    function EventSidebarPanel() {
        var postType = useSelect( function ( select ) {
            return select( 'core/editor' ).getCurrentPostType();
        }, [] );

        if ( postType !== 'gk_event' ) {
            return null;
        }

        var meta = useSelect( function ( select ) {
            return select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
        }, [] );

        var editPost = useDispatch( 'core/editor' ).editPost;

        function setMeta( key, value ) {
            var update = {};
            update[ key ] = value;
            editPost( { meta: update } );
        }

        return el( Fragment, {},
            el( PluginDocumentSettingPanel, {
                name: 'gk-event-datetime',
                title: 'Datum & Zeit',
            },
                el( TextControl, {
                    label: 'Startdatum',
                    type: 'date',
                    value: meta.gk_event_start_date || '',
                    onChange: function ( v ) { setMeta( 'gk_event_start_date', v ); },
                    required: true,
                } ),
                el( TextControl, {
                    label: 'Startzeit',
                    type: 'time',
                    value: meta.gk_event_start_time || '',
                    onChange: function ( v ) { setMeta( 'gk_event_start_time', v ); },
                } ),
                el( TextControl, {
                    label: 'Enddatum',
                    type: 'date',
                    value: meta.gk_event_end_date || '',
                    onChange: function ( v ) { setMeta( 'gk_event_end_date', v ); },
                } ),
                el( TextControl, {
                    label: 'Endzeit',
                    type: 'time',
                    value: meta.gk_event_end_time || '',
                    onChange: function ( v ) { setMeta( 'gk_event_end_time', v ); },
                } ),
                el( CheckboxControl, {
                    label: 'Ganztägig',
                    checked: meta.gk_event_all_day === '1',
                    onChange: function ( v ) { setMeta( 'gk_event_all_day', v ? '1' : '0' ); },
                } )
            ),
            el( PluginDocumentSettingPanel, {
                name: 'gk-event-location',
                title: 'Ort & Veranstalter',
            },
                el( TextControl, {
                    label: 'Ort',
                    value: meta.gk_event_location || '',
                    onChange: function ( v ) { setMeta( 'gk_event_location', v ); },
                    help: 'z.B. "Rathaus Starnberg"',
                } ),
                el( TextControl, {
                    label: 'Adresse',
                    value: meta.gk_event_address || '',
                    onChange: function ( v ) { setMeta( 'gk_event_address', v ); },
                    help: 'Strasse, PLZ Ort',
                } ),
                el( TextControl, {
                    label: 'Veranstalter',
                    value: meta.gk_event_organizer || '',
                    onChange: function ( v ) { setMeta( 'gk_event_organizer', v ); },
                } ),
                el( TextControl, {
                    label: 'Link',
                    type: 'url',
                    value: meta.gk_event_url || '',
                    onChange: function ( v ) { setMeta( 'gk_event_url', v ); },
                    help: 'Externer Link zum Termin',
                } )
            )
        );
    }

    registerPlugin( 'gk-event-sidebar', {
        render: EventSidebarPanel,
        icon: 'calendar-alt',
    } );
} )();
