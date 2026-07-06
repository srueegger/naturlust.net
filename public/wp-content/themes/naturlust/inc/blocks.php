<?php
/**
 * Eigene, serverseitig gerenderte Blöcke.
 *
 * Diese Blöcke liefern dynamische Inhalte, die sich pro Beitrag ändern
 * und deshalb nicht als statisches Pattern funktionieren:
 *
 *  - naturlust/post-nav       Vorheriger/nächster Beitrag (unten links/rechts).
 *  - naturlust/related-posts  „Ähnliche Beiträge" nach geteilten Kategorien/Tags.
 *
 * Beide werden im Single-Template (templates/single.html) eingebunden und
 * über assets/css/theme.css gestaltet (inkl. Dark Mode).
 *
 * @package Naturlust
 */

declare( strict_types=1 );

defined( 'ABSPATH' ) || exit;

add_action(
	'init',
	static function (): void {
		register_block_type(
			'naturlust/post-nav',
			array(
				'render_callback' => 'naturlust_render_post_nav',
				'supports'        => array( 'align' => array( 'wide' ) ),
			)
		);

		register_block_type(
			'naturlust/related-posts',
			array(
				'render_callback' => 'naturlust_render_related_posts',
				'supports'        => array( 'align' => array( 'wide' ) ),
			)
		);
	}
);

/**
 * Baut eine einzelne Prev/Next-Karte.
 *
 * @param WP_Post $post Ziel-Beitrag.
 * @param string  $type „prev" oder „next".
 * @return string
 */
function naturlust_post_nav_card( WP_Post $post, string $type ): string {
	$is_prev = ( 'prev' === $type );
	$label   = $is_prev ? __( 'Vorheriger Beitrag', 'naturlust' ) : __( 'Nächster Beitrag', 'naturlust' );
	$arrow   = $is_prev ? '←' : '→';
	$thumb   = get_the_post_thumbnail(
		$post,
		'thumbnail',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => '',
		)
	);

	$arrow_html = sprintf(
		'<span class="naturlust-postnav__arrow" aria-hidden="true">%s</span>',
		esc_html( $arrow )
	);

	$thumb_html = '' !== $thumb
		? '<span class="naturlust-postnav__image">' . $thumb . '</span>'
		: '';

	$body_html = sprintf(
		'<span class="naturlust-postnav__body"><span class="naturlust-postnav__label">%1$s</span><span class="naturlust-postnav__title">%2$s</span></span>',
		esc_html( $label ),
		esc_html( get_the_title( $post ) )
	);

	// Reihenfolge: prev = Pfeil, Bild, Text | next = Text, Bild, Pfeil.
	$inner = $is_prev
		? $arrow_html . $thumb_html . $body_html
		: $body_html . $thumb_html . $arrow_html;

	return sprintf(
		'<a class="naturlust-postnav__link naturlust-postnav__link--%1$s" href="%2$s" rel="%3$s">%4$s</a>',
		esc_attr( $type ),
		esc_url( (string) get_permalink( $post ) ),
		$is_prev ? 'prev' : 'next',
		$inner
	);
}

/**
 * Rendert die Beitragsnavigation (vorheriger/nächster Beitrag).
 *
 * @return string
 */
function naturlust_render_post_nav(): string {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$previous = get_previous_post();
	$next     = get_next_post();

	if ( ! ( $previous instanceof WP_Post ) && ! ( $next instanceof WP_Post ) ) {
		return '';
	}

	$prev_html = ( $previous instanceof WP_Post ) ? naturlust_post_nav_card( $previous, 'prev' ) : '';
	$next_html = ( $next instanceof WP_Post ) ? naturlust_post_nav_card( $next, 'next' ) : '';

	return sprintf(
		'<nav class="naturlust-postnav%1$s" aria-label="%2$s">%3$s%4$s</nav>',
		'' === $prev_html ? ' naturlust-postnav--next-only' : '',
		esc_attr__( 'Beitragsnavigation', 'naturlust' ),
		$prev_html,
		$next_html
	);
}

/**
 * Rendert eine Karte für die „Ähnliche Beiträge"-Liste.
 *
 * @param WP_Post $post Beitrag.
 * @return string
 */
function naturlust_related_card( WP_Post $post ): string {
	$image = get_the_post_thumbnail(
		$post,
		'naturlust-card',
		array(
			'loading'  => 'lazy',
			'decoding' => 'async',
			'alt'      => '',
		)
	);

	if ( '' === $image ) {
		$image = '<span class="naturlust-related__placeholder" aria-hidden="true"></span>';
	}

	return sprintf(
		'<li class="naturlust-related__item"><a class="naturlust-related__card" href="%1$s"><span class="naturlust-related__image">%2$s</span><span class="naturlust-related__name">%3$s</span><span class="naturlust-related__date">%4$s</span></a></li>',
		esc_url( (string) get_permalink( $post ) ),
		$image,
		esc_html( get_the_title( $post ) ),
		esc_html( (string) get_the_date( '', $post ) )
	);
}

/**
 * Rendert „Ähnliche Beiträge" – Beiträge, die sich mit dem aktuellen
 * Beitrag Kategorien oder Schlagwörter teilen. Reicht die Zahl nicht,
 * wird mit den neuesten Beiträgen aufgefüllt.
 *
 * @return string
 */
function naturlust_render_related_posts(): string {
	if ( ! is_singular( 'post' ) ) {
		return '';
	}

	$current_id = get_the_ID();
	$limit      = 3;

	$category_ids = wp_get_post_categories( $current_id );
	$tag_ids      = wp_get_post_tags( $current_id, array( 'fields' => 'ids' ) );

	$tax_query = array( 'relation' => 'OR' );
	if ( ! empty( $category_ids ) ) {
		$tax_query[] = array(
			'taxonomy' => 'category',
			'terms'    => $category_ids,
		);
	}
	if ( ! empty( $tag_ids ) ) {
		$tax_query[] = array(
			'taxonomy' => 'post_tag',
			'terms'    => $tag_ids,
		);
	}

	$found = array();

	if ( count( $tax_query ) > 1 ) {
		$related = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $limit,
				'post__not_in'        => array( $current_id ),
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
				'tax_query'           => $tax_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			)
		);
		$found = $related;
	}

	// Auffüllen mit neuesten Beiträgen, falls zu wenige thematisch passen.
	if ( count( $found ) < $limit ) {
		$exclude = array_merge(
			array( $current_id ),
			array_map(
				static fn( WP_Post $p ): int => $p->ID,
				$found
			)
		);

		$fill = get_posts(
			array(
				'post_type'           => 'post',
				'posts_per_page'      => $limit - count( $found ),
				'post__not_in'        => $exclude,
				'ignore_sticky_posts' => true,
				'no_found_rows'       => true,
			)
		);

		$found = array_merge( $found, $fill );
	}

	if ( empty( $found ) ) {
		return '';
	}

	$cards = '';
	foreach ( $found as $post ) {
		$cards .= naturlust_related_card( $post );
	}

	return sprintf(
		'<section class="naturlust-related" aria-labelledby="naturlust-related-title"><h2 id="naturlust-related-title" class="naturlust-related__title">%1$s</h2><ul class="naturlust-related__grid">%2$s</ul></section>',
		esc_html__( 'Ähnliche Beiträge', 'naturlust' ),
		$cards
	);
}
