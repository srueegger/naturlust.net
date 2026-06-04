/**
 * Naturlust – Hamburger-Overlay.
 *
 * Steuert das Vollbild-Menü (Pattern naturlust/hamburger): Öffnen/Schliessen,
 * ESC, Klick auf den Hintergrund, Schliessen beim Klick auf einen Eintrag,
 * Scroll-Sperre und einfache Fokus-Verwaltung.
 */
( function () {
	'use strict';

	var root = document.querySelector( '[data-naturlust-nav]' );
	if ( ! root ) {
		return;
	}

	var toggle = root.querySelector( '.naturlust-nav__toggle' );
	var overlay = root.querySelector( '.naturlust-nav__overlay' );
	var closeBtn = root.querySelector( '.naturlust-nav__close' );

	if ( ! toggle || ! overlay || ! closeBtn ) {
		return;
	}

	var lastFocus = null;

	function onKeydown( event ) {
		if ( event.key === 'Escape' || event.key === 'Esc' ) {
			closeMenu();
			return;
		}

		// Einfacher Fokus-Trap innerhalb des Overlays.
		if ( event.key === 'Tab' ) {
			var focusable = overlay.querySelectorAll(
				'button, a[href], [tabindex]:not([tabindex="-1"])'
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

	function openMenu() {
		lastFocus = document.activeElement;
		overlay.hidden = false;
		toggle.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'naturlust-nav-open' );
		closeBtn.focus();
		document.addEventListener( 'keydown', onKeydown );
	}

	function closeMenu() {
		overlay.hidden = true;
		toggle.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'naturlust-nav-open' );
		document.removeEventListener( 'keydown', onKeydown );
		if ( lastFocus && typeof lastFocus.focus === 'function' ) {
			lastFocus.focus();
		}
	}

	toggle.addEventListener( 'click', openMenu );
	closeBtn.addEventListener( 'click', closeMenu );

	// Klick auf den Hintergrund (nicht auf Inhalt) schliesst.
	overlay.addEventListener( 'click', function ( event ) {
		if ( event.target === overlay ) {
			closeMenu();
		}
	} );

	// Beim Navigieren das Overlay schliessen.
	var links = overlay.querySelectorAll( 'a[href]' );
	for ( var i = 0; i < links.length; i++ ) {
		links[ i ].addEventListener( 'click', closeMenu );
	}
} )();
