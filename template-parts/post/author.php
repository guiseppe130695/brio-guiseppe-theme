<?php
/**
 * Single post — Bloc auteur (E-E-A-T)
 *
 * Photo + nom + bio + lien vers tous les articles de l'auteur.
 * N'affiche rien si la bio est vide (pas de bloc creux).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$author_id  = get_the_author_meta( 'ID' );
$author_bio = get_the_author_meta( 'description' );

if ( empty( $author_bio ) ) {
	return;
}

$author_name   = get_the_author_meta( 'display_name' );
$author_url    = get_author_posts_url( $author_id );
$avatar_url    = get_avatar_url( $author_id, [ 'size' => 120 ] );
?>
<aside class="post-author" aria-label="<?php esc_attr_e( 'À propos de l\'auteur', 'brio-guiseppe' ); ?>">

	<?php if ( $avatar_url ) : ?>
		<img
			class="post-author__avatar"
			src="<?php echo esc_url( $avatar_url ); ?>"
			alt="<?php echo esc_attr( $author_name ); ?>"
			width="80"
			height="80"
			loading="lazy"
		/>
	<?php endif; ?>

	<div class="post-author__body">
		<p class="post-author__label"><?php esc_html_e( 'Écrit par', 'brio-guiseppe' ); ?></p>
		<a class="post-author__name" href="<?php echo esc_url( $author_url ); ?>">
			<?php echo esc_html( $author_name ); ?>
		</a>
		<p class="post-author__bio"><?php echo esc_html( wp_strip_all_tags( $author_bio ) ); ?></p>
		<a class="post-author__link" href="<?php echo esc_url( $author_url ); ?>">
			<?php esc_html_e( 'Voir tous ses articles →', 'brio-guiseppe' ); ?>
		</a>
	</div>

</aside>
