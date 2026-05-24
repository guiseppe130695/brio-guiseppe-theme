<?php
/**
 * Landing Section — Hero
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero    = brio_get_landing_hero_data( get_queried_object_id() );
$home    = brio_get_hero_data();
$rating  = $home['rating'];
$avatars = [ 'avatar_1', 'avatar_2', 'avatar_3', 'avatar_4' ];
?>
<section class="landing-hero" aria-labelledby="landing-hero-title">
	<div class="container landing-hero__inner">

		<!-- Colonne gauche -->
		<div class="landing-hero__content">

			<div class="landing-hero__social-proof">
				<div class="landing-hero__avatars" aria-hidden="true">
					<?php foreach ( $avatars as $key ) : ?>
						<img src="<?php echo esc_url( brio_asset( 'hero', $key ) ); ?>"
						     alt="" class="landing-hero__avatar"
						     width="36" height="36" decoding="async" />
					<?php endforeach; ?>
				</div>
				<div class="landing-hero__rating-text">
					<div class="landing-hero__stars" role="img" aria-label="<?php
						echo esc_attr( sprintf( __( '%1$d étoiles sur %2$d', 'brio-guiseppe' ), $rating['value'], $rating['max'] ) );
					?>"></div>
					<p class="landing-hero__rating-caption">
						<a href="<?php echo esc_url( $rating['href'] ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( $rating['caption'] ); ?>
						</a>
					</p>
				</div>
			</div>

			<h1 id="landing-hero-title" class="landing-hero__title">
				<?php echo esc_html( $hero['title'] ); ?>
			</h1>

			<?php if ( ! empty( $hero['subtitle'] ) ) : ?>
				<p class="landing-hero__subtitle">
					<?php echo wp_kses_post( $hero['subtitle'] ); ?>
				</p>
			<?php endif; ?>

		</div>

		<!-- Colonne droite — formulaire -->
		<div class="landing-hero__form">

			<p class="landing-form__heading">
				<?php esc_html_e( 'Réservez votre audit gratuit', 'brio-guiseppe' ); ?>
			</p>
			<p class="landing-form__sub">
				<?php esc_html_e( 'Dites-moi où vous en êtes, je vous réponds personnellement sous 24h avec des pistes concrètes pour votre établissement.', 'brio-guiseppe' ); ?>
				<?php esc_html_e( 'Gratuit, sans engagement, et toujours une réponse humaine.', 'brio-guiseppe' ); ?>
			</p>

			<?php if ( isset( $_GET['contact'] ) && $_GET['contact'] === 'success' ) : ?>
				<p class="landing-form__success">
					<?php esc_html_e( '✓ Message envoyé ! Nous vous répondons sous 24h.', 'brio-guiseppe' ); ?>
				</p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>

					<?php wp_nonce_field( 'brio_landing_contact', 'brio_landing_contact_nonce' ); ?>
					<input type="hidden" name="action" value="brio_landing_contact" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>" />

					<input class="landing-form__input" type="text" name="lf_name" autocomplete="given-name" required
					       placeholder="<?php esc_attr_e( 'Votre prénom', 'brio-guiseppe' ); ?>" />

					<input class="landing-form__input" type="email" name="lf_email" autocomplete="email" required
					       placeholder="<?php esc_attr_e( 'Email professionnel', 'brio-guiseppe' ); ?>" />

					<input class="landing-form__input" type="text" name="lf_hotel" autocomplete="organization"
					       placeholder="<?php esc_attr_e( 'Nom de votre établissement', 'brio-guiseppe' ); ?>" />

					<textarea class="landing-form__input landing-form__textarea" name="lf_message" rows="3"
					          placeholder="<?php esc_attr_e( 'Votre situation en quelques mots', 'brio-guiseppe' ); ?>"></textarea>

					<button type="submit" class="landing-form__submit">
						<?php esc_html_e( 'Réserver mon audit gratuit →', 'brio-guiseppe' ); ?>
					</button>

					</form>
			<?php endif; ?>

		</div>

	</div>
</section>
