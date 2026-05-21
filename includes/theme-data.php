<?php
/**
 * Theme Data Provider
 *
 * Centralized configuration for company contact information, footer columns,
 * legal data, and other site-wide constants. Provides a single source of truth
 * for content that appears in multiple template locations (header, footer, etc.).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get company contact information.
 *
 * Used by header (phones, demo CTA) and footer (contact column, branding).
 * Filterable to allow plugins/child themes to override values.
 *
 * @since 1.0.0
 *
 * @return array {
 *     Company contact data.
 *
 *     @type string $name    Company display name.
 *     @type string $tagline Marketing tagline.
 *     @type string $address Postal address.
 *     @type array  $phones  List of phone numbers (label + tel: href).
 *     @type string $email   Primary contact email.
 *     @type array  $social  Social media URLs keyed by network.
 * }
 */
function brio_get_company_data() {
	$data = [
		'name'    => 'Brio Guiseppe',
		'tagline' => __( 'Votre hôtel mérite mieux qu\'une page Booking.', 'brio-guiseppe' ),
		'address' => __( '5º étage N°19, Res Moulay Ismail Av. Moulay Ismail, Tanger 90000', 'brio-guiseppe' ),
		'phones'  => [
			[ 'label' => '+33 6 16 97 58 44',  'tel' => '+33616975844' ],
			[ 'label' => '+212 7 70 74 03 11', 'tel' => '+212770740311' ],
		],
		'email'   => 'contact@brioguiseppe.fr',
		'social'  => [
			'linkedin' => 'https://www.linkedin.com/in/brioguiseppe/',
		],
	];

	return apply_filters( 'brio_company_data', $data );
}

/**
 * Get footer navigation columns (Explorer + Services).
 *
 * The Contact and Social columns are built inline in the footer template
 * because they use specific data (phones, social URLs) from company data.
 *
 * @since 1.0.0
 *
 * @return array Footer navigation columns with titles and links.
 */
function brio_get_footer_columns() {
	$columns = [
		'explorer' => [
			'title' => __( 'Explorer', 'brio-guiseppe' ),
			'links' => [
				[ 'label' => __( 'Accueil', 'brio-guiseppe' ),   'url' => '#' ],
				[ 'label' => __( 'Expertise', 'brio-guiseppe' ), 'url' => '#' ],
				[ 'label' => __( 'Services', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'Blog', 'brio-guiseppe' ),      'url' => '#' ],
				[ 'label' => __( 'Contact', 'brio-guiseppe' ),   'url' => '#' ],
			],
		],
		'services' => [
			'title' => __( 'Services', 'brio-guiseppe' ),
			'links' => [
				[ 'label' => __( 'Site Web Hôtel Conversion', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'SEO Tourisme & Destination', 'brio-guiseppe' ), 'url' => '#' ],
				[ 'label' => __( 'Revenue Management System', 'brio-guiseppe' ),  'url' => '#' ],
				[ 'label' => __( 'Audit Distribution OTA', 'brio-guiseppe' ),     'url' => '#' ],
				[ 'label' => __( 'Optimisation Conversion', 'brio-guiseppe' ),    'url' => '#' ],
			],
		],
	];

	return apply_filters( 'brio_footer_columns', $columns );
}

/**
 * Get company legal information shown in the footer bottom row.
 *
 * @since 1.0.0
 *
 * @return array {
 *     Legal data.
 *
 *     @type string $ice       Identifiant Commun de l'Entreprise (Morocco).
 *     @type string $fiscal_id French tax identification number.
 *     @type array  $pages     Legal pages (privacy policy, legal mentions).
 * }
 */
function brio_get_legal_data() {
	$data = [
		'ice'       => '002333763000084',
		'fiscal_id' => '37690548',
		'pages'     => [
			'privacy' => [ 'label' => __( 'Politique de confidentialité', 'brio-guiseppe' ), 'url' => '#' ],
			'legal'   => [ 'label' => __( 'Mentions légales', 'brio-guiseppe' ),             'url' => '#' ],
		],
	];

	return apply_filters( 'brio_legal_data', $data );
}

/**
 * Get static asset URLs (CDN images, logos, etc.).
 *
 * Centralized to allow easy migration from CDN to local uploads, or to
 * swap visual assets without editing template files.
 *
 * @since 1.0.0
 *
 * @return array Asset URLs keyed by purpose.
 */
function brio_get_assets() {
	$assets = [
		'footer_logo'          => 'https://www.brioguiseppe.fr/wp-content/uploads/2026/04/Brio-Guiseppe-Logo-1.webp',
		'footer_decoration'    => 'https://www.brioguiseppe.fr/wp-content/uploads/2026/04/asset-2.png',
		'newsletter_image'     => 'https://www.brioguiseppe.fr/wp-content/uploads/2026/04/Brio-Guiseppe-Background.webp',
	];

	return apply_filters( 'brio_theme_assets', $assets );
}
