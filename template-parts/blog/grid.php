<?php
/**
 * Blog — Recent posts grid
 *
 * Reproduit le second widget ElementsKit "elementskit-post-image-card" :
 *   - 6 articles récents (hors featured)
 *   - cards verticales image-en-haut, border-radius 25px
 *   - background #F5F0E2 → hover #173E04 (inversion couleurs)
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$posts = brio_get_blog_recent_posts();

if ( empty( $posts ) ) {
	return;
}
?>
<section class="blog-grid" aria-label="<?php esc_attr_e( 'Articles récents', 'brio-guiseppe' ); ?>">
	<?php foreach ( $posts as $post_obj ) :
		$permalink = get_permalink( $post_obj );
		$thumb     = get_the_post_thumbnail_url( $post_obj, 'medium_large' );
		$author    = get_the_author_meta( 'display_name', $post_obj->post_author );
		$excerpt   = has_excerpt( $post_obj )
			? get_the_excerpt( $post_obj )
			: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post_obj->post_content ) ), 14, '…' );
		?>
		<article class="blog-card">
			<a class="blog-card__link" href="<?php echo esc_url( $permalink ); ?>">

				<?php if ( $thumb ) : ?>
					<figure class="blog-card__media">
						<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="lazy" decoding="async" />
					</figure>
				<?php endif; ?>

				<div class="blog-card__body">
					<ul class="blog-card__meta">
						<li><?php echo esc_html( $author ); ?></li>
						<li>
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_obj ) ); ?>">
								<?php echo esc_html( get_the_date( '', $post_obj ) ); ?>
							</time>
						</li>
					</ul>

					<h3 class="blog-card__title"><?php echo esc_html( get_the_title( $post_obj ) ); ?></h3>

					<p class="blog-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>

					<span class="blog-card__cta"><?php esc_html_e( 'Read More', 'brio-guiseppe' ); ?></span>
				</div>

			</a>
		</article>
	<?php endforeach; ?>
</section>
