<?php
/**
 * Meta Boxes — Outils template
 *
 * Only displayed when the page is using "Template Name: Outils".
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_outils_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-outils.php' ) ) {
		return;
	}

	$sections = [
		'intro'   => __( 'Outils — Intro',         'brio-guiseppe' ),
		'tool'    => __( 'Outils — Bloc outil',    'brio-guiseppe' ),
		'result'  => __( 'Outils — Résultats',     'brio-guiseppe' ),
		'how_to'  => __( 'Outils — Mode d\'emploi','brio-guiseppe' ),
		'related' => __( 'Outils — Ressources liées','brio-guiseppe' ),
		'cta'     => __( 'Outils — CTA final',     'brio-guiseppe' ),
	];

	foreach ( $sections as $slug => $title ) {
		add_meta_box(
			'brio_outils_' . $slug,
			$title,
			'brio_outils_render_' . $slug,
			'page',
			'normal',
			'default'
		);
	}
}
add_action( 'add_meta_boxes_page', 'brio_outils_register_meta_boxes' );

/** Intro */
function brio_outils_render_intro( $post ) {
	wp_nonce_field( 'brio_outils_save', 'brio_outils_nonce' );
	brio_field_text(     'brio_outils_intro_eyebrow', __( 'Surtitre',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'intro', 'eyebrow' ) );
	brio_field_text(     'brio_outils_intro_title',   __( 'Titre',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'intro', 'title' ) );
	brio_field_textarea( 'brio_outils_intro_lead',    __( 'Accroche',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'intro', 'lead' ) );
}

/** Tool */
function brio_outils_render_tool( $post ) {
	brio_field_text(     'brio_outils_tool_title',  __( 'Titre du bloc outil',       'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'tool', 'title' ) );
	brio_field_textarea( 'brio_outils_tool_intro',  __( 'Texte avant l\'outil',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'tool', 'intro' ) );
	brio_field_textarea( 'brio_outils_tool_embed',  __( 'Shortcode / embed (HTML)',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'tool', 'embed' ), 6 );
}

/** Result */
function brio_outils_render_result( $post ) {
	brio_field_text(     'brio_outils_result_title',  __( 'Titre',         'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'result', 'title' ) );
	brio_field_textarea( 'brio_outils_result_lead',   __( 'Texte de cadrage','brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'result', 'lead' ) );
	brio_field_text(     'brio_outils_result_anchor', __( 'ID d\'ancrage (où l\'outil injecte ses résultats)', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'result', 'anchor' ) );
}

/** How-to */
function brio_outils_render_how_to( $post ) {
	brio_field_text( 'brio_outils_how_to_title', __( 'Titre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'how_to', 'title' ) );
	brio_field_json(
		'brio_outils_how_to_steps',
		__( 'Étapes', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'outils', 'how_to', 'steps' ),
		'[{"title":"Étape 1","desc":"Description"}]'
	);
}

/** Related */
function brio_outils_render_related( $post ) {
	brio_field_text( 'brio_outils_related_title', __( 'Titre', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'related', 'title' ) );
	brio_field_json(
		'brio_outils_related_items',
		__( 'Ressources liées', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'outils', 'related', 'items' ),
		'[{"label":"Ressource","url":"https://…","desc":"Courte description"}]'
	);
}

/** CTA */
function brio_outils_render_cta( $post ) {
	brio_field_text(     'brio_outils_cta_heading', __( 'Titre',    'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'cta', 'heading' ) );
	brio_field_textarea( 'brio_outils_cta_tagline', __( 'Accroche', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'cta', 'tagline' ) );
	brio_field_text(     'brio_outils_cta_label',   __( 'Libellé',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'cta', 'label' ) );
	brio_field_url(      'brio_outils_cta_url',     __( 'URL',      'brio-guiseppe' ), brio_meta_get( $post->ID, 'outils', 'cta', 'url' ) );
}

function brio_outils_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_outils_nonce', 'brio_outils_save' ) ) {
		return;
	}

	$map = [
		'intro'   => [
			[ 'eyebrow', 'text' ],
			[ 'title',   'text' ],
			[ 'lead',    'textarea' ],
		],
		'tool'    => [
			[ 'title', 'text' ],
			[ 'intro', 'textarea' ],
			[ 'embed', 'textarea' ],
		],
		'result'  => [
			[ 'title',  'text' ],
			[ 'lead',   'textarea' ],
			[ 'anchor', 'text' ],
		],
		'how_to'  => [ [ 'title', 'text' ], [ 'steps', 'json' ] ],
		'related' => [ [ 'title', 'text' ], [ 'items', 'json' ] ],
		'cta'     => [
			[ 'heading', 'text' ],
			[ 'tagline', 'textarea' ],
			[ 'label',   'text' ],
			[ 'url',     'url' ],
		],
	];

	foreach ( $map as $section => $fields ) {
		foreach ( $fields as $f ) {
			brio_meta_save_field( $post_id, 'outils', $section, $f[0], $f[1] );
		}
	}
}
add_action( 'save_post_page', 'brio_outils_save_meta' );
