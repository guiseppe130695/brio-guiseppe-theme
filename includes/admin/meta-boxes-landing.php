<?php
/**
 * Meta Boxes — Landing Page template
 *
 * One meta box per section of template-landing.php. Each section maps to its
 * equivalent homepage section so landing pages can have fully independent copy.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_landing_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-landing.php' ) ) {
		return;
	}

	$sections = [
		'hero'        => __( 'Landing — Hero', 'brio-guiseppe' ),
		'about'       => __( 'Landing — À propos', 'brio-guiseppe' ),
		'partners'    => __( 'Landing — Partenaires', 'brio-guiseppe' ),
		'programs'    => __( 'Landing — Programmes', 'brio-guiseppe' ),
		'philosophy'  => __( 'Landing — Philosophie', 'brio-guiseppe' ),
		'showcase'    => __( 'Landing — Showcase', 'brio-guiseppe' ),
		'funfacts'    => __( 'Landing — Chiffres clés', 'brio-guiseppe' ),
		'pricing'     => __( 'Landing — Tarifs', 'brio-guiseppe' ),
		'faqs'        => __( 'Landing — FAQ', 'brio-guiseppe' ),
		'cta'         => __( 'Landing — CTA final', 'brio-guiseppe' ),
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

/* ── Hero ── */
function brio_landing_render_hero( $post ) {
	wp_nonce_field( 'brio_landing_save', 'brio_landing_nonce' );
	brio_field_text(     'brio_landing_hero_title',    __( 'Titre H1',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'title' ) );
	brio_field_textarea( 'brio_landing_hero_subtitle', __( 'Sous-titre',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'hero', 'subtitle' ) );
}

/* ── About ── */
function brio_landing_render_about( $post ) {
	brio_field_text(     'brio_landing_about_overline',    __( 'Surtitre',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'overline' ) );
	brio_field_text(     'brio_landing_about_heading',     __( 'Titre',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'heading' ) );
	brio_field_textarea( 'brio_landing_about_description', __( 'Description',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'description' ) );
	brio_field_text(     'brio_landing_about_cta_label',   __( 'Libellé CTA',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'cta_label' ) );
	brio_field_url(      'brio_landing_about_cta_url',     __( 'URL CTA',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'cta_url' ) );
	brio_field_image(    'brio_landing_about_image',       __( 'Image',        'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'about', 'image' ) );
}

/* ── Partners ── */
function brio_landing_render_partners( $post ) {
	brio_field_text( 'brio_landing_partners_label', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'partners', 'label' ) );
	brio_field_json(
		'brio_landing_partners_items',
		__( 'Logos partenaires', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'partners', 'items' ),
		'[{"url":"https://…/logo.svg","alt":"Nom partenaire"}]'
	);
}

/* ── Programs ── */
function brio_landing_render_programs( $post ) {
	brio_field_text(     'brio_landing_programs_overline',  __( 'Surtitre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'programs', 'overline' ) );
	brio_field_text(     'brio_landing_programs_heading',   __( 'Titre',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'programs', 'heading' ) );
	brio_field_json(
		'brio_landing_programs_items',
		__( 'Programmes (accordéon)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'programs', 'items' ),
		'[{"title":"Nom du programme","content":"<p>Contenu HTML</p>"}]'
	);
	brio_field_text(     'brio_landing_programs_cta_label', __( 'Libellé CTA', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'programs', 'cta_label' ) );
	brio_field_url(      'brio_landing_programs_cta_url',   __( 'URL CTA',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'programs', 'cta_url' ) );
	brio_field_text(     'brio_landing_programs_note',      __( 'Note bas',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'programs', 'note' ) );
}

/* ── Philosophy ── */
function brio_landing_render_philosophy( $post ) {
	brio_field_text(     'brio_landing_philosophy_overline',    __( 'Surtitre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'philosophy', 'overline' ) );
	brio_field_text(     'brio_landing_philosophy_heading',     __( 'Titre',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'philosophy', 'heading' ) );
	brio_field_textarea( 'brio_landing_philosophy_description', __( 'Description', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'philosophy', 'description' ) );
	brio_field_image(    'brio_landing_philosophy_visual',      __( 'Image',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'philosophy', 'visual' ) );
	brio_field_json(
		'brio_landing_philosophy_features',
		__( 'Points forts (icônes)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'philosophy', 'features' ),
		'[{"icon":"check","title":"Point fort","text":"Description"}]'
	);
}

/* ── Showcase ── */
function brio_landing_render_showcase( $post ) {
	brio_field_image( 'brio_landing_showcase_bg', __( 'Image de fond', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'showcase', 'bg' ) );
	brio_field_json(
		'brio_landing_showcase_images',
		__( 'Images flottantes', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'showcase', 'images' ),
		'[{"url":"https://…/img.jpg","alt":"Description","position":"top-left"}]'
	);
}

/* ── Fun Facts ── */
function brio_landing_render_funfacts( $post ) {
	brio_field_text( 'brio_landing_funfacts_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'funfacts', 'overline' ) );
	brio_field_text( 'brio_landing_funfacts_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'funfacts', 'heading' ) );
	brio_field_json(
		'brio_landing_funfacts_cards',
		__( 'Cartes chiffres', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'funfacts', 'cards' ),
		'[{"variant":"light","icon":"https://…/icon.svg","number":40,"suffix":"+","title":"Hôteliers accompagnés"}]'
	);
}

/* ── Pricing ── */
function brio_landing_render_pricing( $post ) {
	brio_field_text( 'brio_landing_pricing_overline',  __( 'Surtitre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'pricing', 'overline' ) );
	brio_field_text( 'brio_landing_pricing_heading',   __( 'Titre',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'pricing', 'heading' ) );
	brio_field_text( 'brio_landing_pricing_cta_label', __( 'Libellé CTA', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'pricing', 'cta_label' ) );
	brio_field_url(  'brio_landing_pricing_cta_url',   __( 'URL CTA',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'pricing', 'cta_url' ) );
	brio_field_json(
		'brio_landing_pricing_plans',
		__( 'Plans tarifaires', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'pricing', 'plans' ),
		'[{"variant":"light","rooms":"1 chambre","title":"Starter","price":"990","price_prefix":"€","tagline":"Pour démarrer","cta":{"href":"#","label":"Choisir"},"includes":["Moteur de réservation","Design mobile-first"],"ideal":"Hôtel indépendant"}]'
	);
}

/* ── FAQs ── */
function brio_landing_render_faqs( $post ) {
	brio_field_text(  'brio_landing_faqs_overline', __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'faqs', 'overline' ) );
	brio_field_text(  'brio_landing_faqs_heading',  __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'faqs', 'heading' ) );
	brio_field_image( 'brio_landing_faqs_visual',   __( 'Image',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'faqs', 'visual' ) );
	brio_field_json(
		'brio_landing_faqs_items',
		__( 'Questions / Réponses', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'faqs', 'items' ),
		'[{"question":"Question ?","answer":"<p>Réponse.</p>"}]'
	);
}

/* ── CTA final ── */
function brio_landing_render_cta( $post ) {
	brio_field_text(     'brio_landing_cta_heading',   __( 'Titre',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'heading' ) );
	brio_field_json(
		'brio_landing_cta_taglines',
		__( 'Accroches (3 phrases)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'landing', 'cta', 'taglines' ),
		'["Phrase 1","Phrase 2","Phrase 3"]'
	);
	brio_field_text( 'brio_landing_cta_label', __( 'Libellé CTA', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'label' ) );
	brio_field_url(  'brio_landing_cta_url',   __( 'URL CTA',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'landing', 'cta', 'url' ) );
}

/**
 * Persist all Landing fields when the page is saved.
 */
function brio_landing_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_landing_nonce', 'brio_landing_save' ) ) {
		return;
	}

	$map = [
		'hero' => [
			[ 'title',    'text' ],
			[ 'subtitle', 'textarea' ],
		],
		'about' => [
			[ 'overline',    'text' ],
			[ 'heading',     'text' ],
			[ 'description', 'textarea' ],
			[ 'cta_label',   'text' ],
			[ 'cta_url',     'url' ],
			[ 'image',       'url' ],
		],
		'partners' => [
			[ 'label', 'text' ],
			[ 'items', 'json' ],
		],
		'programs' => [
			[ 'overline',  'text' ],
			[ 'heading',   'text' ],
			[ 'items',     'json' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
			[ 'note',      'text' ],
		],
		'philosophy' => [
			[ 'overline',    'text' ],
			[ 'heading',     'text' ],
			[ 'description', 'textarea' ],
			[ 'visual',      'url' ],
			[ 'features',    'json' ],
		],
		'showcase' => [
			[ 'bg',     'url' ],
			[ 'images', 'json' ],
		],
		'funfacts' => [
			[ 'overline', 'text' ],
			[ 'heading',  'text' ],
			[ 'cards',    'json' ],
		],
		'pricing' => [
			[ 'overline',  'text' ],
			[ 'heading',   'text' ],
			[ 'cta_label', 'text' ],
			[ 'cta_url',   'url' ],
			[ 'plans',     'json' ],
		],
		'faqs' => [
			[ 'overline', 'text' ],
			[ 'heading',  'text' ],
			[ 'visual',   'url' ],
			[ 'items',    'json' ],
		],
		'cta' => [
			[ 'heading',  'text' ],
			[ 'taglines', 'json' ],
			[ 'label',    'text' ],
			[ 'url',      'url' ],
		],
	];

	foreach ( $map as $section => $fields ) {
		foreach ( $fields as $f ) {
			brio_meta_save_field( $post_id, 'landing', $section, $f[0], $f[1] );
		}
	}
}
add_action( 'save_post_page', 'brio_landing_save_meta' );
