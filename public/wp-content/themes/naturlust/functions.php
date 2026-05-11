<?php
/**
 * Naturlust – Theme-Setup.
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'NATURLUST_VERSION' ) ) {
	define( 'NATURLUST_VERSION', wp_get_theme()->get( 'Version' ) );
}

if ( ! defined( 'NATURLUST_DIR' ) ) {
	define( 'NATURLUST_DIR', trailingslashit( __DIR__ ) );
}

if ( ! defined( 'NATURLUST_URI' ) ) {
	define( 'NATURLUST_URI', trailingslashit( get_stylesheet_directory_uri() ) );
}

require_once NATURLUST_DIR . 'inc/setup.php';
require_once NATURLUST_DIR . 'inc/assets.php';
require_once NATURLUST_DIR . 'inc/shortcodes.php';
