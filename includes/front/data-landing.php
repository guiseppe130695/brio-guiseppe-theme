<?php
/**
 * Front-end data providers — Landing Page template
 *
 * Each function reads post_meta for the current landing page first, then falls
 * back to the global homepage data so a new landing page renders gracefully
 * before any field is filled in.
 *
 * Meta key pattern: _brio_landing_{section}_{field}
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Helper: return meta value if set, otherwise a fallback.
 */
function brio_lmeta( $post_id, $section, $field, $fallback = '' ) {
	return brio_meta_get( $post_id, 'landing', $section, $field, $fallback );
}

/* ── Hero ── */
function brio_get_landing_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	return apply_filters( 'brio_landing_hero_data', [
		'title'    => brio_lmeta( $post_id, 'hero', 'title',    __( 'Titre principal de votre landing', 'brio-guiseppe' ) ),
		'subtitle' => brio_lmeta( $post_id, 'hero', 'subtitle', __( 'Sous-titre qui développe la promesse en une phrase.', 'brio-guiseppe' ) ),
	], $post_id );
}

/* ── About ── */
function brio_get_landing_about_data( $post_id = 0 ) {
	$post_id  = $post_id ?: get_queried_object_id();
	$home     = brio_get_about_data();
	return apply_filters( 'brio_landing_about_data', [
		'overline'    => brio_lmeta( $post_id, 'about', 'overline',    $home['overline'] ),
		'heading'     => brio_lmeta( $post_id, 'about', 'heading',     $home['heading'] ),
		'description' => brio_lmeta( $post_id, 'about', 'description', $home['description'] ),
		'cta'         => [
			'label' => brio_lmeta( $post_id, 'about', 'cta_label', $home['cta']['label'] ),
			'href'  => brio_lmeta( $post_id, 'about', 'cta_url',   $home['cta']['href'] ),
		],
		'image'       => brio_lmeta( $post_id, 'about', 'image', $home['image'] ),
	], $post_id );
}

/* ── Partners ── */
function brio_get_landing_partners_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_partners_data();
	return apply_filters( 'brio_landing_partners_data', [
		'label' => brio_lmeta( $post_id, 'partners', 'label', $home['label'] ),
		'items' => brio_meta_json_decode(
			brio_lmeta( $post_id, 'partners', 'items', '' ),
			$home['items']
		),
	], $post_id );
}

/* ── Programs ── */
function brio_get_landing_programs_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_programs_data();
	return apply_filters( 'brio_landing_programs_data', [
		'overline' => brio_lmeta( $post_id, 'programs', 'overline',  $home['overline'] ),
		'heading'  => brio_lmeta( $post_id, 'programs', 'heading',   $home['heading'] ),
		'items'    => brio_meta_json_decode(
			brio_lmeta( $post_id, 'programs', 'items', '' ),
			$home['items']
		),
		'cta'      => [
			'label' => brio_lmeta( $post_id, 'programs', 'cta_label', $home['cta']['label'] ),
			'href'  => brio_lmeta( $post_id, 'programs', 'cta_url',   $home['cta']['href'] ),
		],
		'note'     => brio_lmeta( $post_id, 'programs', 'note', $home['note'] ?? '' ),
	], $post_id );
}

/* ── Philosophy ── */
function brio_get_landing_philosophy_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_philosophy_data();
	return apply_filters( 'brio_landing_philosophy_data', [
		'overline'    => brio_lmeta( $post_id, 'philosophy', 'overline',    $home['overline'] ),
		'heading'     => brio_lmeta( $post_id, 'philosophy', 'heading',     $home['heading'] ),
		'description' => brio_lmeta( $post_id, 'philosophy', 'description', $home['description'] ),
		'visual'      => brio_lmeta( $post_id, 'philosophy', 'visual',      $home['visual'] ),
		'mission'     => $home['mission'] ?? null,
		'features'    => brio_meta_json_decode(
			brio_lmeta( $post_id, 'philosophy', 'features', '' ),
			$home['features']
		),
	], $post_id );
}

/* ── Showcase ── */
function brio_get_landing_showcase_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_showcase_data();
	return apply_filters( 'brio_landing_showcase_data', [
		'bg'     => brio_lmeta( $post_id, 'showcase', 'bg', $home['bg'] ),
		'images' => brio_meta_json_decode(
			brio_lmeta( $post_id, 'showcase', 'images', '' ),
			$home['images']
		),
	], $post_id );
}

/* ── Fun Facts ── */
function brio_get_landing_funfacts_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_fun_facts_data();
	return apply_filters( 'brio_landing_funfacts_data', [
		'overline' => brio_lmeta( $post_id, 'funfacts', 'overline', $home['overline'] ),
		'heading'  => brio_lmeta( $post_id, 'funfacts', 'heading',  $home['heading'] ),
		'cards'    => brio_meta_json_decode(
			brio_lmeta( $post_id, 'funfacts', 'cards', '' ),
			$home['cards']
		),
	], $post_id );
}

/* ── Pricing ── */
function brio_get_landing_pricing_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_pricing_data();
	return apply_filters( 'brio_landing_pricing_data', [
		'overline' => brio_lmeta( $post_id, 'pricing', 'overline',  $home['overline'] ),
		'heading'  => brio_lmeta( $post_id, 'pricing', 'heading',   $home['heading'] ),
		'cta'      => [
			'label' => brio_lmeta( $post_id, 'pricing', 'cta_label', $home['cta']['label'] ),
			'href'  => brio_lmeta( $post_id, 'pricing', 'cta_url',   $home['cta']['href'] ),
		],
		'plans'    => brio_meta_json_decode(
			brio_lmeta( $post_id, 'pricing', 'plans', '' ),
			$home['plans']
		),
	], $post_id );
}

/* ── FAQs ── */
function brio_get_landing_faqs_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_faqs_data();
	return apply_filters( 'brio_landing_faqs_data', [
		'overline' => brio_lmeta( $post_id, 'faqs', 'overline', $home['overline'] ),
		'heading'  => brio_lmeta( $post_id, 'faqs', 'heading',  $home['heading'] ),
		'visual'   => brio_lmeta( $post_id, 'faqs', 'visual',   $home['visual'] ),
		'items'    => brio_meta_json_decode(
			brio_lmeta( $post_id, 'faqs', 'items', '' ),
			$home['items']
		),
	], $post_id );
}

/* ── CTA final ── */
function brio_get_landing_cta_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_queried_object_id();
	$home    = brio_get_cta_data();
	return apply_filters( 'brio_landing_cta_data', [
		'heading'  => brio_lmeta( $post_id, 'cta', 'heading',  $home['heading'] ),
		'taglines' => brio_meta_json_decode(
			brio_lmeta( $post_id, 'cta', 'taglines', '' ),
			$home['taglines'] ?? []
		),
		'cta'      => [
			'label' => brio_lmeta( $post_id, 'cta', 'label', $home['cta']['label'] ),
			'href'  => brio_lmeta( $post_id, 'cta', 'url',   $home['cta']['href'] ),
		],
	], $post_id );
}
