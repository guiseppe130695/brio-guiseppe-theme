<?php
/**
 * Search results template.
 *
 * Même structure que archive.php (hero + toolbar + topics + JSON initial)
 * mais le titre reflète la requête de recherche et la contrainte initiale
 * est `search` plutôt qu'une taxonomie ou un auteur.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

$search_query = get_search_query();

/* ── Données initiales filtrées sur le terme recherché ── */
$paged   = max( 1, (int) get_query_var( 'paged' ) ?: 1 );
$initial = brio_get_archive_initial_data( [ 'search' => $search_query ], $paged );
$topics  = brio_get_blog_topics_data( 0 );
?>
<main id="main"
      class="site-main site-main--blog"
      role="main"
      data-blog-app
      data-rest-url="<?php echo esc_url( rest_url( 'brio/v1/blog/posts' ) ); ?>"
      data-per-page="12"
      data-paged="<?php echo (int) $paged; ?>"
      data-initial-offset="<?php echo (int) count( $initial['topics'] ); ?>"
      data-archive-search="<?php echo esc_attr( $search_query ); ?>">

	<header class="blog-hero">
		<h1 class="blog-hero__title">
			<?php if ( $search_query ) : ?>
				<?php printf(
					/* translators: %s: search term */
					esc_html__( 'Résultats pour « %s »', 'brio-guiseppe' ),
					'<span>' . esc_html( $search_query ) . '</span>'
				); ?>
			<?php else : ?>
				<?php esc_html_e( 'Recherche', 'brio-guiseppe' ); ?>
			<?php endif; ?>
		</h1>

		<?php if ( $initial['topics_total'] > 0 ) : ?>
			<p class="blog-hero__intro">
				<?php printf(
					/* translators: %d: number of results */
					esc_html( _n( '%d article trouvé', '%d articles trouvés', $initial['topics_total'], 'brio-guiseppe' ) ),
					(int) $initial['topics_total']
				); ?>
			</p>
		<?php else : ?>
			<p class="blog-hero__intro">
				<?php esc_html_e( 'Aucun article ne correspond à votre recherche.', 'brio-guiseppe' ); ?>
			</p>
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
			'archive'        => [ 'search' => $search_query ],
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
