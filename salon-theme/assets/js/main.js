/**
 * Site-wide behavior: mobile nav toggle only. Kept deliberately small —
 * everything else on the site works with plain HTML/CSS, and the
 * booking flow has its own dedicated script (booking.js) loaded only on
 * the booking page.
 */
( function () {
	'use strict';

	var toggle = document.getElementById( 'nav-toggle' );
	var nav = document.getElementById( 'primary-navigation' );

	if ( ! toggle || ! nav ) {
		return;
	}

	toggle.addEventListener( 'click', function () {
		var isOpen = nav.classList.toggle( 'is-open' );
		toggle.setAttribute( 'aria-expanded', isOpen ? 'true' : 'false' );
	} );

	// Close the mobile menu when a link inside it is followed.
	nav.addEventListener( 'click', function ( event ) {
		if ( event.target.tagName === 'A' ) {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
		}
	} );

	// Close on Escape for keyboard users.
	document.addEventListener( 'keydown', function ( event ) {
		if ( event.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
			nav.classList.remove( 'is-open' );
			toggle.setAttribute( 'aria-expanded', 'false' );
			toggle.focus();
		}
	} );
} )();
