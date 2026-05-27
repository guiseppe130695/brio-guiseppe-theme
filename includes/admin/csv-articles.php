<?php
/**
 * Admin — Articles CSV importer (registered with the shared csv-job-engine)
 *
 * SEO-focused: lets you bulk-edit post_title, post_status, seo_title and
 * seo_description for every blog post. The article content itself is read-only
 * here (round-tripping rich content through CSV breaks too often). Use the
 * normal post editor or the WXR exporter if you need to migrate content.
 *
 * Match rule: post_id wins, then slug. Rows with neither are skipped.
 *
 * Columns: post_id, slug, post_title, post_status, category, tags,
 *          seo_title, seo_description
 *
 * @package Brio_Guiseppe
 * @since   1.7.0
 */

defined( 'ABSPATH' ) || exit;

add_action( 'admin_init', function () {
	brio_csv_register_importer( 'articles', [
		'label'      => __( 'Articles', 'brio-guiseppe' ),
		'chunk_size' => 20,
		'columns'    => [ 'post_id', 'slug', 'post_title', 'post_status', 'category', 'tags', 'seo_title', 'seo_description' ],
		'help'       => __( 'Match par post_id, sinon par slug. Le contenu de l\'article n\'est jamais modifié — seuls le titre, le statut et les champs SEO sont mis à jour.', 'brio-guiseppe' ),

		'row_processor' => function ( $data, &$job ) {
			// Resolve post.
			$post = null;
			if ( ! empty( $data['post_id'] ) ) {
				$p = get_post( (int) $data['post_id'] );
				if ( $p && 'post' === $p->post_type ) {
					$post = $p;
				}
			}
			if ( ! $post && ! empty( $data['slug'] ) ) {
				$found = get_posts( [
					'post_type'      => 'post',
					'name'           => sanitize_title( $data['slug'] ),
					'post_status'    => 'any',
					'posts_per_page' => 1,
				] );
				if ( ! empty( $found ) ) { $post = $found[0]; }
			}
			if ( ! $post ) {
				return [
					'verdict' => 'skp',
					'message' => sprintf(
						__( 'Article introuvable : %s', 'brio-guiseppe' ),
						$data['slug'] ?? $data['post_id'] ?? '?'
					),
				];
			}

			// Update title + status if provided (non-destructive).
			$post_update = [ 'ID' => $post->ID ];
			if ( isset( $data['post_title'] ) && '' !== trim( $data['post_title'] ) ) {
				$post_update['post_title'] = sanitize_text_field( $data['post_title'] );
			}
			if ( isset( $data['post_status'] ) && in_array( $data['post_status'], [ 'publish', 'draft', 'private', 'pending', 'future' ], true ) ) {
				$post_update['post_status'] = $data['post_status'];
			}
			if ( count( $post_update ) > 1 ) {
				wp_update_post( $post_update );
			}

			// SEO override (always processed if column present).
			if ( isset( $data['seo_title'] ) ) {
				$v = sanitize_text_field( $data['seo_title'] );
				if ( '' === $v ) { delete_post_meta( $post->ID, '_brio_seo_title' ); }
				else { update_post_meta( $post->ID, '_brio_seo_title', $v ); }
			}
			if ( isset( $data['seo_description'] ) ) {
				$v = sanitize_textarea_field( $data['seo_description'] );
				if ( '' === $v ) { delete_post_meta( $post->ID, '_brio_seo_description' ); }
				else { update_post_meta( $post->ID, '_brio_seo_description', $v ); }
			}

			return [ 'verdict' => 'upd', 'message' => get_the_title( $post ) . ' (#' . $post->ID . ')' ];
		},

		'export_rows' => function () {
			$posts = get_posts( [
				'post_type'      => 'post',
				'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
				'posts_per_page' => -1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			] );
			$rows = [];
			foreach ( $posts as $post ) {
				$cats = get_the_category( $post->ID );
				$tags = get_the_tags( $post->ID );
				$rows[] = [
					'post_id'         => $post->ID,
					'slug'            => $post->post_name,
					'post_title'      => $post->post_title,
					'post_status'     => $post->post_status,
					'category'        => ! empty( $cats ) ? $cats[0]->name : '',
					'tags'            => $tags ? implode( ', ', wp_list_pluck( $tags, 'name' ) ) : '',
					'seo_title'       => (string) get_post_meta( $post->ID, '_brio_seo_title', true ),
					'seo_description' => (string) get_post_meta( $post->ID, '_brio_seo_description', true ),
				];
			}
			return $rows;
		},
	] );
} );
