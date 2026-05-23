<?php
/**
 * Blog — Hero
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$data = brio_get_blog_hero_data();
?>
<header class="blog-hero">
	<?php if ( ! empty( $data['eyebrow'] ) ) : ?>
		<p class="blog-hero__eyebrow"><?php echo esc_html( $data['eyebrow'] ); ?></p>
	<?php endif; ?>

	<h1 class="blog-hero__title"><?php echo esc_html( $data['title'] ); ?></h1>

	<?php if ( ! empty( $data['intro'] ) ) : ?>
		<p class="blog-hero__intro"><?php echo esc_html( $data['intro'] ); ?></p>
	<?php endif; ?>
</header>
