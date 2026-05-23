<?php
/**
 * Blog — Empty state
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="blog-empty">
	<p><?php esc_html_e( 'Aucun article ne correspond à votre sélection pour le moment.', 'brio-guiseppe' ); ?></p>
	<a href="<?php echo esc_url( get_permalink() ); ?>" class="blog-empty__reset">
		<?php esc_html_e( 'Voir tous les articles', 'brio-guiseppe' ); ?>
	</a>
</section>
