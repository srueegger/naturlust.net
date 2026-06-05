/**
 * Editor-Vorschau des Tagebuch-Sliders über ServerSideRender.
 * Reines JavaScript gegen die globalen wp.*-Pakete (kein Build-Step).
 */
( function ( wp ) {
	'use strict';

	if ( ! wp || ! wp.blocks ) {
		return;
	}

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'naturlust/tagebuch-slider', {
		edit: function ( props ) {
			return el(
				'div',
				useBlockProps(),
				el( ServerSideRender, {
					block: 'naturlust/tagebuch-slider',
					attributes: props.attributes
				} )
			);
		},
		save: function () {
			return null;
		}
	} );
} )( window.wp );
