<?php
/**
 * Sitemap XML — sans plugin.
 *
 * Accessible sur /sitemap.xml via une règle de réécriture WordPress.
 * Inclut : homepage, pages publiées, articles publiés, catégories.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Enregistre la règle de réécriture /sitemap.xml → query var interne.
 *
 * @since 1.0.0
 */
function brio_sitemap_rewrite_rule() {
	add_rewrite_rule( '^sitemap\.xml$', 'index.php?brio_sitemap=1', 'top' );
}
add_action( 'init', 'brio_sitemap_rewrite_rule' );

/**
 * Déclare la query var personnalisée.
 *
 * @since 1.0.0
 */
function brio_sitemap_query_var( $vars ) {
	$vars[] = 'brio_sitemap';
	return $vars;
}
add_filter( 'query_vars', 'brio_sitemap_query_var' );

/**
 * Génère et envoie le sitemap XML quand la query var est présente.
 *
 * @since 1.0.0
 */
function brio_sitemap_render() {
	if ( ! get_query_var( 'brio_sitemap' ) ) {
		return;
	}

	/* Flush les règles si besoin (première visite après activation). */
	if ( ! get_option( 'brio_sitemap_flushed' ) ) {
		flush_rewrite_rules();
		update_option( 'brio_sitemap_flushed', 1 );
	}

	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'X-Robots-Tag: noindex' ); /* Le sitemap lui-même ne doit pas être indexé. */

	$urls = [];

	/* Homepage */
	$urls[] = [
		'loc'        => home_url( '/' ),
		'lastmod'    => date( 'c', strtotime( get_lastpostmodified( 'GMT' ) ) ),
		'changefreq' => 'daily',
		'priority'   => '1.0',
	];

	/* Pages publiées (hors page d'accueil et page blog) */
	$pages = get_pages( [
		'post_status'    => 'publish',
		'exclude'        => array_filter( [
			get_option( 'page_on_front' ),
			get_option( 'page_for_posts' ),
		] ),
	] );
	foreach ( $pages as $page ) {
		// Skip landings flagged as noindex by anti-scaled-content audit.
		if (
			'template-landing.php' === get_page_template_slug( $page->ID )
			&& function_exists( 'brio_landing_audit' )
		) {
			$audit = brio_landing_audit( $page->ID );
			if ( $audit['noindex'] ) {
				continue;
			}
		}
		$urls[] = [
			'loc'        => get_permalink( $page->ID ),
			'lastmod'    => date( 'c', strtotime( $page->post_modified_gmt ) ),
			'changefreq' => 'monthly',
			'priority'   => '0.8',
		];
	}

	/* Articles publiés */
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
			'image'      => has_post_thumbnail( $post->ID )
				? get_the_post_thumbnail_url( $post->ID, 'large' )
				: '',
		];
	}

	/* Catégories non vides */
	$categories = get_categories( [ 'hide_empty' => true ] );
	foreach ( $categories as $cat ) {
		$urls[] = [
			'loc'        => get_category_link( $cat->term_id ),
			'changefreq' => 'weekly',
			'priority'   => '0.6',
		];
	}

	/* Génération XML */
	echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
	echo '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

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
		if ( ! empty( $url['image'] ) ) {
			echo "\t\t<image:image>\n";
			echo "\t\t\t<image:loc>" . esc_url( $url['image'] ) . "</image:loc>\n";
			echo "\t\t</image:image>\n";
		}
		echo "\t</url>\n";
	}

	echo '</urlset>';
	exit;
}
add_action( 'template_redirect', 'brio_sitemap_render' );

/**
 * Ajoute <link rel="sitemap"> dans le <head>.
 *
 * @since 1.0.0
 */
function brio_sitemap_head_link() {
	printf(
		'<link rel="sitemap" type="application/xml" href="%s" />' . "\n",
		esc_url( home_url( '/sitemap.xml' ) )
	);
}
add_action( 'wp_head', 'brio_sitemap_head_link', 1 );
