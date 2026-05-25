<?php
/**
 * Landing Section — Contenu unique par landing
 *
 * Affiche le texte libre stocké dans _brio_landing_unique_content. C'est LE
 * champ qui doit varier sur chaque landing pour passer les filtres
 * anti-scaled-content de Google. Quand le champ est vide, la section ne sort
 * rien (pas de placeholder visible côté front).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$post_id = get_queried_object_id();
$content = brio_meta_get( $post_id, 'landing', 'unique', 'content', '' );
$heading = brio_meta_get( $post_id, 'landing', 'unique', 'heading', '' );

if ( '' === trim( (string) $content ) ) {
	return;
}
?>
<section class="landing-unique-content" aria-labelledby="landing-unique-title">
	<div class="container landing-unique-content__inner">
		<?php if ( ! empty( $heading ) ) : ?>
			<h2 id="landing-unique-title" class="landing-unique-content__title">
				<?php echo esc_html( $heading ); ?>
			</h2>
		<?php endif; ?>
		<div class="landing-unique-content__body">
			<?php echo wp_kses_post( wpautop( $content ) ); ?>
		</div>
	</div>
</section>
