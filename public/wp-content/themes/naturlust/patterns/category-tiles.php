<?php
/**
 * Title: Kategorie-Kacheln
 * Slug: naturlust/category-tiles
 * Description: Vier runde Kategorie-Buttons (Wandern, Radfahren, Fotografieren, Waldbaden) mit kleinem Logo in der Mitte.
 * Categories: featured
 * Keywords: naturlust, kategorien, startseite
 * Block Types: core/group
 * Viewport Width: 1200
 */

$naturlust_base = esc_url( get_stylesheet_directory_uri() . '/assets/images/categories' );

/* Kleines Mittellogo aus custom_logo holen. */
$naturlust_center_logo_id  = (int) get_theme_mod( 'custom_logo' );
$naturlust_center_logo_url = $naturlust_center_logo_id
	? wp_get_attachment_image_url( $naturlust_center_logo_id, 'thumbnail' )
	: '';

$naturlust_tiles = array(
	array(
		'slug'  => 'wandern',
		'label' => __( 'Wandern', 'naturlust' ),
		'href'  => esc_url( home_url( '/category/wandern/' ) ),
		'img'   => $naturlust_base . '/wandern.png',
		'alt'   => __( 'Skizze von zwei Wanderern in den Bergen', 'naturlust' ),
	),
	array(
		'slug'  => 'radfahren',
		'label' => __( 'Radfahren', 'naturlust' ),
		'href'  => esc_url( home_url( '/category/radfahren/' ) ),
		'img'   => $naturlust_base . '/radfahren.png',
		'alt'   => __( 'Skizze eines Radfahrers vor einer Berglandschaft', 'naturlust' ),
	),
	array(
		'slug'  => 'fotografieren',
		'label' => __( 'Fotografieren', 'naturlust' ),
		'href'  => esc_url( home_url( '/category/fotografieren/' ) ),
		'img'   => $naturlust_base . '/fotografieren.png',
		'alt'   => __( 'Skizze einer Person mit Kamera im Wald', 'naturlust' ),
	),
	array(
		'slug'  => 'waldbaden',
		'label' => __( 'Waldbaden', 'naturlust' ),
		'href'  => esc_url( home_url( '/category/waldbaden/' ) ),
		'img'   => $naturlust_base . '/waldbaden.png',
		'alt'   => __( 'Skizze einer Person beim Waldbaden zwischen Tannen', 'naturlust' ),
	),
);
?>
<!-- wp:html -->
<nav class="naturlust-category-tiles" aria-label="<?php esc_attr_e( 'Hauptkategorien', 'naturlust' ); ?>">
	<?php
	foreach ( $naturlust_tiles as $i => $tile ) :
		/* Mittellogo zwischen Kachel 2 (Index 1) und Kachel 3 (Index 2) einfügen. */
		if ( 2 === $i && $naturlust_center_logo_url ) :
			?>
			<span class="naturlust-category-tiles__center" aria-hidden="true">
				<img src="<?php echo esc_url( $naturlust_center_logo_url ); ?>" alt="" loading="lazy" decoding="async" width="80" height="80" />
			</span>
			<?php
		endif;
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
