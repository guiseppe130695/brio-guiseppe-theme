<?php
/**
 * Blog — Hero
 *
 * Reproduit le container "Page Hero" du json/Blog.json :
 *   - fond primary, padding-top 100px (70px tablet)
 *   - <h1> centré
 *   - intro <p> centrée, largeur max 65%
 *   - breadcrumb inline (Accueil › Blog) avec séparateur cercle 8px
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero       = brio_get_blog_hero_data();
$breadcrumb = $hero['breadcrumb'];
$last       = count( $breadcrumb ) - 1;
?>
<header class="blog-hero">
	<h1 class="blog-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>

	<?php if ( ! empty( $hero['intro'] ) ) : ?>
		<p class="blog-hero__intro"><?php echo esc_html( $hero['intro'] ); ?></p>
	<?php endif; ?>

	<?php if ( ! empty( $breadcrumb ) ) : ?>
		<nav class="blog-hero__crumbs" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'brio-guiseppe' ); ?>">
			<ol>
				<?php foreach ( $breadcrumb as $i => $crumb ) : ?>
					<li>
						<?php if ( $i !== $last && ! empty( $crumb['url'] ) ) : ?>
							<a href="<?php echo esc_url( $crumb['url'] ); ?>"><?php echo esc_html( $crumb['label'] ?? '' ); ?></a>
						<?php else : ?>
							<span aria-current="page"><?php echo esc_html( $crumb['label'] ?? '' ); ?></span>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ol>
		</nav>
	<?php endif; ?>
</header>
