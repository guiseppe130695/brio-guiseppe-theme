<?php
/**
 * Landing Section — Hero
 *
 * Pure renderer. All content is read from post meta via
 * brio_get_landing_hero_data(). Editable in the "Landing — Hero" meta box on
 * any page using the Landing Page template.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero = brio_get_landing_hero_data();
?>
<section class="landing-hero" aria-labelledby="landing-hero-title">
	<div class="container">

		<h1 id="landing-hero-title" class="landing-hero__title">
			<?php echo esc_html( $hero['title'] ); ?>
		</h1>

		<?php if ( ! empty( $hero['subtitle'] ) ) : ?>
			<p class="landing-hero__lead"><?php echo esc_html( $hero['subtitle'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $hero['cta_label'] ) ) : ?>
			<div class="landing-hero__actions">
				<a href="<?php echo esc_url( $hero['cta_url'] ); ?>" class="btn btn-primary">
					<?php echo esc_html( $hero['cta_label'] ); ?>
				</a>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $hero['image'] ) ) : ?>
			<div class="landing-hero__media">
				<img src="<?php echo esc_url( $hero['image'] ); ?>" alt="" class="landing-hero__image" loading="eager" decoding="async" />
			</div>
		<?php endif; ?>

	</div>
</section>
