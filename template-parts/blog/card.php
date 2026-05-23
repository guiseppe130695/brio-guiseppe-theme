<?php
/**
 * Blog — Single article card
 *
 * Expects to be called inside a loop (the_post() already fired). Pulls the
 * primary category (first one) for the tag chip — adjust if a custom
 * "primary category" plugin is in use.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$cats        = get_the_category();
$primary_cat = ! empty( $cats ) ? $cats[0] : null;
?>
<article <?php post_class( 'blog-card' ); ?>>
	<a class="blog-card__link" href="<?php the_permalink(); ?>">

		<?php if ( has_post_thumbnail() ) : ?>
			<figure class="blog-card__media">
				<?php the_post_thumbnail( 'medium_large', [ 'loading' => 'lazy', 'decoding' => 'async' ] ); ?>
			</figure>
		<?php endif; ?>

		<div class="blog-card__body">
			<?php if ( $primary_cat ) : ?>
				<span class="blog-card__category"><?php echo esc_html( $primary_cat->name ); ?></span>
			<?php endif; ?>

			<h2 class="blog-card__title"><?php the_title(); ?></h2>

			<p class="blog-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22, '…' ) ); ?></p>

			<footer class="blog-card__meta">
				<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
					<?php echo esc_html( get_the_date() ); ?>
				</time>
			</footer>
		</div>

	</a>
</article>
