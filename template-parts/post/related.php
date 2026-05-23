<?php
/**
 * Single post — Articles liés (même catégorie, hors article courant)
 *
 * Affiche jusqu'à 3 articles de la même catégorie. N'affiche rien
 * s'il n'y a pas d'autres articles dans la catégorie.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_id    = get_the_ID();
$categories = get_the_category( $post_id );

if ( empty( $categories ) ) {
	return;
}

$related = new WP_Query( [
	'post_type'           => 'post',
	'post_status'         => 'publish',
	'posts_per_page'      => 3,
	'post__not_in'        => [ $post_id ],
	'category__in'        => wp_list_pluck( $categories, 'term_id' ),
	'orderby'             => 'date',
	'order'               => 'DESC',
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
] );

if ( ! $related->have_posts() ) {
	return;
}
?>
<section class="post-related" aria-labelledby="post-related-title">

	<h2 id="post-related-title" class="post-related__title">
		<?php esc_html_e( 'Articles dans la même catégorie', 'brio-guiseppe' ); ?>
	</h2>

	<ul class="post-related__grid">
		<?php while ( $related->have_posts() ) : $related->the_post(); ?>
			<li class="post-related__item">
				<a href="<?php the_permalink(); ?>" class="post-related__card">
					<div class="post-related__thumb">
						<img
							src="<?php echo esc_url( brio_post_thumbnail_url( get_the_ID(), 'medium' ) ); ?>"
							alt="<?php the_title_attribute(); ?>"
							loading="lazy"
						/>
					</div>
					<div class="post-related__info">
						<span class="post-related__date"><?php echo esc_html( get_the_date() ); ?></span>
						<p class="post-related__card-title"><?php the_title(); ?></p>
					</div>
				</a>
			</li>
		<?php endwhile; wp_reset_postdata(); ?>
	</ul>

</section>
