<?php
/**
 * Landing Section — Hero
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero    = brio_get_landing_hero_data( get_queried_object_id() );
$rating  = brio_get_landing_rating_data( get_queried_object_id() );
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
							<?php
							if ( ! empty( $rating['count'] ) ) {
								echo esc_html( sprintf(
									/* translators: 1: rating value (e.g. 5), 2: count (e.g. 12), 3: caption */
									__( '%1$s/5 — %2$d recommandations · %3$s', 'brio-guiseppe' ),
									number_format_i18n( $rating['value'], ( fmod( $rating['value'], 1 ) === 0.0 ? 0 : 1 ) ),
									(int) $rating['count'],
									$rating['caption']
								) );
							} else {
								echo esc_html( $rating['caption'] );
							}
							?>
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
			<?php else :
				// Parse field errors from URL
				$lf_errors = [];
				if ( isset( $_GET['contact'], $_GET['lf_err'] ) && $_GET['contact'] === 'error' ) {
					$lf_errors = array_flip( explode( ',', sanitize_text_field( wp_unslash( $_GET['lf_err'] ) ) ) );
				}
				$err_name    = isset( $lf_errors['name_length'] ) || isset( $lf_errors['name_format'] );
				$err_email   = isset( $lf_errors['email_invalid'] ) || isset( $lf_errors['email_disposable'] );
				$err_hotel   = isset( $lf_errors['hotel_length'] ) || isset( $lf_errors['hotel_html'] );
				$err_message = isset( $lf_errors['message_too_long'] ) || isset( $lf_errors['message_html'] ) || isset( $lf_errors['message_spam'] );
			?>
				<?php if ( ! empty( $lf_errors ) ) : ?>
					<p class="landing-form__error">
						<?php esc_html_e( '⚠ Certains champs sont invalides. Vérifiez les champs en rouge.', 'brio-guiseppe' ); ?>
					</p>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>

					<?php wp_nonce_field( 'brio_landing_contact', 'brio_landing_contact_nonce' ); ?>
					<input type="hidden" name="action" value="brio_landing_contact" />
					<input type="hidden" name="redirect_to" value="<?php echo esc_url( get_permalink() ); ?>" />
					<div style="position:absolute;left:-9999px;opacity:0;pointer-events:none" aria-hidden="true">
						<input type="text" name="lf_website" value="" tabindex="-1" autocomplete="off" />
					</div>

					<input class="landing-form__input<?php echo $err_name ? ' landing-form__input--error' : ''; ?>"
					       type="text" name="lf_name" autocomplete="given-name" required
					       placeholder="<?php esc_attr_e( 'Votre prénom', 'brio-guiseppe' ); ?>" />

					<input class="landing-form__input<?php echo $err_email ? ' landing-form__input--error' : ''; ?>"
					       type="email" name="lf_email" autocomplete="email" required
					       placeholder="<?php esc_attr_e( 'Email professionnel', 'brio-guiseppe' ); ?>" />

					<input class="landing-form__input<?php echo $err_hotel ? ' landing-form__input--error' : ''; ?>"
					       type="text" name="lf_hotel" autocomplete="organization"
					       placeholder="<?php esc_attr_e( 'Nom de votre établissement', 'brio-guiseppe' ); ?>" />

					<textarea class="landing-form__input landing-form__textarea<?php echo $err_message ? ' landing-form__input--error' : ''; ?>"
					          name="lf_message" rows="3"
					          placeholder="<?php esc_attr_e( 'Votre situation en quelques mots', 'brio-guiseppe' ); ?>"></textarea>

					<button type="submit" class="landing-form__submit">
						<?php esc_html_e( 'Réserver mon audit gratuit →', 'brio-guiseppe' ); ?>
					</button>

				</form>
			<?php endif; ?>

		</div>

	</div>
</section>
