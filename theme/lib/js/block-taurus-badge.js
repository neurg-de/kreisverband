( function ( blocks, element, blockEditor, components, i18n ) {
	var el          = element.createElement;
	var Fragment    = element.Fragment;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody   = components.PanelBody;
	var TextControl = components.TextControl;
	var SelectControl = components.SelectControl;
	var __ = i18n.__;

	blocks.registerBlockType( 'neurg/taurus-badge', {
		title: 'The Taurus Badge',
		icon: 'shield',
		category: 'widgets',
		description: __( 'Compliance-Badge oder Registrierungs-CTA von The Taurus.', 'neurg-kreisverband' ),
		attributes: {
			slug: { type: 'string', default: '' },
			lang: { type: 'string', default: 'auto' }
		},
		edit: function ( props ) {
			var slug = props.attributes.slug;
			var lang = props.attributes.lang;

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: 'The Taurus', initialOpen: true },
						el( TextControl, {
							label: __( 'Profil-Slug', 'neurg-kreisverband' ),
							help: __( 'Organisations-Slug auf thetaurus.com. Leer = Customizer-Wert.', 'neurg-kreisverband' ),
							value: slug,
							onChange: function ( val ) { props.setAttributes( { slug: val } ); },
							placeholder: 'gruene-kv-freiburg'
						} ),
						el( SelectControl, {
							label: __( 'Sprache', 'neurg-kreisverband' ),
							value: lang,
							options: [
								{ label: __( 'Automatisch', 'neurg-kreisverband' ), value: 'auto' },
								{ label: 'Deutsch', value: 'de' },
								{ label: 'English', value: 'en' }
							],
							onChange: function ( val ) { props.setAttributes( { lang: val } ); }
						} )
					)
				),
				el(
					'div',
					{ className: 'neurg-taurus-badge-placeholder', style: { padding: '16px', border: '1px dashed #ccc', textAlign: 'center', background: '#f9f9f9' } },
					el( 'strong', null, 'The Taurus Compliance Badge' ),
					el( 'br' ),
					el( 'small', null, slug ? ( 'Slug: ' + slug ) : __( 'Kein Slug — zeigt Registrierungs-CTA', 'neurg-kreisverband' ) )
				)
			);
		},
		save: function () {
			// Server-side rendered
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.wp.components, window.wp.i18n );
