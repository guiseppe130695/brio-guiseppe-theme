<?php
/**
 * Archive template — catégories, tags, dates, auteurs.
 *
 * Même structure que template-blog.php (hero + toolbar + topics + JSON initial)
 * mais le titre du hero est généré dynamiquement par WordPress selon le contexte
 * d'archive actif (catégorie, tag, date, auteur…).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/* ── Titre dynamique selon le type d'archive ── */
if ( is_category() ) {
	$archive_title = single_cat_title( '', false );
	$archive_intro = category_description();
} elseif ( is_tag() ) {
	$archive_title = single_tag_title( '', false );
	$archive_intro = tag_description();
} elseif ( is_author() ) {
	$archive_title = get_the_author();
	$archive_intro = get_the_author_meta( 'description' );
} elseif ( is_year() ) {
	$archive_title = get_the_date( 'Y' );
	$archive_intro = '';
} elseif ( is_month() ) {
	$archive_title = get_the_date( 'F Y' );
	$archive_intro = '';
} elseif ( is_day() ) {
	$archive_title = get_the_date( get_option( 'date_format' ) );
	$archive_intro = '';
} elseif ( is_tax() ) {
	$archive_title = single_term_title( '', false );
	$archive_intro = term_description();
} else {
	$archive_title = __( 'Archives', 'brio-guiseppe' );
	$archive_intro = '';
}

$archive_intro = wp_strip_all_tags( (string) $archive_intro );

/* ── Données initiales (identiques au template-blog) ── */
$initial = brio_get_blog_initial_data();
$topics  = brio_get_blog_topics_data();
?>
<main id="main"
      class="site-main site-main--blog"
      role="main"
      data-blog-app
      data-rest-url="<?php echo esc_url( rest_url( 'brio/v1/blog/posts' ) ); ?>"
      data-per-page="12"
      data-initial-offset="<?php echo (int) count( $initial['topics'] ); ?>">

	<header class="blog-hero">
		<h1 class="blog-hero__title"><?php echo esc_html( $archive_title ); ?></h1>

		<?php if ( ! empty( $archive_intro ) ) : ?>
			<p class="blog-hero__intro"><?php echo esc_html( $archive_intro ); ?></p>
		<?php endif; ?>
	</header>

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
