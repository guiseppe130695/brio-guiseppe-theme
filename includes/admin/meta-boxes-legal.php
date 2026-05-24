<?php
/**
 * Meta Boxes — Page légale template
 *
 * Aligned with the Elementor design (json/PDC.json): a hero with the page
 * title and a breadcrumb, followed by the WordPress editor content. Only the
 * hero exposes editable fields — the body is the standard `the_content()`
 * editor visible above the meta boxes.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_legal_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-legal.php' ) ) {
		return;
	}

	add_meta_box(
		'brio_legal_hero',
		__( 'Page légale — Hero (titre + fil d\'Ariane)', 'brio-guiseppe' ),
		'brio_legal_render_hero',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'brio_legal_register_meta_boxes' );

function brio_legal_render_hero( $post ) {
	wp_nonce_field( 'brio_legal_save', 'brio_legal_nonce' );

	brio_field_text(
		'brio_legal_hero_title_override',
		__( 'Titre du hero (laissez vide pour utiliser le titre de la page)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'legal', 'hero', 'title_override' )
	);

	brio_field_json(
		'brio_legal_hero_breadcrumb',
		__( 'Fil d\'Ariane (override — sinon généré automatiquement)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'legal', 'hero', 'breadcrumb' ),
		'[{"label":"Accueil","url":"/"},{"label":"Politique de confidentialité"}]'
	);
}

function brio_legal_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_legal_nonce', 'brio_legal_save' ) ) {
		return;
	}

	brio_meta_save_field( $post_id, 'legal', 'hero', 'title_override', 'text' );
	brio_meta_save_field( $post_id, 'legal', 'hero', 'breadcrumb',     'json' );
}
add_action( 'save_post_page', 'brio_legal_save_meta' );
