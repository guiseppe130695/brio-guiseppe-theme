<?php
/**
 * Template Name: Page légale
 *
 * Layout dédié aux pages légales / réglementaires : Politique de
 * confidentialité, Mentions légales, CGV, CGU. Calque le design Elementor
 * "Politique de Confidentialité" (cf. json/PDC.json) : un hero sur fond
 * primary avec titre + fil d'Ariane, suivi du contenu riche éditorial.
 *
 * Les autres pages annexes au design distinct (À propos, Contact…) ont leurs
 * propres templates dédiés et ne réutilisent PAS ce gabarit.
 *
 * Sections filtrables via `brio_legal_sections`.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Legal page section render order.
 *
 * @since 1.0.0
 *
 * @return string[] Section slugs (matching template-parts/legal/{slug}.php).
 */
$sections = apply_filters( 'brio_legal_sections', [
	'hero',     // Titre + breadcrumb (fond primary)
	'content',  // Corps éditorial via the_content()
] );

?>
<main id="main" class="site-main site-main--legal" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part( 'template-parts/legal/' . $section );
}
?>
</main>
<?php
get_footer();
