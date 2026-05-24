<?php
/**
 * Blog — Topics grid + Load more
 *
 * Le titre `{category} topics` est imprimé tel quel par PHP (catégorie "Tous"
 * par défaut) ; le JS le réécrit quand l'utilisateur change de tab.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$initial = get_query_var( 'brio_blog_initial' ) ?: brio_get_blog_initial_data();
$topics  = get_query_var( 'brio_blog_topics' )  ?: brio_get_blog_topics_data();

$initial_title = str_replace( '{category}', __( 'Tous les articles', 'brio-guiseppe' ), $topics['title_template'] );
?>
<section class="blog-topics" aria-labelledby="blog-topics-title" data-blog-topics>

	<header class="blog-topics__head">
		<div class="blog-topics__head-text">
			<h2 id="blog-topics-title" class="blog-topics__title" data-blog-topics-title>
				<?php echo esc_html( $initial_title ); ?>
			</h2>

			<?php /* Description de catégorie WP : vide à l'état "Tous", remplie
			          par le JS quand l'utilisateur sélectionne une catégorie. */ ?>
			<p class="blog-topics__description" data-blog-topics-description hidden></p>
		</div>

		<?php if ( ! empty( $topics['see_all_url'] ) ) : ?>
			<a class="blog-topics__see-all" href="<?php echo esc_url( $topics['see_all_url'] ); ?>">
				<?php echo esc_html( $topics['see_all_label'] ); ?>
			</a>
		<?php endif; ?>
	</header>

	<div class="blog-topics__grid" data-blog-grid>
		<?php foreach ( $initial['topics'] as $post ) : ?>
			<article class="blog-card">
				<a class="blog-card__link" href="<?php echo esc_url( $post['url'] ); ?>">

					<?php if ( ! empty( $post['thumbnail'] ) ) : ?>
						<figure class="blog-card__media">
							<img src="<?php echo esc_url( $post['thumbnail'] ); ?>"
							     alt=""
							     loading="lazy"
							     decoding="async" />
						</figure>
					<?php endif; ?>

					<div class="blog-card__body">
						<time class="blog-card__date" datetime="<?php echo esc_attr( $post['date_iso'] ); ?>">
							<?php echo esc_html( $post['date_display'] ); ?>
						</time>
						<h3 class="blog-card__title"><?php echo esc_html( $post['title'] ); ?></h3>
						<?php if ( ! empty( $post['excerpt'] ) ) : ?>
							<p class="blog-card__excerpt"><?php echo esc_html( $post['excerpt'] ); ?></p>
						<?php endif; ?>
					</div>

				</a>
			</article>
		<?php endforeach; ?>
	</div>

	<p class="blog-topics__empty" data-blog-empty hidden>
		<?php esc_html_e( 'Aucun article ne correspond à votre recherche.', 'brio-guiseppe' ); ?>
	</p>

	<div class="blog-topics__more">
		<button type="button"
		        class="blog-topics__load-more"
		        data-blog-load-more
		        hidden>
			<?php esc_html_e( 'Charger plus', 'brio-guiseppe' ); ?>
		</button>
	</div>

	<?php
	/* Pagination HTML — crawlable par Google, masquée par JS dès que blog.js init */
	$paged     = isset( $initial['paged'] )    ? (int) $initial['paged']    : 1;
	$per_page  = isset( $initial['per_page'] ) ? (int) $initial['per_page'] : 12;
	$total     = isset( $initial['topics_total'] ) ? (int) $initial['topics_total'] : 0;
	$max_pages = $total > 0 ? (int) ceil( $total / $per_page ) : 1;

	if ( $max_pages > 1 ) : ?>
		<nav class="blog-pagination" aria-label="<?php esc_attr_e( 'Pagination', 'brio-guiseppe' ); ?>" data-blog-pagination>
			<?php if ( $paged > 1 ) : ?>
				<a class="blog-pagination__link blog-pagination__link--prev"
				   href="<?php echo esc_url( get_pagenum_link( $paged - 1 ) ); ?>"
				   rel="prev">
					<?php esc_html_e( '← Précédent', 'brio-guiseppe' ); ?>
				</a>
			<?php endif; ?>

			<span class="blog-pagination__info">
				<?php printf(
					esc_html__( 'Page %1$d sur %2$d', 'brio-guiseppe' ),
					$paged,
					$max_pages
				); ?>
			</span>

			<?php if ( $paged < $max_pages ) : ?>
				<a class="blog-pagination__link blog-pagination__link--next"
				   href="<?php echo esc_url( get_pagenum_link( $paged + 1 ) ); ?>"
				   rel="next">
					<?php esc_html_e( 'Suivant →', 'brio-guiseppe' ); ?>
				</a>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

</section>
