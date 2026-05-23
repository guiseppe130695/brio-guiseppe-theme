<?php
/**
 * Outils — How-to
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_how_to_data();
if ( empty( $data['steps'] ) ) {
	return;
}
?>
<section class="outils-how-to" aria-labelledby="outils-how-to-title">
	<div class="container">

		<h2 id="outils-how-to-title" class="outils-how-to__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<ol class="outils-how-to__steps">
			<?php foreach ( $data['steps'] as $step ) : ?>
				<li class="outils-how-to__step">
					<?php if ( ! empty( $step['title'] ) ) : ?>
						<h3 class="outils-how-to__step-title"><?php echo esc_html( $step['title'] ); ?></h3>
					<?php endif; ?>
					<?php if ( ! empty( $step['desc'] ) ) : ?>
						<p class="outils-how-to__step-desc"><?php echo esc_html( $step['desc'] ); ?></p>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>

	</div>
</section>
