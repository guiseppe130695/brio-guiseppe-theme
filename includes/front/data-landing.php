<?php
/**
 * Front-end data providers — Landing Page template
 *
 * Each function reads its section's post_meta and falls back to sensible
 * defaults so a brand-new Landing page renders gracefully before any field
 * is filled in. Partials in template-parts/landing/ consume these.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/** Hero */
function brio_get_landing_hero_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_hero_data', [
		'title'     => brio_meta_get( $post_id, 'landing', 'hero', 'title',     __( 'Titre principal de votre landing', 'brio-guiseppe' ) ),
		'subtitle'  => brio_meta_get( $post_id, 'landing', 'hero', 'subtitle',  __( 'Sous-titre qui développe la promesse en une phrase.', 'brio-guiseppe' ) ),
		'image'     => brio_meta_get( $post_id, 'landing', 'hero', 'image',     '' ),
		'cta_label' => brio_meta_get( $post_id, 'landing', 'hero', 'cta_label', __( 'Planifiez votre démo', 'brio-guiseppe' ) ),
		'cta_url'   => brio_meta_get( $post_id, 'landing', 'hero', 'cta_url',   '#' ),
	], $post_id );
}

/** Benefits */
function brio_get_landing_benefits_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_benefits_data', [
		'title' => brio_meta_get( $post_id, 'landing', 'benefits', 'title', __( 'Pourquoi choisir Brio Guiseppe', 'brio-guiseppe' ) ),
		'items' => brio_meta_json_decode(
			brio_meta_get( $post_id, 'landing', 'benefits', 'items', '' ),
			[]
		),
	], $post_id );
}

/** Proof */
function brio_get_landing_proof_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_proof_data', [
		'title'  => brio_meta_get( $post_id, 'landing', 'proof', 'title',  __( 'Ils nous font confiance', 'brio-guiseppe' ) ),
		'quote'  => brio_meta_get( $post_id, 'landing', 'proof', 'quote',  '' ),
		'author' => brio_meta_get( $post_id, 'landing', 'proof', 'author', '' ),
		'logos'  => brio_meta_json_decode(
			brio_meta_get( $post_id, 'landing', 'proof', 'logos', '' ),
			[]
		),
	], $post_id );
}

/** Offer */
function brio_get_landing_offer_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_offer_data', [
		'title'     => brio_meta_get( $post_id, 'landing', 'offer', 'title',    __( 'Notre offre', 'brio-guiseppe' ) ),
		'subtitle'  => brio_meta_get( $post_id, 'landing', 'offer', 'subtitle', '' ),
		'price'     => brio_meta_get( $post_id, 'landing', 'offer', 'price',    '' ),
		'features'  => brio_meta_json_decode(
			brio_meta_get( $post_id, 'landing', 'offer', 'features', '' ),
			[]
		),
		'cta_label' => brio_meta_get( $post_id, 'landing', 'offer', 'cta_label', __( 'Je m\'inscris', 'brio-guiseppe' ) ),
		'cta_url'   => brio_meta_get( $post_id, 'landing', 'offer', 'cta_url',   '#' ),
	], $post_id );
}

/** FAQ */
function brio_get_landing_faq_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_faq_data', [
		'title' => brio_meta_get( $post_id, 'landing', 'faq', 'title', __( 'Questions fréquentes', 'brio-guiseppe' ) ),
		'items' => brio_meta_json_decode(
			brio_meta_get( $post_id, 'landing', 'faq', 'items', '' ),
			[]
		),
	], $post_id );
}

/** CTA */
function brio_get_landing_cta_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_landing_cta_data', [
		'heading' => brio_meta_get( $post_id, 'landing', 'cta', 'heading', __( 'Prêt à passer à l\'action ?', 'brio-guiseppe' ) ),
		'tagline' => brio_meta_get( $post_id, 'landing', 'cta', 'tagline', '' ),
		'label'   => brio_meta_get( $post_id, 'landing', 'cta', 'label',   __( 'Planifiez votre démo', 'brio-guiseppe' ) ),
		'url'     => brio_meta_get( $post_id, 'landing', 'cta', 'url',     '#' ),
	], $post_id );
}
