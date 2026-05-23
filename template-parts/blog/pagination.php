<?php
/**
 * Blog — Numeric pagination
 *
 * Built from paginate_links() against our custom WP_Query (not the main one,
 * which is for the Page itself). `format=?paged=%#%` because the Page
 * Template lives at a static URL — we can't rely on pretty `/page/2/` rewrites
 * without adding our own rules.
 *
 * @package Brio_Guiseppe
 *
 * @var array $args Template part args: { query, current_cat_slug, paged }.
 */

defined( 'ABSPATH' ) || exit;

$query = $args['query']            ?? null;
$paged = $args['paged']            ?? 1;
$cat   = $args['current_cat_slug'] ?? '';

if ( ! ( $query instanceof WP_Query ) || (int) $query->max_num_pages < 2 ) {
	return;
}

$base = get_permalink();
$add_args = [];
if ( $cat ) {
	$add_args['categorie'] = $cat;
}

$links = paginate_links( [
	'base'      => add_query_arg( 'paged', '%#%', $base ),
	'format'    => '?paged=%#%',
	'current'   => max( 1, (int) $paged ),
	'total'     => (int) $query->max_num_pages,
	'add_args'  => $add_args,
	'prev_text' => __( '‹ Précédent', 'brio-guiseppe' ),
	'next_text' => __( 'Suivant ›', 'brio-guiseppe' ),
	'type'      => 'array',
	'end_size'  => 1,
	'mid_size'  => 2,
] );

if ( empty( $links ) ) {
	return;
}
?>
<nav class="blog-pagination" aria-label="<?php esc_attr_e( 'Pagination des articles', 'brio-guiseppe' ); ?>">
	<ul>
		<?php foreach ( $links as $link ) : ?>
			<li><?php echo $link; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — paginate_links() returns sanitized markup. */ ?></li>
		<?php endforeach; ?>
	</ul>
</nav>
