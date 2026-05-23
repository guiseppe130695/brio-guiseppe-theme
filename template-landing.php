<?php
/**
 * Template Name: Landing Page
 *
 * Template dédié aux landing pages de conversion (campagnes, offres ciblées,
 * pages d'atterrissage publicitaires). Structure modulaire identique à
 * front-page.php : chaque bloc vit dans template-parts/landing/{slug}.php
 * et l'ordre est filtrable via `brio_landing_sections`.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Landing page section render order.
 *
 * @since 1.0.0
 *
 * @return string[] Section slugs (matching template-parts/landing/{slug}.php).
 */
$sections = apply_filters( 'brio_landing_sections', [
	'hero',      // Accroche principale + CTA above the fold
	'benefits',  // Bénéfices clés / proposition de valeur
	'proof',     // Témoignages, chiffres, logos clients
	'offer',     // Détail de l'offre / pricing simplifié
	'faq',       // Objections fréquentes
	'cta',       // CTA final de conversion
] );

?>
<main id="main" class="site-main site-main--landing" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part( 'template-parts/landing/' . $section );
}
?>
</main>
<?php
get_footer();
