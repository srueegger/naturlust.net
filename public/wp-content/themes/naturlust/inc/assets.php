<?php
/**
 * Front- und Editor-Assets.
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		$style_path = NATURLUST_DIR . 'assets/css/theme.css';
		$style_uri  = NATURLUST_URI . 'assets/css/theme.css';

		if ( file_exists( $style_path ) ) {
			wp_enqueue_style(
				'naturlust-theme',
				$style_uri,
				array(),
				filemtime( $style_path )
			);
		}

		// Hamburger-Overlay-Steuerung (Pattern naturlust/hamburger).
		$script_path = NATURLUST_DIR . 'assets/js/hamburger.js';
		$script_uri  = NATURLUST_URI . 'assets/js/hamburger.js';

		if ( file_exists( $script_path ) ) {
			wp_enqueue_script(
				'naturlust-hamburger',
				$script_uri,
				array(),
				filemtime( $script_path ),
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}
	}
);

add_action(
	'after_setup_theme',
	static function (): void {
		$style_path = NATURLUST_DIR . 'assets/css/editor.css';
		if ( file_exists( $style_path ) ) {
			add_editor_style( 'assets/css/editor.css' );
		}
	}
);
