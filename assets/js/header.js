/**
 * Header — mobile burger toggle + submenu accordion.
 *
 * Vanilla JS, no jQuery. Listens for the burger click, toggles aria-expanded
 * on the button and `is-open` on the nav. Also handles tap-to-open for
 * submenu items inside the drawer.
 */
( function () {
	'use strict';

	var burger = document.querySelector( '.site-header__burger' );
	var nav    = document.getElementById( 'brio-primary-nav' );
	if ( ! burger || ! nav ) return;

	function setOpen( open ) {
		burger.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		nav.classList.toggle( 'is-open', open );
		document.body.classList.toggle( 'has-mobile-nav-open', open );
	}

	burger.addEventListener( 'click', function () {
		var isOpen = burger.getAttribute( 'aria-expanded' ) === 'true';
		setOpen( ! isOpen );
	} );

	// Close drawer when a regular link (no submenu) is clicked.
	nav.addEventListener( 'click', function ( e ) {
		var a = e.target.closest( 'a' );
		if ( ! a ) return;
		var parent = a.parentElement;
		if ( parent && parent.classList.contains( 'menu-item-has-children' ) ) {
			// Toggle submenu instead of navigating only on mobile (no href #).
			if ( window.matchMedia( '(max-width: 768px)' ).matches && a.getAttribute( 'href' ) === '#' ) {
				e.preventDefault();
				parent.classList.toggle( 'open' );
				return;
			}
		}
		setOpen( false );
	} );

	// Close on Escape.
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && nav.classList.contains( 'is-open' ) ) {
			setOpen( false );
			burger.focus();
		}
	} );

	// Reset when crossing the desktop breakpoint.
	var mql = window.matchMedia( '(min-width: 769px)' );
	mql.addEventListener && mql.addEventListener( 'change', function ( ev ) {
		if ( ev.matches ) setOpen( false );
	} );
} )();
