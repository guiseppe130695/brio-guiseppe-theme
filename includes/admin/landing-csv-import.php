<?php
/**
 * Admin — Landing Page CSV Importer (AJAX chunked)
 *
 * Tools > Import Landing. Accepts a UTF-8 CSV where each row creates (or
 * updates) one landing page with all section meta fields. Rows are processed
 * in small AJAX chunks so large files (200+ pages) finish without PHP timeouts
 * and show live progress + a streaming log. Job state is persisted in a
 * transient, so a closed tab can resume.
 *
 * CSV column names follow the pattern:  {section}_{field}
 * Reserved columns: page_title, page_slug (required), page_status (optional).
 *
 * @package Brio_Guiseppe
 * @since   1.1.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_CSV_CHUNK_SIZE   = 10;
const BRIO_CSV_JOB_TTL      = DAY_IN_SECONDS;
const BRIO_CSV_LOG_TAIL     = 400; // keep last N log lines in transient
const BRIO_CSV_JOB_OPTION   = 'brio_csv_active_job';

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
	$map = [
		'hero_title'        => [ 'hero',  'title',       'text' ],
		'hero_subtitle'     => [ 'hero',  'subtitle',    'textarea' ],
		'about_overline'    => [ 'about', 'overline',    'text' ],
		'about_heading'     => [ 'about', 'heading',     'text' ],
		'about_description' => [ 'about', 'description', 'textarea' ],
		'about_cta_label'   => [ 'about', 'cta_label',   'text' ],
		'about_cta_url'     => [ 'about', 'cta_url',     'url' ],
		'about_image'       => [ 'about', 'image',       'url' ],
		// Contenu unique par landing (anti scaled-content)
		'unique_heading'    => [ 'unique', 'heading', 'text' ],
		'unique_content'    => [ 'unique', 'content', 'textarea' ],
		'partners_label'    => [ 'partners', 'label',    'text' ],
	];
	for ( $n = 1; $n <= 6; $n++ ) {
		$map[ "partners_logo{$n}_url" ] = [ "partners_logo{$n}", 'url', 'url' ];
		$map[ "partners_logo{$n}_alt" ] = [ "partners_logo{$n}", 'alt', 'text' ];
	}

	$map['programs_overline'] = [ 'programs', 'overline', 'text' ];
	$map['programs_heading']  = [ 'programs', 'heading',  'text' ];
	for ( $n = 1; $n <= 6; $n++ ) {
		$map[ "programs_item{$n}_title" ]   = [ "programs_item{$n}", 'title',   'text' ];
		$map[ "programs_item{$n}_content" ] = [ "programs_item{$n}", 'content', 'textarea' ];
	}
	$map['programs_cta_label'] = [ 'programs', 'cta_label', 'text' ];
	$map['programs_cta_url']   = [ 'programs', 'cta_url',   'url' ];
	$map['programs_note']      = [ 'programs', 'note',      'text' ];

	$map['philosophy_overline']    = [ 'philosophy', 'overline',    'text' ];
	$map['philosophy_heading']     = [ 'philosophy', 'heading',     'text' ];
	$map['philosophy_description'] = [ 'philosophy', 'description', 'textarea' ];
	$map['philosophy_visual']      = [ 'philosophy', 'visual',      'url' ];
	for ( $n = 1; $n <= 3; $n++ ) {
		$map[ "philosophy_feature{$n}_icon" ]  = [ "philosophy_feature{$n}", 'icon',  'text' ];
		$map[ "philosophy_feature{$n}_title" ] = [ "philosophy_feature{$n}", 'title', 'text' ];
		$map[ "philosophy_feature{$n}_text" ]  = [ "philosophy_feature{$n}", 'text',  'text' ];
	}

	$map['showcase_bg']     = [ 'showcase', 'bg',     'url' ];
	$map['showcase_images'] = [ 'showcase', 'images', 'json' ];

	$map['funfacts_overline'] = [ 'funfacts', 'overline', 'text' ];
	$map['funfacts_heading']  = [ 'funfacts', 'heading',  'text' ];
	for ( $n = 1; $n <= 4; $n++ ) {
		$map[ "funfacts_card{$n}_icon" ]   = [ "funfacts_card{$n}", 'icon',   'url' ];
		$map[ "funfacts_card{$n}_number" ] = [ "funfacts_card{$n}", 'number', 'text' ];
		$map[ "funfacts_card{$n}_suffix" ] = [ "funfacts_card{$n}", 'suffix', 'text' ];
		$map[ "funfacts_card{$n}_title" ]  = [ "funfacts_card{$n}", 'title',  'text' ];
	}

	$map['pricing_overline']  = [ 'pricing', 'overline',  'text' ];
	$map['pricing_heading']   = [ 'pricing', 'heading',   'text' ];
	$map['pricing_cta_label'] = [ 'pricing', 'cta_label', 'text' ];
	$map['pricing_cta_url']   = [ 'pricing', 'cta_url',   'url' ];
	foreach ( [ 1, 2, 3 ] as $n ) {
		$p = "plan{$n}";
		$map[ "pricing_{$p}_title" ]        = [ "pricing_{$p}", 'title',        'text' ];
		$map[ "pricing_{$p}_rooms" ]        = [ "pricing_{$p}", 'rooms',        'text' ];
		$map[ "pricing_{$p}_price" ]        = [ "pricing_{$p}", 'price',        'text' ];
		$map[ "pricing_{$p}_price_prefix" ] = [ "pricing_{$p}", 'price_prefix', 'text' ];
		$map[ "pricing_{$p}_tagline" ]      = [ "pricing_{$p}", 'tagline',      'text' ];
		$map[ "pricing_{$p}_ideal" ]        = [ "pricing_{$p}", 'ideal',        'text' ];
		$map[ "pricing_{$p}_cta_label" ]    = [ "pricing_{$p}", 'cta_label',    'text' ];
		$map[ "pricing_{$p}_cta_url" ]      = [ "pricing_{$p}", 'cta_url',      'url' ];
		$map[ "pricing_{$p}_includes" ]     = [ "pricing_{$p}", 'includes',     'textarea' ];
	}

	$map['faqs_overline'] = [ 'faqs', 'overline', 'text' ];
	$map['faqs_heading']  = [ 'faqs', 'heading',  'text' ];
	$map['faqs_visual']   = [ 'faqs', 'visual',   'url' ];
	for ( $n = 1; $n <= 8; $n++ ) {
		$map[ "faqs_item{$n}_question" ] = [ "faqs_item{$n}", 'question', 'text' ];
		$map[ "faqs_item{$n}_answer" ]   = [ "faqs_item{$n}", 'answer',   'textarea' ];
	}

	// Rating (visible dans le hero + AggregateRating JSON-LD)
	$map['rating_value']   = [ 'rating', 'value',   'text' ];
	$map['rating_count']   = [ 'rating', 'count',   'text' ];
	$map['rating_caption'] = [ 'rating', 'caption', 'text' ];
	$map['rating_href']    = [ 'rating', 'href',    'url' ];

	$map['cta_heading']  = [ 'cta', 'heading',  'text' ];
	$map['cta_tagline1'] = [ 'cta', 'tagline1', 'text' ];
	$map['cta_tagline2'] = [ 'cta', 'tagline2', 'text' ];
	$map['cta_tagline3'] = [ 'cta', 'tagline3', 'text' ];
	$map['cta_label']    = [ 'cta', 'label',    'text' ];
	$map['cta_url']      = [ 'cta', 'url',      'url' ];

	return $map;
}

/* ── Sanitize a single value ── */
function brio_csv_sanitize( $value, $type ) {
	switch ( $type ) {
		case 'url':
			return esc_url_raw( $value );
		case 'textarea':
			return sanitize_textarea_field( $value );
		case 'json':
			if ( '' === trim( $value ) ) {
				return '';
			}
			$decoded = json_decode( $value, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
				return wp_json_encode( $decoded );
			}
			return '';
		default:
			return sanitize_text_field( $value );
	}
}

/* ── Job storage helpers ── */
function brio_csv_job_dir() {
	$uploads = wp_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . 'brio-import';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	return $dir;
}

function brio_csv_job_get( $job_id ) {
	return get_transient( 'brio_csv_job_' . $job_id );
}

function brio_csv_job_save( $job_id, $job ) {
	if ( count( $job['log'] ) > BRIO_CSV_LOG_TAIL ) {
		$job['log'] = array_slice( $job['log'], -BRIO_CSV_LOG_TAIL );
	}
	set_transient( 'brio_csv_job_' . $job_id, $job, BRIO_CSV_JOB_TTL );
	update_option( BRIO_CSV_JOB_OPTION, $job_id, false );
}

function brio_csv_job_clear( $job_id ) {
	$job = brio_csv_job_get( $job_id );
	if ( $job && ! empty( $job['file'] ) && file_exists( $job['file'] ) ) {
		@unlink( $job['file'] );
	}
	delete_transient( 'brio_csv_job_' . $job_id );
	if ( get_option( BRIO_CSV_JOB_OPTION ) === $job_id ) {
		delete_option( BRIO_CSV_JOB_OPTION );
	}
}

/* ── AJAX: start a new job (upload + index headers + row count) ── */
function brio_csv_ajax_start() {
	check_ajax_referer( 'brio_csv_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission refusée.', 'brio-guiseppe' ) ] );
	}
	if ( empty( $_FILES['file']['tmp_name'] ) || UPLOAD_ERR_OK !== $_FILES['file']['error'] ) {
		wp_send_json_error( [ 'message' => __( 'Upload échoué.', 'brio-guiseppe' ) ] );
	}
	if ( 'csv' !== strtolower( pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ) ) {
		wp_send_json_error( [ 'message' => __( 'Le fichier doit être un .csv', 'brio-guiseppe' ) ] );
	}

	$job_id = wp_generate_password( 12, false, false );
	$dest   = trailingslashit( brio_csv_job_dir() ) . $job_id . '.csv';
	if ( ! move_uploaded_file( $_FILES['file']['tmp_name'], $dest ) ) {
		wp_send_json_error( [ 'message' => __( 'Impossible de sauvegarder le fichier.', 'brio-guiseppe' ) ] );
	}

	// Read headers + count rows
	$h = fopen( $dest, 'r' );
	if ( ! $h ) {
		@unlink( $dest );
		wp_send_json_error( [ 'message' => __( 'Impossible d\'ouvrir le CSV.', 'brio-guiseppe' ) ] );
	}
	$bom = fread( $h, 3 );
	if ( "\xEF\xBB\xBF" !== $bom ) {
		rewind( $h );
	}
	$headers = fgetcsv( $h, 0, ',' );
	if ( ! $headers ) {
		fclose( $h );
		@unlink( $dest );
		wp_send_json_error( [ 'message' => __( 'CSV vide ou invalide.', 'brio-guiseppe' ) ] );
	}
	$total = 0;
	while ( false !== fgetcsv( $h, 0, ',' ) ) {
		$total++;
	}
	fclose( $h );

	$job = [
		'id'         => $job_id,
		'file'       => $dest,
		'headers'    => array_map( 'trim', $headers ),
		'total'      => $total,
		'cursor'     => 0,       // 0-indexed row pointer (after header)
		'created'    => 0,
		'updated'    => 0,
		'skipped'    => 0,
		'errors'     => 0,
		'seen_slugs' => [], // track slugs already processed in this CSV
		'log'        => [],
		'started_at' => time(),
		'done'       => false,
	];
	brio_csv_job_save( $job_id, $job );

	wp_send_json_success( [
		'job_id' => $job_id,
		'total'  => $total,
	] );
}
add_action( 'wp_ajax_brio_csv_start', 'brio_csv_ajax_start' );

/* ── AJAX: process one chunk ── */
function brio_csv_ajax_chunk() {
	check_ajax_referer( 'brio_csv_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission refusée.', 'brio-guiseppe' ) ] );
	}

	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	$job    = brio_csv_job_get( $job_id );
	if ( ! $job ) {
		wp_send_json_error( [ 'message' => __( 'Job introuvable ou expiré.', 'brio-guiseppe' ) ] );
	}
	if ( $job['done'] ) {
		wp_send_json_success( brio_csv_job_status_payload( $job ) );
	}

	$h = fopen( $job['file'], 'r' );
	if ( ! $h ) {
		wp_send_json_error( [ 'message' => __( 'Fichier introuvable.', 'brio-guiseppe' ) ] );
	}
	// Skip BOM + header
	$bom = fread( $h, 3 );
	if ( "\xEF\xBB\xBF" !== $bom ) {
		rewind( $h );
	}
	fgetcsv( $h, 0, ',' ); // skip header

	// Skip already-processed rows
	for ( $i = 0; $i < $job['cursor']; $i++ ) {
		if ( false === fgetcsv( $h, 0, ',' ) ) {
			break;
		}
	}

	wp_suspend_cache_addition( true );
	wp_defer_term_counting( true );

	$field_map  = brio_csv_field_map();
	$processed  = 0;

	while ( $processed < BRIO_CSV_CHUNK_SIZE ) {
		$row = fgetcsv( $h, 0, ',' );
		if ( false === $row ) {
			$job['done'] = true;
			break;
		}
		$job['cursor']++;
		$row_no = $job['cursor'];

		if ( count( $row ) !== count( $job['headers'] ) ) {
			$job['errors']++;
			$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => __( 'Nombre de colonnes incorrect.', 'brio-guiseppe' ) ];
			$processed++;
			continue;
		}

		$data   = array_combine( $job['headers'], $row );
		$title  = sanitize_text_field( $data['page_title'] ?? '' );
		$slug   = sanitize_title( $data['page_slug'] ?? '' );
		$status = in_array( $data['page_status'] ?? 'draft', [ 'publish', 'draft', 'private' ], true ) ? $data['page_status'] : 'draft';

		if ( ! $title || ! $slug ) {
			$job['errors']++;
			$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => __( 'page_title ou page_slug manquant.', 'brio-guiseppe' ) ];
			$processed++;
			continue;
		}

		// Guard 1: duplicate slug within this CSV → skip
		if ( in_array( $slug, $job['seen_slugs'], true ) ) {
			$job['skipped']++;
			$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => sprintf( __( 'Doublon dans le CSV : slug "%s" déjà traité — ligne ignorée.', 'brio-guiseppe' ), $slug ) ];
			$processed++;
			continue;
		}
		$job['seen_slugs'][] = $slug;

		// Find existing landing page (same slug + landing template + not trashed)
		$found = get_posts( [
			'post_type'      => 'page',
			'name'           => $slug,
			'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future' ],
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'template-landing.php',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		] );
		$post_id = ! empty( $found ) ? (int) $found[0] : 0;

		// Guard 2: slug taken by another (non-landing) page or trashed page → skip
		if ( ! $post_id ) {
			$conflict = get_posts( [
				'post_type'      => 'page',
				'name'           => $slug,
				'post_status'    => [ 'publish', 'draft', 'private', 'pending', 'future', 'trash' ],
				'posts_per_page' => 1,
				'fields'         => 'ids',
				'no_found_rows'  => true,
			] );
			if ( ! empty( $conflict ) ) {
				$job['skipped']++;
				$conflict_id = (int) $conflict[0];
				$conflict_status = get_post_status( $conflict_id );
				$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => sprintf( __( 'Slug "%1$s" déjà pris par page #%2$d (%3$s) — non-landing ou corbeille. Ignorée pour éviter doublon.', 'brio-guiseppe' ), $slug, $conflict_id, $conflict_status ) ];
				$processed++;
				continue;
			}
		}

		$post_data = [
			'post_title'    => $title,
			'post_name'     => $slug,
			'post_status'   => $status,
			'post_type'     => 'page',
			'page_template' => 'template-landing.php',
		];

		if ( $post_id ) {
			$post_data['ID'] = $post_id;
			wp_update_post( $post_data );
			$job['updated']++;
			$job['log'][] = [ 't' => 'upd', 'r' => $row_no, 'm' => $title . ' (#' . $post_id . ')' ];
		} else {
			$post_id = wp_insert_post( $post_data, true );
			if ( is_wp_error( $post_id ) ) {
				$job['errors']++;
				$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => sprintf( '%s — %s', $title, $post_id->get_error_message() ) ];
				$processed++;
				continue;
			}
			update_post_meta( $post_id, '_wp_page_template', 'template-landing.php' );
			$job['created']++;
			$job['log'][] = [ 't' => 'new', 'r' => $row_no, 'm' => $title . ' (#' . $post_id . ')' ];
		}

		foreach ( $field_map as $col => $def ) {
			if ( ! isset( $data[ $col ] ) || '' === trim( $data[ $col ] ) ) {
				continue;
			}
			[ $section, $field, $sanitizer ] = $def;
			$meta_key = '_brio_landing_' . $section . '_' . $field;
			$clean    = brio_csv_sanitize( $data[ $col ], $sanitizer );
			update_post_meta( $post_id, $meta_key, $clean );
		}

		/* SEO override fields — stored under a different meta key prefix
		 * (_brio_seo_*) than the landing fields, so they get their own handling. */
		if ( isset( $data['seo_title'] ) ) {
			$v = sanitize_text_field( $data['seo_title'] );
			if ( '' === $v ) {
				delete_post_meta( $post_id, '_brio_seo_title' );
			} else {
				update_post_meta( $post_id, '_brio_seo_title', $v );
			}
		}
		if ( isset( $data['seo_description'] ) ) {
			$v = sanitize_textarea_field( $data['seo_description'] );
			if ( '' === $v ) {
				delete_post_meta( $post_id, '_brio_seo_description' );
			} else {
				update_post_meta( $post_id, '_brio_seo_description', $v );
			}
		}

		$processed++;
	}
	fclose( $h );

	if ( $job['cursor'] >= $job['total'] ) {
		$job['done'] = true;
	}

	brio_csv_job_save( $job_id, $job );

	if ( $job['done'] ) {
		// keep file around for inspection but free disk soon by clearing on next "new import"
		$job['log'][] = [ 't' => 'ok', 'r' => 0, 'm' => __( 'Import terminé.', 'brio-guiseppe' ) ];
		brio_csv_job_save( $job_id, $job );
	}

	wp_send_json_success( brio_csv_job_status_payload( $job ) );
}
add_action( 'wp_ajax_brio_csv_chunk', 'brio_csv_ajax_chunk' );

/* ── AJAX: status (used by Resume) ── */
function brio_csv_ajax_status() {
	check_ajax_referer( 'brio_csv_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}
	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	$job    = brio_csv_job_get( $job_id );
	if ( ! $job ) {
		wp_send_json_error( [ 'message' => __( 'Job introuvable.', 'brio-guiseppe' ) ] );
	}
	wp_send_json_success( brio_csv_job_status_payload( $job ) );
}
add_action( 'wp_ajax_brio_csv_status', 'brio_csv_ajax_status' );

/* ── AJAX: cancel ── */
function brio_csv_ajax_cancel() {
	check_ajax_referer( 'brio_csv_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error();
	}
	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	brio_csv_job_clear( $job_id );
	wp_send_json_success();
}
add_action( 'wp_ajax_brio_csv_cancel', 'brio_csv_ajax_cancel' );

function brio_csv_job_status_payload( $job ) {
	return [
		'job_id'  => $job['id'],
		'total'   => (int) $job['total'],
		'cursor'  => (int) $job['cursor'],
		'created' => (int) $job['created'],
		'updated' => (int) $job['updated'],
		'skipped' => (int) ( $job['skipped'] ?? 0 ),
		'errors'  => (int) $job['errors'],
		'log'     => $job['log'],
		'done'    => (bool) $job['done'],
	];
}

/* ── Admin page HTML + JS/CSS ── */
function brio_csv_import_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$nonce       = wp_create_nonce( 'brio_csv_ajax' );
	$ajax_url    = admin_url( 'admin-ajax.php' );
	$active_job  = get_option( BRIO_CSV_JOB_OPTION );
	$resume_job  = $active_job ? brio_csv_job_get( $active_job ) : null;
	$can_resume  = $resume_job && ! $resume_job['done'];
	$field_map   = brio_csv_field_map();
	?>
	<div class="wrap brio-import-wrap">
		<header class="brio-import-hero">
			<div>
				<h1><?php esc_html_e( 'Import Landing Pages', 'brio-guiseppe' ); ?></h1>
				<p class="brio-import-sub"><?php esc_html_e( 'Importez un CSV UTF-8 — une ligne = une landing page. Traitement par lots, sans timeout, avec progression en direct.', 'brio-guiseppe' ); ?></p>
			</div>
			<div class="brio-import-actions">
				<a class="button" href="<?php echo esc_url( admin_url( 'tools.php?page=brio-landing-import&brio_csv_template=1' ) ); ?>"><?php esc_html_e( 'Modèle vide', 'brio-guiseppe' ); ?></a>
				<a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( admin_url( 'tools.php?page=brio-landing-import&brio_csv_export=1' ), 'brio_csv_export' ) ); ?>"><?php esc_html_e( 'Exporter tout', 'brio-guiseppe' ); ?></a>
			</div>
		</header>

		<div class="brio-import-grid">
			<section class="brio-card brio-card--upload">
				<h2><?php esc_html_e( '1. Fichier CSV', 'brio-guiseppe' ); ?></h2>

				<?php if ( $can_resume ) : ?>
					<div class="brio-resume-banner">
						<strong><?php esc_html_e( 'Import en cours détecté', 'brio-guiseppe' ); ?></strong>
						<span><?php printf( esc_html__( '%1$d / %2$d lignes traitées.', 'brio-guiseppe' ), (int) $resume_job['cursor'], (int) $resume_job['total'] ); ?></span>
						<button class="button button-primary" id="brio-resume-btn" data-job="<?php echo esc_attr( $active_job ); ?>"><?php esc_html_e( 'Reprendre', 'brio-guiseppe' ); ?></button>
						<button class="button" id="brio-discard-btn" data-job="<?php echo esc_attr( $active_job ); ?>"><?php esc_html_e( 'Abandonner', 'brio-guiseppe' ); ?></button>
					</div>
				<?php endif; ?>

				<label class="brio-dropzone" id="brio-dropzone">
					<input type="file" id="brio-file" accept=".csv" hidden />
					<div class="brio-dropzone__inner">
						<span class="brio-dropzone__icon" aria-hidden="true">⬆</span>
						<strong><?php esc_html_e( 'Glissez un .csv ou cliquez pour parcourir', 'brio-guiseppe' ); ?></strong>
						<span class="brio-dropzone__hint"><?php esc_html_e( 'UTF-8 — séparateur virgule', 'brio-guiseppe' ); ?></span>
						<span class="brio-dropzone__file" id="brio-file-name"></span>
					</div>
				</label>

				<div class="brio-import-controls">
					<button class="button button-primary button-hero" id="brio-start-btn" disabled><?php esc_html_e( 'Lancer l\'import', 'brio-guiseppe' ); ?></button>
					<button class="button" id="brio-pause-btn" disabled><?php esc_html_e( 'Pause', 'brio-guiseppe' ); ?></button>
					<button class="button" id="brio-cancel-btn" disabled><?php esc_html_e( 'Annuler', 'brio-guiseppe' ); ?></button>
				</div>
			</section>

			<section class="brio-card brio-card--progress">
				<h2><?php esc_html_e( '2. Progression', 'brio-guiseppe' ); ?></h2>
				<div class="brio-progress">
					<div class="brio-progress__bar"><div class="brio-progress__fill" id="brio-progress-fill" style="width:0"></div></div>
					<div class="brio-progress__meta">
						<span id="brio-progress-text">0 / 0</span>
						<span id="brio-progress-pct">0%</span>
					</div>
				</div>
				<div class="brio-stats">
					<div class="brio-stat brio-stat--new"><span class="brio-stat__num" id="brio-stat-new">0</span><span><?php esc_html_e( 'Créées', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--upd"><span class="brio-stat__num" id="brio-stat-upd">0</span><span><?php esc_html_e( 'Mises à jour', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--skp"><span class="brio-stat__num" id="brio-stat-skp">0</span><span><?php esc_html_e( 'Ignorées', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--err"><span class="brio-stat__num" id="brio-stat-err">0</span><span><?php esc_html_e( 'Erreurs', 'brio-guiseppe' ); ?></span></div>
				</div>

				<h3><?php esc_html_e( 'Journal', 'brio-guiseppe' ); ?> <button type="button" class="button-link" id="brio-log-clear"><?php esc_html_e( 'Effacer', 'brio-guiseppe' ); ?></button></h3>
				<div class="brio-log" id="brio-log" aria-live="polite"></div>
			</section>
		</div>

		<details class="brio-card brio-card--ref">
			<summary><?php esc_html_e( 'Colonnes disponibles', 'brio-guiseppe' ); ?></summary>
			<p><strong><?php esc_html_e( 'Obligatoires :', 'brio-guiseppe' ); ?></strong> <code>page_title</code>, <code>page_slug</code> &nbsp;·&nbsp; <strong><?php esc_html_e( 'Optionnelle :', 'brio-guiseppe' ); ?></strong> <code>page_status</code> (publish / draft / private — défaut : draft)</p>
			<table class="widefat striped">
				<thead><tr><th><?php esc_html_e( 'Colonne CSV', 'brio-guiseppe' ); ?></th><th><?php esc_html_e( 'Type', 'brio-guiseppe' ); ?></th></tr></thead>
				<tbody>
				<?php foreach ( $field_map as $col => $def ) : ?>
					<tr><td><code><?php echo esc_html( $col ); ?></code></td><td><?php echo esc_html( $def[2] ); ?></td></tr>
				<?php endforeach; ?>
				<tr><td><code>seo_title</code></td><td>text — <em><?php esc_html_e( 'override SEO du <title> et og:title', 'brio-guiseppe' ); ?></em></td></tr>
				<tr><td><code>seo_description</code></td><td>textarea — <em><?php esc_html_e( 'override SEO de la meta description', 'brio-guiseppe' ); ?></em></td></tr>
				</tbody>
			</table>
		</details>
	</div>

	<style>
		.brio-import-wrap { max-width: 1180px; }
		.brio-import-hero { display:flex; justify-content:space-between; align-items:flex-end; gap:16px; padding:24px 28px; margin:16px 0 24px; background:linear-gradient(135deg,#0f172a 0%,#1e293b 100%); color:#f8fafc; border-radius:14px; box-shadow:0 8px 24px -12px rgba(15,23,42,.4); }
		.brio-import-hero h1 { color:#fff; font-size:24px; margin:0 0 4px; padding:0; }
		.brio-import-sub { color:#cbd5e1; margin:0; max-width:640px; }
		.brio-import-actions { display:flex; gap:8px; flex-shrink:0; }
		.brio-import-actions .button { background:rgba(255,255,255,.1); border-color:rgba(255,255,255,.2); color:#fff; }
		.brio-import-actions .button:hover { background:rgba(255,255,255,.18); color:#fff; }
		.brio-import-actions .button-primary { background:#fff; color:#0f172a; border-color:#fff; }
		.brio-import-actions .button-primary:hover { background:#e2e8f0; color:#0f172a; }

		.brio-import-grid { display:grid; grid-template-columns: 1fr 1.2fr; gap:20px; }
		@media (max-width: 960px) { .brio-import-grid { grid-template-columns: 1fr; } }

		.brio-card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:22px 24px; box-shadow:0 1px 2px rgba(0,0,0,.04); }
		.brio-card h2 { margin:0 0 16px; font-size:15px; text-transform:uppercase; letter-spacing:.05em; color:#475569; }
		.brio-card h3 { display:flex; justify-content:space-between; align-items:center; margin:18px 0 8px; font-size:13px; text-transform:uppercase; letter-spacing:.04em; color:#64748b; }

		.brio-resume-banner { display:flex; align-items:center; gap:12px; padding:12px 14px; margin-bottom:14px; background:#fef3c7; border:1px solid #fcd34d; border-radius:8px; }
		.brio-resume-banner strong { color:#92400e; }
		.brio-resume-banner span { flex:1; color:#78350f; font-size:13px; }

		.brio-dropzone { display:block; border:2px dashed #cbd5e1; border-radius:10px; padding:32px 16px; text-align:center; cursor:pointer; transition:all .15s; background:#f8fafc; }
		.brio-dropzone:hover, .brio-dropzone.is-drag { border-color:#3b82f6; background:#eff6ff; }
		.brio-dropzone__inner { display:flex; flex-direction:column; gap:6px; align-items:center; }
		.brio-dropzone__icon { font-size:28px; color:#3b82f6; }
		.brio-dropzone__hint { font-size:12px; color:#64748b; }
		.brio-dropzone__file { font-size:13px; color:#0f172a; font-weight:600; margin-top:6px; }

		.brio-import-controls { display:flex; gap:8px; margin-top:16px; }

		.brio-progress { margin-bottom:18px; }
		.brio-progress__bar { height:10px; background:#e2e8f0; border-radius:999px; overflow:hidden; }
		.brio-progress__fill { height:100%; width:0; background:linear-gradient(90deg,#3b82f6,#06b6d4); border-radius:999px; transition:width .3s ease; }
		.brio-progress__meta { display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color:#64748b; font-variant-numeric:tabular-nums; }

		.brio-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:6px; }
		.brio-stat { display:flex; flex-direction:column; padding:12px; border-radius:8px; background:#f8fafc; border:1px solid #e5e7eb; }
		.brio-stat__num { font-size:22px; font-weight:700; line-height:1; font-variant-numeric:tabular-nums; }
		.brio-stat span:last-child { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin-top:4px; }
		.brio-stat--new .brio-stat__num { color:#16a34a; }
		.brio-stat--upd .brio-stat__num { color:#2563eb; }
		.brio-stat--skp .brio-stat__num { color:#d97706; }
		.brio-stat--err .brio-stat__num { color:#dc2626; }

		.brio-log { height:280px; overflow-y:auto; background:#0f172a; color:#e2e8f0; font-family:Menlo,Consolas,monospace; font-size:12px; padding:12px 14px; border-radius:8px; line-height:1.6; }
		.brio-log__line { display:flex; gap:10px; padding:1px 0; }
		.brio-log__r { color:#64748b; min-width:48px; }
		.brio-log__t { font-weight:700; min-width:42px; }
		.brio-log__t--new { color:#4ade80; }
		.brio-log__t--upd { color:#60a5fa; }
		.brio-log__t--err { color:#f87171; }
		.brio-log__t--ok  { color:#fbbf24; }
		.brio-log__m { flex:1; }
		.brio-log:empty::before { content:"En attente…"; color:#475569; font-style:italic; }

		.brio-card--ref { margin-top:20px; }
		.brio-card--ref summary { cursor:pointer; font-weight:600; color:#475569; }
		.brio-card--ref table { margin-top:14px; max-width:720px; }
	</style>

	<script>
	(function(){
		var AJAX = <?php echo wp_json_encode( $ajax_url ); ?>;
		var NONCE = <?php echo wp_json_encode( $nonce ); ?>;

		var $ = function(id){ return document.getElementById(id); };
		var fileInput = $('brio-file'), dz = $('brio-dropzone'), fileName = $('brio-file-name');
		var startBtn = $('brio-start-btn'), pauseBtn = $('brio-pause-btn'), cancelBtn = $('brio-cancel-btn');
		var fill = $('brio-progress-fill'), pctEl = $('brio-progress-pct'), txtEl = $('brio-progress-text');
		var sNew = $('brio-stat-new'), sUpd = $('brio-stat-upd'), sSkp = $('brio-stat-skp'), sErr = $('brio-stat-err');
		var logEl = $('brio-log');
		var resumeBtn = $('brio-resume-btn'), discardBtn = $('brio-discard-btn');

		var state = { jobId: null, paused: false, running: false };

		function post(action, data){
			data = data || {};
			data.action = action;
			data.nonce = NONCE;
			var body = new FormData();
			Object.keys(data).forEach(function(k){
				if (data[k] instanceof File) body.append(k, data[k]);
				else body.append(k, data[k]);
			});
			return fetch(AJAX, { method:'POST', body:body, credentials:'same-origin' }).then(function(r){ return r.json(); });
		}

		function logLine(entry){
			var line = document.createElement('div');
			line.className = 'brio-log__line';
			var typeMap = { new:'NEW', upd:'UPD', err:'ERR', ok:'OK' };
			line.innerHTML = '<span class="brio-log__r">' + (entry.r ? '#' + entry.r : '—') + '</span>'
				+ '<span class="brio-log__t brio-log__t--' + entry.t + '">' + (typeMap[entry.t] || entry.t) + '</span>'
				+ '<span class="brio-log__m"></span>';
			line.querySelector('.brio-log__m').textContent = entry.m;
			logEl.appendChild(line);
			logEl.scrollTop = logEl.scrollHeight;
		}

		function applyStatus(s){
			var pct = s.total ? Math.round( s.cursor / s.total * 100 ) : 0;
			fill.style.width = pct + '%';
			pctEl.textContent = pct + '%';
			txtEl.textContent = s.cursor + ' / ' + s.total;
			sNew.textContent = s.created;
			sUpd.textContent = s.updated;
			sSkp.textContent = s.skipped || 0;
			sErr.textContent = s.errors;
		}

		function renderInitialLog(log){
			logEl.innerHTML = '';
			(log || []).forEach(logLine);
		}

		function setRunning(on){
			state.running = on;
			startBtn.disabled = on || !fileInput.files[0];
			pauseBtn.disabled = !on;
			cancelBtn.disabled = !state.jobId;
			pauseBtn.textContent = state.paused ? '<?php echo esc_js( __( 'Reprendre', 'brio-guiseppe' ) ); ?>' : '<?php echo esc_js( __( 'Pause', 'brio-guiseppe' ) ); ?>';
		}

		function chunkLoop(){
			if (!state.running || state.paused) return;
			post('brio_csv_chunk', { job_id: state.jobId }).then(function(res){
				if (!res || !res.success) {
					logLine({ t:'err', r:0, m: (res && res.data && res.data.message) || 'Erreur AJAX' });
					setRunning(false);
					return;
				}
				var s = res.data;
				applyStatus(s);
				// only append new log lines we don't already have
				var existing = logEl.children.length;
				if (s.log && s.log.length > existing) {
					for (var i = existing; i < s.log.length; i++) logLine(s.log[i]);
				}
				if (s.done) {
					setRunning(false);
					return;
				}
				chunkLoop();
			}).catch(function(e){
				logLine({ t:'err', r:0, m:'Réseau: ' + e.message + ' — nouvelle tentative dans 3s' });
				setTimeout(chunkLoop, 3000);
			});
		}

		// Dropzone
		dz.addEventListener('click', function(){ fileInput.click(); });
		dz.addEventListener('dragover', function(e){ e.preventDefault(); dz.classList.add('is-drag'); });
		dz.addEventListener('dragleave', function(){ dz.classList.remove('is-drag'); });
		dz.addEventListener('drop', function(e){
			e.preventDefault(); dz.classList.remove('is-drag');
			if (e.dataTransfer.files[0]) { fileInput.files = e.dataTransfer.files; onFile(); }
		});
		fileInput.addEventListener('change', onFile);
		function onFile(){
			var f = fileInput.files[0];
			if (!f) return;
			fileName.textContent = f.name + ' (' + Math.round(f.size / 1024) + ' KB)';
			startBtn.disabled = false;
		}

		startBtn.addEventListener('click', function(){
			var f = fileInput.files[0];
			if (!f) return;
			startBtn.disabled = true;
			logEl.innerHTML = '';
			logLine({ t:'ok', r:0, m:'Upload…' });
			post('brio_csv_start', { file: f }).then(function(res){
				if (!res.success) { logLine({ t:'err', r:0, m: res.data.message }); return; }
				state.jobId = res.data.job_id;
				applyStatus({ total: res.data.total, cursor:0, created:0, updated:0, errors:0 });
				logLine({ t:'ok', r:0, m:'Job ' + state.jobId + ' — ' + res.data.total + ' lignes' });
				state.paused = false;
				setRunning(true);
				chunkLoop();
			});
		});

		pauseBtn.addEventListener('click', function(){
			state.paused = !state.paused;
			pauseBtn.textContent = state.paused ? '<?php echo esc_js( __( 'Reprendre', 'brio-guiseppe' ) ); ?>' : '<?php echo esc_js( __( 'Pause', 'brio-guiseppe' ) ); ?>';
			if (!state.paused) chunkLoop();
		});

		cancelBtn.addEventListener('click', function(){
			if (!state.jobId || !confirm('Annuler l\'import ?')) return;
			post('brio_csv_cancel', { job_id: state.jobId }).then(function(){
				logLine({ t:'err', r:0, m:'Import annulé.' });
				state.jobId = null; state.running = false; state.paused = false;
				setRunning(false);
			});
		});

		$('brio-log-clear').addEventListener('click', function(){ logEl.innerHTML = ''; });

		if (resumeBtn) {
			resumeBtn.addEventListener('click', function(){
				state.jobId = resumeBtn.dataset.job;
				post('brio_csv_status', { job_id: state.jobId }).then(function(res){
					if (!res.success) { alert(res.data.message); return; }
					applyStatus(res.data);
					renderInitialLog(res.data.log);
					logLine({ t:'ok', r:0, m:'Reprise du job ' + state.jobId });
					state.paused = false;
					setRunning(true);
					chunkLoop();
				});
			});
			discardBtn.addEventListener('click', function(){
				if (!confirm('Abandonner ce job ?')) return;
				post('brio_csv_cancel', { job_id: discardBtn.dataset.job }).then(function(){ location.reload(); });
			});
		}
	})();
	</script>
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
	// Reserved columns first, all landing field columns, then SEO overrides.
	$cols = array_merge(
		[ 'page_title', 'page_slug', 'page_status' ],
		array_keys( $field_map ),
		[ 'seo_title', 'seo_description' ]
	);

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
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $out, $cols );

	foreach ( $pages as $page ) {
		$row = [ $page->post_title, $page->post_name, $page->post_status ];
		foreach ( $field_map as $def ) {
			[ $section, $field ] = $def;
			$row[] = get_post_meta( $page->ID, '_brio_landing_' . $section . '_' . $field, true );
		}
		// SEO overrides (separate meta key prefix).
		$row[] = (string) get_post_meta( $page->ID, '_brio_seo_title', true );
		$row[] = (string) get_post_meta( $page->ID, '_brio_seo_description', true );
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
		array_keys( brio_csv_field_map() ),
		[ 'seo_title', 'seo_description' ]
	);
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="landing-import-template.csv"' );
	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $out, $cols );
	fputcsv( $out, array_fill( 0, count( $cols ), '' ) );
	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_csv_template_download' );
