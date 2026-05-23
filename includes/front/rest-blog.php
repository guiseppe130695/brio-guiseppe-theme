<?php
/**
 * Blog — REST endpoint for AJAX (filter, search, Load more)
 *
 * Single endpoint /wp-json/brio/v1/blog/posts that the client JS hits when
 * the user changes category, types in the search box, or clicks "Load more".
 * Returns the same shape as brio_blog_serialize_post(), so the client
 * renderer is symmetric with the initial PHP render.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_blog_register_rest() {
	register_rest_route( 'brio/v1', '/blog/posts', [
		'methods'             => WP_REST_Server::READABLE,
		'permission_callback' => '__return_true',
		'args'                => [
			'category' => [
				'description'       => 'Category slug (empty = all)',
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_title',
			],
			'search'   => [
				'description'       => 'Free-text query',
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_text_field',
			],
			'offset'   => [
				'description'       => 'Pagination offset',
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'per_page' => [
				'description'       => 'Items per request',
				'type'              => 'integer',
				'default'           => 12,
				'minimum'           => 1,
				'maximum'           => 48,
				'sanitize_callback' => 'absint',
			],
			'author_id' => [
				'description'       => 'Filter by author ID',
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'tag' => [
				'description'       => 'Tag slug',
				'type'              => 'string',
				'default'           => '',
				'sanitize_callback' => 'sanitize_title',
			],
			'year' => [
				'description'       => 'Filter by year',
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'month' => [
				'description'       => 'Filter by month',
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
			'day' => [
				'description'       => 'Filter by day',
				'type'              => 'integer',
				'default'           => 0,
				'sanitize_callback' => 'absint',
			],
		],
		'callback' => 'brio_blog_rest_get_posts',
	] );
}
add_action( 'rest_api_init', 'brio_blog_register_rest' );

/**
 * Endpoint handler — returns serialized posts + pagination metadata.
 *
 * @since 1.0.0
 *
 * @param WP_REST_Request $request
 * @return WP_REST_Response
 */
function brio_blog_rest_get_posts( WP_REST_Request $request ) {
	$category = $request->get_param( 'category' );
	$search   = $request->get_param( 'search' );
	$offset   = $request->get_param( 'offset' );
	$per_page = $request->get_param( 'per_page' );

	$result = brio_blog_query_posts( [
		'category'  => is_string( $category ) ? $category : '',
		'search'    => is_string( $search )   ? $search   : '',
		'offset'    => (int) $offset,
		'per_page'  => (int) $per_page,
		'author_id' => (int) $request->get_param( 'author_id' ),
		'tag'       => (string) $request->get_param( 'tag' ),
		'year'      => (int) $request->get_param( 'year' ),
		'monthnum'  => (int) $request->get_param( 'month' ),
		'day'       => (int) $request->get_param( 'day' ),
	] );

	$posts = array_map( 'brio_blog_serialize_post', $result['posts'] );

	return new WP_REST_Response( [
		'posts'    => $posts,
		'total'    => $result['total'],
		'has_more' => ( (int) $offset + count( $posts ) ) < $result['total'],
	], 200 );
}
