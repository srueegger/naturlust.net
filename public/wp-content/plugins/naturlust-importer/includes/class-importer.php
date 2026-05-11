<?php
/**
 * Kernlogik des Naturlust-Importers.
 *
 * @package NaturlustImporter
 */

declare( strict_types=1 );

namespace Naturlust_Importer;

defined( 'ABSPATH' ) || exit;

/**
 * Importer-Service: holt Inhalte einer entfernten WordPress-Site per
 * REST-API und legt sie lokal an.
 */
class Importer {

	public const ORIGIN_META_KEY = '_naturlust_origin_id';
	public const ORIGIN_URL_META = '_naturlust_origin_url';

	/**
	 * Basis-URL der Quell-Site, ohne abschliessenden Slash.
	 */
	private string $source;

	/**
	 * Mapping: entfernte Term-ID → lokale Term-ID, pro Taxonomie.
	 *
	 * @var array<string, array<int,int>>
	 */
	private array $term_map = array(
		'category' => array(),
		'post_tag' => array(),
	);

	/**
	 * Mapping: entfernte Attachment-ID → lokale Attachment-ID.
	 *
	 * @var array<int,int>
	 */
	private array $media_map = array();

	/**
	 * Mapping: entfernte Media-URL → lokale Media-URL.
	 *
	 * @var array<string,string>
	 */
	private array $media_url_map = array();

	/**
	 * Logger-Callback (z. B. WP-CLI::log).
	 *
	 * @var callable
	 */
	private $logger;

	public function __construct( string $source = NATURLUST_IMPORTER_DEFAULT_SOURCE, ?callable $logger = null ) {
		$this->source = untrailingslashit( $source );
		$this->logger = $logger ?? static function ( string $msg ): void {
			error_log( '[naturlust-importer] ' . $msg ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		};

		$this->require_wp_filesystem();
		$this->prepare_runtime();
	}

	/**
	 * Speicherprofil des langen Imports niedrig halten:
	 *   - Object-Cache nicht weiter befüllen
	 *   - WP_IMPORTING aktivieren (überspringt z. B. einige
	 *     Slow-Queries und Pings)
	 *   - lastPostModified / Sitemap-Updates pausieren
	 */
	private function prepare_runtime(): void {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}
		wp_suspend_cache_addition( true );
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );
	}

	/**
	 * Speicher zwischen Items wieder freigeben.
	 */
	private function release_memory(): void {
		// WP behält interne Listen aller geladenen Posts/Terms.
		wp_cache_flush_runtime();
		if ( function_exists( 'gc_collect_cycles' ) ) {
			gc_collect_cycles();
		}
	}

	/**
	 * Bootstrap-Includes, die im CLI-Kontext nicht automatisch geladen werden.
	 */
	private function require_wp_filesystem(): void {
		if ( ! function_exists( 'download_url' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_read_image_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'media_handle_sideload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
	}

	/* ----------------------------------------------------------- */
	/*  REST-Helper                                                */
	/* ----------------------------------------------------------- */

	/**
	 * Holt eine REST-Ressource und folgt der Pagination automatisch.
	 *
	 * @param string               $route Pfad relativ zu /wp-json/wp/v2/.
	 * @param array<string, mixed> $args  Query-Parameter.
	 * @return array<int, array<string, mixed>>
	 */
	private function fetch_all( string $route, array $args = array() ): array {
		$results  = array();
		$per_page = 100;
		$page     = 1;

		do {
			$query_args = array_merge(
				$args,
				array(
					'per_page' => $per_page,
					'page'     => $page,
				)
			);
			$url = add_query_arg(
				$query_args,
				$this->source . '/wp-json/wp/v2/' . ltrim( $route, '/' )
			);

			$response = wp_remote_get(
				$url,
				array(
					'timeout' => 30,
					'headers' => array( 'Accept' => 'application/json' ),
				)
			);

			if ( is_wp_error( $response ) ) {
				$this->log( sprintf( 'Fehler beim Abruf von %s: %s', $url, $response->get_error_message() ) );
				break;
			}

			$code = (int) wp_remote_retrieve_response_code( $response );
			if ( 200 !== $code ) {
				if ( 400 === $code && $page > 1 ) {
					// REST-API antwortet 400, wenn man über die letzte Seite hinausfragt.
					break;
				}
				$this->log( sprintf( 'HTTP %d bei %s', $code, $url ) );
				break;
			}

			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( ! is_array( $body ) || empty( $body ) ) {
				break;
			}

			$results = array_merge( $results, $body );

			$total_pages = (int) wp_remote_retrieve_header( $response, 'x-wp-totalpages' );
			if ( $total_pages > 0 && $page >= $total_pages ) {
				break;
			}

			++$page;
		} while ( true );

		return $results;
	}

	/* ----------------------------------------------------------- */
	/*  Public API                                                 */
	/* ----------------------------------------------------------- */

	public function import_all(): array {
		$stats = array();
		$stats['terms']  = $this->import_terms();
		$stats['media']  = $this->import_media();
		$stats['posts']  = $this->import_posts();
		$stats['pages']  = $this->import_pages();

		return $stats;
	}

	/* ----------------------------------------------------------- */
	/*  Terms (Categories + Tags)                                  */
	/* ----------------------------------------------------------- */

	public function import_terms(): array {
		$stats = array(
			'category' => array( 'created' => 0, 'updated' => 0 ),
			'post_tag' => array( 'created' => 0, 'updated' => 0 ),
		);

		foreach ( array( 'categories' => 'category', 'tags' => 'post_tag' ) as $remote => $taxonomy ) {
			$items = $this->fetch_all( $remote );
			$this->log( sprintf( 'REST: %d %s gefunden.', count( $items ), $remote ) );

			foreach ( $items as $item ) {
				$result = $this->upsert_term( $item, $taxonomy );
				if ( null === $result ) {
					continue;
				}
				$this->term_map[ $taxonomy ][ (int) $item['id'] ] = (int) $result['term_id'];
				++$stats[ $taxonomy ][ $result['created'] ? 'created' : 'updated' ];
			}
		}

		// Eltern-Beziehungen nachpflegen (Kategorien können hierarchisch sein).
		foreach ( $this->fetch_all( 'categories' ) as $remote_cat ) {
			$parent_remote = (int) ( $remote_cat['parent'] ?? 0 );
			$local_id      = $this->term_map['category'][ (int) $remote_cat['id'] ] ?? 0;
			if ( $parent_remote && $local_id ) {
				$local_parent = $this->term_map['category'][ $parent_remote ] ?? 0;
				if ( $local_parent ) {
					wp_update_term( $local_id, 'category', array( 'parent' => $local_parent ) );
				}
			}
		}

		return $stats;
	}

	/**
	 * @return array{term_id:int, created:bool}|null
	 */
	private function upsert_term( array $item, string $taxonomy ): ?array {
		$name = wp_strip_all_tags( (string) ( $item['name'] ?? '' ) );
		$slug = sanitize_title( (string) ( $item['slug'] ?? $name ) );
		if ( '' === $slug ) {
			return null;
		}

		$existing = get_term_by( 'slug', $slug, $taxonomy );
		if ( $existing instanceof \WP_Term ) {
			wp_update_term(
				$existing->term_id,
				$taxonomy,
				array(
					'name'        => $name,
					'description' => (string) ( $item['description'] ?? '' ),
				)
			);
			update_term_meta( $existing->term_id, self::ORIGIN_META_KEY, (int) $item['id'] );
			return array( 'term_id' => $existing->term_id, 'created' => false );
		}

		$inserted = wp_insert_term(
			$name,
			$taxonomy,
			array(
				'slug'        => $slug,
				'description' => (string) ( $item['description'] ?? '' ),
			)
		);

		if ( is_wp_error( $inserted ) ) {
			$this->log( sprintf( 'Term-Insert fehlgeschlagen (%s/%s): %s', $taxonomy, $slug, $inserted->get_error_message() ) );
			return null;
		}

		update_term_meta( (int) $inserted['term_id'], self::ORIGIN_META_KEY, (int) $item['id'] );
		return array( 'term_id' => (int) $inserted['term_id'], 'created' => true );
	}

	/* ----------------------------------------------------------- */
	/*  Media                                                      */
	/* ----------------------------------------------------------- */

	public function import_media(): array {
		$stats = array( 'created' => 0, 'skipped' => 0, 'failed' => 0 );
		$items = $this->fetch_all( 'media' );
		$this->log( sprintf( 'REST: %d Medieneinträge gefunden.', count( $items ) ) );

		foreach ( $items as $item ) {
			$origin_id  = (int) ( $item['id'] ?? 0 );
			$source_url = (string) ( $item['source_url'] ?? '' );
			if ( ! $origin_id || ! $source_url ) {
				continue;
			}

			$existing = $this->find_by_origin( $origin_id, 'attachment' );
			if ( $existing ) {
				$this->media_map[ $origin_id ]          = $existing;
				$this->media_url_map[ $source_url ]     = (string) wp_get_attachment_url( $existing );
				++$stats['skipped'];
				continue;
			}

			$attachment_id = $this->sideload_attachment( $item );
			if ( $attachment_id ) {
				$this->media_map[ $origin_id ]      = $attachment_id;
				$this->media_url_map[ $source_url ] = (string) wp_get_attachment_url( $attachment_id );
				++$stats['created'];
			} else {
				++$stats['failed'];
			}

			// Pro Item Speicher freigeben.
			unset( $item );
			$this->release_memory();
		}

		return $stats;
	}

	private function sideload_attachment( array $item ): ?int {
		$source_url = (string) $item['source_url'];
		$tmp        = download_url( $source_url );
		if ( is_wp_error( $tmp ) ) {
			$this->log( sprintf( 'Download fehlgeschlagen (%s): %s', $source_url, $tmp->get_error_message() ) );
			return null;
		}

		$file_array = array(
			'name'     => basename( wp_parse_url( $source_url, PHP_URL_PATH ) ?? 'datei' ),
			'tmp_name' => $tmp,
		);

		$post_data = array(
			'post_title'   => wp_strip_all_tags( (string) ( $item['title']['rendered'] ?? $file_array['name'] ) ),
			'post_content' => (string) ( $item['description']['rendered'] ?? '' ),
			'post_excerpt' => (string) ( $item['caption']['rendered'] ?? '' ),
			'post_date'    => (string) ( $item['date'] ?? '' ),
		);

		$attachment_id = media_handle_sideload( $file_array, 0, null, $post_data );

		if ( is_wp_error( $attachment_id ) ) {
			if ( file_exists( $tmp ) ) {
				wp_delete_file( $tmp );
			}
			$this->log( sprintf( 'Sideload fehlgeschlagen (%s): %s', $source_url, $attachment_id->get_error_message() ) );
			return null;
		}

		$alt_text = (string) ( $item['alt_text'] ?? '' );
		if ( '' !== $alt_text ) {
			update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
		}

		update_post_meta( $attachment_id, self::ORIGIN_META_KEY, (int) $item['id'] );
		update_post_meta( $attachment_id, self::ORIGIN_URL_META, $source_url );

		return (int) $attachment_id;
	}

	/* ----------------------------------------------------------- */
	/*  Posts                                                      */
	/* ----------------------------------------------------------- */

	public function import_posts(): array {
		return $this->import_post_objects( 'posts', 'post' );
	}

	public function import_pages(): array {
		return $this->import_post_objects( 'pages', 'page' );
	}

	private function import_post_objects( string $rest_route, string $post_type ): array {
		$stats = array( 'created' => 0, 'updated' => 0, 'failed' => 0 );
		$items = $this->fetch_all( $rest_route );
		$this->log( sprintf( 'REST: %d %s gefunden.', count( $items ), $rest_route ) );

		foreach ( $items as $item ) {
			$origin_id = (int) ( $item['id'] ?? 0 );
			if ( ! $origin_id ) {
				continue;
			}

			$content = $this->rewrite_media_urls( (string) ( $item['content']['rendered'] ?? '' ) );
			$excerpt = $this->rewrite_media_urls( (string) ( $item['excerpt']['rendered'] ?? '' ) );

			$postarr = array(
				'post_type'    => $post_type,
				'post_status'  => 'publish',
				'post_title'   => wp_strip_all_tags( (string) ( $item['title']['rendered'] ?? '' ) ),
				'post_name'    => sanitize_title( (string) ( $item['slug'] ?? '' ) ),
				'post_content' => $content,
				'post_excerpt' => $excerpt,
				'post_date'    => (string) ( $item['date'] ?? '' ),
				'post_date_gmt'=> (string) ( $item['date_gmt'] ?? '' ),
				'meta_input'   => array(
					self::ORIGIN_META_KEY => $origin_id,
				),
			);

			$existing_id = $this->find_by_origin( $origin_id, $post_type );
			if ( $existing_id ) {
				$postarr['ID'] = $existing_id;
				$result        = wp_update_post( $postarr, true );
				$created       = false;
			} else {
				$result  = wp_insert_post( $postarr, true );
				$created = true;
			}

			if ( is_wp_error( $result ) ) {
				$this->log( sprintf( 'Post-Insert/Update fehlgeschlagen (%s #%d): %s', $post_type, $origin_id, $result->get_error_message() ) );
				++$stats['failed'];
				continue;
			}

			$post_id = (int) $result;

			$this->assign_terms( $post_id, $item );
			$this->assign_featured_image( $post_id, $item );

			++$stats[ $created ? 'created' : 'updated' ];

			unset( $item, $content, $excerpt, $postarr, $result );
			$this->release_memory();
		}

		return $stats;
	}

	private function assign_terms( int $post_id, array $item ): void {
		foreach ( array( 'category' => 'categories', 'post_tag' => 'tags' ) as $taxonomy => $field ) {
			$remote_ids = (array) ( $item[ $field ] ?? array() );
			$local_ids  = array();
			foreach ( $remote_ids as $rid ) {
				$rid = (int) $rid;
				if ( isset( $this->term_map[ $taxonomy ][ $rid ] ) ) {
					$local_ids[] = $this->term_map[ $taxonomy ][ $rid ];
				}
			}
			if ( $local_ids ) {
				wp_set_post_terms( $post_id, $local_ids, $taxonomy, false );
			}
		}
	}

	private function assign_featured_image( int $post_id, array $item ): void {
		$origin_media = (int) ( $item['featured_media'] ?? 0 );
		if ( ! $origin_media ) {
			return;
		}
		$local_id = $this->media_map[ $origin_media ] ?? 0;
		if ( ! $local_id ) {
			return;
		}
		set_post_thumbnail( $post_id, $local_id );
	}

	/* ----------------------------------------------------------- */
	/*  Utilities                                                  */
	/* ----------------------------------------------------------- */

	private function rewrite_media_urls( string $html ): string {
		if ( empty( $this->media_url_map ) ) {
			$this->rebuild_media_url_map();
		}
		if ( empty( $this->media_url_map ) ) {
			return $html;
		}

		$search  = array_keys( $this->media_url_map );
		$replace = array_values( $this->media_url_map );
		return str_replace( $search, $replace, $html );
	}

	/**
	 * Wenn ein vorheriger Import ohne Media-Schritt lief, lassen sich
	 * die Mappings nachträglich aus der lokalen DB rekonstruieren.
	 */
	private function rebuild_media_url_map(): void {
		global $wpdb;
		$rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				self::ORIGIN_URL_META
			)
		);
		foreach ( (array) $rows as $row ) {
			$this->media_url_map[ (string) $row->meta_value ] = (string) wp_get_attachment_url( (int) $row->post_id );
		}

		$origin_rows = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->prepare(
				"SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
				self::ORIGIN_META_KEY
			)
		);
		foreach ( (array) $origin_rows as $row ) {
			$post = get_post( (int) $row->post_id );
			if ( $post && 'attachment' === $post->post_type ) {
				$this->media_map[ (int) $row->meta_value ] = (int) $row->post_id;
			}
		}
	}

	/**
	 * Sucht einen Post oder ein Attachment nach Origin-ID.
	 */
	private function find_by_origin( int $origin_id, string $post_type ): int {
		$query = new \WP_Query(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
				'meta_key'       => self::ORIGIN_META_KEY, // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'     => $origin_id,            // phpcs:ignore WordPress.DB.SlowDBQuery
			)
		);
		$ids = $query->posts;
		return $ids ? (int) $ids[0] : 0;
	}

	private function log( string $message ): void {
		( $this->logger )( $message );
	}
}
