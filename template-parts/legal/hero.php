<?php
/**
 * Page légale — Hero
 *
 * DOM volontairement plat : un <header> + <h1> + <nav><ol>. Pas de wrapper
 * `.container` intermédiaire — le centrage et la largeur max sont gérés en
 * CSS directement sur .legal-hero. Le dernier crumb porte aria-current="page"
 * (pattern breadcrumb W3C).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero       = brio_get_legal_hero_data();
$breadcrumb = $hero['breadcrumb'];
$last       = count( $breadcrumb ) - 1;
?>
<header class="legal-hero">
	<h1 class="legal-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>

	<?php if ( ! empty( $breadcrumb ) ) : ?>
		<nav class="legal-hero__crumbs" aria-label="<?php esc_attr_e( 'Fil d\'Ariane', 'brio-guiseppe' ); ?>">
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
