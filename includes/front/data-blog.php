<?php
/**
 * Front-end data providers — Blog template
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero data — title / intro / breadcrumb (auto with optional override).
 *
 * @since 1.0.0
 */
function brio_get_blog_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	$title = brio_meta_get( $post_id, 'blog', 'hero', 'title', '' );
	if ( ! $title ) {
		$title = get_the_title( $post_id ) ?: __( 'Insights & Stratégies', 'brio-guiseppe' );
	}

	$breadcrumb_override = brio_meta_json_decode(
		brio_meta_get( $post_id, 'blog', 'hero', 'breadcrumb', '' ),
		[]
	);
	$breadcrumb = ! empty( $breadcrumb_override ) ? $breadcrumb_override : [
		[ 'label' => __( 'Accueil', 'brio-guiseppe' ), 'url' => home_url( '/' ) ],
		[ 'label' => get_the_title( $post_id ) ?: __( 'Blog', 'brio-guiseppe' ) ],
	];

	return apply_filters( 'brio_blog_hero_data', [
		'title'      => $title,
		'intro'      => brio_meta_get( $post_id, 'blog', 'hero', 'intro', '' ),
		'breadcrumb' => $breadcrumb,
	], $post_id );
}

/**
 * Featured post = most recent published post.
 *
 * Returned as a WP_Post or null when the blog has no published article yet.
 *
 * @since 1.0.0
 */
function brio_get_blog_featured_post() {
	$posts = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	] );

	$featured = ! empty( $posts ) ? $posts[0] : null;

	return apply_filters( 'brio_blog_featured_post', $featured );
}

/**
 * Recent posts grid = 6 most recent posts AFTER the featured one.
 *
 * Excludes the featured ID so we never duplicate it in the grid.
 *
 * @since 1.0.0
 *
 * @return WP_Post[]
 */
function brio_get_blog_recent_posts() {
	$featured = brio_get_blog_featured_post();
	$exclude  = $featured ? [ $featured->ID ] : [];

	$posts = get_posts( [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 6,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post__not_in'   => $exclude,
	] );

	return apply_filters( 'brio_blog_recent_posts', $posts );
}

/**
 * Append CollectionPage + ItemList nodes to the JSON-LD graph.
 *
 * Only runs on pages using template-blog.php. ItemList combines the featured
 * post + the recent grid so structured data reflects what visitors see.
 *
 * @since 1.0.0
 */
function brio_blog_jsonld_graph( $graph ) {
	if ( ! is_page_template( 'template-blog.php' ) ) {
		return $graph;
	}

	$post_id = get_queried_object_id();
	$hero    = brio_get_blog_hero_data( $post_id );
	$url     = get_permalink( $post_id );

	/** Breadcrumb mirrors the hero trail. */
	$crumbs = [];
	foreach ( $hero['breadcrumb'] as $i => $crumb ) {
		$entry = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'name'     => $crumb['label'] ?? '',
		];
		if ( ! empty( $crumb['url'] ) ) {
			$entry['item'] = $crumb['url'];
		}
		$crumbs[] = $entry;
	}

	/** Article list = featured + recents, in display order. */
	$articles = [];
	$featured = brio_get_blog_featured_post();
	$recent   = brio_get_blog_recent_posts();
	$all      = array_filter( array_merge( [ $featured ], $recent ) );

	foreach ( $all as $i => $p ) {
		$articles[] = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'url'      => get_permalink( $p ),
			'name'     => get_the_title( $p ),
		];
	}

	$graph[] = [
		'@type'           => 'BreadcrumbList',
		'@id'             => $url . '#breadcrumb',
		'itemListElement' => $crumbs,
	];

	$graph[] = [
		'@type'       => 'CollectionPage',
		'@id'         => $url . '#webpage',
		'url'         => $url,
		'name'        => $hero['title'],
		'description' => brio_seo_get_description(),
		'inLanguage'  => get_bloginfo( 'language' ),
		'isPartOf'    => [ '@id' => home_url( '/#website' ) ],
		'breadcrumb'  => [ '@id' => $url . '#breadcrumb' ],
		'hasPart'     => [ '@id' => $url . '#itemlist' ],
	];

	if ( ! empty( $articles ) ) {
		$graph[] = [
			'@type'           => 'ItemList',
			'@id'             => $url . '#itemlist',
			'itemListElement' => $articles,
		];
	}

	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_blog_jsonld_graph' );
