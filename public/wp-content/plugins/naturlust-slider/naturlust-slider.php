<?php
/**
 * Plugin Name:       Naturlust Tagebuch-Slider
 * Description:       Gutenberg-Block: Slider mit den neuesten Beiträgen einer Kategorie (Standard: Tagebuch) – Beitragsbild als Slide, mit Titel und Datum, verlinkt auf den Beitrag.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Samuel Rüegger
 * Author URI:        https://rueegger.me
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       naturlust-slider
 *
 * Kontakt: samuel@rueegger.me
 *
 * @package NaturlustSlider
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Frontend-Assets registrieren und Block aus block.json anmelden.
 *
 * View-Script und Style werden mit filemtime versioniert (sauberes
 * Cache-Busting) und nur geladen, wenn der Block auf der Seite vorkommt.
 */
add_action(
	'init',
	static function (): void {
		$dir = __DIR__;

		wp_register_script(
			'naturlust-slider-view',
			plugins_url( 'view.js', __FILE__ ),
			array(),
			(string) filemtime( $dir . '/view.js' ),
			true
		);

		wp_register_style(
			'naturlust-slider-style',
			plugins_url( 'style.css', __FILE__ ),
			array(),
			(string) filemtime( $dir . '/style.css' )
		);

		register_block_type( $dir );
	}
);

/**
 * Editor-Komponente (Server-Side-Render-Vorschau). Bewusst ohne Build-Step:
 * reines JavaScript gegen die globalen `wp.*`-Pakete.
 */
add_action(
	'enqueue_block_editor_assets',
	static function (): void {
		$edit = __DIR__ . '/edit.js';
		if ( ! file_exists( $edit ) ) {
			return;
		}

		wp_enqueue_script(
			'naturlust-slider-edit',
			plugins_url( 'edit.js', __FILE__ ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
			(string) filemtime( $edit ),
			true
		);
	}
);
