<?php
/**
 * Single post — Bloc auteur (E-E-A-T)
 *
 * Photo + nom + bio + lien articles + icône LinkedIn (champ user_url ou
 * meta _brio_linkedin renseigné dans le profil WordPress de l'auteur).
 * N'affiche rien si la bio est vide.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$author_id  = get_the_author_meta( 'ID' );
$author_bio = get_the_author_meta( 'description' );

if ( empty( $author_bio ) ) {
	return;
}

$author_name     = get_the_author_meta( 'display_name' );
$author_url      = get_author_posts_url( $author_id );
$avatar_url      = get_avatar_url( $author_id, [ 'size' => 120 ] );
$linkedin_url    = get_the_author_meta( 'linkedin', $author_id );

/* Fallback : si le champ linkedin est vide, on essaie le champ "Site web"
   du profil WP uniquement s'il pointe vers linkedin.com. */
if ( ! $linkedin_url ) {
	$user_url = get_the_author_meta( 'user_url', $author_id );
	if ( $user_url && str_contains( $user_url, 'linkedin.com' ) ) {
		$linkedin_url = $user_url;
	}
}
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

		<div class="post-author__name-row">
			<a class="post-author__name" href="<?php echo esc_url( $author_url ); ?>">
				<?php echo esc_html( $author_name ); ?>
			</a>
			<?php if ( $linkedin_url ) : ?>
				<a class="post-author__linkedin"
				   href="<?php echo esc_url( $linkedin_url ); ?>"
				   target="_blank"
				   rel="noopener noreferrer me"
				   aria-label="<?php printf( esc_attr__( 'Profil LinkedIn de %s', 'brio-guiseppe' ), esc_attr( $author_name ) ); ?>">
					<svg class="post-author__linkedin-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
						<path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
					</svg>
				</a>
			<?php endif; ?>
		</div>

		<p class="post-author__bio"><?php echo esc_html( wp_strip_all_tags( $author_bio ) ); ?></p>

		<a class="post-author__link" href="<?php echo esc_url( $author_url ); ?>">
			<?php esc_html_e( 'Voir tous ses articles →', 'brio-guiseppe' ); ?>
		</a>

	</div>

</aside>
