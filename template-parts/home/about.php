<?php
/**
 * About Section
 *
 * Two-column layout: agency pitch (left 60%) + visual asset (right 35%).
 * Responsive: stacks on mobile with image below text.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_about_data();
?>

<section class="home-section home-about" id="about">
	<div class="container">

		<div class="home-about__content section-stack">
			<?php if ( ! empty( $data['overline'] ) ) : ?>
				<p class="overline home-about__overline section-stack__overline text-accent">
					<?php echo esc_html( $data['overline'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['heading'] ) ) : ?>
				<h2 class="home-about__heading section-stack__heading">
					<?php echo wp_kses_post( nl2br( $data['heading'] ) ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['description'] ) ) : ?>
				<p class="home-about__description section-stack__body">
					<?php echo wp_kses_post( $data['description'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['cta'] ) ) : ?>
				<a href="<?php echo esc_url( $data['cta']['href'] ); ?>"
				   class="btn-cta btn-cta--dark home-about__btn"
				   target="_blank"
				   rel="noopener noreferrer">
					<?php echo esc_html( $data['cta']['label'] ); ?>
				</a>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $data['image'] ) ) : ?>
			<img src="<?php echo esc_url( $data['image'] ); ?>"
			     alt="<?php echo esc_attr( __( 'Stop aux commissions invisibles', 'brio-guiseppe' ) ); ?>"
			     class="home-about__visual"
			     loading="lazy"
			     <?php echo brio_img_dims( $data['image'] ); ?>>
		<?php endif; ?>

	</div>
</section>
