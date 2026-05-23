<?php
/**
 * Single post — Contenu + footer (tags, partage social, navigation).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_url   = urlencode( get_permalink() );
$post_title = urlencode( get_the_title() );
$tags       = get_the_tags();
?>
<div class="post-content">

	<div class="post-content__body">
		<?php the_content(); ?>
	</div>

	<footer class="post-content__footer">

		<!-- Tags + partage social -->
		<div class="post-footer">

			<!-- Tags -->
			<div class="post-footer__tags">
				<?php if ( ! empty( $tags ) ) : ?>
					<p class="post-footer__tags-label">
						<?php esc_html_e( 'Sujets abordés dans cet article :', 'brio-guiseppe' ); ?>
					</p>
					<ul class="post-footer__tag-list">
						<?php foreach ( $tags as $tag ) : ?>
							<li>
								<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<!-- Partage social -->
			<div class="post-footer__share">
				<p class="post-footer__share-label">
					<?php esc_html_e( 'Vous avez trouvé ça utile ? Partagez-le', 'brio-guiseppe' ); ?>
				</p>
				<ul class="post-footer__share-list" aria-label="<?php esc_attr_e( 'Partager cet article', 'brio-guiseppe' ); ?>">
					<li>
						<a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>"
						   class="post-footer__share-btn post-footer__share-btn--fb"
						   target="_blank" rel="noopener noreferrer"
						   aria-label="<?php esc_attr_e( 'Partager sur Facebook', 'brio-guiseppe' ); ?>">
							Fb
						</a>
					</li>
					<li>
						<a href="https://twitter.com/intent/tweet?url=<?php echo $post_url; ?>&text=<?php echo $post_title; ?>"
						   class="post-footer__share-btn post-footer__share-btn--tw"
						   target="_blank" rel="noopener noreferrer"
						   aria-label="<?php esc_attr_e( 'Partager sur Twitter / X', 'brio-guiseppe' ); ?>">
							Tw
						</a>
					</li>
					<li>
						<a href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $post_url; ?>&title=<?php echo $post_title; ?>"
						   class="post-footer__share-btn post-footer__share-btn--ln"
						   target="_blank" rel="noopener noreferrer"
						   aria-label="<?php esc_attr_e( 'Partager sur LinkedIn', 'brio-guiseppe' ); ?>">
							Ln
						</a>
					</li>
				</ul>
			</div>

		</div>

		<!-- Navigation prev / next -->
		<nav class="post-nav" aria-label="<?php esc_attr_e( 'Navigation entre articles', 'brio-guiseppe' ); ?>">
			<?php
			$prev = get_previous_post();
			$next = get_next_post();
			?>
			<?php if ( $prev ) : ?>
				<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="post-nav__link post-nav__link--prev">
					<span class="post-nav__label"><?php esc_html_e( 'Retour à l\'article précédent', 'brio-guiseppe' ); ?></span>
					<span class="post-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $next ) : ?>
				<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="post-nav__link post-nav__link--next">
					<span class="post-nav__label"><?php esc_html_e( 'Découvrir l\'article suivant', 'brio-guiseppe' ); ?></span>
					<span class="post-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
				</a>
			<?php endif; ?>
		</nav>

	</footer>

</div>
