<?php
/**
 * Plugin Name:       Naturlust Importer
 * Description:       Importiert Beiträge, Seiten, Kategorien, Tags und Medien aus einer entfernten WordPress-Installation (Standard: https://naturlust.net) per REST-API. Idempotent über Origin-IDs und Slugs.
 * Version:           0.1.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Samuel Rüegger
 * Author URI:        https://rueegger.dev
 * Text Domain:       naturlust-importer
 *
 * @package NaturlustImporter
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

define( 'NATURLUST_IMPORTER_VERSION', '0.1.0' );
define( 'NATURLUST_IMPORTER_DIR', plugin_dir_path( __FILE__ ) );
define( 'NATURLUST_IMPORTER_DEFAULT_SOURCE', 'https://naturlust.net' );

require_once NATURLUST_IMPORTER_DIR . 'includes/class-importer.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once NATURLUST_IMPORTER_DIR . 'includes/class-cli.php';
	WP_CLI::add_command( 'naturlust', Naturlust_Importer\CLI::class );
}
