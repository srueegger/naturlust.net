<?php
/**
 * Title: Suche (Lupen-Icon + Overlay)
 * Slug: naturlust/search
 * Description: Lupen-Anker oben rechts im Header. Beim Klick öffnet sich ein
 *              Vollbild-Overlay mit einem Suchfeld. Die Suche läuft über die
 *              normale WordPress-Suche (/?s=…) und damit – sofern aktiv – über
 *              Relevanssi. Steuerung in assets/js/search.js.
 * Categories: featured
 * Keywords: naturlust, suche, search, lupe, overlay
 */

?>
<!-- wp:html -->
<div class="naturlust-search" data-naturlust-search>
	<button type="button" class="naturlust-search__toggle" aria-expanded="false" aria-controls="naturlust-search-overlay" aria-label="<?php esc_attr_e( 'Suche öffnen', 'naturlust' ); ?>">
		<svg viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false">
			<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" fill="none" />
			<path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
		</svg>
	</button>

	<div class="naturlust-search__overlay" id="naturlust-search-overlay" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Suche', 'naturlust' ); ?>" hidden>
		<button type="button" class="naturlust-search__close" aria-label="<?php esc_attr_e( 'Suche schließen', 'naturlust' ); ?>">
			<svg viewBox="0 0 24 24" width="32" height="32" aria-hidden="true" focusable="false">
				<path d="M5 5l14 14M19 5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
			</svg>
		</button>

		<form role="search" method="get" class="naturlust-search__form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="naturlust-search__label" for="naturlust-search-field"><?php esc_html_e( 'Blog durchsuchen', 'naturlust' ); ?></label>
			<div class="naturlust-search__field-wrap">
				<input
					type="search"
					id="naturlust-search-field"
					class="naturlust-search__field"
					name="s"
					placeholder="<?php esc_attr_e( 'Suchbegriff …', 'naturlust' ); ?>"
					autocomplete="off"
				/>
				<button type="submit" class="naturlust-search__submit" aria-label="<?php esc_attr_e( 'Suchen', 'naturlust' ); ?>">
					<svg viewBox="0 0 24 24" width="26" height="26" aria-hidden="true" focusable="false">
						<circle cx="11" cy="11" r="7" stroke="currentColor" stroke-width="2" fill="none" />
						<path d="M16.5 16.5L21 21" stroke="currentColor" stroke-width="2" stroke-linecap="round" fill="none" />
					</svg>
				</button>
			</div>
		</form>
	</div>
</div>
<!-- /wp:html -->
