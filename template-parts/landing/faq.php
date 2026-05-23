<?php
/**
 * Landing Section — FAQ
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_landing_faq_data();
if ( empty( $data['items'] ) ) {
	return;
}
?>
<section class="landing-faq" aria-labelledby="landing-faq-title">
	<div class="container">

		<h2 id="landing-faq-title" class="landing-faq__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<ul class="landing-faq__list">
			<?php foreach ( $data['items'] as $item ) : ?>
				<li class="landing-faq__item">
					<details>
						<summary><?php echo esc_html( $item['q'] ?? '' ); ?></summary>
						<div class="landing-faq__answer">
							<?php echo wp_kses_post( nl2br( esc_html( $item['a'] ?? '' ) ) ); ?>
						</div>
					</details>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</section>
