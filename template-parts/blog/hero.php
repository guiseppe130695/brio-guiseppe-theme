<?php
/**
 * Blog — Hero
 *
 * Titre H1 + intro (meta box) + the_content() de la page Blog (long-form
 * SEO statique, toujours visible quel que soit le filtre actif).
 *
 * @package Brio_Guiseppe
 */

defined( 'ABSPATH' ) || exit;

$hero = brio_get_blog_hero_data();
?>
<header class="blog-hero">
	<h1 class="blog-hero__title"><?php echo esc_html( $hero['title'] ); ?></h1>

	<?php if ( ! empty( $hero['intro'] ) ) : ?>
		<p class="blog-hero__intro"><?php echo esc_html( $hero['intro'] ); ?></p>
	<?php endif; ?>

	<?php
	/**
	 * Long-form SEO content (the_content of the Blog page). On l'imprime hors
	 * de la grille pour qu'il reste indexable même quand l'utilisateur filtre
	 * la liste d'articles côté client.
	 */
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			$content = get_the_content();
			if ( trim( wp_strip_all_tags( $content ) ) !== '' ) :
				?>
				<div class="blog-hero__content">
					<?php the_content(); ?>
				</div>
				<?php
			endif;
		endwhile;
		rewind_posts();
	endif;
	?>
</header>
