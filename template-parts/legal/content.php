<?php
/**
 * Page légale — Main content
 *
 * DOM plat : un <article> qui hérite directement de l'éditeur WordPress.
 * Pas de wrapper .container : largeur max et padding gérés en CSS sur
 * .legal-content.
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;
?>
<article class="legal-content">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			the_content();
		endwhile;
	endif;
	?>
</article>
