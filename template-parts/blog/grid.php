<?php
/**
 * Blog — Articles grid
 *
 * Iterates the custom WP_Query passed via $args['query'] and delegates each
 * card render to template-parts/blog/card.php. Empty result → empty.php.
 *
 * @package Brio_Guiseppe
 *
 * @var array $args Template part args: { query }.
 */

defined( 'ABSPATH' ) || exit;

$query = $args['query'] ?? null;

if ( ! ( $query instanceof WP_Query ) || ! $query->have_posts() ) {
	get_template_part( 'template-parts/blog/empty' );
	return;
}
?>
<section class="blog-grid" aria-label="<?php esc_attr_e( 'Articles', 'brio-guiseppe' ); ?>">
	<?php
	while ( $query->have_posts() ) :
		$query->the_post();
		get_template_part( 'template-parts/blog/card' );
	endwhile;
	wp_reset_postdata();
	?>
</section>
