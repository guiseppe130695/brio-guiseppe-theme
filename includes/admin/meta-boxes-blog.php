<?php
/**
 * Meta Boxes — Blog template
 *
 * Aligned with json/Blog.json: only the hero (title + intro + breadcrumb
 * override) is editable from the meta box. The article-pilier is edited
 * in the standard WordPress editor (the_content) and pulled into the
 * "content" partial.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_blog_register_meta_boxes() {
	global $post;
	if ( ! brio_meta_box_applies( $post, 'template-blog.php' ) ) {
		return;
	}

	add_meta_box(
		'brio_blog_hero',
		__( 'Blog — Hero (titre + intro + fil d\'Ariane)', 'brio-guiseppe' ),
		'brio_blog_render_hero',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'brio_blog_register_meta_boxes' );

function brio_blog_render_hero( $post ) {
	wp_nonce_field( 'brio_blog_save', 'brio_blog_nonce' );

	brio_field_text(
		'brio_blog_hero_title',
		__( 'Titre du hero (laissez vide pour utiliser le titre de la page)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'hero', 'title' )
	);

	brio_field_textarea(
		'brio_blog_hero_intro',
		__( 'Intro (paragraphe sous le titre, max ~65% de largeur)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'hero', 'intro' ),
		4
	);

	brio_field_json(
		'brio_blog_hero_breadcrumb',
		__( 'Fil d\'Ariane (override — sinon Accueil › Blog automatique)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'hero', 'breadcrumb' ),
		'[{"label":"Accueil","url":"/"},{"label":"Blog"}]'
	);
}

function brio_blog_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_blog_nonce' ) ) {
		return;
	}
	brio_meta_save_field( $post_id, 'blog', 'hero', 'title',      'text' );
	brio_meta_save_field( $post_id, 'blog', 'hero', 'intro',      'textarea' );
	brio_meta_save_field( $post_id, 'blog', 'hero', 'breadcrumb', 'json' );
}
add_action( 'save_post_page', 'brio_blog_save_meta' );
