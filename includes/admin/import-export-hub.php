<?php
/**
 * Admin — Import / Export hub
 *
 * Single Tools entry point that tabs through every content type the theme
 * lets you bulk-edit via CSV:
 *
 *   • Landings  → reuses brio_csv_import_page()    (landing-csv-import.php)
 *   • Tags      → reuses brio_tags_export_page()   (tags-export.php)
 *   • Articles  → handled in this file (export + SEO-only import)
 *
 * The legacy per-module admin pages stay in code but stop registering their
 * own menu entries (see brio_iohub_suppress_legacy_menus()) so the user
 * sees a single unified UI.
 *
 * @package Brio_Guiseppe
 * @since   1.7.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_IOHUB_SLUG = 'brio-import-export';

/* ── Register the unified menu entry ── */
function brio_iohub_menu() {
	add_management_page(
		__( 'Import / Export', 'brio-guiseppe' ),
		__( 'Import / Export', 'brio-guiseppe' ),
		'manage_options',
		BRIO_IOHUB_SLUG,
		'brio_iohub_render'
	);
}
add_action( 'admin_menu', 'brio_iohub_menu', 9 ); // before legacy hooks (priority 10)

/* ── Hide the legacy menu entries (Outils → Import Landing / Tags CSV) ── */
function brio_iohub_suppress_legacy_menus() {
	remove_submenu_page( 'tools.php', 'brio-landing-import' );
	remove_submenu_page( 'tools.php', 'brio-tags-export' );
}
add_action( 'admin_menu', 'brio_iohub_suppress_legacy_menus', 100 );

/* ── Hub renderer with tabs ── */
function brio_iohub_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'landings';
	$tabs = [
		'landings' => __( 'Landing Pages', 'brio-guiseppe' ),
		'tags'     => __( 'Tags (Definitions)', 'brio-guiseppe' ),
		'articles' => __( 'Articles', 'brio-guiseppe' ),
	];
	if ( ! isset( $tabs[ $active ] ) ) {
		$active = 'landings';
	}
	?>
	<div class="wrap brio-iohub">
		<h1><?php esc_html_e( 'Import / Export', 'brio-guiseppe' ); ?></h1>

		<nav class="nav-tab-wrapper" style="margin-bottom:18px">
			<?php foreach ( $tabs as $slug => $label ) :
				$url = add_query_arg( [ 'page' => BRIO_IOHUB_SLUG, 'tab' => $slug ], admin_url( 'tools.php' ) );
				$class = 'nav-tab' . ( $slug === $active ? ' nav-tab-active' : '' );
				?>
				<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $class ); ?>">
					<?php echo esc_html( $label ); ?>
				</a>
			<?php endforeach; ?>
		</nav>

		<div class="brio-iohub__panel">
			<?php
			switch ( $active ) {
				case 'tags':
					if ( function_exists( 'brio_csv_render_importer' ) ) {
						brio_csv_render_importer( 'tags' );
					}
					break;
				case 'articles':
					if ( function_exists( 'brio_csv_render_importer' ) ) {
						brio_csv_render_importer( 'articles' );
					}
					break;
				case 'landings':
				default:
					if ( function_exists( 'brio_csv_import_page' ) ) {
						brio_csv_import_page();
					}
					break;
			}
			?>
		</div>
	</div>
	<style>
	.brio-iohub__panel .wrap { padding: 0; margin: 0; }
	.brio-iohub__panel .wrap > h1 { display: none; } /* legacy pages already render their own h1 — hide */
	</style>
	<?php
}
