<?php
/**
 * Front-end data providers — Blog template
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero data — eyebrow / title / intro with sensible fallbacks.
 *
 * @since 1.0.0
 */
function brio_get_blog_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	$title = brio_meta_get( $post_id, 'blog', 'hero', 'title', '' );
	if ( ! $title ) {
		$title = get_the_title( $post_id ) ?: __( 'Insights & Stratégies', 'brio-guiseppe' );
	}

	return apply_filters( 'brio_blog_hero_data', [
		'eyebrow' => brio_meta_get( $post_id, 'blog', 'hero', 'eyebrow', '' ),
		'title'   => $title,
		'intro'   => brio_meta_get( $post_id, 'blog', 'hero', 'intro',   '' ),
	], $post_id );
}

/**
 * List of published categories used by the filter tabs.
 *
 * Hidden categories (`hide_empty=true`) drop out automatically. Returned in
 * the same shape the filters partial consumes.
 *
 * @since 1.0.0
 *
 * @return array<int, array{slug:string, name:string, count:int}>
 */
function brio_get_blog_categories() {
	$terms = get_terms( [
		'taxonomy'   => 'category',
		'hide_empty' => true,
	] );

	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return [];
	}

	$out = [];
	foreach ( $terms as $t ) {
		$out[] = [
			'slug'  => $t->slug,
			'name'  => $t->name,
			'count' => (int) $t->count,
		];
	}

	return apply_filters( 'brio_blog_categories', $out );
}

/**
 * Append CollectionPage + ItemList nodes to the JSON-LD graph for the
 * blog archive. Only runs on pages using template-blog.php.
 *
 * @since 1.0.0
 *
 * @param array $graph Current @graph.
 * @return array
 */
function brio_blog_jsonld_graph( $graph ) {
	if ( ! is_page_template( 'template-blog.php' ) ) {
		return $graph;
	}

	$post_id = get_queried_object_id();
	$hero    = brio_get_blog_hero_data( $post_id );
	$url     = get_permalink( $post_id );

	/** Build a lightweight ItemList from the current query (visible page only). */
	global $wp_query;
	$items = [];
	if ( $wp_query instanceof WP_Query ) {
		// The main query on a template-page is the Page itself, not the posts —
		// so we run a small read-only query just for the structured data.
		$peek = new WP_Query( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 12,
			'no_found_rows'  => true,
			'fields'         => 'ids',
		] );
		foreach ( $peek->posts as $i => $pid ) {
			$items[] = [
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'url'      => get_permalink( $pid ),
				'name'     => get_the_title( $pid ),
			];
		}
		wp_reset_postdata();
	}

	$graph[] = [
		'@type'       => 'CollectionPage',
		'@id'         => $url . '#webpage',
		'url'         => $url,
		'name'        => $hero['title'],
		'description' => brio_seo_get_description(),
		'isPartOf'    => [ '@id' => home_url( '/#website' ) ],
		'inLanguage'  => get_bloginfo( 'language' ),
		'hasPart'     => [ '@id' => $url . '#itemlist' ],
	];

	if ( ! empty( $items ) ) {
		$graph[] = [
			'@type'           => 'ItemList',
			'@id'             => $url . '#itemlist',
			'itemListElement' => $items,
		];
	}

	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_blog_jsonld_graph' );
