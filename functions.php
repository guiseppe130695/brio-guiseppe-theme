<?php
/**
 * Brio Guiseppe Theme Functions
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting toggle for development.
 * Set to true to append a timestamp to asset versions (forces browser reload).
 */
define( 'JU_DEV_MODE', true );

/**
 * Load theme files.
 */
require_once get_theme_file_path( '/includes/theme-data.php' );
require_once get_theme_file_path( '/includes/front/enqueue.php' );
require_once get_theme_file_path( '/includes/setup.php' );
require_once get_theme_file_path( '/includes/cleanup.php' );
require_once get_theme_file_path( '/includes/icons.php' );
require_once get_theme_file_path( '/includes/security-headers.php' );
require_once get_theme_file_path( '/includes/custom-nav-walker.php' );
require_once get_theme_file_path( '/includes/widgets.php' );

/**
 * Per-template editable content (meta boxes + front-end data providers).
 *
 * Helpers must load first; meta box registrars rely on them. The front data
 * providers also use brio_meta_get() / brio_meta_json_decode() from the
 * helpers file, so this ordering serves both admin and front contexts.
 */
require_once get_theme_file_path( '/includes/admin/meta-boxes-helpers.php' );
if ( is_admin() ) {
	require_once get_theme_file_path( '/includes/admin/meta-box-seo.php' );
	require_once get_theme_file_path( '/includes/admin/meta-boxes-landing.php' );
	require_once get_theme_file_path( '/includes/admin/meta-boxes-legal.php' );
	require_once get_theme_file_path( '/includes/admin/meta-boxes-outils.php' );
	require_once get_theme_file_path( '/includes/admin/meta-boxes-blog.php' );
}

/**
 * SEO baseline (meta description, Open Graph, JSON-LD @graph). Must load
 * before per-template data providers since they enrich the graph via
 * `brio_jsonld_graph` and read brio_seo_get_description().
 */
require_once get_theme_file_path( '/includes/front/seo.php' );

require_once get_theme_file_path( '/includes/front/data-landing.php' );
require_once get_theme_file_path( '/includes/front/data-legal.php' );
require_once get_theme_file_path( '/includes/front/data-outils.php' );
require_once get_theme_file_path( '/includes/front/data-blog.php' );

/**
 * Register theme hooks.
 */
add_action( 'wp_enqueue_scripts', 'ju_enqueue' );
add_action( 'after_setup_theme',  'ju_setup_theme' );
add_action( 'widgets_init',       'ju_widgets' );
