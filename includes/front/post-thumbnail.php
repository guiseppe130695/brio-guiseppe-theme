<?php
/**
 * Post thumbnail helpers (with brand-aware placeholder).
 *
 * Single source of truth for "what URL to use for a post's thumbnail" :
 * every section of the theme (blog grid, single article, related posts,
 * homepage blog block, etc.) should call brio_post_thumbnail_url() instead
 * of get_the_post_thumbnail_url() directly. This way the placeholder swap
 * happens once, and tweaking it later (different placeholder per category,
 * picking the first image from content, etc.) is a one-file change.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL of the global fallback placeholder image.
 *
 * Stored as an SVG in assets/images/ so it scales perfectly and weighs ~1 KB.
 * The filter lets a child theme / plugin override it without touching this
 * file (e.g. a different placeholder per category).
 *
 * @since 1.0.0
 *
 * @return string Absolute URL.
 */
function brio_post_thumbnail_placeholder_url() {
	return apply_filters(
		'brio_post_thumbnail_placeholder_url',
		get_theme_file_uri( '/assets/images/Image_A_La_Une.svg' )
	);
}

/**
 * Resolve the thumbnail URL for a given post, falling back to the brand
 * placeholder when no featured image is set.
 *
 * Returns a string ALWAYS (never empty), so partials and JS renderers can
 * assume a usable URL and skip the "if empty hide figure" branch.
 *
 * @since 1.0.0
 *
 * @param int|WP_Post|null $post Post identifier (defaults to current).
 * @param string           $size Registered image size (default 'medium_large').
 * @return string Absolute URL.
 */
function brio_post_thumbnail_url( $post = null, $size = 'medium_large' ) {
	$post_id = $post ? ( is_object( $post ) ? (int) $post->ID : (int) $post ) : (int) get_the_ID();

	if ( $post_id && has_post_thumbnail( $post_id ) ) {
		$url = get_the_post_thumbnail_url( $post_id, $size );
		if ( $url ) {
			return apply_filters( 'brio_post_thumbnail_url', $url, $post_id, $size, false );
		}
	}

	return apply_filters(
		'brio_post_thumbnail_url',
		brio_post_thumbnail_placeholder_url(),
		$post_id,
		$size,
		true /* is_placeholder */
	);
}
