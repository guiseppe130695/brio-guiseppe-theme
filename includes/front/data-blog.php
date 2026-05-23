<?php
/**
 * Front-end data providers — Blog template
 *
 * Two-grid layout (cf. nouveau design : hero + toolbar + featured + topics) :
 *   • "featured" : 2 articles en grand en haut.
 *   • "topics"   : grille 3 colonnes, articles de la catégorie active.
 *
 * Mécanique hybride : on imprime les 12 premiers articles (featured + topics)
 * en HTML serveur pour le SEO/LCP, puis JS prend le relais pour filtres,
 * recherche et "Load more" via l'endpoint REST /brio/v1/blog/posts.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Hero data — éditable depuis la meta box (titre + intro, plus de breadcrumb).
 *
 * @since 1.0.0
 */
function brio_get_blog_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	$title = brio_meta_get( $post_id, 'blog', 'hero', 'title', '' );
	if ( ! $title ) {
		$title = get_the_title( $post_id ) ?: __( 'Blog', 'brio-guiseppe' );
	}

	return apply_filters( 'brio_blog_hero_data', [
		'title' => $title,
		'intro' => brio_meta_get( $post_id, 'blog', 'hero', 'intro', '' ),
	], $post_id );
}

/**
 * "Topics" section heading.
 *
 * Modèle de titre éditable avec un placeholder {category} qui sera remplacé
 * par le JS quand l'utilisateur change de catégorie. Côté serveur on imprime
 * la chaîne brute (avec placeholder) pour que le JS n'ait qu'à substituer.
 *
 * @since 1.0.0
 */
function brio_get_blog_topics_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	return apply_filters( 'brio_blog_topics_data', [
		'title_template' => brio_meta_get( $post_id, 'blog', 'topics', 'title_template', __( '{category} topics', 'brio-guiseppe' ) ),
		'see_all_label'  => brio_meta_get( $post_id, 'blog', 'topics', 'see_all_label', __( 'See all posts', 'brio-guiseppe' ) ),
		'see_all_url'    => brio_meta_get( $post_id, 'blog', 'topics', 'see_all_url', '' ),
	], $post_id );
}

/**
 * Categories used for the toolbar tabs.
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
			'slug'        => $t->slug,
			'name'        => $t->name,
			'count'       => (int) $t->count,
			'description' => wp_strip_all_tags( (string) $t->description ),
		];
	}

	return apply_filters( 'brio_blog_categories', $out );
}

/**
 * Serialize one post into the lightweight card schema consumed by JS + PHP partials.
 *
 * Single source of truth : both the initial render (PHP) and the REST endpoint
 * (AJAX Load more / filter / search) emit posts in this exact shape so the
 * client renderer doesn't need to branch on data origin.
 *
 * @since 1.0.0
 *
 * @param WP_Post|int $post
 * @return array
 */
function brio_blog_serialize_post( $post ) {
	$post = get_post( $post );
	if ( ! $post instanceof WP_Post ) {
		return [];
	}

	$post_id = (int) $post->ID;

	$cats = get_the_terms( $post_id, 'category' );
	if ( ! is_array( $cats ) ) {
		$cats = [];
	}

	$cat_slugs = wp_list_pluck( $cats, 'slug' );
	$cat_names = wp_list_pluck( $cats, 'name' );

	/* Hors-loop (contexte REST) : éviter get_the_excerpt() qui fait du setup
	   implicite. On reconstruit l'extrait à la main, c'est plus prédictible. */
	$excerpt_raw = ! empty( $post->post_excerpt )
		? $post->post_excerpt
		: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 28, '…' );

	/* Toujours une URL (vraie image ou placeholder Brio). Le partial PHP
	   et le renderer JS ne testent plus l'absence de thumbnail — l'image
	   est garantie côté serveur. */
	$thumbnail       = brio_post_thumbnail_url( $post_id, 'medium_large' );
	$thumbnail_large = brio_post_thumbnail_url( $post_id, 'large' );

	return [
		'id'              => $post_id,
		'url'             => (string) get_permalink( $post_id ),
		'title'           => (string) get_the_title( $post_id ),
		'excerpt'         => (string) $excerpt_raw,
		'date_iso'        => (string) get_the_date( DATE_W3C, $post_id ),
		'date_display'    => (string) get_the_date( '', $post_id ),
		'thumbnail'       => $thumbnail,
		'thumbnail_large' => $thumbnail_large,
		'category_slugs'  => array_values( $cat_slugs ),
		'category_names'  => array_values( $cat_names ),
	];
}

/**
 * Fetch posts with the same arguments used by both the initial render and
 * the REST endpoint, so behavior is consistent across server + client.
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     @type string $category Slug (empty = all categories).
 *     @type string $search   Free-text query.
 *     @type int    $offset   Pagination offset (Load more).
 *     @type int    $per_page Items to fetch.
 * }
 * @return array{posts: WP_Post[], total: int}
 */
function brio_blog_query_posts( $args = [] ) {
	$args = wp_parse_args( $args, [
		'category' => '',
		'search'   => '',
		'offset'   => 0,
		'per_page' => 12,
	] );

	$query_args = [
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => (int) $args['per_page'],
		'offset'         => (int) $args['offset'],
		'orderby'        => 'date',
		'order'          => 'DESC',
	];

	if ( ! empty( $args['category'] ) ) {
		$query_args['category_name'] = sanitize_title( $args['category'] );
	}

	if ( ! empty( $args['tag'] ) ) {
		$query_args['tag'] = sanitize_title( $args['tag'] );
	}

	if ( ! empty( $args['author_id'] ) ) {
		$query_args['author'] = (int) $args['author_id'];
	}

	if ( ! empty( $args['year'] ) ) {
		$query_args['year'] = (int) $args['year'];
	}

	if ( ! empty( $args['monthnum'] ) ) {
		$query_args['monthnum'] = (int) $args['monthnum'];
	}

	if ( ! empty( $args['day'] ) ) {
		$query_args['day'] = (int) $args['day'];
	}

	if ( ! empty( $args['search'] ) ) {
		$query_args['s'] = sanitize_text_field( $args['search'] );
	}

	$query = new WP_Query( $query_args );

	return [
		'posts' => $query->posts,
		'total' => (int) $query->found_posts,
	];
}

/**
 * Initial dataset baked into the page for the first paint.
 *
 * Returns 12 most recent posts (all categories) + the categories list.
 *
 * @since 1.0.0
 */
function brio_get_blog_initial_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();

	$topics_query = brio_blog_query_posts( [ 'per_page' => 12 ] );

	return apply_filters( 'brio_blog_initial_data', [
		'topics'       => array_map( 'brio_blog_serialize_post', $topics_query['posts'] ),
		'topics_total' => (int) $topics_query['total'],
		'categories'   => brio_get_blog_categories(),
	], $post_id );
}

/**
 * Initial dataset for archive pages (category, tag, author, date).
 *
 * Same shape as brio_get_blog_initial_data() but accepts extra WP_Query
 * constraints so the first paint only shows posts relevant to the archive.
 *
 * @since 1.0.0
 *
 * @param array $archive_args Extra query args (category, tag, author_id, year…).
 * @return array{topics: array, topics_total: int, categories: array}
 */
function brio_get_archive_initial_data( $archive_args = [] ) {
	$query_args = array_merge( [ 'per_page' => 12 ], $archive_args );
	$result     = brio_blog_query_posts( $query_args );

	return [
		'topics'       => array_map( 'brio_blog_serialize_post', $result['posts'] ),
		'topics_total' => (int) $result['total'],
		'categories'   => brio_get_blog_categories(),
	];
}

/**
 * Append JSON-LD nodes (BreadcrumbList trimé au minimum + CollectionPage + ItemList).
 *
 * Only runs on pages using template-blog.php.
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
	$initial = brio_get_blog_initial_data( $post_id );

	$articles = [];
	foreach ( $initial['topics'] as $i => $item ) {
		$articles[] = [
			'@type'    => 'ListItem',
			'position' => $i + 1,
			'url'      => $item['url'],
			'name'     => $item['title'],
		];
	}

	$graph[] = [
		'@type'       => 'CollectionPage',
		'@id'         => $url . '#webpage',
		'url'         => $url,
		'name'        => $hero['title'],
		'description' => brio_seo_get_description(),
		'inLanguage'  => get_bloginfo( 'language' ),
		'isPartOf'    => [ '@id' => home_url( '/#website' ) ],
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
