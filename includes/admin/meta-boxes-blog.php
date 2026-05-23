<?php
/**
 * Meta Boxes — Blog template
 *
 * Only the hero is editable; the article grid is driven by published posts.
 * Shown only when "Template Name: Blog" is selected.
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
		__( 'Blog — Hero', 'brio-guiseppe' ),
		'brio_blog_render_hero',
		'page',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes_page', 'brio_blog_register_meta_boxes' );

function brio_blog_render_hero( $post ) {
	wp_nonce_field( 'brio_blog_save', 'brio_blog_nonce' );
	brio_field_text(     'brio_blog_hero_eyebrow', __( 'Surtitre',  'brio-guiseppe' ), brio_meta_get( $post->ID, 'blog', 'hero', 'eyebrow' ) );
	brio_field_text(     'brio_blog_hero_title',   __( 'Titre (vide = titre de la page)', 'brio-guiseppe' ), brio_meta_get( $post->ID, 'blog', 'hero', 'title' ) );
	brio_field_textarea( 'brio_blog_hero_intro',   __( 'Intro',     'brio-guiseppe' ), brio_meta_get( $post->ID, 'blog', 'hero', 'intro' ) );
}

function brio_blog_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_blog_nonce' ) ) {
		return;
	}
	brio_meta_save_field( $post_id, 'blog', 'hero', 'eyebrow', 'text' );
	brio_meta_save_field( $post_id, 'blog', 'hero', 'title',   'text' );
	brio_meta_save_field( $post_id, 'blog', 'hero', 'intro',   'textarea' );
}
add_action( 'save_post_page', 'brio_blog_save_meta' );
