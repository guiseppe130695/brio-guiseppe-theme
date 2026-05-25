<?php
/**
 * Sitemaps XML — segmentés, sans plugin.
 *
 * Index : /sitemap.xml
 * Segments :
 *   /sitemap-pages.xml     pages publiées (hors landings, hors home, hors blog index)
 *   /sitemap-landings.xml  pages avec template-landing.php (exclut celles en noindex)
 *   /sitemap-posts.xml     articles
 *   /sitemap-terms.xml     catégories non vides
 *
 * Pourquoi segmenter : Google ingère mieux et signale les erreurs par segment ;
 * sur 200+ landings, l'index permet à Search Console de mesurer la couverture
 * de ce segment précisément.
 *
 * @package Brio_Guiseppe
 * @since   1.4.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_SITEMAP_TYPES = [ 'pages', 'landings', 'posts', 'terms' ];

/**
 * Rewrite rules : index + 4 segments.
 */
function brio_sitemap_rewrite_rule() {
	add_rewrite_rule( '^sitemap\.xml$',                       'index.php?brio_sitemap=index',    'top' );
	add_rewrite_rule( '^sitemap-(pages|landings|posts|terms)\.xml$', 'index.php?brio_sitemap=$matches[1]', 'top' );
}
add_action( 'init', 'brio_sitemap_rewrite_rule' );

function brio_sitemap_query_var( $vars ) {
	$vars[] = 'brio_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'brio_sitemap_query_var' );

/**
 * Dispatcher.
 */
function brio_sitemap_render() {
	$which = get_query_var( 'brio_sitemap' );
	if ( ! $which ) {
		return;
	}

	if ( ! get_option( 'brio_sitemap_flushed_v2' ) ) {
		flush_rewrite_rules();
		update_option( 'brio_sitemap_flushed_v2', 1 );
	}

	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex' );

	if ( 'index' === $which ) {
		brio_sitemap_render_index();
	} elseif ( in_array( $which, BRIO_SITEMAP_TYPES, true ) ) {
		brio_sitemap_render_segment( $which );
	}
	exit;
}
add_action( 'template_redirect', 'brio_sitemap_render' );

/**
 * Index sitemap pointing to all segments.
 */
function brio_sitemap_render_index() {
	$segments = [];
	foreach ( BRIO_SITEMAP_TYPES as $type ) {
		$lastmod = brio_sitemap_segment_lastmod( $type );
		if ( null === $lastmod ) {
			continue; // skip empty segments
		}
		$segments[] = [
			'loc'     => home_url( "/sitemap-{$type}.xml" ),
			'lastmod' => $lastmod,
		];
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $segments as $s ) {
		echo "\t<sitemap>\n";
		echo "\t\t<loc>" . esc_url( $s['loc'] ) . "</loc>\n";
		echo "\t\t<lastmod>" . esc_html( $s['lastmod'] ) . "</lastmod>\n";
		echo "\t</sitemap>\n";
	}
	echo '</sitemapindex>';
}

function brio_sitemap_segment_lastmod( $type ) {
	switch ( $type ) {
		case 'pages':
		case 'landings':
			$args = [
				'post_type'      => 'page',
				'post_status'    => 'publish',
				'posts_per_page' => 1,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'fields'         => 'ids',
				'no_found_rows'  => true,
			];
			if ( 'landings' === $type ) {
				$args['meta_key']   = '_wp_page_template';
				$args['meta_value'] = 'template-landing.php';
			}
			$q = get_posts( $args );
			if ( empty( $q ) ) {
				return null;
			}
			return date( 'c', strtotime( get_post( $q[0] )->post_modified_gmt ) );
		case 'posts':
			$last = get_lastpostmodified( 'GMT', 'post' );
			return $last ? date( 'c', strtotime( $last ) ) : null;
		case 'terms':
			$cats = get_categories( [ 'hide_empty' => true, 'number' => 1 ] );
			return ! empty( $cats ) ? date( 'c' ) : null;
	}
	return null;
}

/**
 * Render one segment.
 */
function brio_sitemap_render_segment( $type ) {
	$urls = [];

	if ( 'pages' === $type ) {
		// Home included here so it appears in the pages segment.
		$urls[] = [
			'loc'        => home_url( '/' ),
			'lastmod'    => date( 'c', strtotime( get_lastpostmodified( 'GMT' ) ) ),
			'changefreq' => 'daily',
			'priority'   => '1.0',
		];

		$pages = get_pages( [
			'post_status' => 'publish',
			'exclude'     => array_filter( [
				get_option( 'page_on_front' ),
				get_option( 'page_for_posts' ),
			] ),
		] );
		foreach ( $pages as $page ) {
			// Landings live in their own segment.
			if ( 'template-landing.php' === get_page_template_slug( $page->ID ) ) {
				continue;
			}
			$urls[] = [
				'loc'        => get_permalink( $page->ID ),
				'lastmod'    => date( 'c', strtotime( $page->post_modified_gmt ) ),
				'changefreq' => 'monthly',
				'priority'   => '0.8',
				'hreflang'   => brio_sitemap_hreflang_entries( get_permalink( $page->ID ) ),
			];
		}
	}

	if ( 'landings' === $type ) {
		$landings = get_posts( [
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'template-landing.php',
			'posts_per_page' => -1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		] );
		foreach ( $landings as $page ) {
			if ( function_exists( 'brio_landing_audit' ) ) {
				$audit = brio_landing_audit( $page->ID );
				if ( $audit['noindex'] ) {
					continue;
				}
			}
			$urls[] = [
				'loc'        => get_permalink( $page->ID ),
				'lastmod'    => date( 'c', strtotime( $page->post_modified_gmt ) ),
				'changefreq' => 'monthly',
				'priority'   => '0.7',
				'hreflang'   => brio_sitemap_hreflang_entries( get_permalink( $page->ID ) ),
			];
		}
	}

	if ( 'posts' === $type ) {
		$posts = get_posts( [
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		] );
		foreach ( $posts as $post ) {
			$urls[] = [
				'loc'        => get_permalink( $post->ID ),
				'lastmod'    => date( 'c', strtotime( $post->post_modified_gmt ) ),
				'changefreq' => 'weekly',
				'priority'   => '0.9',
				'image'      => has_post_thumbnail( $post->ID ) ? get_the_post_thumbnail_url( $post->ID, 'large' ) : '',
			];
		}
	}

	if ( 'terms' === $type ) {
		$categories = get_categories( [ 'hide_empty' => true ] );
		foreach ( $categories as $cat ) {
			$urls[] = [
				'loc'        => get_category_link( $cat->term_id ),
				'changefreq' => 'weekly',
				'priority'   => '0.6',
			];
		}
	}

	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
	echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"' . "\n";
	echo '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

	foreach ( $urls as $url ) {
		echo "\t<url>\n";
		echo "\t\t<loc>" . esc_url( $url['loc'] ) . "</loc>\n";
		if ( ! empty( $url['lastmod'] ) ) {
			echo "\t\t<lastmod>" . esc_html( $url['lastmod'] ) . "</lastmod>\n";
		}
		if ( ! empty( $url['changefreq'] ) ) {
			echo "\t\t<changefreq>" . esc_html( $url['changefreq'] ) . "</changefreq>\n";
		}
		if ( ! empty( $url['priority'] ) ) {
			echo "\t\t<priority>" . esc_html( $url['priority'] ) . "</priority>\n";
		}
		if ( ! empty( $url['hreflang'] ) ) {
			foreach ( $url['hreflang'] as $hl ) {
				printf(
					"\t\t<xhtml:link rel=\"alternate\" hreflang=\"%s\" href=\"%s\" />\n",
					esc_attr( $hl['lang'] ),
					esc_url( $hl['href'] )
				);
			}
		}
		if ( ! empty( $url['image'] ) ) {
			echo "\t\t<image:image>\n";
			echo "\t\t\t<image:loc>" . esc_url( $url['image'] ) . "</image:loc>\n";
			echo "\t\t</image:image>\n";
		}
		echo "\t</url>\n";
	}

	echo '</urlset>';
}

/**
 * Build hreflang alternates for a URL.
 *
 * The site serves the same content to FR and MA audiences. We declare:
 *   - fr-FR : France market
 *   - fr-MA : Morocco market
 *   - x-default : same URL, fallback
 *
 * Override via the `brio_hreflang_map` filter when a true multilingual
 * setup (Polylang/WPML) lands.
 *
 * @param string $url Canonical URL.
 * @return array<int,array{lang:string,href:string}>
 */
function brio_sitemap_hreflang_entries( $url ) {
	$map = apply_filters( 'brio_hreflang_map', [
		'fr-FR'     => $url,
		'fr-MA'     => $url,
		'x-default' => $url,
	], $url );

	$out = [];
	foreach ( $map as $lang => $href ) {
		if ( ! $href ) {
			continue;
		}
		$out[] = [ 'lang' => $lang, 'href' => $href ];
	}
	return $out;
}

/**
 * <link rel="sitemap"> in <head>.
 */
function brio_sitemap_head_link() {
	printf(
		'<link rel="sitemap" type="application/xml" href="%s" />' . "\n",
		esc_url( home_url( '/sitemap.xml' ) )
	);
}
add_action( 'wp_head', 'brio_sitemap_head_link', 1 );

/**
 * Backwards compat: if anyone hits /sitemap-pages.xml from the old single
 * sitemap, we now serve the segmented version. Nothing to do — the rewrite
 * matches it.
 *
 * One-time flush handled inside brio_sitemap_render() via the v2 option key.
 */
