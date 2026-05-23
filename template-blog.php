<?php
/**
 * Template Name: Blog (vitrine + article-pilier)
 *
 * Calque le design Elementor json/Blog.json :
 *   1. Page Hero (fond primary) : titre + intro + breadcrumb
 *   2. Vitrine articles (fond accent) : 1 featured (post le plus récent) +
 *      grille des 6 articles récents suivants
 *   3. Contenu SEO statique : the_content() de la page (article-pilier)
 *
 * Volontairement PAS de pagination ni de filtres catégorie : ce n'est pas
 * une archive paginée, c'est une landing SEO Blog. L'archive paginée
 * native WordPress reste disponible sur les pages d'archive de catégorie
 * (/category/<slug>/) si besoin.
 *
 * Sections filtrables via `brio_blog_sections`.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$sections = apply_filters( 'brio_blog_sections', [
	'hero',      // titre + intro + breadcrumb (fond primary)
	'featured',  // post le plus récent en grand format paysage (fond accent)
	'grid',      // 6 articles récents suivants en cartes verticales (fond accent)
	'content',   // article-pilier statique via the_content()
] );

?>
<main id="main" class="site-main site-main--blog" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part( 'template-parts/blog/' . $section );
}
?>
</main>
<?php
get_footer();
