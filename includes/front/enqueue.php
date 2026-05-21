<?php
/**
 * Front-end Asset Enqueue
 *
 * Registers and enqueues theme stylesheets and scripts. Page-specific assets
 * (homepage, blog, etc.) are loaded conditionally to keep page weight down.
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

	// ---- Register stylesheets in dependency order ----
	wp_register_style( 'ju_fonts',             $uri . '/assets/css/fonts.css',             [],                 $ver );
	wp_register_style( 'ju_variables',         $uri . '/assets/css/variables.css',         [ 'ju_fonts' ],     $ver );
	wp_register_style( 'ju_font_icons',        $uri . '/assets/css/font-icons.css',        [],                 $ver );
	wp_register_style( 'ju_header',            $uri . '/assets/css/header.css',            [ 'ju_variables' ], $ver );
	wp_register_style( 'ju_header_responsive', $uri . '/assets/css/header-responsive.css', [ 'ju_header' ],    $ver );
	wp_register_style( 'ju_footer',            $uri . '/assets/css/footer.css',            [ 'ju_variables' ], $ver );
	wp_register_style( 'ju_home',              $uri . '/assets/css/home.css',              [ 'ju_variables' ], $ver );

	// ---- Global stylesheets (every page) ----
	wp_enqueue_style( 'ju_fonts' );
	wp_enqueue_style( 'ju_variables' );
	wp_enqueue_style( 'ju_font_icons' );
	wp_enqueue_style( 'ju_header' );
	wp_enqueue_style( 'ju_header_responsive' );
	wp_enqueue_style( 'ju_footer' );

	// ---- Conditional stylesheets ----
	if ( is_front_page() ) {
		wp_enqueue_style( 'ju_home' );
	}

	// ---- Scripts ----
	// Legacy _s/canvas bundles (plugins.js 590 KB + functions.js 132 KB + jQuery)
	// are not used by the custom theme. Keeping them costs LCP and triggers the
	// Lighthouse "missing source maps for large JS" warning. Re-enable per-page
	// only if a specific feature needs them.
}
