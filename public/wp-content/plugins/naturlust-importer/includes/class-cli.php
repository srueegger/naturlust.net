<?php
/**
 * WP-CLI-Kommandos für den Naturlust-Importer.
 *
 * @package NaturlustImporter
 */

declare( strict_types=1 );

namespace Naturlust_Importer;

defined( 'ABSPATH' ) || exit;

if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
	return;
}

/**
 * Spiegelt eine entfernte WordPress-Site lokal.
 *
 * ## EXAMPLES
 *
 *     # Komplett-Import von der Standardquelle (https://naturlust.net):
 *     wp naturlust import
 *
 *     # Nur Medien neu ziehen:
 *     wp naturlust import --only=media
 *
 *     # Von einer anderen Quelle ziehen:
 *     wp naturlust import --source=https://example.com
 */
class CLI {

	/**
	 * Importiert Terms, Medien, Beiträge und Seiten.
	 *
	 * ## OPTIONS
	 *
	 * [--source=<url>]
	 * : Basis-URL der Quell-Site.
	 * ---
	 * default: https://naturlust.net
	 * ---
	 *
	 * [--only=<scope>]
	 * : Begrenzt den Import auf einen Bereich.
	 * ---
	 * options:
	 *   - all
	 *   - terms
	 *   - media
	 *   - posts
	 *   - pages
	 * default: all
	 * ---
	 *
	 * @when after_wp_load
	 *
	 * @param array<int,string>    $args       Positional args (none).
	 * @param array<string,string> $assoc_args Flags.
	 */
	public function import( array $args, array $assoc_args ): void {
		$source = (string) ( $assoc_args['source'] ?? \NATURLUST_IMPORTER_DEFAULT_SOURCE );
		$only   = (string) ( $assoc_args['only']   ?? 'all' );

		\WP_CLI::log( "Quelle: $source" );
		\WP_CLI::log( "Bereich: $only" );

		$importer = new Importer( $source, static function ( string $msg ): void {
			\WP_CLI::log( $msg );
		} );

		$totals = array();

		if ( 'all' === $only || 'terms' === $only ) {
			\WP_CLI::log( '— Terms —' );
			$totals['terms'] = $importer->import_terms();
		}
		if ( 'all' === $only || 'media' === $only ) {
			\WP_CLI::log( '— Medien —' );
			$totals['media'] = $importer->import_media();
		}
		if ( 'all' === $only || 'posts' === $only ) {
			\WP_CLI::log( '— Beiträge —' );
			$totals['posts'] = $importer->import_posts();
		}
		if ( 'all' === $only || 'pages' === $only ) {
			\WP_CLI::log( '— Seiten —' );
			$totals['pages'] = $importer->import_pages();
		}

		\WP_CLI::log( '' );
		\WP_CLI::log( 'Zusammenfassung:' );
		\WP_CLI::log( wp_json_encode( $totals, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) );
		\WP_CLI::success( 'Import abgeschlossen.' );
	}

	/**
	 * Zeigt, was bereits importiert wurde.
	 *
	 * @when after_wp_load
	 */
	public function status(): void {
		global $wpdb;

		$counts = array(
			'attachments' => (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s", Importer::ORIGIN_META_KEY ) ), // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		);

		foreach ( array( 'post', 'page' ) as $type ) {
			$counts[ $type ] = (int) $wpdb->get_var( $wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->postmeta} pm INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id WHERE pm.meta_key = %s AND p.post_type = %s",
				Importer::ORIGIN_META_KEY,
				$type
			) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		}

		$terms = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->termmeta} WHERE meta_key = %s", Importer::ORIGIN_META_KEY ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		\WP_CLI::log( sprintf( 'Posts mit Origin-ID:        %d', $counts['post'] ) );
		\WP_CLI::log( sprintf( 'Pages mit Origin-ID:        %d', $counts['page'] ) );
		\WP_CLI::log( sprintf( 'Attachments mit Origin-ID:  %d', $counts['attachments'] - $counts['post'] - $counts['page'] ) );
		\WP_CLI::log( sprintf( 'Terms mit Origin-ID:        %d', $terms ) );
	}
}
