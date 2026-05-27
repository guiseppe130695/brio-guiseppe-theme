<?php
/**
 * Admin — CSV Job Engine (shared progress-bar UI for chunked CSV imports)
 *
 * Provides the UI primitives + backend lifecycle that the original Landings
 * importer used (dropzone, AJAX chunks, progress bar, log feed, pause/cancel,
 * resume after page reload) so other content types (tags, articles) can reuse
 * them with a single PHP callback to define how to process one row.
 *
 * Register an importer type with `brio_csv_register_importer( $type, $config )`
 * once during admin init. Render its UI inside any admin page (typically a
 * tab inside the Import/Export hub) by calling `brio_csv_render_importer( $type )`.
 *
 * Config keys:
 *   label              string  — human label ("Tags", "Articles"…)
 *   chunk_size         int     — rows per AJAX chunk (default 10)
 *   columns            array   — header row of the template/export CSV
 *   row_processor      callable($data, &$job) : array{verdict,message}
 *                                'verdict' ∈ new|upd|err|skp ; 'message' string
 *   export_rows        callable() : iterable<array>  (optional — for the
 *                                "Exporter tout" button)
 *
 * @package Brio_Guiseppe
 * @since   1.7.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_CSV_DEFAULT_CHUNK   = 10;
const BRIO_CSV_LOG_TAIL_LIMIT  = 400;
const BRIO_CSV_JOB_TTL_SECONDS = DAY_IN_SECONDS;

/**
 * Registry of importer types, keyed by string identifier.
 *
 * Stored in $GLOBALS so the registry survives across function calls — PHP
 * `static` inside one function isn't visible from another without explicit
 * return-by-reference semantics, which are easy to get wrong.
 */
function brio_csv_register_importer( $type, $config ) {
	if ( ! isset( $GLOBALS['brio_csv_importers'] ) ) {
		$GLOBALS['brio_csv_importers'] = [];
	}
	$GLOBALS['brio_csv_importers'][ $type ] = wp_parse_args( $config, [
		'label'         => ucfirst( $type ),
		'chunk_size'    => BRIO_CSV_DEFAULT_CHUNK,
		'columns'       => [],
		'row_processor' => null,
		'export_rows'   => null,
		'help'          => '',
	] );
}

function brio_csv_get_importer( $type ) {
	return $GLOBALS['brio_csv_importers'][ $type ] ?? null;
}

/* ─────────────────────────────────────────────────────────────────────
 * Job storage (file on disk + transient)
 * ────────────────────────────────────────────────────────────────── */

function brio_csv2_job_dir() {
	$uploads = wp_upload_dir();
	$dir     = trailingslashit( $uploads['basedir'] ) . 'brio-import';
	if ( ! file_exists( $dir ) ) {
		wp_mkdir_p( $dir );
	}
	return $dir;
}

function brio_csv2_job_get( $job_id ) {
	return get_transient( 'brio_csv_job_' . $job_id );
}

function brio_csv2_job_save( $job_id, $job ) {
	if ( count( $job['log'] ) > BRIO_CSV_LOG_TAIL_LIMIT ) {
		$job['log'] = array_slice( $job['log'], -BRIO_CSV_LOG_TAIL_LIMIT );
	}
	set_transient( 'brio_csv_job_' . $job_id, $job, BRIO_CSV_JOB_TTL_SECONDS );
	update_option( 'brio_csv_active_job_' . $job['type'], $job_id, false );
}

function brio_csv2_job_clear( $job_id ) {
	$job = brio_csv2_job_get( $job_id );
	if ( $job && ! empty( $job['file'] ) && file_exists( $job['file'] ) ) {
		@unlink( $job['file'] );
	}
	delete_transient( 'brio_csv_job_' . $job_id );
	if ( $job && get_option( 'brio_csv_active_job_' . $job['type'] ) === $job_id ) {
		delete_option( 'brio_csv_active_job_' . $job['type'] );
	}
}

function brio_csv2_job_status_payload( $job ) {
	return [
		'job_id'  => $job['id'],
		'type'    => $job['type'],
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

/* ─────────────────────────────────────────────────────────────────────
 * AJAX handlers (single set, dispatches via $_POST['type'])
 * ────────────────────────────────────────────────────────────────── */

function brio_csv_ajax_validate( $type_required = true ) {
	check_ajax_referer( 'brio_csv2_ajax', 'nonce' );
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( [ 'message' => __( 'Permission refusée.', 'brio-guiseppe' ) ] );
	}
	if ( $type_required ) {
		$type = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : '';
		if ( ! $type || ! brio_csv_get_importer( $type ) ) {
			wp_send_json_error( [ 'message' => __( 'Type d\'import inconnu.', 'brio-guiseppe' ) ] );
		}
		return $type;
	}
}

function brio_csv_ajax_start_handler() {
	$type     = brio_csv_ajax_validate();
	$importer = brio_csv_get_importer( $type );

	if ( empty( $_FILES['file']['tmp_name'] ) || UPLOAD_ERR_OK !== $_FILES['file']['error'] ) {
		wp_send_json_error( [ 'message' => __( 'Upload échoué.', 'brio-guiseppe' ) ] );
	}
	if ( 'csv' !== strtolower( pathinfo( $_FILES['file']['name'], PATHINFO_EXTENSION ) ) ) {
		wp_send_json_error( [ 'message' => __( 'Le fichier doit être un .csv', 'brio-guiseppe' ) ] );
	}

	$job_id = $type . '_' . wp_generate_password( 10, false, false );
	$dest   = trailingslashit( brio_csv2_job_dir() ) . $job_id . '.csv';
	if ( ! move_uploaded_file( $_FILES['file']['tmp_name'], $dest ) ) {
		wp_send_json_error( [ 'message' => __( 'Impossible de sauvegarder le fichier.', 'brio-guiseppe' ) ] );
	}

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
		'type'       => $type,
		'file'       => $dest,
		'headers'    => array_map( 'trim', $headers ),
		'total'      => $total,
		'cursor'     => 0,
		'created'    => 0,
		'updated'    => 0,
		'skipped'    => 0,
		'errors'     => 0,
		'log'        => [],
		'started_at' => time(),
		'done'       => false,
	];
	brio_csv2_job_save( $job_id, $job );

	wp_send_json_success( [ 'job_id' => $job_id, 'total' => $total ] );
}
add_action( 'wp_ajax_brio_csv2_start', 'brio_csv_ajax_start_handler' );

function brio_csv_ajax_chunk_handler() {
	$type     = brio_csv_ajax_validate();
	$importer = brio_csv_get_importer( $type );

	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	$job    = brio_csv2_job_get( $job_id );
	if ( ! $job ) {
		wp_send_json_error( [ 'message' => __( 'Job introuvable ou expiré.', 'brio-guiseppe' ) ] );
	}
	if ( $job['done'] ) {
		wp_send_json_success( brio_csv2_job_status_payload( $job ) );
	}

	$h = fopen( $job['file'], 'r' );
	if ( ! $h ) {
		wp_send_json_error( [ 'message' => __( 'Fichier introuvable.', 'brio-guiseppe' ) ] );
	}
	$bom = fread( $h, 3 );
	if ( "\xEF\xBB\xBF" !== $bom ) {
		rewind( $h );
	}
	fgetcsv( $h, 0, ',' ); // header line
	// Skip already-processed rows.
	for ( $i = 0; $i < $job['cursor']; $i++ ) {
		if ( false === fgetcsv( $h, 0, ',' ) ) {
			break;
		}
	}

	wp_suspend_cache_addition( true );
	wp_defer_term_counting( true );

	$processed = 0;
	$max       = (int) $importer['chunk_size'];
	$processor = $importer['row_processor'];

	while ( $processed < $max ) {
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

		$data = array_combine( $job['headers'], $row );

		// Delegate to type-specific processor.
		$result = is_callable( $processor )
			? call_user_func_array( $processor, [ $data, &$job ] )
			: [ 'verdict' => 'err', 'message' => 'No processor registered' ];

		$verdict = $result['verdict'] ?? 'err';
		$msg     = $result['message'] ?? '';

		switch ( $verdict ) {
			case 'new':
				$job['created']++;
				$job['log'][] = [ 't' => 'new', 'r' => $row_no, 'm' => $msg ];
				break;
			case 'upd':
				$job['updated']++;
				$job['log'][] = [ 't' => 'upd', 'r' => $row_no, 'm' => $msg ];
				break;
			case 'skp':
				$job['skipped']++;
				$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => $msg ];
				break;
			case 'err':
			default:
				$job['errors']++;
				$job['log'][] = [ 't' => 'err', 'r' => $row_no, 'm' => $msg ];
				break;
		}

		$processed++;
	}
	fclose( $h );

	if ( $job['cursor'] >= $job['total'] ) {
		$job['done'] = true;
	}
	if ( $job['done'] ) {
		$job['log'][] = [ 't' => 'ok', 'r' => 0, 'm' => __( 'Import terminé.', 'brio-guiseppe' ) ];
	}
	brio_csv2_job_save( $job['id'], $job );

	wp_send_json_success( brio_csv2_job_status_payload( $job ) );
}
add_action( 'wp_ajax_brio_csv2_chunk', 'brio_csv_ajax_chunk_handler' );

function brio_csv_ajax_status_handler() {
	brio_csv_ajax_validate( false );
	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	$job    = brio_csv2_job_get( $job_id );
	if ( ! $job ) {
		wp_send_json_error( [ 'message' => __( 'Job introuvable.', 'brio-guiseppe' ) ] );
	}
	wp_send_json_success( brio_csv2_job_status_payload( $job ) );
}
add_action( 'wp_ajax_brio_csv2_status', 'brio_csv_ajax_status_handler' );

function brio_csv_ajax_cancel_handler() {
	brio_csv_ajax_validate( false );
	$job_id = isset( $_POST['job_id'] ) ? sanitize_text_field( wp_unslash( $_POST['job_id'] ) ) : '';
	brio_csv2_job_clear( $job_id );
	wp_send_json_success();
}
add_action( 'wp_ajax_brio_csv2_cancel', 'brio_csv_ajax_cancel_handler' );

/* ─────────────────────────────────────────────────────────────────────
 * UI rendering
 * ────────────────────────────────────────────────────────────────── */

function brio_csv_render_importer( $type ) {
	$importer = brio_csv_get_importer( $type );
	if ( ! $importer ) {
		echo '<p>' . esc_html__( 'Type d\'import inconnu.', 'brio-guiseppe' ) . '</p>';
		return;
	}

	$nonce      = wp_create_nonce( 'brio_csv2_ajax' );
	$ajax_url   = admin_url( 'admin-ajax.php' );
	$active_job = get_option( 'brio_csv_active_job_' . $type );
	$resume_job = $active_job ? brio_csv2_job_get( $active_job ) : null;
	$can_resume = $resume_job && ! $resume_job['done'];
	$label      = $importer['label'];

	// Static helper actions.
	$export_url   = is_callable( $importer['export_rows'] )
		? wp_nonce_url( add_query_arg( [ 'page' => 'brio-import-export', 'tab' => $type, 'brio_csv_export' => 1 ], admin_url( 'tools.php' ) ), 'brio_csv_export_' . $type )
		: '';
	$template_url = ! empty( $importer['columns'] )
		? add_query_arg( [ 'page' => 'brio-import-export', 'tab' => $type, 'brio_csv_template' => 1 ], admin_url( 'tools.php' ) )
		: '';
	?>
	<div class="brio-import-wrap" data-type="<?php echo esc_attr( $type ); ?>">
		<div class="brio-import-toolbar">
			<p class="brio-import-sub">
				<?php esc_html_e( 'Traitement par lots, sans timeout, avec progression en direct.', 'brio-guiseppe' ); ?>
			</p>
			<div class="brio-import-actions">
				<?php if ( $template_url ) : ?>
					<a class="button" href="<?php echo esc_url( $template_url ); ?>"><?php esc_html_e( 'Modèle vide', 'brio-guiseppe' ); ?></a>
				<?php endif; ?>
				<?php if ( $export_url ) : ?>
					<a class="button button-primary" href="<?php echo esc_url( $export_url ); ?>"><?php esc_html_e( 'Exporter tout', 'brio-guiseppe' ); ?></a>
				<?php endif; ?>
			</div>
		</div>

		<div class="brio-import-grid">
			<section class="brio-card brio-card--upload">
				<h3>1. <?php esc_html_e( 'Fichier CSV', 'brio-guiseppe' ); ?></h3>

				<?php if ( $can_resume ) : ?>
					<div class="brio-resume-banner">
						<strong><?php esc_html_e( 'Import en cours détecté', 'brio-guiseppe' ); ?></strong>
						<span><?php printf( esc_html__( '%1$d / %2$d lignes traitées.', 'brio-guiseppe' ), (int) $resume_job['cursor'], (int) $resume_job['total'] ); ?></span>
						<button class="button button-primary brio-resume-btn" data-job="<?php echo esc_attr( $active_job ); ?>"><?php esc_html_e( 'Reprendre', 'brio-guiseppe' ); ?></button>
						<button class="button brio-discard-btn" data-job="<?php echo esc_attr( $active_job ); ?>"><?php esc_html_e( 'Abandonner', 'brio-guiseppe' ); ?></button>
					</div>
				<?php endif; ?>

				<label class="brio-dropzone">
					<input type="file" class="brio-file" accept=".csv" hidden />
					<div class="brio-dropzone__inner">
						<span class="brio-dropzone__icon" aria-hidden="true">⬆</span>
						<strong><?php esc_html_e( 'Glissez un .csv ou cliquez pour parcourir', 'brio-guiseppe' ); ?></strong>
						<span class="brio-dropzone__hint"><?php esc_html_e( 'UTF-8 — séparateur virgule', 'brio-guiseppe' ); ?></span>
						<span class="brio-dropzone__file brio-file-name"></span>
					</div>
				</label>

				<div class="brio-import-controls">
					<button class="button button-primary button-hero brio-start-btn" disabled><?php esc_html_e( 'Lancer l\'import', 'brio-guiseppe' ); ?></button>
					<button class="button brio-pause-btn" disabled><?php esc_html_e( 'Pause', 'brio-guiseppe' ); ?></button>
					<button class="button brio-cancel-btn" disabled><?php esc_html_e( 'Annuler', 'brio-guiseppe' ); ?></button>
				</div>
			</section>

			<section class="brio-card brio-card--progress">
				<h3>2. <?php esc_html_e( 'Progression', 'brio-guiseppe' ); ?></h3>
				<div class="brio-progress">
					<div class="brio-progress__bar"><div class="brio-progress__fill" style="width:0"></div></div>
					<div class="brio-progress__meta">
						<span class="brio-progress-text">0 / 0</span>
						<span class="brio-progress-pct">0%</span>
					</div>
				</div>
				<div class="brio-stats">
					<div class="brio-stat brio-stat--new"><span class="brio-stat__num brio-stat-new">0</span><span><?php esc_html_e( 'Créées', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--upd"><span class="brio-stat__num brio-stat-upd">0</span><span><?php esc_html_e( 'Mises à jour', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--skp"><span class="brio-stat__num brio-stat-skp">0</span><span><?php esc_html_e( 'Ignorées', 'brio-guiseppe' ); ?></span></div>
					<div class="brio-stat brio-stat--err"><span class="brio-stat__num brio-stat-err">0</span><span><?php esc_html_e( 'Erreurs', 'brio-guiseppe' ); ?></span></div>
				</div>
				<h4><?php esc_html_e( 'Journal', 'brio-guiseppe' ); ?> <button type="button" class="button-link brio-log-clear"><?php esc_html_e( 'Effacer', 'brio-guiseppe' ); ?></button></h4>
				<div class="brio-log" aria-live="polite"></div>
			</section>
		</div>

		<?php if ( ! empty( $importer['columns'] ) ) : ?>
			<details class="brio-card brio-card--ref" style="margin-top:16px">
				<summary><?php esc_html_e( 'Colonnes attendues', 'brio-guiseppe' ); ?></summary>
				<p style="font-size:12px;color:#475569">
					<?php echo esc_html( implode( ' · ', $importer['columns'] ) ); ?>
				</p>
				<?php if ( ! empty( $importer['help'] ) ) : ?>
					<p style="font-size:12px;color:#475569"><?php echo wp_kses_post( $importer['help'] ); ?></p>
				<?php endif; ?>
			</details>
		<?php endif; ?>
	</div>

	<style>
	/* Use the user's admin color scheme. WordPress exposes the active scheme
	 * via --wp-admin-theme-color (and -darker-10/-20). Fallback to the default
	 * "fresh" blue (#2271b1) when unset. */
	.brio-import-wrap {
		--brio-accent: var(--wp-admin-theme-color, #2271b1);
		--brio-accent-soft: var(--wp-admin-theme-color-darker-10, #135e96);
		--brio-bg: #fff;
		--brio-bg-soft: #f6f7f7;
		--brio-border: #c3c4c7;
		--brio-text: #1d2327;
		--brio-text-soft: #50575e;
		max-width: 1180px;
	}
	.brio-import-toolbar { display:flex; justify-content:space-between; align-items:center; gap:16px; margin:0 0 14px; padding:0; }
	.brio-import-sub { color: var(--brio-text-soft); margin:0; font-size:13px; }
	.brio-import-actions { display:flex; gap:8px; flex-shrink:0; }

	.brio-import-grid { display:grid; grid-template-columns: 1fr 1.2fr; gap:16px; }
	@media (max-width:960px) { .brio-import-grid { grid-template-columns:1fr; } }

	.brio-card { background: var(--brio-bg); border:1px solid var(--brio-border); border-radius:4px; padding:18px 20px; }
	.brio-card h3 { margin:0 0 12px; font-size:14px; color: var(--brio-text); font-weight:600; }
	.brio-card h4 { display:flex; justify-content:space-between; align-items:center; margin:16px 0 6px; font-size:13px; color: var(--brio-text); font-weight:600; }
	.brio-card h4 .button-link { font-size:12px; }

	.brio-resume-banner {
		display:flex; align-items:center; gap:10px;
		padding:10px 12px; margin-bottom:12px;
		background:#fcf9e8; border-left:4px solid #dba617;
		font-size:13px;
	}
	.brio-resume-banner strong { color: var(--brio-text); }
	.brio-resume-banner span { flex:1; color: var(--brio-text-soft); }

	.brio-dropzone {
		display:block; border:1px dashed var(--brio-border); border-radius:4px;
		padding:28px 14px; text-align:center; cursor:pointer;
		transition: border-color .15s, background .15s;
		background: var(--brio-bg-soft);
	}
	.brio-dropzone:hover, .brio-dropzone.is-drag {
		border-color: var(--brio-accent);
		border-style: solid;
		background: #fff;
	}
	.brio-dropzone__inner { display:flex; flex-direction:column; gap:6px; align-items:center; }
	.brio-dropzone__icon { font-size:22px; color: var(--brio-accent); line-height:1; }
	.brio-dropzone__hint { font-size:12px; color: var(--brio-text-soft); }
	.brio-dropzone__file { font-size:13px; color: var(--brio-text); font-weight:600; margin-top:4px; }

	.brio-import-controls { display:flex; gap:6px; margin-top:14px; }

	.brio-progress { margin-bottom:16px; }
	.brio-progress__bar { height:8px; background: var(--brio-bg-soft); border:1px solid var(--brio-border); border-radius:999px; overflow:hidden; }
	.brio-progress__fill { height:100%; width:0; background: var(--brio-accent); transition: width .3s ease; }
	.brio-progress__meta { display:flex; justify-content:space-between; margin-top:6px; font-size:12px; color: var(--brio-text-soft); font-variant-numeric:tabular-nums; }

	.brio-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:4px; }
	.brio-stat {
		display:flex; flex-direction:column;
		padding:10px 12px; border:1px solid var(--brio-border); border-radius:4px;
		background: var(--brio-bg);
	}
	.brio-stat__num { font-size:20px; font-weight:600; line-height:1; font-variant-numeric:tabular-nums; color: var(--brio-text); }
	.brio-stat span:last-child { font-size:11px; color: var(--brio-text-soft); margin-top:4px; }

	.brio-log {
		height:240px; overflow-y:auto;
		background: var(--brio-bg-soft);
		border:1px solid var(--brio-border); border-radius:4px;
		font-family: Consolas, Menlo, monospace; font-size:12px;
		padding:8px 10px; line-height:1.55;
		color: var(--brio-text);
	}
	.brio-log__line { display:flex; gap:10px; padding:1px 0; }
	.brio-log__r { color: var(--brio-text-soft); min-width:44px; }
	.brio-log__t { font-weight:600; min-width:38px; }
	.brio-log__t--new { color: #008a20; }
	.brio-log__t--upd { color: var(--brio-accent-soft); }
	.brio-log__t--err { color: #d63638; }
	.brio-log__t--ok  { color: #996800; }
	.brio-log__m { flex:1; word-break:break-word; }
	.brio-log:empty::before { content:"En attente…"; color: var(--brio-text-soft); font-style:italic; }

	.brio-card--ref summary { cursor:pointer; font-weight:600; color: var(--brio-text); }
	</style>

	<script>
	( function () {
		var AJAX  = <?php echo wp_json_encode( $ajax_url ); ?>;
		var NONCE = <?php echo wp_json_encode( $nonce ); ?>;
		var root  = document.currentScript.closest( '.wrap' ) || document;
		var wrap  = root.querySelector( '.brio-import-wrap[data-type="<?php echo esc_js( $type ); ?>"]' );
		if ( ! wrap || wrap.dataset.brioInited ) return;
		wrap.dataset.brioInited = '1';

		var $ = function ( sel ) { return wrap.querySelector( sel ); };
		var fileInput = $( '.brio-file' ), dz = $( '.brio-dropzone' ), fileName = $( '.brio-file-name' );
		var startBtn = $( '.brio-start-btn' ), pauseBtn = $( '.brio-pause-btn' ), cancelBtn = $( '.brio-cancel-btn' );
		var fill = $( '.brio-progress__fill' ), pctEl = $( '.brio-progress-pct' ), txtEl = $( '.brio-progress-text' );
		var sNew = $( '.brio-stat-new' ), sUpd = $( '.brio-stat-upd' ), sSkp = $( '.brio-stat-skp' ), sErr = $( '.brio-stat-err' );
		var logEl = $( '.brio-log' );
		var resumeBtn = $( '.brio-resume-btn' ), discardBtn = $( '.brio-discard-btn' );

		var state = { type: wrap.dataset.type, jobId: null, paused: false, running: false };

		function post( action, data ) {
			data = data || {};
			data.action = action;
			data.nonce  = NONCE;
			data.type   = state.type;
			var body = new FormData();
			Object.keys( data ).forEach( function ( k ) { body.append( k, data[ k ] ); } );
			return fetch( AJAX, { method: 'POST', body: body, credentials: 'same-origin' } ).then( function ( r ) { return r.json(); } );
		}

		function logLine( e ) {
			var line = document.createElement( 'div' );
			line.className = 'brio-log__line';
			var typeMap = { new: 'NEW', upd: 'UPD', err: 'ERR', ok: 'OK' };
			line.innerHTML =
				'<span class="brio-log__r">' + ( e.r ? '#' + e.r : '—' ) + '</span>' +
				'<span class="brio-log__t brio-log__t--' + e.t + '">' + ( typeMap[ e.t ] || e.t ) + '</span>' +
				'<span class="brio-log__m"></span>';
			line.querySelector( '.brio-log__m' ).textContent = e.m || '';
			logEl.appendChild( line );
			logEl.scrollTop = logEl.scrollHeight;
		}

		function applyStatus( s ) {
			var pct = s.total ? Math.round( s.cursor / s.total * 100 ) : 0;
			fill.style.width = pct + '%';
			pctEl.textContent = pct + '%';
			txtEl.textContent = s.cursor + ' / ' + s.total;
			sNew.textContent = s.created;
			sUpd.textContent = s.updated;
			sSkp.textContent = s.skipped || 0;
			sErr.textContent = s.errors;
		}

		function renderInitialLog( log ) { logEl.innerHTML = ''; ( log || [] ).forEach( logLine ); }

		function setRunning( on ) {
			state.running = on;
			startBtn.disabled = on || ! ( fileInput.files && fileInput.files[0] );
			pauseBtn.disabled = ! on;
			cancelBtn.disabled = ! state.jobId;
			pauseBtn.textContent = state.paused
				? '<?php echo esc_js( __( 'Reprendre', 'brio-guiseppe' ) ); ?>'
				: '<?php echo esc_js( __( 'Pause', 'brio-guiseppe' ) ); ?>';
		}

		function chunkLoop() {
			if ( ! state.running || state.paused ) return;
			post( 'brio_csv2_chunk', { job_id: state.jobId } ).then( function ( res ) {
				if ( ! res || ! res.success ) {
					logLine( { t: 'err', r: 0, m: ( res && res.data && res.data.message ) || 'Erreur AJAX' } );
					setRunning( false );
					return;
				}
				var s = res.data;
				applyStatus( s );
				var existing = logEl.children.length;
				if ( s.log && s.log.length > existing ) {
					for ( var i = existing; i < s.log.length; i++ ) logLine( s.log[ i ] );
				}
				if ( s.done ) { setRunning( false ); return; }
				chunkLoop();
			} ).catch( function ( e ) {
				logLine( { t: 'err', r: 0, m: 'Réseau: ' + e.message } );
				setTimeout( chunkLoop, 3000 );
			} );
		}

		dz.addEventListener( 'click', function () { fileInput.click(); } );
		dz.addEventListener( 'dragover', function ( e ) { e.preventDefault(); dz.classList.add( 'is-drag' ); } );
		dz.addEventListener( 'dragleave', function () { dz.classList.remove( 'is-drag' ); } );
		dz.addEventListener( 'drop', function ( e ) {
			e.preventDefault();
			dz.classList.remove( 'is-drag' );
			if ( e.dataTransfer.files[0] ) { fileInput.files = e.dataTransfer.files; onFile(); }
		} );
		fileInput.addEventListener( 'change', onFile );
		function onFile() {
			var f = fileInput.files[0];
			if ( ! f ) return;
			fileName.textContent = f.name + ' (' + Math.round( f.size / 1024 ) + ' KB)';
			startBtn.disabled = false;
		}

		startBtn.addEventListener( 'click', function () {
			var f = fileInput.files[0];
			if ( ! f ) return;
			startBtn.disabled = true;
			logEl.innerHTML = '';
			logLine( { t: 'ok', r: 0, m: 'Upload…' } );
			post( 'brio_csv2_start', { file: f } ).then( function ( res ) {
				if ( ! res.success ) { logLine( { t: 'err', r: 0, m: res.data.message } ); return; }
				state.jobId = res.data.job_id;
				applyStatus( { total: res.data.total, cursor: 0, created: 0, updated: 0, skipped: 0, errors: 0 } );
				logLine( { t: 'ok', r: 0, m: 'Job ' + state.jobId + ' — ' + res.data.total + ' lignes' } );
				state.paused = false;
				setRunning( true );
				chunkLoop();
			} );
		} );

		pauseBtn.addEventListener( 'click', function () {
			state.paused = ! state.paused;
			pauseBtn.textContent = state.paused
				? '<?php echo esc_js( __( 'Reprendre', 'brio-guiseppe' ) ); ?>'
				: '<?php echo esc_js( __( 'Pause', 'brio-guiseppe' ) ); ?>';
			if ( ! state.paused ) chunkLoop();
		} );

		cancelBtn.addEventListener( 'click', function () {
			if ( ! state.jobId || ! confirm( 'Annuler l\'import ?' ) ) return;
			post( 'brio_csv2_cancel', { job_id: state.jobId } ).then( function () {
				logLine( { t: 'err', r: 0, m: 'Import annulé.' } );
				state.jobId = null; state.running = false; state.paused = false;
				setRunning( false );
			} );
		} );

		$( '.brio-log-clear' ).addEventListener( 'click', function () { logEl.innerHTML = ''; } );

		if ( resumeBtn ) {
			resumeBtn.addEventListener( 'click', function () {
				state.jobId = resumeBtn.dataset.job;
				post( 'brio_csv2_status', { job_id: state.jobId } ).then( function ( res ) {
					if ( ! res.success ) { alert( res.data.message ); return; }
					applyStatus( res.data );
					renderInitialLog( res.data.log );
					logLine( { t: 'ok', r: 0, m: 'Reprise du job ' + state.jobId } );
					state.paused = false;
					setRunning( true );
					chunkLoop();
				} );
			} );
			discardBtn.addEventListener( 'click', function () {
				if ( ! confirm( 'Abandonner ce job ?' ) ) return;
				post( 'brio_csv2_cancel', { job_id: discardBtn.dataset.job } ).then( function () { location.reload(); } );
			} );
		}
	} )();
	</script>
	<?php
}

/* ─────────────────────────────────────────────────────────────────────
 * Generic export + template download handlers (dispatched by tab + type)
 * ────────────────────────────────────────────────────────────────── */

function brio_csv_handle_export() {
	if (
		! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['brio_csv_export'] ) ||
		( $_GET['page'] ?? '' ) !== 'brio-import-export'
	) {
		return;
	}
	$type     = sanitize_key( wp_unslash( $_GET['tab'] ?? '' ) );
	$importer = brio_csv_get_importer( $type );
	if ( ! $importer || ! is_callable( $importer['export_rows'] ) ) {
		return;
	}
	if (
		! isset( $_GET['_wpnonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'brio_csv_export_' . $type )
	) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ) );
	}

	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $type . '-export-' . gmdate( 'Y-m-d' ) . '.csv"' );
	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );

	$rows = call_user_func( $importer['export_rows'] );
	$wrote_header = false;
	foreach ( $rows as $row ) {
		if ( ! $wrote_header ) {
			fputcsv( $out, array_keys( $row ) );
			$wrote_header = true;
		}
		fputcsv( $out, array_values( $row ) );
	}
	if ( ! $wrote_header && ! empty( $importer['columns'] ) ) {
		fputcsv( $out, $importer['columns'] );
	}
	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_csv_handle_export' );

function brio_csv_handle_template() {
	if (
		! is_admin() ||
		! current_user_can( 'manage_options' ) ||
		! isset( $_GET['brio_csv_template'] ) ||
		( $_GET['page'] ?? '' ) !== 'brio-import-export'
	) {
		return;
	}
	$type     = sanitize_key( wp_unslash( $_GET['tab'] ?? '' ) );
	$importer = brio_csv_get_importer( $type );
	if ( ! $importer || empty( $importer['columns'] ) ) {
		return;
	}
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="' . $type . '-template.csv"' );
	$out = fopen( 'php://output', 'w' );
	fprintf( $out, chr( 0xEF ) . chr( 0xBB ) . chr( 0xBF ) );
	fputcsv( $out, $importer['columns'] );
	fputcsv( $out, array_fill( 0, count( $importer['columns'] ), '' ) );
	fclose( $out );
	exit;
}
add_action( 'admin_init', 'brio_csv_handle_template' );
