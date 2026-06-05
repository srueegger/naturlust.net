/**
 * Naturlust Tagebuch-Slider – Frontend.
 *
 * Leichtgewichtig, ohne Abhängigkeiten: Pfeile, dynamische Punkte,
 * Autoplay mit Pause bei Hover/Fokus/Tab-Wechsel. Respektiert
 * prefers-reduced-motion (kein Autoplay).
 */
( function () {
	'use strict';

	var INTERVAL = 6000;
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function initSlider( root ) {
		var track = root.querySelector( '.naturlust-slider__track' );
		var slides = Array.prototype.slice.call( root.querySelectorAll( '.naturlust-slider__slide' ) );
		var prev = root.querySelector( '.naturlust-slider__nav--prev' );
		var next = root.querySelector( '.naturlust-slider__nav--next' );
		var dotsWrap = root.querySelector( '.naturlust-slider__dots' );

		if ( ! track || slides.length === 0 ) {
			return;
		}

		// Eine einzelne Folie braucht keine Steuerung.
		if ( slides.length < 2 ) {
			if ( prev ) { prev.style.display = 'none'; }
			if ( next ) { next.style.display = 'none'; }
			return;
		}

		var index = 0;
		var timer = null;
		var dots = [];

		slides.forEach( function ( slide, i ) {
			var dot = document.createElement( 'button' );
			dot.type = 'button';
			dot.className = 'naturlust-slider__dot';
			dot.setAttribute( 'role', 'tab' );
			dot.setAttribute( 'aria-label', 'Beitrag ' + ( i + 1 ) );
			dot.addEventListener( 'click', function () {
				goTo( i );
				restart();
			} );
			dotsWrap.appendChild( dot );
			dots.push( dot );
		} );

		function goTo( i ) {
			index = ( i + slides.length ) % slides.length;
			track.style.transform = 'translateX(' + ( -index * 100 ) + '%)';
			dots.forEach( function ( d, di ) {
				d.setAttribute( 'aria-selected', di === index ? 'true' : 'false' );
			} );
			slides.forEach( function ( s, si ) {
				s.setAttribute( 'aria-hidden', si === index ? 'false' : 'true' );
			} );
		}

		function startAutoplay() {
			if ( reduceMotion || timer ) {
				return;
			}
			timer = window.setInterval( function () {
				goTo( index + 1 );
			}, INTERVAL );
		}

		function stopAutoplay() {
			if ( timer ) {
				window.clearInterval( timer );
				timer = null;
			}
		}

		function restart() {
			stopAutoplay();
			startAutoplay();
		}

		if ( prev ) {
			prev.addEventListener( 'click', function () {
				goTo( index - 1 );
				restart();
			} );
		}
		if ( next ) {
			next.addEventListener( 'click', function () {
				goTo( index + 1 );
				restart();
			} );
		}

		root.addEventListener( 'mouseenter', stopAutoplay );
		root.addEventListener( 'mouseleave', startAutoplay );
		root.addEventListener( 'focusin', stopAutoplay );
		root.addEventListener( 'focusout', startAutoplay );

		document.addEventListener( 'visibilitychange', function () {
			if ( document.hidden ) {
				stopAutoplay();
			} else {
				startAutoplay();
			}
		} );

		goTo( 0 );
		startAutoplay();
	}

	var sliders = document.querySelectorAll( '.naturlust-slider' );
	for ( var i = 0; i < sliders.length; i++ ) {
		initSlider( sliders[ i ] );
	}
} )();
