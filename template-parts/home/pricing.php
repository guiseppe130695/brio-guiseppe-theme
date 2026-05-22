<?php
/**
 * Pricing Section
 *
 * Header row (overline + h2 on left, top CTA on right) followed by a
 * 3-card grid: light / dark (featured) / light. Each card lists rooms
 * tag → plan title → price → tagline → CTA → includes list → ideal-for.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_pricing_data();
?>

<section class="home-section home-pricing" id="pricing">
	<div class="container">

		<?php /* --- Header row --- */ ?>
		<header class="home-pricing__header">
			<div class="home-pricing__intro">
				<?php if ( ! empty( $data['overline'] ) ) : ?>
					<p class="overline home-pricing__overline">
						<?php echo esc_html( $data['overline'] ); ?>
					</p>
				<?php endif; ?>
				<?php if ( ! empty( $data['heading'] ) ) : ?>
					<h2 class="home-pricing__heading">
						<?php echo esc_html( $data['heading'] ); ?>
					</h2>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $data['cta'] ) ) : ?>
				<a href="<?php echo esc_url( $data['cta']['href'] ); ?>"
				   class="btn-cta home-pricing__cta-top"
				   target="_blank"
				   rel="noopener noreferrer">
					<?php echo esc_html( $data['cta']['label'] ); ?>
				</a>
			<?php endif; ?>
		</header>

		<?php /* --- 3-card grid --- */ ?>
		<?php if ( ! empty( $data['plans'] ) ) : ?>
			<div class="home-pricing__grid">
				<?php foreach ( $data['plans'] as $plan ) :
					$variant = isset( $plan['variant'] ) ? $plan['variant'] : 'light';
					?>
					<article class="home-pricing__card home-pricing__card--<?php echo esc_attr( $variant ); ?>">

						<?php if ( ! empty( $plan['rooms'] ) ) : ?>
							<p class="home-pricing__rooms">
								<strong><?php echo esc_html( $plan['rooms'] ); ?></strong>
							</p>
						<?php endif; ?>

						<?php if ( ! empty( $plan['title'] ) ) : ?>
							<h3 class="home-pricing__plan">
								<?php echo esc_html( $plan['title'] ); ?>
							</h3>
						<?php endif; ?>

						<?php if ( ! empty( $plan['price'] ) ) : ?>
							<p class="home-pricing__price">
								<?php if ( ! empty( $plan['price_prefix'] ) ) : ?>
									<span class="home-pricing__price-prefix"><?php echo esc_html( $plan['price_prefix'] ); ?></span>
								<?php endif; ?>
								<span class="home-pricing__price-value"><?php echo esc_html( $plan['price'] ); ?></span>
							</p>
						<?php endif; ?>

						<?php if ( ! empty( $plan['tagline'] ) ) : ?>
							<p class="home-pricing__tagline">
								<?php echo esc_html( $plan['tagline'] ); ?>
							</p>
						<?php endif; ?>

						<?php if ( ! empty( $plan['cta'] ) ) : ?>
							<a href="<?php echo esc_url( $plan['cta']['href'] ); ?>"
							   class="btn-cta<?php echo 'dark' === $variant ? ' btn-cta--dark' : ''; ?>"
							   target="_blank"
							   rel="noopener noreferrer">
								<?php echo esc_html( $plan['cta']['label'] ); ?>
							</a>
						<?php endif; ?>

						<hr class="home-pricing__divider">

						<p class="home-pricing__list-label">
							<?php esc_html_e( 'Ce qui est inclus :', 'brio-guiseppe' ); ?>
						</p>

						<?php if ( ! empty( $plan['includes'] ) ) : ?>
							<ul class="home-pricing__includes">
								<?php foreach ( $plan['includes'] as $item ) : ?>
									<li>
										<?php echo brio_icon( 'dot' ); ?>
										<span><?php echo esc_html( $item ); ?></span>
									</li>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<hr class="home-pricing__divider">

						<p class="home-pricing__list-label">
							<?php esc_html_e( 'Idéal pour :', 'brio-guiseppe' ); ?>
						</p>

						<?php if ( ! empty( $plan['ideal'] ) ) : ?>
							<p class="home-pricing__ideal">
								<?php echo esc_html( $plan['ideal'] ); ?>
							</p>
						<?php endif; ?>

					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>
