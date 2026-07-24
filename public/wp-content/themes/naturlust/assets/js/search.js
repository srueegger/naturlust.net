/**
 * Naturlust – Such-Overlay.
 *
 * Steuert das Vollbild-Suchfeld (Pattern naturlust/search): Öffnen/Schliessen,
 * ESC, Klick auf den Hintergrund, Scroll-Sperre, Fokus aufs Eingabefeld und
 * einfache Fokus-Verwaltung. Bewusst gleich aufgebaut wie assets/js/hamburger.js.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-naturlust-search]' );
	if ( ! root ) {
		return;
	}

	var toggle = root.querySelector( '.naturlust-search__toggle' );
	var overlay = root.querySelector( '.naturlust-search__overlay' );
	var closeBtn = root.querySelector( '.naturlust-search__close' );
	var field = root.querySelector( '.naturlust-search__field' );

	if ( ! toggle || ! overlay || ! closeBtn ) {
		return;
	}

	var lastFocus = null;

	function onKeydown( event ) {
		if ( event.key === 'Escape' || event.key === 'Esc' ) {
			closeSearch();
			return;
		}

		// Einfacher Fokus-Trap innerhalb des Overlays.
		if ( event.key === 'Tab' ) {
			var focusable = overlay.querySelectorAll(
				'button, a[href], input, [tabindex]:not([tabindex="-1"])'
			);
			if ( ! focusable.length ) {
				return;
			}
			var first = focusable[ 0 ];
			var last = focusable[ focusable.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		}
	}

	function openSearch() {
		lastFocus = document.activeElement;
		overlay.hidden = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'naturlust-search-open' );
		document.addEventListener( 'keydown', onKeydown );
		// Nach dem Einblenden das Feld fokussieren.
		if ( field ) {
			window.setTimeout( function () {
				field.focus();
			}, 50 );
		} else {
			closeBtn.focus();
		}
	}

	function closeSearch() {
		overlay.hidden = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'naturlust-search-open' );
		document.removeEventListener( 'keydown', onKeydown );
		if ( lastFocus && typeof lastFocus.focus === 'function' ) {
			lastFocus.focus();
		}
	}

	toggle.addEventListener( 'click', openSearch );
	closeBtn.addEventListener( 'click', closeSearch );

	// Klick auf den Hintergrund (nicht auf Formular/Feld) schliesst.
	overlay.addEventListener( 'click', function ( event ) {
		if ( event.target === overlay ) {
			closeSearch();
		}
	} );
} )();
