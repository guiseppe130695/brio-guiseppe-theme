<?php
/**
 * Landing Section — Benefits
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_landing_benefits_data();
if ( empty( $data['items'] ) ) {
	return;
}
?>
<section class="landing-benefits" aria-labelledby="landing-benefits-title">
	<div class="container">

		<h2 id="landing-benefits-title" class="landing-benefits__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<ul class="landing-benefits__grid">
			<?php foreach ( $data['items'] as $item ) : ?>
				<li class="landing-benefits__item">
					<?php if ( ! empty( $item['icon'] ) ) : ?>
						<img src="<?php echo esc_url( $item['icon'] ); ?>" alt="" class="landing-benefits__icon" loading="lazy" />
					<?php endif; ?>
					<?php if ( ! empty( $item['title'] ) ) : ?>
						<h3 class="landing-benefits__item-title"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $item['desc'] ) ) : ?>
						<p class="landing-benefits__item-desc"><?php echo esc_html( $item['desc'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
