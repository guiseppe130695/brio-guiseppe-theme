<?php
/**
 * Final CTA Section
 *
 * Jumbo light-green card (border-radius 50px) sitting on an accent strip.
 * Centered vertical stack: decorative icon → headline → linear tagline
 * (3 phrases joined by · on a single line) → primary CTA button.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_cta_data();
?>

<section class="home-section home-section--flush-top home-cta" id="cta">
	<div class="container">
		<div class="home-cta__card">

			<?php if ( ! empty( $data['icon'] ) ) : ?>
				<img class="home-cta__icon"
				     src="<?php echo esc_url( $data['icon'] ); ?>"
				     alt=""
				     loading="lazy"
				     aria-hidden="true">
			<?php endif; ?>

			<?php if ( ! empty( $data['heading'] ) ) : ?>
				<h2 class="home-cta__heading">
					<?php echo wp_kses_post( nl2br( esc_html( $data['heading'] ) ) ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['taglines'] ) ) : ?>
				<p class="home-cta__tagline">
					<?php echo esc_html( implode( ' · ', $data['taglines'] ) ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['cta'] ) ) : ?>
				<a href="<?php echo esc_url( $data['cta']['href'] ); ?>"
				   class="btn-cta btn-cta--lg home-cta__btn"
				   target="_blank"
				   rel="noopener noreferrer">
					<?php echo esc_html( $data['cta']['label'] ); ?>
				</a>
			<?php endif; ?>

		</div>
	</div>
</section>
