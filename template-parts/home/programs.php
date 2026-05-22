<?php
/**
 * Programs Section ("Solutions")
 *
 * Dark hero-style block: overline + heading, then a 4-item native
 * <details> accordion, followed by a primary CTA + reassurance note.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_programs_data();
?>

<section class="home-section home-programs" id="programs">
	<div class="container">

		<?php if ( ! empty( $data['overline'] ) ) : ?>
			<p class="overline home-programs__overline">
				<?php echo esc_html( $data['overline'] ); ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $data['heading'] ) ) : ?>
			<h2 class="home-programs__heading">
				<?php echo esc_html( $data['heading'] ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $data['items'] ) ) : ?>
			<ul class="home-programs__list">
				<?php foreach ( $data['items'] as $i => $item ) : ?>
					<li class="home-programs__item">
						<details class="home-programs__details"<?php echo 0 === $i ? ' open' : ''; ?>>
							<summary class="home-programs__summary">
								<span class="home-programs__title"><?php echo esc_html( $item['title'] ); ?></span>
							</summary>
							<div class="home-programs__content">
								<?php echo wp_kses_post( $item['content'] ); ?>
							</div>
						</details>
					</li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

		<?php if ( ! empty( $data['cta'] ) ) : ?>
			<a href="<?php echo esc_url( $data['cta']['href'] ); ?>"
			   class="btn-cta home-programs__btn"
			   target="_blank"
			   rel="noopener noreferrer">
				<?php echo esc_html( $data['cta']['label'] ); ?>
			</a>
		<?php endif; ?>

		<?php if ( ! empty( $data['note'] ) ) : ?>
			<p class="home-programs__note">
				<?php echo esc_html( $data['note'] ); ?>
			</p>
		<?php endif; ?>

	</div>
</section>
