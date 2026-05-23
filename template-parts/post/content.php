<?php
/**
 * Single post — Corps de l'article + footer (tags, partage, navigation).
 *
 * Structure HTML sémantique :
 *   <article>           — contenu principal (hentry + Schema Article)
 *     <div.entry-content> — corps éditorial (convention WP + microformat)
 *     <aside>           — bloc auteur (information complémentaire)
 *     <section>         — articles liés
 *     <footer>          — tags + partage social
 *   <nav>               — navigation prev/next (hors article, niveau page)
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_id    = get_the_ID();
$post_url   = urlencode( get_permalink() );
$post_title = urlencode( get_the_title() );
$tags       = get_the_tags();
?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-content hentry' ); ?>>

	<div class="post-content__body entry-content">
		<?php the_content(); ?>
	</div>

	<?php get_template_part( 'template-parts/post/author' ); ?>
	<?php get_template_part( 'template-parts/post/related' ); ?>

	<footer class="post-content__footer">

		<div class="post-footer">

			<!-- Tags -->
			<?php if ( ! empty( $tags ) ) : ?>
				<div class="post-footer__tags">
					<p class="post-footer__tags-label">
						<?php esc_html_e( 'Sujets abordés dans cet article :', 'brio-guiseppe' ); ?>
					</p>
					<ul class="post-footer__tag-list" aria-label="<?php esc_attr_e( 'Tags', 'brio-guiseppe' ); ?>">
						<?php foreach ( $tags as $tag ) : ?>
							<li>
								<a href="<?php echo esc_url( get_tag_link( $tag->term_id ) ); ?>" rel="tag">
									<?php echo esc_html( $tag->name ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

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

	</footer>

</article>

<!-- Navigation prev / next — hors <article>, niveau page -->
<nav class="post-nav" aria-label="<?php esc_attr_e( 'Navigation entre articles', 'brio-guiseppe' ); ?>">
	<?php
	$prev = get_previous_post();
	$next = get_next_post();
	?>
	<?php if ( $prev ) : ?>
		<a href="<?php echo esc_url( get_permalink( $prev ) ); ?>" class="post-nav__link post-nav__link--prev" rel="prev">
			<span class="post-nav__label"><?php esc_html_e( 'Retour à l\'article précédent', 'brio-guiseppe' ); ?></span>
			<span class="post-nav__title"><?php echo esc_html( get_the_title( $prev ) ); ?></span>
		</a>
	<?php endif; ?>
	<?php if ( $next ) : ?>
		<a href="<?php echo esc_url( get_permalink( $next ) ); ?>" class="post-nav__link post-nav__link--next" rel="next">
			<span class="post-nav__label"><?php esc_html_e( 'Découvrir l\'article suivant', 'brio-guiseppe' ); ?></span>
			<span class="post-nav__title"><?php echo esc_html( get_the_title( $next ) ); ?></span>
		</a>
	<?php endif; ?>
</nav>
