<?php
/**
 * Front-end data providers — Page légale template
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build a default breadcrumb trail from the WordPress page hierarchy.
 *
 * Starts with "Accueil", walks down ancestors top-to-bottom, ends on the
 * current page without URL (current item should not be a link per
 * breadcrumb a11y patterns).
 *
 * @since 1.0.0
 *
 * @param int $post_id Page being rendered.
 * @return array<int, array{label:string, url?:string}>
 */
function brio_legal_default_breadcrumb( $post_id ) {
	$trail = [
		[
			'label' => __( 'Accueil', 'brio-guiseppe' ),
			'url'   => home_url( '/' ),
		],
	];

	$ancestors = array_reverse( get_post_ancestors( $post_id ) );
	foreach ( $ancestors as $ancestor_id ) {
		$trail[] = [
			'label' => get_the_title( $ancestor_id ),
			'url'   => get_permalink( $ancestor_id ),
		];
	}

	$trail[] = [
		'label' => get_the_title( $post_id ),
	];

	return $trail;
}

/**
 * Hero data — title (override or post title) + breadcrumb (override or auto).
 *
 * @since 1.0.0
 */
function brio_get_legal_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	$title_override = brio_meta_get( $post_id, 'legal', 'hero', 'title_override', '' );
	$title          = $title_override ?: get_the_title( $post_id );

	$breadcrumb_override = brio_meta_json_decode(
		brio_meta_get( $post_id, 'legal', 'hero', 'breadcrumb', '' ),
		[]
	);
	$breadcrumb = ! empty( $breadcrumb_override )
		? $breadcrumb_override
		: brio_legal_default_breadcrumb( $post_id );

	return apply_filters( 'brio_legal_hero_data', [
		'title'      => $title,
		'breadcrumb' => $breadcrumb,
	], $post_id );
}
