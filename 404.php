<?php
/**
 * 404 — Page introuvable.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main site-main--404" role="main">
	<?php get_template_part( 'template-parts/404/content' ); ?>
</main>
<?php
get_footer();
