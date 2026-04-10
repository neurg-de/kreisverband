( function () {
    var el              = wp.element.createElement;
    var registerBlock   = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var PanelBody       = wp.components.PanelBody;
    var SelectControl   = wp.components.SelectControl;
    var RangeControl    = wp.components.RangeControl;
    var Placeholder     = wp.components.Placeholder;
    var ServerSideRender = wp.serverSideRender;
    var useBlockProps   = wp.blockEditor.useBlockProps;

    var icon = el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', width: 24, height: 24 },
        el( 'path', { d: 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-6 8c0-2.67 5.33-4 6-4s6 1.33 6 4v1H6v-1zM1 18c0-1.78 2.67-3 4-3 .34 0 .7.04 1.07.1C5.4 16.04 5 17 5 18v1H1v-1zm22 0c0-1.78-2.67-3-4-3-.34 0-.7.04-1.07.1.67.94 1.07 1.96 1.07 2.9v1h4v-1zM7 12c1.66 0 3-1.34 3-3S8.66 6 7 6 4 7.34 4 9s1.34 3 3 3zm10 0c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3z', fill: 'currentColor' } )
    );

    var options = ( window.gkAbteilungBlock && window.gkAbteilungBlock.options ) || [];

    // Find label for a slug value
    function labelForSlug( slug ) {
        for ( var i = 0; i < options.length; i++ ) {
            if ( options[ i ].value === slug ) return options[ i ].label;
        }
        return slug;
    }

    // Inject editor-only styles once
    var styleId = 'gk-block-abteilung-editor-css';
    if ( ! document.getElementById( styleId ) ) {
        var style = document.createElement( 'style' );
        style.id = styleId;
        style.textContent =
            '.gk-block-abteilung-wrap { position: relative; max-height: 360px; overflow: hidden; border-radius: 4px; }' +
            '.gk-block-abteilung-wrap::after { content: ""; position: absolute; bottom: 0; left: 0; right: 0; height: 80px; background: linear-gradient(transparent, #fff); pointer-events: none; }' +
            '.gk-block-abteilung-wrap, .gk-block-abteilung-wrap * { pointer-events: none !important; }' +
            '.gk-block-abteilung-wrap .wp-block-server-side-render { transform: scale(0.85); transform-origin: top center; }' +
            '.gk-block-abteilung-bar { display: flex; align-items: center; gap: 8px; padding: 10px 14px; background: #f0f0f0; border-radius: 4px 4px 0 0; font-size: 13px; color: #1e1e1e; }' +
            '.gk-block-abteilung-bar svg { flex-shrink: 0; fill: #1e1e1e; }' +
            '.gk-block-abteilung-bar strong { font-weight: 600; }' +
            '.gk-block-abteilung-bar span { color: #757575; margin-left: auto; font-size: 12px; }';
        document.head.appendChild( style );
    }

    registerBlock( 'gk/abteilung', {
        title: 'Abteilung',
        description: 'Zeigt eine filterbare Personenliste einer Abteilung.',
        icon: icon,
        category: 'widgets',
        keywords: [ 'personen', 'liste', 'abteilung', 'personenliste', 'grid' ],
        supports: {
            html: false,
            align: [ 'wide', 'full' ],
        },

        edit: function ( props ) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps();

            var inspectorControls = el( InspectorControls, {},
                el( PanelBody, { title: 'Einstellungen', initialOpen: true },
                    el( SelectControl, {
                        label: 'Abteilung',
                        value: attributes.slug,
                        options: options,
                        onChange: function ( value ) {
                            setAttributes( { slug: value } );
                        },
                    } ),
                    el( RangeControl, {
                        label: 'Limit',
                        help: '0 = alle anzeigen',
                        value: attributes.limit,
                        onChange: function ( value ) {
                            setAttributes( { limit: value } );
                        },
                        min: 0,
                        max: 100,
                        allowReset: true,
                        resetFallbackValue: 0,
                    } )
                )
            );

            if ( ! attributes.slug ) {
                return el( 'div', blockProps,
                    inspectorControls,
                    el( Placeholder, {
                        icon: icon,
                        label: 'Abteilung',
                        instructions: 'Wähle eine Abteilung aus.',
                    },
                        el( SelectControl, {
                            value: attributes.slug,
                            options: options,
                            onChange: function ( value ) {
                                setAttributes( { slug: value } );
                            },
                        } )
                    )
                );
            }

            var limitLabel = attributes.limit > 0 ? 'Limit: ' + attributes.limit : '';

            return el( 'div', blockProps,
                inspectorControls,
                el( 'div', { className: 'gk-block-abteilung-bar' },
                    el( 'svg', { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', width: 18, height: 18 },
                        el( 'path', { d: 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-6 8c0-2.67 5.33-4 6-4s6 1.33 6 4v1H6v-1z' } )
                    ),
                    el( 'strong', {}, labelForSlug( attributes.slug ) ),
                    limitLabel ? el( 'span', {}, limitLabel ) : null
                ),
                el( 'div', { className: 'gk-block-abteilung-wrap' },
                    el( ServerSideRender, {
                        block: 'gk/abteilung',
                        attributes: attributes,
                    } )
                )
            );
        },

        save: function () {
            return null;
        },
    } );
} )();
