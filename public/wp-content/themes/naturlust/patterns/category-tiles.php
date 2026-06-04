<?php
/**
 * Title: Kategorie-Kacheln
 * Slug: naturlust/category-tiles
 * Description: Vier runde Kategorie-Buttons (Wandern, Radfahren, Fotografieren, Waldbaden).
 * Categories: featured
 * Keywords: naturlust, kategorien, startseite
 * Block Types: core/group
 * Viewport Width: 1200
 */

$naturlust_base = esc_url( get_stylesheet_directory_uri() . '/assets/images/categories' );

/**
 * Liefert das Kategorie-Archiv anhand des Slugs. Die Live-Site nutzt die
 * Permalink-Struktur /%category%/%postname%/, das Archiv liegt also unter
 * /<slug>/ (ohne /category/-Präfix). Fehlt der Begriff, zeigt der Link
 * sicherheitshalber auf die Startseite.
 *
 * @param string $slug Kategorie-Slug.
 * @return string
 */
$naturlust_term_url = static function ( string $slug ): string {
	$link = get_term_link( $slug, 'category' );

	return is_wp_error( $link ) ? home_url( '/' ) : $link;
};

$naturlust_tiles = array(
	array(
		'slug'  => 'wandern',
		'label' => __( 'Wandern', 'naturlust' ),
		'href'  => esc_url( $naturlust_term_url( 'wandern' ) ),
		'img'   => $naturlust_base . '/wandern.png',
		'alt'   => __( 'Skizze von zwei Wanderern in den Bergen', 'naturlust' ),
	),
	array(
		'slug'  => 'radfahren',
		'label' => __( 'Radfahren', 'naturlust' ),
		'href'  => esc_url( $naturlust_term_url( 'radfahren' ) ),
		'img'   => $naturlust_base . '/radfahren.png',
		'alt'   => __( 'Skizze eines Radfahrers vor einer Berglandschaft', 'naturlust' ),
	),
	array(
		'slug'  => 'naturfotografie',
		'label' => __( 'Fotografieren', 'naturlust' ),
		'href'  => esc_url( $naturlust_term_url( 'naturfotografie' ) ),
		'img'   => $naturlust_base . '/fotografieren.png',
		'alt'   => __( 'Skizze einer Person mit Kamera im Wald', 'naturlust' ),
	),
	array(
		'slug'  => 'waldbaden',
		'label' => __( 'Waldbaden', 'naturlust' ),
		'href'  => esc_url( $naturlust_term_url( 'waldbaden' ) ),
		'img'   => $naturlust_base . '/waldbaden.png',
		'alt'   => __( 'Skizze einer Person beim Waldbaden zwischen Tannen', 'naturlust' ),
	),
);
?>
<!-- wp:html -->
<nav class="naturlust-category-tiles" aria-label="<?php esc_attr_e( 'Hauptkategorien', 'naturlust' ); ?>">
	<?php
	foreach ( $naturlust_tiles as $tile ) :
		?>
		<a class="naturlust-category-tiles__item" href="<?php echo esc_url( $tile['href'] ); ?>">
			<span class="naturlust-category-tiles__image">
				<img src="<?php echo esc_url( $tile['img'] ); ?>" alt="<?php echo esc_attr( $tile['alt'] ); ?>" loading="lazy" decoding="async" width="600" height="600" />
			</span>
			<span class="naturlust-category-tiles__label"><?php echo esc_html( $tile['label'] ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>
<!-- /wp:html -->
