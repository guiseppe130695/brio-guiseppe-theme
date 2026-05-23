<?php
/**
 * Outils — Tool block
 *
 * The `embed` field accepts shortcodes (do_shortcode) and trusted HTML. Edited
 * only by capable users (current_user_can('edit_post')) so we allow more tags
 * than the default kses set for plain content.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_tool_data();
?>
<section class="outils-tool" aria-labelledby="outils-tool-title">
	<div class="container">

		<?php if ( ! empty( $data['title'] ) ) : ?>
			<h2 id="outils-tool-title" class="outils-tool__title">
				<?php echo esc_html( $data['title'] ); ?>
			</h2>
		<?php endif; ?>

		<?php if ( ! empty( $data['intro'] ) ) : ?>
			<p class="outils-tool__intro"><?php echo esc_html( $data['intro'] ); ?></p>
		<?php endif; ?>

		<?php if ( ! empty( $data['embed'] ) ) : ?>
			<div class="outils-tool__embed">
				<?php echo do_shortcode( wp_kses_post( $data['embed'] ) ); ?>
			</div>
		<?php endif; ?>

	</div>
</section>
