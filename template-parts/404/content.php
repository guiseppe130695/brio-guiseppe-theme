<?php
/**
 * 404 — Contenu principal.
 *
 * Fidèle au JSON 404.json :
 *   • Image 404 centrée (40% desktop, 75% mobile)
 *   • Titre H2 Nebeco (color-primary, 70% width)
 *   • Texte corps centré (44% desktop)
 *   • Bouton CTA → WhatsApp audit gratuit
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$wa_url = 'https://wa.me/212770740311?text=Bonjour%2C%20je%20souhaite%20r%C3%A9server%20un%20appel%20strat%C3%A9gique%20gratuit%20pour%20analyser%20les%20performances%20de%20mon%20h%C3%B4tel%20%2F%20riad%20et%20identifier%20des%20opportunit%C3%A9s%20d%E2%80%99augmentation%20des%20r%C3%A9servations%20directes';

$image_id  = 1140;
$image_url = wp_get_attachment_image_url( $image_id, 'large' );
?>
<section class="not-found">

	<?php if ( $image_url ) : ?>
		<img
			class="not-found__image"
			src="<?php echo esc_url( $image_url ); ?>"
			alt="<?php esc_attr_e( 'Page introuvable', 'brio-guiseppe' ); ?>"
			width="600"
			loading="eager"
		/>
	<?php endif; ?>

	<h1 class="not-found__title">
		<?php esc_html_e( "Cette page n’existe pas… mais vos revenus, eux, peuvent augmenter", 'brio-guiseppe' ); ?>
	</h1>

	<p class="not-found__body">
		<?php esc_html_e( 'Vous êtes peut-être arrivé ici par erreur.', 'brio-guiseppe' ); ?><br>
		<?php esc_html_e( 'Pendant ce temps, vous perdez peut-être des réservations directes chaque jour.', 'brio-guiseppe' ); ?>
	</p>

	<a href="<?php echo esc_url( $wa_url ); ?>"
	   class="btn-cta not-found__cta"
	   target="_blank"
	   rel="noopener noreferrer">
		<?php esc_html_e( 'Réserver mon audit gratuit', 'brio-guiseppe' ); ?>
	</a>

</section>
