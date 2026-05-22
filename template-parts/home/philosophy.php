<?php
/**
 * Philosophy ("Approche") Section
 *
 * Two-column block: visual with floating yellow "mission" card on the left,
 * text stack with 3 feature icon-boxes on the right.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_philosophy_data();
?>

<section class="home-section home-philosophy" id="philosophy">
	<div class="container">

		<?php /* Left column — image + mission card (bottom-left) */ ?>
		<aside class="home-philosophy__visual"<?php echo ! empty( $data['visual'] ) ? ' style="background-image:url(' . esc_url( $data['visual'] ) . ')"' : ''; ?>>
			<?php if ( ! empty( $data['mission'] ) ) : ?>
				<div class="home-philosophy__mission">
					<?php if ( ! empty( $data['mission']['label'] ) ) : ?>
						<p class="overline home-philosophy__mission-label">
							<?php echo esc_html( $data['mission']['label'] ); ?>
						</p>
					<?php endif; ?>
					<?php if ( ! empty( $data['mission']['text'] ) ) : ?>
						<p class="home-philosophy__mission-text">
							<?php echo esc_html( $data['mission']['text'] ); ?>
						</p>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</aside>

		<?php /* Right column — text + features */ ?>
		<div class="home-philosophy__content">

			<?php if ( ! empty( $data['overline'] ) ) : ?>
				<p class="overline home-philosophy__overline">
					<?php echo esc_html( $data['overline'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['heading'] ) ) : ?>
				<h2 class="home-philosophy__heading">
					<?php echo esc_html( $data['heading'] ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="home-philosophy__description">
					<?php echo esc_html( $data['description'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['features'] ) ) : ?>
				<ul class="home-philosophy__features">
					<?php foreach ( $data['features'] as $f ) : ?>
						<li class="home-philosophy__feature">
							<?php echo brio_icon( $f['icon'], [ 'class' => 'home-philosophy__icon' ] ); ?>
							<div class="home-philosophy__feature-body">
								<h3 class="home-philosophy__feature-title">
									<?php echo esc_html( $f['title'] ); ?>
								</h3>
								<p class="home-philosophy__feature-text">
									<?php echo esc_html( $f['text'] ); ?>
								</p>
							</div>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		</div>

	</div>
</section>
