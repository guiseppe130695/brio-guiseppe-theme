<?php
/**
 * WordPress Front-end Cleanup
 *
 * Strips WordPress defaults this theme doesn't use so the front-end ships
 * the minimum possible HTML/network footprint. Anything removed here is
 * either dead weight, console noise, or a confidentiality leak.
 *
 *   • wp-emoji         → inline 5 KB JS + s.w.org icon fetch + console warning
 *   • block-library    → ~70 KB Gutenberg CSS we never render
 *   • classic-themes   → 1 KB CSS for the WP block editor's classic preset
 *   • global styles    → inline <style id="global-styles-inline-css"> from WP 6.x
 *   • generator meta   → leaks WP version to anyone who can View Source
 *   • shortlink/rsd    → unused REST/XMLRPC discovery links
 *   • wlwmanifest      → 20-year-old Windows Live Writer compat link
 *   • RSS auto-discov. → only re-enable if you actually want feed clients
 *   • oembed routes    → trim the REST API surface a static site doesn't need
 *
 * Each block is wrapped in its own filter so it's easy to flip a single
 * cleanup off without touching the others.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove wp-emoji's inline script + companion stylesheet.
 *
 * The emoji loader hits s.w.org and is the #1 source of "Browser errors
 * were logged to the console" on a fresh WP site.
 *
 * @since 1.0.0
 */
function brio_disable_emojis() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	// Strip the DNS-prefetch hint Core would otherwise inject.
	add_filter( 'wp_resource_hints', function ( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_filter( $urls, function ( $url ) {
				return false === strpos( (string) $url, 's.w.org' );
			} );
		}
		return $urls;
	}, 10, 2 );

	// TinyMCE in the admin doesn't need to convert emoji either.
	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, [ 'wpemoji' ] ) : [];
	} );
}
add_action( 'init', 'brio_disable_emojis' );

/**
 * Drop block editor CSS on the front-end.
 *
 * This theme renders its own templates — no Gutenberg blocks shown to
 * visitors. The bundled block-library stylesheet is ~70 KB of pure waste.
 *
 * @since 1.0.0
 */
function brio_dequeue_block_library() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );        // WP 6.x inline global styles.
}
add_action( 'wp_enqueue_scripts', 'brio_dequeue_block_library', 100 );

/**
 * Suppress the WP 6.x SVG filter duotone <style> block.
 *
 * Only useful when block themes use duotone presets. Saves a handful of
 * inline KB on every page.
 *
 * @since 1.0.0
 */
remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

/**
 * Strip head clutter that leaks version info or links to unused endpoints.
 *
 * @since 1.0.0
 */
remove_action( 'wp_head', 'wp_generator' );                     // <meta name="generator" content="WordPress X.X">
remove_action( 'wp_head', 'wlwmanifest_link' );                 // Windows Live Writer.
remove_action( 'wp_head', 'rsd_link' );                         // Really Simple Discovery (XMLRPC).
remove_action( 'wp_head', 'wp_shortlink_wp_head' );             // <link rel="shortlink"> alt URL.
remove_action( 'wp_head', 'feed_links', 2 );                    // RSS feed auto-discovery.
remove_action( 'wp_head', 'feed_links_extra', 3 );              // Category/comment RSS feeds.
remove_action( 'wp_head', 'rest_output_link_wp_head' );         // <link rel="https://api.w.org/">
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );    // oEmbed JSON/XML discovery links.
remove_action( 'wp_head', 'wp_oembed_add_host_js' );            // Now unused on modern WP, kept for safety.
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );  // <link rel="next"|"prev"> on posts.

/**
 * Disable comments site-wide.
 *
 * Comments are not part of this theme's design. Closing them at the theme
 * level means no plugin or content import can accidentally re-open them.
 *
 * @since 1.0.0
 */
function brio_disable_comments() {
	// Force-close comments on all post types.
	add_filter( 'comments_open', '__return_false', 20 );
	add_filter( 'pings_open',    '__return_false', 20 );

	// Hide existing comment counts everywhere.
	add_filter( 'comments_array', '__return_empty_array', 10 );

	// Remove comment-related items from the admin menu.
	add_action( 'admin_menu', function () {
		remove_menu_page( 'edit-comments.php' );
	} );

	// Redirect any direct attempt to reach the comments admin screen.
	add_action( 'admin_init', function () {
		global $pagenow;
		if ( 'edit-comments.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	} );

	// Remove comment support from all post types.
	add_action( 'init', function () {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	} );

	// Drop the comment-reply script — never needed.
	add_action( 'wp_enqueue_scripts', function () {
		wp_dequeue_script( 'comment-reply' );
	}, 100 );

	// Strip comment feeds from <head>.
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'brio_disable_comments' );

/**
 * Strip the version query string off every enqueued style/script URL.
 *
 * `?ver=6.x` doesn't help cache-busting once long-lived Cache-Control
 * headers are in place, and it adds noise to View Source.
 *
 * @since 1.0.0
 */
function brio_strip_version_query( $src ) {
	if ( JU_DEV_MODE ) {
		return $src; // Keep cache-busting timestamps during development.
	}
	return remove_query_arg( 'ver', $src );
}
add_filter( 'script_loader_src', 'brio_strip_version_query', 15 );
add_filter( 'style_loader_src',  'brio_strip_version_query', 15 );
