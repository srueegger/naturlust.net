<?php
/**
 * Theme-Support, Menüs, Bildgrössen.
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	static function (): void {
		load_theme_textdomain( 'naturlust', NATURLUST_DIR . 'languages' );

		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'html5', array( 'comment-list', 'comment-form', 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support(
			'custom-logo',
			array(
				'height'               => 1000,
				'width'                => 1000,
				'flex-height'          => true,
				'flex-width'           => true,
				'unlink-homepage-logo' => false,
			)
		);

		add_image_size( 'naturlust-card', 960, 540, true );
		add_image_size( 'naturlust-hero', 1920, 1080, true );

		register_nav_menus(
			array(
				'primary'   => __( 'Hamburger-Menü', 'naturlust' ),
				'footer'    => __( 'Fussbereich', 'naturlust' ),
				'social'    => __( 'Soziale Netzwerke', 'naturlust' ),
			)
		);
	}
);

add_filter(
	'should_load_separate_core_block_assets',
	'__return_true'
);

/**
 * Block-Pattern-Cache im Entwicklungsmodus bei jedem Admin-Aufruf
 * leeren. WordPress cached die Pattern-Liste eines Themes pro
 * Stylesheet-Version – ohne diesen Hook müsste man nach jeder
 * Änderung an einer Datei in /patterns/ manuell flushen.
 */
add_action(
	'admin_init',
	static function (): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			wp_get_theme()->delete_pattern_cache();
		}
	}
);
