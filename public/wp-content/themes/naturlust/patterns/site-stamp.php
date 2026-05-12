<?php
/**
 * Title: Naturlust-Stempel
 * Slug: naturlust/site-stamp
 * Description: Rundes Skizzen-Logo, das auf der Startseite links neben dem Hero-Bild als Stempel auf der Karte sitzt.
 * Categories: featured
 */

$naturlust_logo_id = (int) get_theme_mod( 'custom_logo' );
if ( ! $naturlust_logo_id ) {
	return;
}

$naturlust_logo_url = wp_get_attachment_image_url( $naturlust_logo_id, 'medium' );
if ( ! $naturlust_logo_url ) {
	return;
}

$naturlust_logo_alt = trim( (string) get_post_meta( $naturlust_logo_id, '_wp_attachment_image_alt', true ) );
if ( '' === $naturlust_logo_alt ) {
	$naturlust_logo_alt = get_bloginfo( 'name', 'display' );
}
?>
<!-- wp:html -->
<a class="naturlust-stamp" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( sprintf( __( 'Zur Startseite von %s', 'naturlust' ), get_bloginfo( 'name' ) ) ); ?>">
	<img src="<?php echo esc_url( $naturlust_logo_url ); ?>" alt="<?php echo esc_attr( $naturlust_logo_alt ); ?>" width="200" height="200" loading="eager" decoding="async" />
</a>
<!-- /wp:html -->
