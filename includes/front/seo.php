<?php
/**
 * SEO — meta description, Open Graph, JSON-LD
 *
 * Lightweight, plugin-free SEO layer for the theme. Mirrors the subset of
 * Yoast / Rank Math we actually need (meta description, OG basics, schema)
 * without the bloat. Specialised templates (legal, landing, outils…) hook
 * `brio_jsonld_graph` to append their own @graph nodes — e.g. legal pages
 * add BreadcrumbList + WebPage.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Resolve the meta description for the current request.
 *
 * Priority:
 *   1. Per-page override stored in post meta `_brio_seo_description`
 *   2. Manual excerpt
 *   3. Auto-trimmed excerpt from the_content() (160 chars)
 *   4. Site tagline as a last resort
 *
 * @since 1.0.0
 *
 * @return string Plain-text meta description, never empty on real posts.
 */
function brio_seo_get_description() {
	if ( is_singular() ) {
		$post_id = get_queried_object_id();

		$override = get_post_meta( $post_id, '_brio_seo_description', true );
		if ( $override ) {
			return wp_strip_all_tags( $override );
		}

		$excerpt = get_post_field( 'post_excerpt', $post_id );
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}

		$content = get_post_field( 'post_content', $post_id );
		if ( $content ) {
			return wp_trim_words( wp_strip_all_tags( strip_shortcodes( $content ) ), 28, '…' );
		}
	}

	return wp_strip_all_tags( get_bloginfo( 'description' ) );
}

/**
 * Print the <meta name="description"> + Open Graph + Twitter Card tags.
 *
 * Hooked late on wp_head so we sit after WordPress core's own emissions.
 *
 * @since 1.0.0
 */
function brio_seo_head_meta() {
	$desc  = brio_seo_get_description();
	$title = is_singular() ? get_the_title() : wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$type  = is_singular( 'post' ) ? 'article' : 'website';

	$thumb = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : '';

	echo "\n<!-- Brio SEO -->\n";
	printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );

	printf( '<meta property="og:type" content="%s" />' . "\n",        esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s" />' . "\n",       esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s" />' . "\n",         esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n",   esc_attr( get_bloginfo( 'name' ) ) );
	if ( $thumb ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $thumb ) );
	}

	echo '<meta name="twitter:card" content="' . ( $thumb ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n",       esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
	if ( $thumb ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $thumb ) );
	}
}
add_action( 'wp_head', 'brio_seo_head_meta', 5 );

/**
 * Print the consolidated JSON-LD @graph for the current request.
 *
 * Always includes Organization + WebSite as baseline. Templates extend the
 * graph via the `brio_jsonld_graph` filter to append page-specific nodes
 * (BreadcrumbList, WebPage, Article, FAQPage…).
 *
 * @since 1.0.0
 */
function brio_seo_jsonld() {
	$company = function_exists( 'brio_get_company_data' ) ? brio_get_company_data() : [];

	$organization = [
		'@type' => 'Organization',
		'@id'   => home_url( '/#organization' ),
		'name'  => $company['name']  ?? get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	];
	if ( ! empty( $company['email'] ) ) {
		$organization['email'] = $company['email'];
	}
	if ( ! empty( $company['social'] ) ) {
		$organization['sameAs'] = array_values( array_filter( $company['social'] ) );
	}

	$website = [
		'@type'     => 'WebSite',
		'@id'       => home_url( '/#website' ),
		'url'       => home_url( '/' ),
		'name'      => get_bloginfo( 'name' ),
		'publisher' => [ '@id' => home_url( '/#organization' ) ],
		'inLanguage'=> get_bloginfo( 'language' ),
	];

	/**
	 * Filter the JSON-LD @graph before output.
	 *
	 * Each element should be a single schema.org node (associative array).
	 * Templates use this hook to append BreadcrumbList, WebPage, Article…
	 *
	 * @since 1.0.0
	 *
	 * @param array $graph Default graph (Organization + WebSite).
	 */
	$graph = apply_filters( 'brio_jsonld_graph', [ $organization, $website ] );

	$payload = [
		'@context' => 'https://schema.org',
		'@graph'   => array_values( $graph ),
	];

	echo "\n<script type=\"application/ld+json\">"
		. wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
		. "</script>\n";
}
add_action( 'wp_head', 'brio_seo_jsonld', 20 );
