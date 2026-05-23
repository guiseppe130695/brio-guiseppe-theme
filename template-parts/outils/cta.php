<?php
/**
 * Outils — Final CTA
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_cta_data();
?>
<section class="outils-cta" aria-labelledby="outils-cta-title">
	<div class="container">
		<div class="outils-cta__card">

			<h2 id="outils-cta-title" class="outils-cta__heading">
				<?php echo esc_html( $data['heading'] ); ?>
			</h2>

			<?php if ( ! empty( $data['tagline'] ) ) : ?>
				<p class="outils-cta__tagline"><?php echo esc_html( $data['tagline'] ); ?></p>
			<?php endif; ?>

			<?php if ( ! empty( $data['label'] ) ) : ?>
				<a href="<?php echo esc_url( $data['url'] ); ?>" class="btn-cta btn-cta--lg outils-cta__btn">
					<?php echo esc_html( $data['label'] ); ?>
				</a>
			<?php endif; ?>

		</div>
	</div>
</section>
