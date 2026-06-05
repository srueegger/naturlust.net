<?php
/**
 * Title: Hamburger-Menü
 * Slug: naturlust/hamburger
 * Description: Hamburger-Anker oben links mit Vollbild-Overlay. Rendert das
 *              klassische WordPress-Menü der Position „primary" (pflegbar
 *              unter Design → Menüs).
 * Categories: featured
 * Keywords: naturlust, menü, navigation, hamburger
 */

// Hat die Position „primary" überhaupt ein Menü? Sonst nichts ausgeben.
if ( ! has_nav_menu( 'primary' ) ) {
	return;
}
?>
<!-- wp:html -->
<div class="naturlust-nav" data-naturlust-nav>
	<button type="button" class="naturlust-nav__toggle" aria-expanded="false" aria-controls="naturlust-nav-overlay" aria-label="<?php esc_attr_e( 'Menü öffnen', 'naturlust' ); ?>">
		<svg viewBox="0 0 24 24" width="32" height="32" aria-hidden="true" focusable="false">
			<path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
		</svg>
	</button>

	<div class="naturlust-nav__overlay" id="naturlust-nav-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Hauptmenü', 'naturlust' ); ?>" hidden>
		<button type="button" class="naturlust-nav__close" aria-label="<?php esc_attr_e( 'Menü schließen', 'naturlust' ); ?>">
			<svg viewBox="0 0 24 24" width="32" height="32" aria-hidden="true" focusable="false">
				<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
			</svg>
		</button>

		<nav class="naturlust-nav__menu" aria-label="<?php esc_attr_e( 'Hauptmenü', 'naturlust' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'naturlust-nav__list',
					'depth'          => 0,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</div>
</div>
<!-- /wp:html -->
