<?php
/**
 * Front-end Asset Enqueue
 *
 * Two execution paths controlled by `BRIO_DEV_MODE`:
 *
 *   • DEV (true)  — loads individual source files with a cache-busting
 *                   timestamp. Easiest to iterate on; one HTTP request per
 *                   stylesheet (with nested @imports kicking off more).
 *
 *   • PROD (false) — loads pre-built minified bundles from
 *                    `assets/css/dist/`. Two files total: `global.min.css`
 *                    everywhere + `home.min.css` on the homepage.
 *                    Run `npm run build:css` to (re)generate the bundles.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enqueue theme assets on the front-end.
 *
 * @since 1.0.0
 */
function brio_enqueue() {
	$uri = get_theme_file_uri();
	$ver = BRIO_DEV_MODE ? time() : wp_get_theme()->get( 'Version' );

	if ( BRIO_DEV_MODE ) {
		// ---- DEV: individual source files (easy iteration) ----
		wp_register_style( 'brio_fonts',             $uri . '/assets/css/fonts.css',             [],                 $ver );
		wp_register_style( 'brio_variables',         $uri . '/assets/css/variables.css',         [ 'brio_fonts' ],     $ver );
		wp_register_style( 'brio_header',            $uri . '/assets/css/layout/header.css',            [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_header_responsive', $uri . '/assets/css/layout/header-responsive.css', [ 'brio_header' ],    $ver );
		wp_register_style( 'brio_footer',            $uri . '/assets/css/layout/footer.css',            [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_home',              $uri . '/assets/css/sections/home.css',            [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_legal',             $uri . '/assets/css/sections/legal.css',    [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_blog',              $uri . '/assets/css/sections/blog-page.css',[ 'brio_variables' ], $ver );
		wp_register_style( 'brio_landing',           $uri . '/assets/css/sections/landing.css',  [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_404',               $uri . '/assets/css/sections/404.css',      [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_single',            $uri . '/assets/css/sections/single.css',   [ 'brio_variables' ], $ver );
		wp_register_style( 'brio_author',            $uri . '/assets/css/sections/author.css',   [ 'brio_variables' ], $ver );

		wp_enqueue_style( 'brio_fonts' );
		wp_enqueue_style( 'brio_variables' );
		wp_enqueue_style( 'brio_header' );
		wp_enqueue_style( 'brio_header_responsive' );
		wp_enqueue_style( 'brio_footer' );

		if ( is_front_page() || is_page_template( 'template-landing.php' ) ) {
			wp_enqueue_style( 'brio_home' );
		}

		if ( is_page_template( 'template-legal.php' ) ) {
			wp_enqueue_style( 'brio_legal' );
		}

		if ( is_page_template( 'template-blog.php' ) || is_archive() || is_search() ) {
			wp_enqueue_style( 'brio_blog' );
		}

		if ( is_page_template( 'template-landing.php' ) ) {
			wp_enqueue_style( 'brio_landing' );
		}

		if ( is_404() ) {
			wp_enqueue_style( 'brio_404' );
		}

		if ( is_single() ) {
			wp_enqueue_style( 'brio_single' );
		}

		if ( is_page_template( 'template-author.php' ) ) {
			wp_enqueue_style( 'brio_author' );
		}
	} else {
		// ---- PROD: minified bundles, regular blocking load. ----
		// We tried media='print' onload swap but it caused FCP regressions
		// in Chromium. The bundles are small enough (~12 KB gzipped) that
		// blocking load + inlined critical CSS is the fastest combo.
		wp_enqueue_style( 'brio_global', $uri . '/assets/css/dist/global.min.css', [], $ver );

		if ( is_front_page() || is_page_template( 'template-landing.php' ) ) {
			wp_enqueue_style( 'brio_home', $uri . '/assets/css/dist/home.min.css', [ 'brio_global' ], $ver );
		}
		if ( is_page_template( 'template-blog.php' ) || is_archive() || is_search() ) {
			wp_enqueue_style( 'brio_blog', $uri . '/assets/css/dist/blog.min.css', [ 'brio_global' ], $ver );
		}
		if ( is_single() ) {
			wp_enqueue_style( 'brio_single', $uri . '/assets/css/dist/single.min.css', [ 'brio_global' ], $ver );
		}
		if ( is_page_template( 'template-legal.php' ) ) {
			wp_enqueue_style( 'brio_legal', $uri . '/assets/css/dist/legal.min.css', [ 'brio_global' ], $ver );
		}
		if ( is_page_template( 'template-author.php' ) ) {
			wp_enqueue_style( 'brio_author', $uri . '/assets/css/dist/author.min.css', [ 'brio_global' ], $ver );
		}
		if ( is_404() ) {
			wp_enqueue_style( 'brio_404', $uri . '/assets/css/dist/404.min.css', [ 'brio_global' ], $ver );
		}
	}

	// ---- Scripts ----
	// Legacy _s/canvas bundles (plugins.js 590 KB + functions.js 132 KB + jQuery)
	// are not used by the custom theme. Keeping them costs LCP and triggers the
	// Lighthouse "missing source maps for large JS" warning. Re-enable per-page
	// only if a specific feature needs them.

	// Header burger — every page.
	wp_enqueue_script(
		'brio_header',
		$uri . '/assets/js/header.js',
		[],
		$ver,
		[ 'in_footer' => true, 'strategy' => 'defer' ]
	);

	if ( is_front_page() || is_page_template( 'template-landing.php' ) ) {
		wp_enqueue_script(
			'brio_counters',
			$uri . '/assets/js/counters.js',
			[],
			$ver,
			[ 'in_footer' => true, 'strategy' => 'defer' ]
		);
	}

	if ( is_page_template( 'template-blog.php' ) || is_archive() || is_search() ) {
		wp_enqueue_script(
			'brio_blog',
			$uri . '/assets/js/blog.js',
			[],
			$ver,
			[ 'in_footer' => true, 'strategy' => 'defer' ]
		);
	}
}

/**
 * Turn render-blocking <link rel="stylesheet"> into async loads via the
 * media="print" + onload swap trick. Critical above-the-fold styles are
 * inlined in header.php so the page is visually complete without these.
 *
 * Only applied to our own theme bundles — leaves admin/plugin styles alone.
 *
 * @param string $tag    The <link> markup.
 * @param string $handle The style handle.
 * @return string
 */
function brio_make_styles_async( $tag, $handle ) {
	$async = [ 'brio_global', 'brio_home', 'brio_blog', 'brio_single', 'brio_legal', 'brio_author', 'brio_404' ];
	if ( ! in_array( $handle, $async, true ) ) {
		return $tag;
	}
	// Replace media='all' with media='print' onload swap, with <noscript> fallback.
	$tag = preg_replace(
		"/media='[^']*'/",
		"media='print' onload=\"this.media='all'\"",
		$tag
	);
	// Append <noscript> fallback so the CSS still loads with JS disabled.
	$noscript_tag = str_replace( " media='print' onload=\"this.media='all'\"", " media='all'", $tag );
	$noscript_tag = preg_replace( '/\sonload="[^"]*"/', '', $noscript_tag );
	return $tag . '<noscript>' . $noscript_tag . '</noscript>';
}
