<?php
/**
 * Fun Facts ("Résultats") Section
 *
 * Yellow accent section with overline + heading + a 4-card asymmetric
 * grid of animated counters (34/34/32 columns, cards 1-2 full height,
 * cards 3-4 stacked in the third column).
 *
 * The counter number animation is a small inline IntersectionObserver
 * script at the bottom — runs once per counter on viewport entry.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_fun_facts_data();
?>

<section class="home-section home-fun-facts" id="fun-facts">
	<div class="container">

		<?php if ( ! empty( $data['overline'] ) ) : ?>
			<p class="overline home-fun-facts__overline">
				<?php echo esc_html( $data['overline'] ); ?>
			</p>
		<?php endif; ?>

		<?php if ( ! empty( $data['heading'] ) ) : ?>
			<h2 class="home-fun-facts__heading">
				<?php echo esc_html( $data['heading'] ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $data['cards'] ) ) : ?>
			<div class="home-fun-facts__grid">
				<?php foreach ( $data['cards'] as $card ) :
					$variant = isset( $card['variant'] ) ? $card['variant'] : 'light';
					$style   = ( 'image' === $variant && ! empty( $card['bg'] ) )
						? ' style="background-image:url(' . esc_url( $card['bg'] ) . ')"'
						: '';
					?>
					<article class="home-fun-facts__card home-fun-facts__card--<?php echo esc_attr( $variant ); ?>"<?php echo $style; ?>>
						<img class="home-fun-facts__icon"
						     src="<?php echo esc_url( $card['icon'] ); ?>"
						     alt=""
						     loading="lazy"
						     <?php echo brio_img_dims( $card['icon'] ); ?>>
						<div class="home-fun-facts__counter">
							<span class="home-fun-facts__value">
								<?php if ( ! empty( $card['prefix'] ) ) : ?>
									<span class="home-fun-facts__prefix"><?php echo esc_html( $card['prefix'] ); ?></span>
								<?php endif; ?>
								<span class="home-fun-facts__number" data-counter="<?php echo esc_attr( $card['number'] ); ?>">0</span>
								<?php if ( ! empty( $card['suffix'] ) ) : ?>
									<span class="home-fun-facts__suffix"><?php echo esc_html( $card['suffix'] ); ?></span>
								<?php endif; ?>
							</span>
							<p class="home-fun-facts__title">
								<?php echo esc_html( $card['title'] ); ?>
							</p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</div>
</section>

