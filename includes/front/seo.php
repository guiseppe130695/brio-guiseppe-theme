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
/**
 * Emit <link rel="canonical"> for every front-end page.
 *
 * Prevents duplicate-content penalties when the same post is reachable
 * via multiple URLs (pagination, query strings, etc.).
 *
 * @since 1.0.0
 */
function brio_seo_canonical() {
	if ( is_singular() ) {
		$canonical = get_permalink();
	} elseif ( is_front_page() ) {
		$canonical = home_url( '/' );
	} elseif ( is_home() ) {
		$page = get_option( 'page_for_posts' );
		$canonical = $page ? get_permalink( $page ) : home_url( '/' );
	} elseif ( is_archive() ) {
		$canonical = get_pagenum_link( 1, false ) ?: '';
	} else {
		$canonical = '';
	}

	if ( $canonical ) {
		printf( '<link rel="canonical" href="%s" />' . "\n", esc_url( $canonical ) );

		/* hreflang alternates — same content served to FR + MA markets. */
		if ( function_exists( 'brio_sitemap_hreflang_entries' ) ) {
			$alts = brio_sitemap_hreflang_entries( $canonical );
			foreach ( $alts as $alt ) {
				printf(
					'<link rel="alternate" hreflang="%s" href="%s" />' . "\n",
					esc_attr( $alt['lang'] ),
					esc_url( $alt['href'] )
				);
			}
		}
	}

	/* prev / next pour les archives paginées */
	if ( is_archive() || is_home() ) {
		$paged      = max( 1, (int) get_query_var( 'paged' ) ?: 1 );
		$per_page   = 12;
		$total      = (int) wp_count_posts( 'post' )->publish;
		$max_pages  = (int) ceil( $total / $per_page );

		if ( $paged > 1 ) {
			printf( '<link rel="prev" href="%s" />' . "\n", esc_url( get_pagenum_link( $paged - 1 ) ) );
		}
		if ( $paged < $max_pages ) {
			printf( '<link rel="next" href="%s" />' . "\n", esc_url( get_pagenum_link( $paged + 1 ) ) );
		}
	}
}
add_action( 'wp_head', 'brio_seo_canonical', 2 );

function brio_seo_head_meta() {
	$desc  = brio_seo_get_description();
	$title = is_singular() ? get_the_title() : wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	$type  = is_singular( 'post' ) ? 'article' : 'website';

	$thumb = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : '';
	$thumb_id = is_singular() ? get_post_thumbnail_id() : 0;
	$thumb_meta = $thumb_id ? wp_get_attachment_metadata( $thumb_id ) : null;
	$thumb_alt  = $thumb_id ? get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';
	if ( ! $thumb_alt && is_singular() ) {
		$thumb_alt = get_the_title();
	}

	// Map WordPress locale (fr_FR) → OG locale (fr_FR) — same format, just normalize
	$locale = get_locale(); // e.g. fr_FR

	echo "\n<!-- Brio SEO -->\n";
	printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $desc ) );

	/* ── Open Graph ── */
	printf( '<meta property="og:type" content="%s" />' . "\n",        esc_attr( $type ) );
	printf( '<meta property="og:locale" content="%s" />' . "\n",      esc_attr( $locale ) );
	printf( '<meta property="og:title" content="%s" />' . "\n",       esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $desc ) );
	printf( '<meta property="og:url" content="%s" />' . "\n",         esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n",   esc_attr( get_bloginfo( 'name' ) ) );

	if ( $thumb ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $thumb ) );
		printf( '<meta property="og:image:secure_url" content="%s" />' . "\n", esc_url( $thumb ) );
		if ( $thumb_meta && ! empty( $thumb_meta['width'] ) ) {
			printf( '<meta property="og:image:width" content="%d" />' . "\n",  (int) $thumb_meta['width'] );
			printf( '<meta property="og:image:height" content="%d" />' . "\n", (int) $thumb_meta['height'] );
		}
		if ( $thumb_alt ) {
			printf( '<meta property="og:image:alt" content="%s" />' . "\n", esc_attr( $thumb_alt ) );
		}
		// MIME type
		if ( $thumb_id ) {
			$mime = get_post_mime_type( $thumb_id );
			if ( $mime ) {
				printf( '<meta property="og:image:type" content="%s" />' . "\n", esc_attr( $mime ) );
			}
		}
	}

	/* ── Article-specific OG tags (single posts) ── */
	if ( is_singular( 'post' ) ) {
		$post_id   = get_queried_object_id();
		$author_id = (int) get_post_field( 'post_author', $post_id );

		printf(
			'<meta property="article:published_time" content="%s" />' . "\n",
			esc_attr( get_the_date( DATE_W3C, $post_id ) )
		);
		printf(
			'<meta property="article:modified_time" content="%s" />' . "\n",
			esc_attr( get_the_modified_date( DATE_W3C, $post_id ) )
		);
		$author_url = get_author_posts_url( $author_id );
		if ( $author_url ) {
			printf( '<meta property="article:author" content="%s" />' . "\n", esc_url( $author_url ) );
		}
		$cats = get_the_category( $post_id );
		if ( ! empty( $cats ) ) {
			printf( '<meta property="article:section" content="%s" />' . "\n", esc_attr( $cats[0]->name ) );
		}
		$tags = get_the_tags( $post_id );
		if ( $tags ) {
			foreach ( $tags as $tag ) {
				printf( '<meta property="article:tag" content="%s" />' . "\n", esc_attr( $tag->name ) );
			}
		}
	}

	/* ── Twitter Card ── */
	echo '<meta name="twitter:card" content="' . ( $thumb ? 'summary_large_image' : 'summary' ) . '" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n",       esc_attr( $title ) );
	printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $desc ) );
	if ( $thumb ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $thumb ) );
		if ( $thumb_alt ) {
			printf( '<meta name="twitter:image:alt" content="%s" />' . "\n", esc_attr( $thumb_alt ) );
		}
	}
	// Twitter site handle — filterable so you can wire it once if/when you add the account
	$twitter_site = apply_filters( 'brio_seo_twitter_site', '' );
	if ( $twitter_site ) {
		printf( '<meta name="twitter:site" content="%s" />' . "\n", esc_attr( $twitter_site ) );
	}
}
add_action( 'wp_head', 'brio_seo_head_meta', 5 );

/**
 * Preload the LCP image so the browser fetches it in parallel with the CSS,
 * shaving 200-500ms off Largest Contentful Paint on heavy pages.
 *
 *  - Front page / landings: the home-hero suitcase asset.
 *  - Single posts:          the featured image (post-hero).
 *
 * fetchpriority hint mirrors the <img> attribute so it's honored even when
 * the browser dedupes the preload against the real <img>.
 *
 * @since 1.4.0
 */
function brio_seo_preload_lcp() {
	$href = '';

	if ( is_front_page() ) {
		if ( function_exists( 'brio_asset' ) ) {
			$href = brio_asset( 'hero', 'suitcase' );
		}
	} elseif ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		if ( has_post_thumbnail( $post_id ) ) {
			$href = function_exists( 'brio_post_thumbnail_url' )
				? brio_post_thumbnail_url( $post_id, 'large' )
				: get_the_post_thumbnail_url( $post_id, 'large' );
		}
	}

	if ( ! $href ) {
		return;
	}
	printf(
		'<link rel="preload" as="image" href="%s" fetchpriority="high" />' . "\n",
		esc_url( $href )
	);
}
add_action( 'wp_head', 'brio_seo_preload_lcp', 3 );

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
		'@type' => [ 'Organization', 'ProfessionalService' ],
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
	if ( ! empty( $company['phones'][0]['tel'] ) ) {
		$organization['telephone'] = $company['phones'][0]['tel'];
		// All phone numbers as contactPoint entries
		$contact_points = [];
		foreach ( $company['phones'] as $p ) {
			if ( empty( $p['tel'] ) ) {
				continue;
			}
			$contact_points[] = [
				'@type'       => 'ContactPoint',
				'telephone'   => $p['tel'],
				'contactType' => 'customer service',
				'areaServed'  => [ 'MA', 'FR' ],
				'availableLanguage' => [ 'French', 'English' ],
			];
		}
		if ( $contact_points ) {
			$organization['contactPoint'] = $contact_points;
		}
	}
	if ( ! empty( $company['address'] ) ) {
		$organization['address'] = [
			'@type'           => 'PostalAddress',
			'streetAddress'   => $company['address'],
			'addressLocality' => 'Tanger',
			'postalCode'      => '90000',
			'addressCountry'  => 'MA',
		];
	}
	// Service area: Morocco + France (the markets you operate in)
	$organization['areaServed'] = [
		[ '@type' => 'Country', 'name' => 'Morocco' ],
		[ '@type' => 'Country', 'name' => 'France' ],
	];
	$organization['priceRange'] = '€€';
	// Logo (used as image for the Organization)
	$logo_url = home_url( '/wp-content/themes/' . get_stylesheet() . '/assets/img/logo.svg' );
	$organization['logo'] = [
		'@type' => 'ImageObject',
		'url'   => $logo_url,
	];
	$organization['image'] = $logo_url;

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

/**
 * JSON-LD Article node for single posts.
 *
 * Appended via brio_jsonld_graph so it lives in the same consolidated
 * @graph as Organization + WebSite. Covers the full E-E-A-T surface:
 * author (Person), dates, image, publisher, breadcrumb reference.
 *
 * @since 1.0.0
 */
function brio_single_jsonld_graph( $graph ) {
	if ( ! is_singular( 'post' ) ) {
		return $graph;
	}

	$post_id      = get_queried_object_id();
	$post         = get_post( $post_id );
	$author_id    = (int) $post->post_author;
	$author_name  = get_the_author_meta( 'display_name', $author_id );
	$author_bio   = get_the_author_meta( 'description', $author_id );
	$author_url   = get_author_posts_url( $author_id );
	$post_url     = get_permalink( $post_id );
	$thumb_url    = get_the_post_thumbnail_url( $post_id, 'large' );
	$categories   = get_the_category( $post_id );
	$first_cat    = ! empty( $categories ) ? $categories[0] : null;

	/* Person node (reusable via @id) */
	$person = [
		'@type' => 'Person',
		'@id'   => $author_url . '#person',
		'name'  => $author_name,
		'url'   => $author_url,
	];
	if ( $author_bio ) {
		$person['description'] = wp_strip_all_tags( $author_bio );
	}

	/* BreadcrumbList */
	$crumbs = [
		[ '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Accueil', 'brio-guiseppe' ), 'item' => home_url( '/' ) ],
	];
	$position = 2;
	if ( $first_cat ) {
		$crumbs[] = [
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => $first_cat->name,
			'item'     => get_category_link( $first_cat->term_id ),
		];
	}
	$crumbs[] = [
		'@type'    => 'ListItem',
		'position' => $position,
		'name'     => get_the_title( $post_id ),
		'item'     => $post_url,
	];

	$breadcrumb = [
		'@type'           => 'BreadcrumbList',
		'@id'             => $post_url . '#breadcrumb',
		'itemListElement' => $crumbs,
	];

	/* Article node */
	$article = [
		'@type'            => 'Article',
		'@id'              => $post_url . '#article',
		'url'              => $post_url,
		'headline'         => get_the_title( $post_id ),
		'description'      => brio_seo_get_description(),
		'datePublished'    => get_the_date( DATE_W3C, $post_id ),
		'dateModified'     => get_the_modified_date( DATE_W3C, $post_id ),
		'inLanguage'       => get_bloginfo( 'language' ),
		'isPartOf'         => [ '@id' => home_url( '/#website' ) ],
		'author'           => [ '@id' => $author_url . '#person' ],
		'publisher'        => [ '@id' => home_url( '/#organization' ) ],
		'breadcrumb'       => [ '@id' => $post_url . '#breadcrumb' ],
	];

	if ( $thumb_url ) {
		$article['image'] = $thumb_url;
	}

	if ( $first_cat ) {
		$article['articleSection'] = $first_cat->name;
	}

	$tags = get_the_tags( $post_id );
	if ( $tags ) {
		$article['keywords'] = implode( ', ', wp_list_pluck( $tags, 'name' ) );
	}

	// Word count — signal de profondeur du contenu (Core Updates 2025+)
	$word_count = str_word_count(
		wp_strip_all_tags( strip_shortcodes( $post->post_content ) ),
		0,
		'àâäéèêëîïôöùûüçÀÂÄÉÈÊËÎÏÔÖÙÛÜÇœŒ'
	);
	if ( $word_count > 0 ) {
		$article['wordCount'] = (int) $word_count;
		$article['timeRequired'] = 'PT' . max( 1, (int) ceil( $word_count / 200 ) ) . 'M';
	}

	// mainEntityOfPage : indique à Google la page principale qui contient l'article
	$article['mainEntityOfPage'] = [
		'@type' => 'WebPage',
		'@id'   => $post_url,
	];

	$graph[] = $person;
	$graph[] = $breadcrumb;
	$graph[] = $article;

	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_single_jsonld_graph' );

/**
 * Generic BreadcrumbList + WebPage nodes for pages that aren't single posts.
 * Covers landings, outils, legal, author pages, and any other non-front page.
 * Uses the page's hierarchy (parent → child) when present, otherwise a flat
 * "Accueil > Title" crumb.
 */
function brio_page_jsonld_graph( $graph ) {
	if ( ! is_page() || is_front_page() ) {
		return $graph;
	}
	$post_id   = get_queried_object_id();
	$permalink = get_permalink( $post_id );

	// Build crumbs from page ancestry.
	$crumbs = [
		[ '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Accueil', 'brio-guiseppe' ), 'item' => home_url( '/' ) ],
	];
	$ancestors = array_reverse( get_post_ancestors( $post_id ) );
	$position  = 2;
	foreach ( $ancestors as $aid ) {
		$crumbs[] = [
			'@type'    => 'ListItem',
			'position' => $position++,
			'name'     => get_the_title( $aid ),
			'item'     => get_permalink( $aid ),
		];
	}
	$crumbs[] = [
		'@type'    => 'ListItem',
		'position' => $position,
		'name'     => get_the_title( $post_id ),
		'item'     => $permalink,
	];

	$breadcrumb = [
		'@type'           => 'BreadcrumbList',
		'@id'             => $permalink . '#breadcrumb',
		'itemListElement' => $crumbs,
	];

	// WebPage node so the author injector has something to attach to.
	$webpage = [
		'@type'      => 'WebPage',
		'@id'        => $permalink . '#webpage',
		'url'        => $permalink,
		'name'       => get_the_title( $post_id ),
		'isPartOf'   => [ '@id' => home_url( '/#website' ) ],
		'breadcrumb' => [ '@id' => $permalink . '#breadcrumb' ],
		'inLanguage' => get_bloginfo( 'language' ),
	];
	$modified = get_the_modified_date( DATE_W3C, $post_id );
	if ( $modified ) {
		$webpage['dateModified'] = $modified;
	}
	$published = get_the_date( DATE_W3C, $post_id );
	if ( $published ) {
		$webpage['datePublished'] = $published;
	}
	if ( has_post_thumbnail( $post_id ) ) {
		$webpage['primaryImageOfPage'] = [
			'@type' => 'ImageObject',
			'url'   => get_the_post_thumbnail_url( $post_id, 'large' ),
		];
	}

	$graph[] = $breadcrumb;
	$graph[] = $webpage;
	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_page_jsonld_graph' );

/**
 * JSON-LD Service node for landing pages.
 *
 * Each landing markets a specific service (website creation for a hospitality
 * niche/city), so we expose a Service node with an AggregateRating sourced
 * from the same hero rating shown on the page. The rating value + reviewCount
 * must remain visible in the markup (hero block) for Google to honor the
 * stars in SERP — that visibility is enforced by the landing hero template.
 *
 * @since 1.2.0
 *
 * @param array $graph Current @graph array.
 * @return array
 */
function brio_landing_jsonld_graph( $graph ) {
	if ( ! is_page() ) {
		return $graph;
	}
	$post_id = get_queried_object_id();
	if ( 'template-landing.php' !== get_page_template_slug( $post_id ) ) {
		return $graph;
	}

	$rating = brio_get_landing_rating_data( $post_id );
	if ( empty( $rating['count'] ) || empty( $rating['value'] ) ) {
		return $graph; // no rating → don't emit AggregateRating (avoids spam markup)
	}

	$permalink = get_permalink( $post_id );
	$service   = [
		'@type'       => 'Service',
		'@id'         => $permalink . '#service',
		'name'        => get_the_title( $post_id ),
		'url'         => $permalink,
		'provider'    => [ '@id' => home_url( '/#organization' ) ],
		'isPartOf'    => [ '@id' => $permalink . '#webpage' ],
		'serviceType' => __( 'Création de site web pour hôtels et hébergements', 'brio-guiseppe' ),
		'aggregateRating' => [
			'@type'       => 'AggregateRating',
			'ratingValue' => (string) $rating['value'],
			'bestRating'  => '5',
			'worstRating' => '1',
			'reviewCount' => (int) $rating['count'],
		],
	];

	$graph[] = $service;
	return $graph;
}
add_filter( 'brio_jsonld_graph', 'brio_landing_jsonld_graph' );
