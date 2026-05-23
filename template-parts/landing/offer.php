<?php
/**
 * Landing Section — Offer
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_landing_offer_data();
?>
<section class="landing-offer" aria-labelledby="landing-offer-title">
	<div class="container">

		<h2 id="landing-offer-title" class="landing-offer__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<?php if ( ! empty( $data['subtitle'] ) ) : ?>
			<p class="landing-offer__lead"><?php echo esc_html( $data['subtitle'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $data['price'] ) ) : ?>
			<p class="landing-offer__price"><?php echo esc_html( $data['price'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $data['features'] ) ) : ?>
			<ul class="landing-offer__features">
				<?php foreach ( $data['features'] as $feature ) : ?>
					<li><?php echo esc_html( $feature ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( ! empty( $data['cta_label'] ) ) : ?>
			<a href="<?php echo esc_url( $data['cta_url'] ); ?>" class="btn btn-primary landing-offer__cta">
				<?php echo esc_html( $data['cta_label'] ); ?>
			</a>
		<?php endif; ?>

	</div>
</section>
