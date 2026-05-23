<?php
/**
 * Template Name: Blog (archive des articles)
 *
 * Gabarit dédié à la page "Blog" : hero éditable + filtres par catégorie
 * (liens classiques ?categorie=slug, rechargement) + grille paginée
 * (12 articles/page). Toujours un WP_Query custom : on ne s'appuie PAS sur
 * le mécanisme natif "page des articles" (home.php) pour rester cohérent
 * avec les autres Page Templates (legal, landing, outils).
 *
 * Pourquoi `paged` est calé sur `paged` ET `page` : sur une Page WordPress
 * la query principale lit `page` (offset interne d'une page statique),
 * tandis que la pagination publique passe par `paged`. On essaie les deux.
 *
 * Sections filtrables via `brio_blog_sections`.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/** Pagination — accepte ?paged=N (lien pagination) ou ?page=N (fallback). */
$paged = max(
	1,
	(int) get_query_var( 'paged' ),
	(int) get_query_var( 'page' )
);

/** Filtre catégorie via ?categorie=slug (préféré au slug "category" pour
 *  ne pas entrer en collision avec les archives /category/<slug>/ natives). */
$current_cat_slug = isset( $_GET['categorie'] )
	? sanitize_title( wp_unslash( $_GET['categorie'] ) )
	: '';

$args = [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 12,
	'paged'               => $paged,
	'ignore_sticky_posts' => true,
];

if ( $current_cat_slug ) {
	$args['category_name'] = $current_cat_slug;
}

/**
 * Filterable query args so a child theme / plugin can adjust ordering or
 * add custom taxonomies without forking this template.
 *
 * @since 1.0.0
 *
 * @param array  $args             WP_Query args.
 * @param string $current_cat_slug Currently selected category slug (or '').
 */
$args      = apply_filters( 'brio_blog_query_args', $args, $current_cat_slug );
$blog_query = new WP_Query( $args );

/** Sections — exposed for filter override. */
$sections = apply_filters( 'brio_blog_sections', [
	'hero',
	'filters',
	'grid',
	'pagination',
] );

?>
<main id="main" class="site-main site-main--blog" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part(
		'template-parts/blog/' . $section,
		null,
		[
			'query'            => $blog_query,
			'current_cat_slug' => $current_cat_slug,
			'paged'            => $paged,
		]
	);
}
?>
</main>
<?php
wp_reset_postdata();
get_footer();
