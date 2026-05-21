<?php
/**
 * Partners Section
 *
 * Infinite scrolling marquee of partner/technology visuals.
 * Items are duplicated in the DOM so the CSS animation loops seamlessly.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_partners_data();
if ( empty( $data['items'] ) ) {
	return;
}
?>

<section class="home-partners" id="partners">
	<div class="container">

		<?php if ( ! empty( $data['label'] ) ) : ?>
			<p class="home-partners__label">
				<?php echo esc_html( $data['label'] ); ?>
			</p>
		<?php endif; ?>

		<div class="home-partners__viewport">
			<ul class="home-partners__track" aria-label="<?php esc_attr_e( 'Partenaires et technologies', 'brio-guiseppe' ); ?>">
				<?php foreach ( $data['items'] as $item ) : ?>
					<li class="home-partners__item">
						<img src="<?php echo esc_url( $item['url'] ); ?>"
						     alt="<?php echo esc_attr( $item['alt'] ); ?>"
						     loading="lazy">
					</li>
				<?php endforeach; ?>
				<?php foreach ( $data['items'] as $item ) : ?>
					<li class="home-partners__item" aria-hidden="true">
						<img src="<?php echo esc_url( $item['url'] ); ?>"
						     alt=""
						     loading="lazy">
					</li>
				<?php endforeach; ?>
			</ul>
		</div>

	</div>
</section>
