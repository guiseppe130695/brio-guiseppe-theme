<?php
/**
 * Front-end data providers — Outils template
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_get_outils_intro_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_intro_data', [
		'eyebrow' => brio_meta_get( $post_id, 'outils', 'intro', 'eyebrow', '' ),
		'title'   => brio_meta_get( $post_id, 'outils', 'intro', 'title',   get_the_title( $post_id ) ),
		'lead'    => brio_meta_get( $post_id, 'outils', 'intro', 'lead',    '' ),
	], $post_id );
}

function brio_get_outils_tool_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_tool_data', [
		'title' => brio_meta_get( $post_id, 'outils', 'tool', 'title', '' ),
		'intro' => brio_meta_get( $post_id, 'outils', 'tool', 'intro', '' ),
		'embed' => brio_meta_get( $post_id, 'outils', 'tool', 'embed', '' ),
	], $post_id );
}

function brio_get_outils_result_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_result_data', [
		'title'  => brio_meta_get( $post_id, 'outils', 'result', 'title', __( 'Vos résultats', 'brio-guiseppe' ) ),
		'lead'   => brio_meta_get( $post_id, 'outils', 'result', 'lead',  '' ),
		'anchor' => brio_meta_get( $post_id, 'outils', 'result', 'anchor', 'brio-outils-result' ),
	], $post_id );
}

function brio_get_outils_how_to_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_how_to_data', [
		'title' => brio_meta_get( $post_id, 'outils', 'how_to', 'title', __( 'Mode d\'emploi', 'brio-guiseppe' ) ),
		'steps' => brio_meta_json_decode(
			brio_meta_get( $post_id, 'outils', 'how_to', 'steps', '' ),
			[]
		),
	], $post_id );
}

function brio_get_outils_related_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_related_data', [
		'title' => brio_meta_get( $post_id, 'outils', 'related', 'title', __( 'Ressources complémentaires', 'brio-guiseppe' ) ),
		'items' => brio_meta_json_decode(
			brio_meta_get( $post_id, 'outils', 'related', 'items', '' ),
			[]
		),
	], $post_id );
}

function brio_get_outils_cta_data( $post_id = 0 ) {
	$post_id = $post_id ?: get_the_ID();
	return apply_filters( 'brio_outils_cta_data', [
		'heading' => brio_meta_get( $post_id, 'outils', 'cta', 'heading', __( 'Allons plus loin ensemble', 'brio-guiseppe' ) ),
		'tagline' => brio_meta_get( $post_id, 'outils', 'cta', 'tagline', '' ),
		'label'   => brio_meta_get( $post_id, 'outils', 'cta', 'label',   __( 'Planifiez votre démo', 'brio-guiseppe' ) ),
		'url'     => brio_meta_get( $post_id, 'outils', 'cta', 'url',     '#' ),
	], $post_id );
}
