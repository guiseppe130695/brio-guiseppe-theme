<?php
/**
 * Outils — Related resources
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_related_data();
if ( empty( $data['items'] ) ) {
	return;
}
?>
<section class="outils-related" aria-labelledby="outils-related-title">
	<div class="container">

		<h2 id="outils-related-title" class="outils-related__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<ul class="outils-related__list">
			<?php foreach ( $data['items'] as $item ) : ?>
				<li class="outils-related__item">
					<a href="<?php echo esc_url( $item['url'] ?? '#' ); ?>" class="outils-related__link">
						<strong><?php echo esc_html( $item['label'] ?? '' ); ?></strong>
						<?php if ( ! empty( $item['desc'] ) ) : ?>
							<span><?php echo esc_html( $item['desc'] ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
