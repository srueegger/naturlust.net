<?php
/**
 * Eigene Shortcodes.
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

/**
 * Gibt das aktuelle Jahr aus. Wird im Footer verwendet, damit das
 * Copyright-Datum automatisch mitwächst.
 */
add_shortcode(
	'naturlust_year',
	static function (): string {
		return esc_html( wp_date( 'Y' ) );
	}
);
