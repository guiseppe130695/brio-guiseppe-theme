<?php
/**
 * Single post template.
 *
 * Layout (d'après Post Template.json) :
 *   1. Hero    — fond primary, titre H1, breadcrumb, meta, image featured
 *   2. Content — the_content(), footer (tags + partage social), nav prev/next
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main site-main--single" role="main">
	<?php
	if ( have_posts() ) :
		the_post();
		get_template_part( 'template-parts/post/hero' );
		get_template_part( 'template-parts/post/content' );
	endif;
	?>
</main>
<?php
get_footer();
