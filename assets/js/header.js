/**
 * Lazy-load the hero video on desktop only, after the first paint.
 * Mobile keeps the poster image only (saves 15 MB transfer).
 */
( function () {
	'use strict';
	if ( window.matchMedia( '(max-width: 768px)' ).matches ) return;
	var v = document.querySelector( '.home-hero__video[data-src]' );
	if ( ! v ) return;

	function load() {
		if ( v.dataset.loaded ) return;
		v.dataset.loaded = '1';
		var src = document.createElement( 'source' );
		src.src = v.dataset.src;
		src.type = 'video/mp4';
		v.appendChild( src );
		v.load();
		v.play().catch( function () {} );
	}

	// Use IntersectionObserver to load only when hero is visible.
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) { load(); io.disconnect(); }
			} );
		}, { rootMargin: '200px' } );
		io.observe( v );
	} else {
		setTimeout( load, 1500 );
	}
} )();

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
