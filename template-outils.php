<?php
/**
 * Template Name: Outils
 *
 * Template pour les pages "outils" interactives (simulateurs, calculateurs
 * de commissions OTA, audits gratuits, générateurs). Structure modulaire
 * identique à front-page.php : chaque bloc vit dans
 * template-parts/outils/{slug}.php et l'ordre est filtrable via
 * `brio_outils_sections`.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Tools page section render order.
 *
 * @since 1.0.0
 *
 * @return string[] Section slugs (matching template-parts/outils/{slug}.php).
 */
$sections = apply_filters( 'brio_outils_sections', [
	'intro',    // Présentation de l'outil + bénéfice
	'tool',     // Outil interactif (formulaire / calculateur)
	'result',   // Zone de restitution des résultats
	'how-to',   // Mode d'emploi / méthodologie
	'related',  // Outils complémentaires / ressources liées
	'cta',      // CTA "prendre rendez-vous" après usage
] );

?>
<main id="main" class="site-main site-main--outils" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part( 'template-parts/outils/' . $section );
}
?>
</main>
<?php
get_footer();
