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
	$author        = get_queried_object();
	$archive_title = $author->display_name ?? '';
	$archive_intro = get_the_author_meta( 'description', $author->ID ?? 0 );
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

/* ── Contrainte d'archive pour la query initiale ── */
$archive_query_args = [];

if ( is_category() ) {
	$archive_query_args['category'] = get_queried_object()->slug;
} elseif ( is_tag() ) {
	$archive_query_args['tag'] = get_queried_object()->slug;
} elseif ( is_author() ) {
	$archive_query_args['author_id'] = get_queried_object_id();
} elseif ( is_year() ) {
	$archive_query_args['year'] = get_query_var( 'year' );
} elseif ( is_month() ) {
	$archive_query_args['year']     = get_query_var( 'year' );
	$archive_query_args['monthnum'] = get_query_var( 'monthnum' );
} elseif ( is_day() ) {
	$archive_query_args['year']     = get_query_var( 'year' );
	$archive_query_args['monthnum'] = get_query_var( 'monthnum' );
	$archive_query_args['day']      = get_query_var( 'day' );
}

/* ── Données initiales filtrées selon le contexte d'archive ── */
$initial = brio_get_archive_initial_data( $archive_query_args );
$topics  = brio_get_blog_topics_data( 0 );
?>
<?php
/* ── Contraintes d'archive à passer au JS via data-* ── */
$archive_data_attrs = '';
if ( is_author() ) {
	$archive_data_attrs .= ' data-archive-author-id="' . (int) get_queried_object_id() . '"';
} elseif ( is_category() ) {
	$archive_data_attrs .= ' data-archive-category="' . esc_attr( get_queried_object()->slug ) . '"';
} elseif ( is_tag() ) {
	$archive_data_attrs .= ' data-archive-tag="' . esc_attr( get_queried_object()->slug ) . '"';
} elseif ( is_year() ) {
	$archive_data_attrs .= ' data-archive-year="' . (int) get_query_var( 'year' ) . '"';
} elseif ( is_month() ) {
	$archive_data_attrs .= ' data-archive-year="' . (int) get_query_var( 'year' ) . '"';
	$archive_data_attrs .= ' data-archive-month="' . (int) get_query_var( 'monthnum' ) . '"';
} elseif ( is_day() ) {
	$archive_data_attrs .= ' data-archive-year="' . (int) get_query_var( 'year' ) . '"';
	$archive_data_attrs .= ' data-archive-month="' . (int) get_query_var( 'monthnum' ) . '"';
	$archive_data_attrs .= ' data-archive-day="' . (int) get_query_var( 'day' ) . '"';
}
?>
<main id="main"
      class="site-main site-main--blog"
      role="main"
      data-blog-app
      data-rest-url="<?php echo esc_url( rest_url( 'brio/v1/blog/posts' ) ); ?>"
      data-per-page="12"
      data-initial-offset="<?php echo (int) count( $initial['topics'] ); ?>"
      <?php echo $archive_data_attrs; ?>>

	<header class="blog-hero">
		<h1 class="blog-hero__title"><?php echo esc_html( $archive_title ); ?></h1>

		<?php if ( ! empty( $archive_intro ) ) : ?>
			<p class="blog-hero__intro"><?php echo esc_html( $archive_intro ); ?></p>
		<?php endif; ?>
	</header>

	<?php get_template_part( 'template-parts/blog/toolbar' ); ?>
	<?php
	set_query_var( 'brio_blog_initial', $initial );
	set_query_var( 'brio_blog_topics', $topics );
	get_template_part( 'template-parts/blog/topics' );
	?>

	<script id="brio-blog-data" type="application/json">
		<?php echo wp_json_encode( [
			'topics'         => $initial['topics'],
			'topics_total'   => $initial['topics_total'],
			'categories'     => $initial['categories'],
			'title_template' => $topics['title_template'],
			'archive'        => $archive_query_args,
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
