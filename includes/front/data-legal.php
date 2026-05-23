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

/**
 * Append BreadcrumbList + WebPage nodes to the site-wide JSON-LD graph.
 *
 * Reuses the breadcrumb the hero already builds, so the visible trail and
 * the structured data can never drift apart. Only runs on pages using the
 * legal template (PDC, mentions, CGV, CGU).
 *
 * @since 1.0.0
 *
 * @param array $graph Current @graph (Organization + WebSite, plus other filters).
 * @return array
 */
function brio_legal_jsonld_graph( $graph ) {
	if ( ! is_page_template( 'template-legal.php' ) ) {
		return $graph;
	}

	$post_id = get_queried_object_id();
	$hero    = brio_get_legal_hero_data( $post_id );

	$items = [];
	foreach ( $hero['breadcrumb'] as $i => $crumb ) {
		$entry = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['label'] ?? '',
		];
		if ( ! empty( $crumb['url'] ) ) {
			$entry['item'] = $crumb['url'];
		}
		$items[] = $entry;
	}

	$page_url = get_permalink( $post_id );

	$graph[] = [
		'@type'           => 'BreadcrumbList',
		'@id'             => $page_url . '#breadcrumb',
		'itemListElement' => $items,
	];

	$graph[] = [
		'@type'         => 'WebPage',
		'@id'           => $page_url . '#webpage',
		'url'           => $page_url,
		'name'          => $hero['title'],
		'description'   => brio_seo_get_description(),
		'inLanguage'    => get_bloginfo( 'language' ),
		'isPartOf'      => [ '@id' => home_url( '/#website' ) ],
		'breadcrumb'    => [ '@id' => $page_url . '#breadcrumb' ],
		'datePublished' => get_the_date( DATE_W3C, $post_id ),
		'dateModified'  => get_the_modified_date( DATE_W3C, $post_id ),
	];

	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_legal_jsonld_graph' );
