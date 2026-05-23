<?php
/**
 * Meta Boxes — Blog template
 *
 * Two meta boxes :
 *   1. "Hero"   — titre + intro.
 *   2. "Topics" — gabarit du titre de section (avec placeholder {category})
 *                  + libellé/URL du bouton "See all posts".
 *
 * Le breadcrumb a été retiré (le nouveau design ne l'utilise pas).
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

	add_meta_box( 'brio_blog_hero',   __( 'Blog — Hero',   'brio-guiseppe' ), 'brio_blog_render_hero',   'page', 'normal', 'default' );
	add_meta_box( 'brio_blog_topics', __( 'Blog — Topics', 'brio-guiseppe' ), 'brio_blog_render_topics', 'page', 'normal', 'default' );
}
add_action( 'add_meta_boxes_page', 'brio_blog_register_meta_boxes' );

function brio_blog_render_hero( $post ) {
	wp_nonce_field( 'brio_blog_save', 'brio_blog_nonce' );

	brio_field_text(
		'brio_blog_hero_title',
		__( 'Titre (laissez vide pour utiliser le titre de la page)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'hero', 'title' )
	);

	brio_field_textarea(
		'brio_blog_hero_intro',
		__( 'Intro (sous le titre)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'hero', 'intro' ),
		3
	);
}

function brio_blog_render_topics( $post ) {
	brio_field_text(
		'brio_blog_topics_title_template',
		__( 'Gabarit du titre de section (utilisez {category} pour insérer le nom de la catégorie active)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'topics', 'title_template', '{category} topics' )
	);

	brio_field_text(
		'brio_blog_topics_see_all_label',
		__( 'Libellé du bouton "Voir tous les articles"', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'topics', 'see_all_label', 'See all posts' )
	);

	brio_field_url(
		'brio_blog_topics_see_all_url',
		__( 'URL du bouton (vide = masqué)', 'brio-guiseppe' ),
		brio_meta_get( $post->ID, 'blog', 'topics', 'see_all_url' )
	);
}

function brio_blog_save_meta( $post_id ) {
	if ( ! brio_meta_can_save( $post_id, 'brio_blog_nonce' ) ) {
		return;
	}

	brio_meta_save_field( $post_id, 'blog', 'hero',   'title',          'text' );
	brio_meta_save_field( $post_id, 'blog', 'hero',   'intro',          'textarea' );
	brio_meta_save_field( $post_id, 'blog', 'topics', 'title_template', 'text' );
	brio_meta_save_field( $post_id, 'blog', 'topics', 'see_all_label',  'text' );
	brio_meta_save_field( $post_id, 'blog', 'topics', 'see_all_url',    'url' );
}
add_action( 'save_post_page', 'brio_blog_save_meta' );
