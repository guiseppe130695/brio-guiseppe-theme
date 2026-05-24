<?php
/**
 * Admin — Landing Page CSV Importer
 *
 * Adds a page under Tools > Import Landing. Accepts a UTF-8 CSV where each
 * row creates (or updates) one landing page with all section meta fields.
 *
 * CSV column names follow the pattern:  {section}_{field}
 * Reserved columns: page_title, page_slug (required), page_status (optional).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ── Register admin page ── */
function brio_csv_import_menu() {
	add_management_page(
		__( 'Import Landing Pages', 'brio-guiseppe' ),
		__( 'Import Landing', 'brio-guiseppe' ),
		'manage_options',
		'brio-landing-import',
		'brio_csv_import_page'
	);
}
add_action( 'admin_menu', 'brio_csv_import_menu' );

/* ── Map: CSV column → ( section, field, sanitizer ) ── */
function brio_csv_field_map() {
	return [
		// Hero
		'hero_title'              => [ 'hero',       'title',       'text' ],
		'hero_subtitle'           => [ 'hero',       'subtitle',    'textarea' ],
		// About
		'about_overline'          => [ 'about',      'overline',    'text' ],
		'about_heading'           => [ 'about',      'heading',     'text' ],
		'about_description'       => [ 'about',      'description', 'textarea' ],
		'about_cta_label'         => [ 'about',      'cta_label',   'text' ],
		'about_cta_url'           => [ 'about',      'cta_url',     'url' ],
		'about_image'             => [ 'about',      'image',       'url' ],
		// Partners
		'partners_label'          => [ 'partners',   'label',       'text' ],
		'partners_items'          => [ 'partners',   'items',       'json' ],
		// Programs
		'programs_overline'       => [ 'programs',   'overline',    'text' ],
		'programs_heading'        => [ 'programs',   'heading',     'text' ],
		'programs_items'          => [ 'programs',   'items',       'json' ],
		'programs_cta_label'      => [ 'programs',   'cta_label',   'text' ],
		'programs_cta_url'        => [ 'programs',   'cta_url',     'url' ],
		'programs_note'           => [ 'programs',   'note',        'text' ],
		// Philosophy
		'philosophy_overline'     => [ 'philosophy', 'overline',    'text' ],
		'philosophy_heading'      => [ 'philosophy', 'heading',     'text' ],
		'philosophy_description'  => [ 'philosophy', 'description', 'textarea' ],
		'philosophy_visual'       => [ 'philosophy', 'visual',      'url' ],
		'philosophy_features'     => [ 'philosophy', 'features',    'json' ],
		// Showcase
		'showcase_bg'             => [ 'showcase',   'bg',          'url' ],
		'showcase_images'         => [ 'showcase',   'images',      'json' ],
		// Fun Facts
		'funfacts_overline'       => [ 'funfacts',   'overline',    'text' ],
		'funfacts_heading'        => [ 'funfacts',   'heading',     'text' ],
		'funfacts_cards'          => [ 'funfacts',   'cards',       'json' ],
		// Pricing
		'pricing_overline'        => [ 'pricing',    'overline',    'text' ],
		'pricing_heading'         => [ 'pricing',    'heading',     'text' ],
		'pricing_cta_label'       => [ 'pricing',    'cta_label',   'text' ],
		'pricing_cta_url'         => [ 'pricing',    'cta_url',     'url' ],
		'pricing_plans'           => [ 'pricing',    'plans',       'json' ],
		// FAQs
		'faqs_overline'           => [ 'faqs',       'overline',    'text' ],
		'faqs_heading'            => [ 'faqs',       'heading',     'text' ],
		'faqs_visual'             => [ 'faqs',       'visual',      'url' ],
		'faqs_items'              => [ 'faqs',       'items',       'json' ],
		// CTA
		'cta_heading'             => [ 'cta',        'heading',     'text' ],
		'cta_taglines'            => [ 'cta',        'taglines',    'json' ],
		'cta_label'               => [ 'cta',        'label',       'text' ],
		'cta_url'                 => [ 'cta',        'url',         'url' ],
	];
}

/* ── Sanitize a single value ── */
function brio_csv_sanitize( $value, $type ) {
	switch ( $type ) {
		case 'url':
			return esc_url_raw( $value );
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'json':
			if ( empty( trim( $value ) ) ) return '';
			$decoded = json_decode( $value, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
				return wp_json_encode( $decoded );
			}
			return '';
		default:
			return sanitize_text_field( $value );
	}
}

/* ── Process uploaded CSV ── */
function brio_csv_process( $file_path ) {
	$handle = fopen( $file_path, 'r' );
	if ( ! $handle ) {
		return new WP_Error( 'open_failed', __( 'Impossible d\'ouvrir le fichier.', 'brio-guiseppe' ) );
	}

	// Detect and strip BOM
	$bom = fread( $handle, 3 );
	if ( $bom !== "\xEF\xBB\xBF" ) {
		rewind( $handle );
	}

	$headers = fgetcsv( $handle, 0, ',' );
	if ( ! $headers ) {
		fclose( $handle );
		return new WP_Error( 'no_headers', __( 'Le fichier CSV est vide ou invalide.', 'brio-guiseppe' ) );
	}

	$headers  = array_map( 'trim', $headers );
	$field_map = brio_csv_field_map();
	$results  = [ 'created' => [], 'updated' => [], 'errors' => [] ];

	while ( ( $row = fgetcsv( $handle, 0, ',' ) ) !== false ) {
		if ( count( $row ) !== count( $headers ) ) continue;

		$data = array_combine( $headers, $row );

		$title  = sanitize_text_field( $data['page_title'] ?? '' );
		$slug   = sanitize_title( $data['page_slug'] ?? '' );
		$status = in_array( $data['page_status'] ?? 'draft', [ 'publish', 'draft', 'private' ], true )
			? $data['page_status']
			: 'draft';

		if ( ! $title || ! $slug ) {
			$results['errors'][] = sprintf( __( 'Ligne ignorée — page_title ou page_slug manquant.', 'brio-guiseppe' ) );
			continue;
		}

		// Check if page with this slug already exists
		$existing = get_page_by_path( $slug, OBJECT, 'page' );
		$post_id  = $existing ? $existing->ID : 0;

		$post_data = [
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_status'  => $status,
			'post_type'    => 'page',
			'page_template' => 'template-landing.php',
		];

		if ( $post_id ) {
			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
			$results['updated'][] = $title;
		} else {
			$post_id = wp_insert_post( $post_data );
			if ( is_wp_error( $post_id ) ) {
				$results['errors'][] = sprintf( __( 'Erreur création "%s" : %s', 'brio-guiseppe' ), $title, $post_id->get_error_message() );
				continue;
			}
			// Set page template meta
			update_post_meta( $post_id, '_wp_page_template', 'template-landing.php' );
			$results['created'][] = $title;
		}

		// Save all meta fields
		foreach ( $field_map as $col => $def ) {
			if ( ! isset( $data[ $col ] ) || '' === trim( $data[ $col ] ) ) continue;
			[ $section, $field, $sanitizer ] = $def;
			$meta_key = '_brio_landing_' . $section . '_' . $field;
			$clean    = brio_csv_sanitize( $data[ $col ], $sanitizer );
			update_post_meta( $post_id, $meta_key, $clean );
		}
	}

	fclose( $handle );
	return $results;
}

/* ── Admin page HTML ── */
function brio_csv_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$results = null;

	if (
		isset( $_POST['brio_csv_import_nonce'] ) &&
		wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_csv_import_nonce'] ) ), 'brio_csv_import' ) &&
		! empty( $_FILES['brio_csv_file']['tmp_name'] )
	) {
		$file = $_FILES['brio_csv_file'];

		if ( $file['error'] !== UPLOAD_ERR_OK ) {
			$results = new WP_Error( 'upload_error', __( 'Erreur lors de l\'upload.', 'brio-guiseppe' ) );
		} elseif ( pathinfo( $file['name'], PATHINFO_EXTENSION ) !== 'csv' ) {
			$results = new WP_Error( 'wrong_type', __( 'Le fichier doit être un .csv', 'brio-guiseppe' ) );
		} else {
			$results = brio_csv_process( $file['tmp_name'] );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Import Landing Pages — CSV', 'brio-guiseppe' ); ?></h1>

		<?php if ( is_wp_error( $results ) ) : ?>
			<div class="notice notice-error"><p><?php echo esc_html( $results->get_error_message() ); ?></p></div>
		<?php elseif ( is_array( $results ) ) : ?>
			<?php if ( ! empty( $results['created'] ) ) : ?>
				<div class="notice notice-success">
					<p><?php printf( esc_html__( '%d page(s) créée(s) : %s', 'brio-guiseppe' ), count( $results['created'] ), implode( ', ', array_map( 'esc_html', $results['created'] ) ) ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( ! empty( $results['updated'] ) ) : ?>
				<div class="notice notice-info">
					<p><?php printf( esc_html__( '%d page(s) mise(s) à jour : %s', 'brio-guiseppe' ), count( $results['updated'] ), implode( ', ', array_map( 'esc_html', $results['updated'] ) ) ); ?></p>
				</div>
			<?php endif; ?>
			<?php foreach ( $results['errors'] as $err ) : ?>
				<div class="notice notice-warning"><p><?php echo esc_html( $err ); ?></p></div>
			<?php endforeach; ?>
		<?php endif; ?>

		<p><?php esc_html_e( 'Importez un fichier CSV UTF-8 pour créer ou mettre à jour des landing pages. Une ligne = une page.', 'brio-guiseppe' ); ?></p>

		<form method="post" enctype="multipart/form-data">
			<?php wp_nonce_field( 'brio_csv_import', 'brio_csv_import_nonce' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="brio_csv_file"><?php esc_html_e( 'Fichier CSV', 'brio-guiseppe' ); ?></label></th>
					<td><input type="file" name="brio_csv_file" id="brio_csv_file" accept=".csv" /></td>
				</tr>
			</table>
			<?php submit_button( __( 'Importer', 'brio-guiseppe' ) ); ?>
		</form>

		<hr>
		<h2><?php esc_html_e( 'Colonnes disponibles', 'brio-guiseppe' ); ?></h2>
		<p><strong><?php esc_html_e( 'Colonnes obligatoires :', 'brio-guiseppe' ); ?></strong> <code>page_title</code>, <code>page_slug</code></p>
		<p><strong><?php esc_html_e( 'Colonne optionnelle :', 'brio-guiseppe' ); ?></strong> <code>page_status</code> (publish / draft / private — défaut : draft)</p>
		<p><strong><?php esc_html_e( 'Colonnes de contenu :', 'brio-guiseppe' ); ?></strong></p>
		<table class="widefat striped" style="max-width:700px">
			<thead><tr><th><?php esc_html_e( 'Colonne CSV', 'brio-guiseppe' ); ?></th><th><?php esc_html_e( 'Type', 'brio-guiseppe' ); ?></th></tr></thead>
			<tbody>
			<?php foreach ( brio_csv_field_map() as $col => $def ) : ?>
				<tr><td><code><?php echo esc_html( $col ); ?></code></td><td><?php echo esc_html( $def[2] ); ?></td></tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<hr>
		<h2><?php esc_html_e( 'Export & Modèle', 'brio-guiseppe' ); ?></h2>
		<p>
			<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'tools.php?page=brio-landing-import&brio_csv_export=1' ), 'brio_csv_export' ) ); ?>" class="button button-primary">
				<?php esc_html_e( 'Exporter toutes les landing pages (.csv)', 'brio-guiseppe' ); ?>
			</a>
			&nbsp;
			<a href="<?php echo esc_url( admin_url( 'tools.php?page=brio-landing-import&brio_csv_template=1' ) ); ?>" class="button">
				<?php esc_html_e( 'Télécharger le modèle vide (.csv)', 'brio-guiseppe' ); ?>
			</a>
		</p>
	</div>
	<?php
}

/* ── Export all landing pages to CSV ── */
function brio_csv_export() {
	if (
		! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['brio_csv_export'] ) ||
		( $_GET['page'] ?? '' ) !== 'brio-landing-import'
	) {
		return;
	}

	if (
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'brio_csv_export' )
	) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ) );
	}

	$field_map = brio_csv_field_map();
	$cols      = array_merge( [ 'page_title', 'page_slug', 'page_status' ], array_keys( $field_map ) );

	$pages = get_posts( [
		'post_type'      => 'page',
		'post_status'    => [ 'publish', 'draft', 'private' ],
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'template-landing.php',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	] );

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="landing-export-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM
	fputcsv( $out, $cols );

	foreach ( $pages as $page ) {
		$row = [
			$page->post_title,
			$page->post_name,
			$page->post_status,
		];
		foreach ( $field_map as $col => $def ) {
			[ $section, $field ] = $def;
			$row[] = get_post_meta( $page->ID, '_brio_landing_' . $section . '_' . $field, true );
		}
		fputcsv( $out, $row );
	}

	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_csv_export' );

/* ── Template CSV download ── */
function brio_csv_template_download() {
	if (
		! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['brio_csv_template'] ) ||
		( $_GET['page'] ?? '' ) !== 'brio-landing-import'
	) {
		return;
	}

	$cols = array_merge(
		[ 'page_title', 'page_slug', 'page_status' ],
		array_keys( brio_csv_field_map() )
	);

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="landing-import-template.csv"' );
	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) ); // UTF-8 BOM
	fputcsv( $out, $cols );
	fputcsv( $out, array_fill( 0, count( $cols ), '' ) ); // empty example row
	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_csv_template_download' );
