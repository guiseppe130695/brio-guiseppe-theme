<?php
/**
 * Meta Boxes — Landing Page template
 *
 * One meta box per section of template-landing.php. Only displayed when the
 * page is using "Template Name: Landing Page".
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register all Landing meta boxes for the page edit screen.
 */
function brio_landing_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-landing.php' ) ) {
		return;
	}

	$sections = [
		'hero'     => __( 'Landing — Hero', 'brio-guiseppe' ),
		'benefits' => __( 'Landing — Bénéfices', 'brio-guiseppe' ),
		'proof'    => __( 'Landing — Preuves sociales', 'brio-guiseppe' ),
		'offer'    => __( 'Landing — Offre', 'brio-guiseppe' ),
		'faq'      => __( 'Landing — FAQ', 'brio-guiseppe' ),
		'cta'      => __( 'Landing — CTA final', 'brio-guiseppe' ),
	];

	foreach ( $sections as $slug => $title ) {
		add_meta_box(
			'brio_landing_' . $slug,
			$title,
			'brio_landing_render_' . $slug,
			'page',
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes_page', 'brio_landing_register_meta_boxes' );

/** Hero */
function brio_landing_render_hero( $post ) {
	wp_nonce_field( 'brio_landing_save', 'brio_landing_nonce' );
	brio_field_text(     'brio_landing_hero_title',     __( 'Titre',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'title' ) );
	brio_field_textarea( 'brio_landing_hero_subtitle',  __( 'Sous-titre',   'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'subtitle' ) );
	brio_field_image(    'brio_landing_hero_image',     __( 'Visuel',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'image' ) );
	brio_field_text(     'brio_landing_hero_cta_label', __( 'Libellé CTA',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'cta_label' ) );
	brio_field_url(      'brio_landing_hero_cta_url',   __( 'URL CTA',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'cta_url' ) );
}

/** Benefits */
function brio_landing_render_benefits( $post ) {
	brio_field_text( 'brio_landing_benefits_title', __( 'Titre de section', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'benefits', 'title' ) );
	brio_field_json(
		'brio_landing_benefits_items',
		__( 'Liste des bénéfices', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'benefits', 'items' ),
		'[{"icon":"https://…/icon.svg","title":"Bénéfice 1","desc":"Description courte"}]'
	);
}

/** Proof */
function brio_landing_render_proof( $post ) {
	brio_field_text(     'brio_landing_proof_title', __( 'Titre',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'proof', 'title' ) );
	brio_field_textarea( 'brio_landing_proof_quote', __( 'Citation',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'proof', 'quote' ) );
	brio_field_text(     'brio_landing_proof_author',__( 'Auteur',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'proof', 'author' ) );
	brio_field_json(
		'brio_landing_proof_logos',
		__( 'Logos clients', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'proof', 'logos' ),
		'["https://…/logo1.png","https://…/logo2.png"]'
	);
}

/** Offer */
function brio_landing_render_offer( $post ) {
	brio_field_text(     'brio_landing_offer_title',    __( 'Titre',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'offer', 'title' ) );
	brio_field_textarea( 'brio_landing_offer_subtitle', __( 'Sous-titre','brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'offer', 'subtitle' ) );
	brio_field_text(     'brio_landing_offer_price',    __( 'Prix',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'offer', 'price' ) );
	brio_field_json(
		'brio_landing_offer_features',
		__( 'Inclus dans l\'offre', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'offer', 'features' ),
		'["Point inclus 1","Point inclus 2"]'
	);
	brio_field_text( 'brio_landing_offer_cta_label', __( 'Libellé CTA', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'offer', 'cta_label' ) );
	brio_field_url(  'brio_landing_offer_cta_url',   __( 'URL CTA',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'offer', 'cta_url' ) );
}

/** FAQ */
function brio_landing_render_faq( $post ) {
	brio_field_text( 'brio_landing_faq_title', __( 'Titre',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'faq', 'title' ) );
	brio_field_json(
		'brio_landing_faq_items',
		__( 'Questions / Réponses', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'faq', 'items' ),
		'[{"q":"Question ?","a":"Réponse."}]'
	);
}

/** CTA final */
function brio_landing_render_cta( $post ) {
	brio_field_text(     'brio_landing_cta_heading',  __( 'Titre',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'heading' ) );
	brio_field_textarea( 'brio_landing_cta_tagline',  __( 'Accroche',   'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'tagline' ) );
	brio_field_text(     'brio_landing_cta_label',    __( 'Libellé',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'label' ) );
	brio_field_url(      'brio_landing_cta_url',      __( 'URL',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'url' ) );
}

/**
 * Persist all Landing fields when the page is saved.
 */
function brio_landing_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_landing_nonce' ) ) {
		return;
	}

	$map = [
		'hero'     => [
			[ 'title',     'text' ],
			[ 'subtitle',  'textarea' ],
			[ 'image',     'url' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
		],
		'benefits' => [ [ 'title', 'text' ], [ 'items', 'json' ] ],
		'proof'    => [
			[ 'title',  'text' ],
			[ 'quote',  'textarea' ],
			[ 'author', 'text' ],
			[ 'logos',  'json' ],
		],
		'offer'    => [
			[ 'title',     'text' ],
			[ 'subtitle',  'textarea' ],
			[ 'price',     'text' ],
			[ 'features',  'json' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
		],
		'faq'      => [ [ 'title', 'text' ], [ 'items', 'json' ] ],
		'cta'      => [
			[ 'heading', 'text' ],
			[ 'tagline', 'textarea' ],
			[ 'label',   'text' ],
			[ 'url',     'url' ],
		],
	];

	foreach ( $map as $section => $fields ) {
		foreach ( $fields as $f ) {
			brio_meta_save_field( $post_id, 'landing', $section, $f[0], $f[1] );
		}
	}
}
add_action( 'save_post_page', 'brio_landing_save_meta' );
