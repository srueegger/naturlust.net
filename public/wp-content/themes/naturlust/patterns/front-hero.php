<?php
/**
 * Title: Startseite Hero
 * Slug: naturlust/front-hero
 * Description: Großes Hero-Bild für die Startseite im Skizzen-Stil.
 * Categories: featured
 * Keywords: naturlust, hero, startseite
 * Viewport Width: 1200
 */

$naturlust_hero = esc_url( get_stylesheet_directory_uri() . '/assets/images/brand/hero-default.jpg' );
?>
<!-- wp:image {"sizeSlug":"full","linkDestination":"none","align":"wide","style":{"border":{"radius":"16px"}}} -->
<figure class="wp-block-image alignwide size-full has-custom-border">
	<img src="<?php echo esc_url( $naturlust_hero ); ?>" alt="<?php esc_attr_e( 'Skizze eines vereisten Bergsees mit verschneiten Gipfeln', 'naturlust' ); ?>" style="border-radius:16px" />
</figure>
<!-- /wp:image -->
