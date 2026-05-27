<?php
/**
 * Admin — Tags export (CSV)
 *
 * Generates a UTF-8 CSV of every tag with all editable fields including the
 * custom SEO title and meta description added by our SEO module. Round-trips
 * with the import handler at the bottom of this file.
 *
 * Columns:
 *   slug, name, description, seo_title, seo_description, post_count
 *
 * @package Brio_Guiseppe
 * @since   1.6.0
 */

defined( 'ABSPATH' ) || exit;

/* ── Register page under Tools ── */
function brio_tags_export_menu() {
	add_management_page(
		__( 'Export / Import Tags', 'brio-guiseppe' ),
		__( 'Tags CSV', 'brio-guiseppe' ),
		'manage_categories',
		'brio-tags-export',
		'brio_tags_export_page'
	);
}
add_action( 'admin_menu', 'brio_tags_export_menu' );

/* ── Export handler ── */
function brio_tags_export_download() {
	if (
		! is_admin() ||
		! current_user_can( 'manage_categories' ) ||
		! isset( $_GET['brio_tags_export'] ) ||
		( $_GET['page'] ?? '' ) !== 'brio-tags-export'
	) {
		return;
	}
	if (
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'brio_tags_export' )
	) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ) );
	}

	$tags = get_tags( [ 'hide_empty' => false ] );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="tags-export-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM (Excel-friendly)

	fputcsv( $out, [
		'slug',
		'name',
		'description',
		'seo_title',
		'seo_description',
		'post_count',
	] );

	foreach ( $tags as $tag ) {
		fputcsv( $out, [
			$tag->slug,
			$tag->name,
			$tag->description,
			(string) get_term_meta( $tag->term_id, '_brio_seo_title', true ),
			(string) get_term_meta( $tag->term_id, '_brio_seo_description', true ),
			(int) $tag->count,
		] );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_tags_export_download' );

/* ── Import handler ── */
function brio_tags_import_process() {
	if (
		! isset( $_POST['brio_tags_import_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_tags_import_nonce'] ) ), 'brio_tags_import' ) ||
		! current_user_can( 'manage_categories' ) ||
		empty( $_FILES['brio_tags_file']['tmp_name'] )
	) {
		return null;
	}

	$file = $_FILES['brio_tags_file'];
	if ( UPLOAD_ERR_OK !== $file['error'] ) {
		return new WP_Error( 'upload', __( 'Upload échoué.', 'brio-guiseppe' ) );
	}
	if ( 'csv' !== strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) ) ) {
		return new WP_Error( 'wrong_type', __( 'Le fichier doit être un .csv', 'brio-guiseppe' ) );
	}

	$handle = fopen( $file['tmp_name'], 'r' );
	if ( ! $handle ) {
		return new WP_Error( 'open', __( 'Impossible d\'ouvrir le CSV.', 'brio-guiseppe' ) );
	}

	// Skip BOM + read headers.
	$bom = fread( $handle, 3 );
	if ( "\xEF\xBB\xBF" !== $bom ) {
		rewind( $handle );
	}
	$headers = fgetcsv( $handle, 0, ',' );
	if ( ! $headers ) {
		fclose( $handle );
		return new WP_Error( 'empty', __( 'CSV vide ou invalide.', 'brio-guiseppe' ) );
	}
	$headers = array_map( 'trim', $headers );

	$created = 0;
	$updated = 0;
	$errors  = [];

	while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
		if ( count( $row ) !== count( $headers ) ) {
			continue;
		}
		$data = array_combine( $headers, $row );

		$slug = sanitize_title( $data['slug'] ?? '' );
		$name = sanitize_text_field( $data['name'] ?? '' );
		if ( '' === $slug || '' === $name ) {
			$errors[] = __( 'Ligne ignorée : slug ou name vide.', 'brio-guiseppe' );
			continue;
		}

		$existing = get_term_by( 'slug', $slug, 'post_tag' );
		$args = [
			'description' => sanitize_textarea_field( $data['description'] ?? '' ),
			'slug'        => $slug,
		];

		if ( $existing ) {
			wp_update_term( $existing->term_id, 'post_tag', array_merge( [ 'name' => $name ], $args ) );
			$term_id = $existing->term_id;
			$updated++;
		} else {
			$res = wp_insert_term( $name, 'post_tag', $args );
			if ( is_wp_error( $res ) ) {
				$errors[] = sprintf( '%s : %s', $slug, $res->get_error_message() );
				continue;
			}
			$term_id = $res['term_id'];
			$created++;
		}

		// SEO fields (optional columns).
		if ( isset( $data['seo_title'] ) ) {
			$v = sanitize_text_field( $data['seo_title'] );
			if ( '' === $v ) {
				delete_term_meta( $term_id, '_brio_seo_title' );
			} else {
				update_term_meta( $term_id, '_brio_seo_title', $v );
			}
		}
		if ( isset( $data['seo_description'] ) ) {
			$v = sanitize_textarea_field( $data['seo_description'] );
			if ( '' === $v ) {
				delete_term_meta( $term_id, '_brio_seo_description' );
			} else {
				update_term_meta( $term_id, '_brio_seo_description', $v );
			}
		}
	}

	fclose( $handle );

	return [ 'created' => $created, 'updated' => $updated, 'errors' => $errors ];
}

/* ── Page renderer ── */
function brio_tags_export_page() {
	if ( ! current_user_can( 'manage_categories' ) ) {
		return;
	}
	$result      = brio_tags_import_process();
	$tag_count   = wp_count_terms( [ 'taxonomy' => 'post_tag', 'hide_empty' => false ] );
	$export_url  = wp_nonce_url(
		admin_url( 'tools.php?page=brio-tags-export&brio_tags_export=1' ),
		'brio_tags_export'
	);
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Export / Import Tags (CSV)', 'brio-guiseppe' ); ?></h1>

		<?php if ( is_wp_error( $result ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $result->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $result ) ) : ?>
			<div class="notice notice-success">
				<p>
					<?php printf(
						/* translators: 1: nb created 2: nb updated */
						esc_html__( 'Import terminé : %1$d créés, %2$d mis à jour.', 'brio-guiseppe' ),
						(int) $result['created'],
						(int) $result['updated']
					); ?>
				</p>
			</div>
			<?php foreach ( $result['errors'] as $err ) : ?>
				<div class="notice notice-warning"><p><?php echo esc_html( $err ); ?></p></div>
			<?php endforeach; ?>
		<?php endif; ?>

		<div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;max-width:980px;margin-top:18px">

			<div class="card" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:22px 24px">
				<h2 style="margin-top:0"><?php esc_html_e( 'Export', 'brio-guiseppe' ); ?></h2>
				<p style="color:#475569">
					<?php
					printf(
						/* translators: %d: tag count */
						esc_html__( '%d tag(s) à exporter, avec leurs champs SEO personnalisés.', 'brio-guiseppe' ),
						(int) $tag_count
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( $export_url ); ?>" class="button button-primary button-hero">
						<?php esc_html_e( 'Télécharger le CSV', 'brio-guiseppe' ); ?>
					</a>
				</p>
				<p style="font-size:12px;color:#64748b">
					<?php esc_html_e( 'Colonnes : slug, name, description, seo_title, seo_description, post_count.', 'brio-guiseppe' ); ?>
				</p>
			</div>

			<div class="card" style="background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:22px 24px">
				<h2 style="margin-top:0"><?php esc_html_e( 'Import / Mise à jour', 'brio-guiseppe' ); ?></h2>
				<p style="color:#475569">
					<?php esc_html_e( 'Charge un CSV avec les mêmes colonnes. Les tags existants (match par slug) sont mis à jour, les nouveaux créés.', 'brio-guiseppe' ); ?>
				</p>
				<form method="post" enctype="multipart/form-data">
					<?php wp_nonce_field( 'brio_tags_import', 'brio_tags_import_nonce' ); ?>
					<input type="file" name="brio_tags_file" accept=".csv" required />
					<p>
						<?php submit_button( __( 'Importer', 'brio-guiseppe' ), 'primary', 'submit', false ); ?>
					</p>
				</form>
				<p style="font-size:12px;color:#64748b">
					<?php esc_html_e( 'Colonnes obligatoires : slug, name. Les autres colonnes (description, seo_title, seo_description) sont optionnelles.', 'brio-guiseppe' ); ?>
				</p>
			</div>

		</div>
	</div>
	<?php
}
