<?php
/**
 * Front Page Template
 *
 * Used automatically by WordPress when "Settings → Reading → A static page"
 * is configured for the front page.
 *
 * Each homepage section lives in its own template part under
 * template-parts/home/ for isolated styling and easier maintenance. The list
 * below controls render order — comment a line to disable a section.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Homepage section render order.
 *
 * Filterable so child themes / plugins can reorder, add, or remove sections
 * without forking this template.
 *
 * @since 1.0.0
 *
 * @return string[] Section slugs (matching template-parts/home/{slug}.php).
 */
$sections = apply_filters( 'brio_home_sections', [
	'hero',       // "Libérez votre Hôtel des commissions OTA"
	'about',      // Pitch personnel : "Je ne crée pas de sites web…"
	'partners',   // Bandeau partenaires / technologies
	'programs',   // Solutions concrètes (accordéon)
	'philosophy', // Approche technique, humaine, orientée résultats
	'fun-facts',  // Chiffres clés (+62 000 € / −30 % / 90 jours / +45 %)
	'pricing',    // Offres : Riad / Boutique / Hôtel indépendant
	'faqs',       // Questions des hôteliers
	'blog',       // Insights & Stratégies
	'cta',        // Bandeau CTA final ("Vous versez plus de 60 000 €/an…")
] );

?>
<main id="main" class="site-main" role="main">
<?php
foreach ( $sections as $section ) {
	get_template_part( 'template-parts/home/' . $section );
}
?>
</main>
<?php
get_footer();
