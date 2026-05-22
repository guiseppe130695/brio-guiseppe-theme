<?php
/**
 * Showcase ("Image" / "Video") Section
 *
 * Pure-visual interlude: a large rounded media container with a background
 * image, plus two decorative images that overflow above (top-left) and
 * below (bottom-right) the container.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_showcase_data();
if ( empty( $data['bg'] ) ) {
	return;
}
?>

<section class="home-section home-section--flush-top home-showcase" id="showcase" aria-hidden="true">
	<div class="container">
		<div class="home-showcase__media" style="background-image:url(<?php echo esc_url( $data['bg'] ); ?>)">
			<?php foreach ( $data['images'] as $img ) : ?>
				<img src="<?php echo esc_url( $img['url'] ); ?>"
				     alt="<?php echo esc_attr( $img['alt'] ); ?>"
				     class="home-showcase__img home-showcase__img--<?php echo esc_attr( $img['position'] ); ?>"
				     loading="lazy">
			<?php endforeach; ?>
		</div>
	</div>
</section>
