<?php
/**
 * Blog — Category filter tabs
 *
 * Plain server-rendered links: ?categorie=<slug>. SEO-friendly, accessible,
 * no JS required. Pagination resets to page 1 (we don't carry `paged`
 * across category changes — switching category invalidates the offset).
 *
 * @package Brio_Guiseppe
 *
 * @var array $args Template part args: { current_cat_slug }.
 */

defined( 'ABSPATH' ) || exit;

$current = $args['current_cat_slug'] ?? '';
$cats    = brio_get_blog_categories();

if ( empty( $cats ) ) {
	return;
}

$base_url = get_permalink();
?>
<nav class="blog-filters" aria-label="<?php esc_attr_e( 'Filtrer par catégorie', 'brio-guiseppe' ); ?>">
	<ul>
		<li>
			<a href="<?php echo esc_url( $base_url ); ?>"
			   class="blog-filters__tab<?php echo '' === $current ? ' is-active' : ''; ?>"
			   <?php echo '' === $current ? 'aria-current="true"' : ''; ?>>
				<?php esc_html_e( 'Tous', 'brio-guiseppe' ); ?>
			</a>
		</li>
		<?php foreach ( $cats as $cat ) :
			$is_active = ( $current === $cat['slug'] );
			$url       = add_query_arg( 'categorie', $cat['slug'], $base_url );
			?>
			<li>
				<a href="<?php echo esc_url( $url ); ?>"
				   class="blog-filters__tab<?php echo $is_active ? ' is-active' : ''; ?>"
				   <?php echo $is_active ? 'aria-current="true"' : ''; ?>>
					<?php echo esc_html( $cat['name'] ); ?>
					<span class="blog-filters__count">(<?php echo (int) $cat['count']; ?>)</span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>
