<?php
/**
 * FAQs Section
 *
 * Two-column block: tall image on the left, content stack on the right
 * (overline + h2 + 7-item native <details> accordion). Each accordion
 * item is a self-contained card that toggles between a cream "closed"
 * state and a dark "open" state.
 *
 * Outputs JSON-LD FAQPage schema for Google rich snippets.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_faqs_data();
?>

<section class="home-section home-faqs" id="faqs">
	<div class="container">

		<?php /* Left column — decorative image */ ?>
		<?php if ( ! empty( $data['visual'] ) ) : ?>
			<aside class="home-faqs__visual"
			       style="background-image:url(<?php echo esc_url( $data['visual'] ); ?>)"
			       aria-hidden="true"></aside>
		<?php endif; ?>

		<?php /* Right column — content + accordion */ ?>
		<div class="home-faqs__content">

			<?php if ( ! empty( $data['overline'] ) ) : ?>
				<p class="overline home-faqs__overline">
					<?php echo esc_html( $data['overline'] ); ?>
				</p>
			<?php endif; ?>

			<?php if ( ! empty( $data['heading'] ) ) : ?>
				<h2 class="home-faqs__heading">
					<?php echo esc_html( $data['heading'] ); ?>
				</h2>
			<?php endif; ?>

			<?php if ( ! empty( $data['items'] ) ) : ?>
				<ul class="home-faqs__list">
					<?php foreach ( $data['items'] as $item ) : ?>
						<li class="home-faqs__item">
							<details class="home-faqs__details">
								<summary class="home-faqs__summary">
									<span class="home-faqs__title"><?php echo esc_html( $item['question'] ); ?></span>
								</summary>
								<div class="home-faqs__answer">
									<?php echo wp_kses_post( wpautop( $item['answer'] ) ); ?>
								</div>
							</details>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

		</div>

	</div>
</section>

<?php
/* JSON-LD FAQPage — outputs structured data for Google rich snippets. */
if ( ! empty( $data['items'] ) ) :
	$schema = [
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map(
			function ( $item ) {
				return [
					'@type'          => 'Question',
					'name'           => wp_strip_all_tags( $item['question'] ),
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => wp_strip_all_tags( $item['answer'] ),
					],
				];
			},
			$data['items']
		),
	];
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); ?></script>
<?php endif; ?>
