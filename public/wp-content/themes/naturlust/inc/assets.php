<?php
/**
 * Front- und Editor-Assets.
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Liefert einen Versions-String aus der Datei-Änderungszeit als
 * Datum/Uhrzeit (z. B. „20260605.143022"). Da sich der Wert bei jeder
 * Änderung einer Datei ändert, lädt der Browser CSS/JS automatisch neu –
 * man muss also keine Caches manuell leeren. Fehlt die Datei, dient die
 * Theme-Version als Rückfallwert.
 *
 * @param string $path Absoluter Pfad zur Asset-Datei.
 * @return string
 */
function naturlust_asset_version( string $path ): string {
	$mtime = file_exists( $path ) ? filemtime( $path ) : false;

	return false !== $mtime ? gmdate( 'Ymd.His', $mtime ) : NATURLUST_VERSION;
}

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
				naturlust_asset_version( $style_path )
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
				naturlust_asset_version( $script_path ),
				array(
					'strategy'  => 'defer',
					'in_footer' => true,
				)
			);
		}

		// „Nach oben"-Button.
		$top_path = NATURLUST_DIR . 'assets/js/back-to-top.js';
		$top_uri  = NATURLUST_URI . 'assets/js/back-to-top.js';

		if ( file_exists( $top_path ) ) {
			wp_enqueue_script(
				'naturlust-back-to-top',
				$top_uri,
				array(),
				naturlust_asset_version( $top_path ),
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
