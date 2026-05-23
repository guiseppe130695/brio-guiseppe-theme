<?php
/**
 * Template Name: Blog (archive interactive)
 *
 * Layout :
 *   1. Hero : titre + intro (éditables)
 *   2. Toolbar : recherche + dropdown catégorie + bouton submit
 *   3. Topics : grille 4 colonnes des articles, paginée via "Load more"
 *
 * Hybride server-render + JS :
 *   • PHP imprime les 12 topics + metadata dans un <script type="application/json">
 *     pour le 1er paint (SEO + LCP rapide).
 *   • Le JS prend le relais (filtre catégorie, recherche, Load more) via
 *     l'endpoint REST /wp-json/brio/v1/blog/posts.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$initial = brio_get_blog_initial_data();
$hero    = brio_get_blog_hero_data();
$topics  = brio_get_blog_topics_data();
?>
<main id="main"
      class="site-main site-main--blog"
      role="main"
      data-blog-app
      data-rest-url="<?php echo esc_url( rest_url( 'brio/v1/blog/posts' ) ); ?>"
      data-per-page="12"
      data-initial-offset="<?php echo (int) count( $initial['topics'] ); ?>">

	<?php get_template_part( 'template-parts/blog/hero' ); ?>
	<?php get_template_part( 'template-parts/blog/toolbar' ); ?>
	<?php get_template_part( 'template-parts/blog/topics' ); ?>

	<script id="brio-blog-data" type="application/json">
		<?php echo wp_json_encode( [
			'topics'         => $initial['topics'],
			'topics_total'   => $initial['topics_total'],
			'categories'     => $initial['categories'],
			'title_template' => $topics['title_template'],
			'i18n'           => [
				'no_results'  => __( 'Aucun article ne correspond à votre recherche.', 'brio-guiseppe' ),
				'load_more'   => __( 'Charger plus', 'brio-guiseppe' ),
				'loading'     => __( 'Chargement…', 'brio-guiseppe' ),
				'all_label'   => __( 'Tous', 'brio-guiseppe' ),
				'all_topics'  => __( 'Tous les articles', 'brio-guiseppe' ),
				'category'    => __( 'Catégorie', 'brio-guiseppe' ),
			],
		], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?>
	</script>

</main>
<?php
get_footer();
