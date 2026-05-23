<?php
/**
 * Outils — Intro
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_outils_intro_data();
?>
<section class="outils-intro" aria-labelledby="outils-intro-title">
	<div class="container">

		<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
			<p class="outils-intro__eyebrow"><?php echo esc_html( $data['eyebrow'] ); ?></p>
		<?php endif; ?>

		<h1 id="outils-intro-title" class="outils-intro__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h1>

		<?php if ( ! empty( $data['lead'] ) ) : ?>
			<p class="outils-intro__lead"><?php echo esc_html( $data['lead'] ); ?></p>
		<?php endif; ?>

	</div>
</section>
