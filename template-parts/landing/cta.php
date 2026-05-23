<?php
/**
 * Landing Section — Final CTA
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_landing_cta_data();
?>
<section class="landing-cta" aria-labelledby="landing-cta-title">
	<div class="container">
		<div class="landing-cta__card">

			<h2 id="landing-cta-title" class="landing-cta__heading">
				<?php echo esc_html( $data['heading'] ); ?>
			</h2>

			<?php if ( ! empty( $data['tagline'] ) ) : ?>
				<p class="landing-cta__tagline"><?php echo esc_html( $data['tagline'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['label'] ) ) : ?>
				<a href="<?php echo esc_url( $data['url'] ); ?>" class="btn-cta btn-cta--lg landing-cta__btn">
					<?php echo esc_html( $data['label'] ); ?>
				</a>
			<?php endif; ?>

		</div>
	</div>
</section>
