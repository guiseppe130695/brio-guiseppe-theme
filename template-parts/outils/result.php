<?php
/**
 * Outils — Result placeholder
 *
 * Renders the container the JS tool injects its results into. The id is
 * driven by the `anchor` meta so different tools can coexist.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_result_data();
?>
<section class="outils-result" aria-labelledby="outils-result-title">
	<div class="container">

		<h2 id="outils-result-title" class="outils-result__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<?php if ( ! empty( $data['lead'] ) ) : ?>
			<p class="outils-result__lead"><?php echo esc_html( $data['lead'] ); ?></p>
		<?php endif; ?>

		<div id="<?php echo esc_attr( $data['anchor'] ); ?>" class="outils-result__target" role="region" aria-live="polite"></div>

	</div>
</section>
