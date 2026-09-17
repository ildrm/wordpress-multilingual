( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';
	var el = element.createElement;
	blocks.registerBlockType( 'multilingual-core/language-switcher', {
		edit: function ( props ) {
			return el(
				element.Fragment,
				null,
				el(
					blockEditor.InspectorControls,
					null,
					el(
						components.PanelBody,
						{ title: i18n.__( 'Display', 'multilingual-core' ) },
						el( components.ToggleControl, {
							label: i18n.__( 'Hide current language', 'multilingual-core' ),
							checked: props.attributes.hideCurrent,
							onChange: function ( value ) { props.setAttributes( { hideCurrent: value } ); }
						} ),
						el( components.ToggleControl, {
							label: i18n.__( 'Hide unavailable translations', 'multilingual-core' ),
							checked: props.attributes.hideMissing,
							onChange: function ( value ) { props.setAttributes( { hideMissing: value } ); }
						} )
					)
				),
				el( components.Placeholder, {
					icon: 'translation',
					label: i18n.__( 'Language Switcher', 'multilingual-core' ),
					instructions: i18n.__( 'Available languages are rendered on the front end.', 'multilingual-core' )
				} )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );

