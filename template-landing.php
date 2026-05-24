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
/* Hero spécifique landing (60/40 avec formulaire) */
$landing_sections = apply_filters( 'brio_landing_sections', [
	'hero',
	'features',
	'about',
	'partners',
	'programs',
	'philosophy',
	'showcase',
	'fun-facts',
	'pricing',
	'faqs',
	'blog',
	'cta',
] );
?>
<main id="main" class="site-main site-main--landing" role="main">
<?php
foreach ( $landing_sections as $section ) {
	// Use landing-specific partial if it exists, otherwise fall back to home.
	$landing_part = 'template-parts/landing/' . $section;
	$home_part    = 'template-parts/home/' . $section;
	if ( locate_template( $landing_part . '.php' ) ) {
		get_template_part( $landing_part );
	} else {
		get_template_part( $home_part );
	}
}
?>
</main>
<?php
get_footer();
