/**
 * Naturlust – „Nach oben"-Button.
 *
 * Zeigt den Button (fix unten rechts) ein, sobald der Nutzer etwa eine
 * Bildschirmhöhe weit nach unten gescrollt hat, und scrollt bei Klick
 * sanft zum Seitenanfang zurück. Respektiert prefers-reduced-motion.
 */
( function () {
	'use strict';

	var btn = document.querySelector( '.naturlust-top' );
	if ( ! btn ) {
		return;
	}

	function threshold() {
		return window.innerHeight * 0.8;
	}

	function onScroll() {
		if ( window.scrollY > threshold() ) {
			btn.classList.add( 'is-visible' );
		} else {
			btn.classList.remove( 'is-visible' );
		}
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	onScroll();

	btn.addEventListener( 'click', function () {
		var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		window.scrollTo( { top: 0, behavior: reduce ? 'auto' : 'smooth' } );
	} );
} )();
