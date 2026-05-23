<?php
/**
 * Front-end Asset Enqueue
 *
 * Two execution paths controlled by `JU_DEV_MODE`:
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
function ju_enqueue() {
	$uri = get_theme_file_uri();
	$ver = JU_DEV_MODE ? time() : wp_get_theme()->get( 'Version' );

	if ( JU_DEV_MODE ) {
		// ---- DEV: individual source files (easy iteration) ----
		wp_register_style( 'ju_fonts',             $uri . '/assets/css/fonts.css',             [],                 $ver );
		wp_register_style( 'ju_variables',         $uri . '/assets/css/variables.css',         [ 'ju_fonts' ],     $ver );
		wp_register_style( 'ju_font_icons',        $uri . '/assets/css/font-icons.css',        [],                 $ver );
		wp_register_style( 'ju_header',            $uri . '/assets/css/header.css',            [ 'ju_variables' ], $ver );
		wp_register_style( 'ju_header_responsive', $uri . '/assets/css/header-responsive.css', [ 'ju_header' ],    $ver );
		wp_register_style( 'ju_footer',            $uri . '/assets/css/footer.css',            [ 'ju_variables' ], $ver );
		wp_register_style( 'ju_home',              $uri . '/assets/css/home.css',              [ 'ju_variables' ], $ver );
		wp_register_style( 'ju_legal',             $uri . '/assets/css/sections/legal.css',    [ 'ju_variables' ], $ver );
		wp_register_style( 'ju_blog',              $uri . '/assets/css/sections/blog-page.css',[ 'ju_variables' ], $ver );
		wp_register_style( 'ju_404',               $uri . '/assets/css/sections/404.css',      [ 'ju_variables' ], $ver );

		wp_enqueue_style( 'ju_fonts' );
		wp_enqueue_style( 'ju_variables' );
		wp_enqueue_style( 'ju_font_icons' );
		wp_enqueue_style( 'ju_header' );
		wp_enqueue_style( 'ju_header_responsive' );
		wp_enqueue_style( 'ju_footer' );

		if ( is_front_page() ) {
			wp_enqueue_style( 'ju_home' );
		}

		if ( is_page_template( 'template-legal.php' ) ) {
			wp_enqueue_style( 'ju_legal' );
		}

		if ( is_page_template( 'template-blog.php' ) ) {
			wp_enqueue_style( 'ju_blog' );
		}

		if ( is_404() ) {
			wp_enqueue_style( 'ju_404' );
		}
	} else {
		// ---- PROD: minified bundles (one global, one home) ----
		wp_enqueue_style( 'ju_global', $uri . '/assets/css/dist/global.min.css', [], $ver );

		if ( is_front_page() ) {
			wp_enqueue_style( 'ju_home', $uri . '/assets/css/dist/home.min.css', [ 'ju_global' ], $ver );
		}
	}

	// ---- Scripts ----
	// Legacy _s/canvas bundles (plugins.js 590 KB + functions.js 132 KB + jQuery)
	// are not used by the custom theme. Keeping them costs LCP and triggers the
	// Lighthouse "missing source maps for large JS" warning. Re-enable per-page
	// only if a specific feature needs them.

	if ( is_front_page() ) {
		wp_enqueue_script(
			'ju_counters',
			$uri . '/assets/js/counters.js',
			[],
			$ver,
			[ 'in_footer' => true, 'strategy' => 'defer' ]
		);
	}

	if ( is_page_template( 'template-blog.php' ) ) {
		wp_enqueue_script(
			'ju_blog',
			$uri . '/assets/js/blog.js',
			[],
			$ver,
			[ 'in_footer' => true, 'strategy' => 'defer' ]
		);
	}
}
