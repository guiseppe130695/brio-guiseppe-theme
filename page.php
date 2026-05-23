<?php
/**
 * Page générique WordPress.
 *
 * Réutilise le layout du template légal (hero fond primary + fil d'Ariane +
 * contenu éditorial) pour toutes les pages qui n'ont pas de template dédié.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="site-main site-main--legal" role="main">
	<?php get_template_part( 'template-parts/legal/hero' ); ?>
	<?php get_template_part( 'template-parts/legal/content' ); ?>
</main>
<?php
get_footer();
