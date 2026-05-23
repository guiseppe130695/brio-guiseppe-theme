<?php
/**
 * Blog — Article-pilier (contenu SEO statique)
 *
 * Reproduit le 3e container du json/Blog.json (max-width 1200px, fond crème,
 * custom_css ciblant #content-blog h2/h3/h4). On garde l'id #content-blog
 * pour que le custom_css du design Elementor reste applicable si copié
 * dans le CSS du thème.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;
?>
<section id="content-blog" class="blog-content">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	endif;
	?>
</section>
