<?php
/**
 * Landing Section — Social Proof
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_landing_proof_data();
?>
<section class="landing-proof" aria-labelledby="landing-proof-title">
	<div class="container">

		<h2 id="landing-proof-title" class="landing-proof__title">
			<?php echo esc_html( $data['title'] ); ?>
		</h2>

		<?php if ( ! empty( $data['quote'] ) ) : ?>
			<blockquote class="landing-proof__quote">
				<p><?php echo esc_html( $data['quote'] ); ?></p>
				<?php if ( ! empty( $data['author'] ) ) : ?>
					<cite><?php echo esc_html( $data['author'] ); ?></cite>
				<?php endif; ?>
			</blockquote>
		<?php endif; ?>

		<?php if ( ! empty( $data['logos'] ) ) : ?>
			<ul class="landing-proof__logos" aria-label="<?php esc_attr_e( 'Clients', 'brio-guiseppe' ); ?>">
				<?php foreach ( $data['logos'] as $logo ) : ?>
					<li><img src="<?php echo esc_url( $logo ); ?>" alt="" loading="lazy" /></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>

	</div>
</section>
