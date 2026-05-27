<?php
/**
 * Admin — Tags CSV importer (registered with the shared csv-job-engine)
 *
 * Provides chunked import + export of post_tag terms with the standard
 * progress bar + log UI shared by every other CSV importer in the theme.
 *
 * Columns: slug, name, description, seo_title, seo_description, post_count
 *
 * Match rule: existing tag is found by slug → update; otherwise → create.
 *
 * @package Brio_Guiseppe
 * @since   1.7.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	brio_csv_register_importer( 'tags', [
		'label'      => __( 'Tags', 'brio-guiseppe' ),
		'chunk_size' => 25,
		'columns'    => [ 'slug', 'name', 'description', 'seo_title', 'seo_description' ],
		'help'       => __( 'Obligatoires : slug, name. Match par slug pour mise à jour, sinon création.', 'brio-guiseppe' ),

		'row_processor' => function ( $data, &$job ) {
			$slug = sanitize_title( $data['slug'] ?? '' );
			$name = sanitize_text_field( $data['name'] ?? '' );
			if ( '' === $slug || '' === $name ) {
				return [ 'verdict' => 'skp', 'message' => __( 'slug ou name vide.', 'brio-guiseppe' ) ];
			}

			$existing = get_term_by( 'slug', $slug, 'post_tag' );
			$args = [
				'slug'        => $slug,
				'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			];

			if ( $existing ) {
				wp_update_term( $existing->term_id, 'post_tag', array_merge( [ 'name' => $name ], $args ) );
				$term_id = $existing->term_id;
				$verdict = 'upd';
				$msg     = $name . ' (#' . $term_id . ')';
			} else {
				$res = wp_insert_term( $name, 'post_tag', $args );
				if ( is_wp_error( $res ) ) {
					return [ 'verdict' => 'err', 'message' => sprintf( '%s : %s', $slug, $res->get_error_message() ) ];
				}
				$term_id = $res['term_id'];
				$verdict = 'new';
				$msg     = $name . ' (#' . $term_id . ')';
			}

			// SEO override fields.
			if ( isset( $data['seo_title'] ) ) {
				$v = sanitize_text_field( $data['seo_title'] );
				if ( '' === $v ) { delete_term_meta( $term_id, '_brio_seo_title' ); }
				else { update_term_meta( $term_id, '_brio_seo_title', $v ); }
			}
			if ( isset( $data['seo_description'] ) ) {
				$v = sanitize_textarea_field( $data['seo_description'] );
				if ( '' === $v ) { delete_term_meta( $term_id, '_brio_seo_description' ); }
				else { update_term_meta( $term_id, '_brio_seo_description', $v ); }
			}

			return [ 'verdict' => $verdict, 'message' => $msg ];
		},

		'export_rows' => function () {
			$tags = get_tags( [ 'hide_empty' => false ] );
			$rows = [];
			foreach ( $tags as $tag ) {
				$rows[] = [
					'slug'            => $tag->slug,
					'name'            => $tag->name,
					'description'     => $tag->description,
					'seo_title'       => (string) get_term_meta( $tag->term_id, '_brio_seo_title', true ),
					'seo_description' => (string) get_term_meta( $tag->term_id, '_brio_seo_description', true ),
					'post_count'      => (int) $tag->count,
				];
			}
			return $rows;
		},
	] );
} );
