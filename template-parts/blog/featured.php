<?php
/**
 * Blog — Featured post (article à la une)
 *
 * Reproduit le premier widget ElementsKit du container "Blog" :
 *   - fond accent global de la section
 *   - card horizontale (image gauche, texte droite) — border-radius 25px,
 *     l'image colle au bord gauche (radius 25 0 0 25)
 *   - meta = auteur + date, titre Nebeco, extrait Manrope, lien "Read More"
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_obj = brio_get_blog_featured_post();

if ( ! $post_obj ) {
	return;
}

$permalink = get_permalink( $post_obj );
$thumb     = get_the_post_thumbnail_url( $post_obj, 'large' );
$author    = get_the_author_meta( 'display_name', $post_obj->post_author );
$excerpt   = has_excerpt( $post_obj )
	? get_the_excerpt( $post_obj )
	: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post_obj->post_content ) ), 33, '…' );
?>
<section class="blog-feature" aria-label="<?php esc_attr_e( 'Article à la une', 'brio-guiseppe' ); ?>">
	<article class="blog-feature__card">

		<?php if ( $thumb ) : ?>
			<a class="blog-feature__media" href="<?php echo esc_url( $permalink ); ?>" aria-hidden="true" tabindex="-1">
				<img src="<?php echo esc_url( $thumb ); ?>" alt="" loading="eager" decoding="async" />
			</a>
		<?php endif; ?>

		<div class="blog-feature__body">
			<ul class="blog-feature__meta">
				<li><?php echo esc_html( $author ); ?></li>
				<li>
					<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C, $post_obj ) ); ?>">
						<?php echo esc_html( get_the_date( '', $post_obj ) ); ?>
					</time>
				</li>
			</ul>

			<h2 class="blog-feature__title">
				<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( get_the_title( $post_obj ) ); ?></a>
			</h2>

			<p class="blog-feature__excerpt"><?php echo esc_html( $excerpt ); ?></p>

			<a class="blog-feature__cta" href="<?php echo esc_url( $permalink ); ?>">
				<?php esc_html_e( 'Read More', 'brio-guiseppe' ); ?>
			</a>
		</div>
	</article>
</section>
