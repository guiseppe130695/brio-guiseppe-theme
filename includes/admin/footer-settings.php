<?php
/**
 * Admin — Footer Settings
 *
 * Interface to edit the two footer columns (Explorer + Services) and the
 * legal pages (Privacy, Mentions) without touching theme-data.php. Stored
 * in a single wp_option (`brio_footer_settings`) so backup/migration just
 * means exporting that row.
 *
 * Theme-data.php's brio_get_footer_columns() and brio_get_legal_data()
 * merge this DB row over the hardcoded defaults, so the page works even
 * when the option is empty.
 *
 * @package Brio_Guiseppe
 * @since   1.5.0
 */

defined( 'ABSPATH' ) || exit;

const BRIO_FOOTER_OPTION = 'brio_footer_settings';

/* ── Register page under Appearance ── */
function brio_footer_settings_menu() {
	add_theme_page(
		__( 'Réglages du Footer', 'brio-guiseppe' ),
		__( 'Footer', 'brio-guiseppe' ),
		'manage_options',
		'brio-footer-settings',
		'brio_footer_settings_render'
	);
}
add_action( 'admin_menu', 'brio_footer_settings_menu' );

/* ── Read current settings (DB row, falls back to empty arrays) ── */
function brio_footer_settings_get() {
	$defaults = [
		'explorer_title' => 'Explorer',
		'services_title' => 'Services',
		'explorer_links' => [
			[ 'label' => 'Accueil',   'url' => '' ],
			[ 'label' => 'Expertise', 'url' => '' ],
			[ 'label' => 'Services',  'url' => '' ],
			[ 'label' => 'Blog',      'url' => '' ],
			[ 'label' => 'Contact',   'url' => '' ],
		],
		'services_links' => [
			[ 'label' => 'Site Web Hôtel Conversion', 'url' => '' ],
			[ 'label' => 'SEO Tourisme & Destination', 'url' => '' ],
			[ 'label' => 'Revenue Management System', 'url' => '' ],
			[ 'label' => 'Audit Distribution OTA', 'url' => '' ],
			[ 'label' => 'Optimisation Conversion', 'url' => '' ],
		],
		'legal_privacy_label' => 'Politique de confidentialité',
		'legal_privacy_url'   => '',
		'legal_terms_label'   => 'Mentions légales',
		'legal_terms_url'     => '',
	];
	$stored = get_option( BRIO_FOOTER_OPTION, [] );
	return array_merge( $defaults, is_array( $stored ) ? $stored : [] );
}

/* ── Save handler ── */
function brio_footer_settings_save() {
	if ( ! isset( $_POST['brio_footer_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_footer_nonce'] ) ), 'brio_footer_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$data = [
		'explorer_title' => sanitize_text_field( wp_unslash( $_POST['explorer_title'] ?? '' ) ),
		'services_title' => sanitize_text_field( wp_unslash( $_POST['services_title'] ?? '' ) ),
		'explorer_links' => [],
		'services_links' => [],
		'legal_privacy_label' => sanitize_text_field( wp_unslash( $_POST['legal_privacy_label'] ?? '' ) ),
		'legal_privacy_url'   => esc_url_raw( wp_unslash( $_POST['legal_privacy_url'] ?? '' ) ),
		'legal_terms_label'   => sanitize_text_field( wp_unslash( $_POST['legal_terms_label'] ?? '' ) ),
		'legal_terms_url'     => esc_url_raw( wp_unslash( $_POST['legal_terms_url'] ?? '' ) ),
	];

	foreach ( [ 'explorer', 'services' ] as $col ) {
		$labels = (array) ( $_POST[ $col . '_label' ] ?? [] );
		$urls   = (array) ( $_POST[ $col . '_url' ] ?? [] );
		$max    = max( count( $labels ), count( $urls ) );
		for ( $i = 0; $i < $max; $i++ ) {
			$label = sanitize_text_field( wp_unslash( $labels[ $i ] ?? '' ) );
			$url   = esc_url_raw( wp_unslash( $urls[ $i ] ?? '' ) );
			if ( '' === $label && '' === $url ) {
				continue; // skip fully empty rows
			}
			$data[ $col . '_links' ][] = [ 'label' => $label, 'url' => $url ];
		}
	}

	update_option( BRIO_FOOTER_OPTION, $data, false );

	add_settings_error(
		'brio_footer',
		'saved',
		__( 'Réglages enregistrés.', 'brio-guiseppe' ),
		'success'
	);
}
add_action( 'admin_init', 'brio_footer_settings_save' );

/* ── Page renderer ── */
function brio_footer_settings_render() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$s = brio_footer_settings_get();
	?>
	<div class="wrap brio-footer-wrap">
		<h1><?php esc_html_e( 'Réglages du Footer', 'brio-guiseppe' ); ?></h1>
		<?php settings_errors( 'brio_footer' ); ?>

		<p style="max-width:680px;color:#475569">
			<?php esc_html_e( 'Modifiez les colonnes de liens affichées dans le pied de page. Laissez une URL vide pour cacher le lien.', 'brio-guiseppe' ); ?>
		</p>

		<form method="post" action="">
			<?php wp_nonce_field( 'brio_footer_save', 'brio_footer_nonce' ); ?>

			<div class="brio-footer-grid">

				<!-- Colonne Explorer -->
				<div class="brio-footer-card">
					<label class="brio-footer-card__head">
						<?php esc_html_e( 'Titre de la colonne 1', 'brio-guiseppe' ); ?>
						<input type="text" name="explorer_title" value="<?php echo esc_attr( $s['explorer_title'] ); ?>" />
					</label>

					<h3><?php esc_html_e( 'Liens', 'brio-guiseppe' ); ?></h3>
					<div class="brio-footer-links" data-col="explorer">
						<?php foreach ( $s['explorer_links'] as $i => $link ) : ?>
							<div class="brio-footer-link-row">
								<input type="text" name="explorer_label[]" placeholder="<?php esc_attr_e( 'Libellé', 'brio-guiseppe' ); ?>" value="<?php echo esc_attr( $link['label'] ); ?>" />
								<input type="url"  name="explorer_url[]"   placeholder="https://…" value="<?php echo esc_attr( $link['url'] ); ?>" />
								<button type="button" class="button brio-footer-remove" aria-label="<?php esc_attr_e( 'Supprimer', 'brio-guiseppe' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button button-secondary brio-footer-add" data-col="explorer">
						+ <?php esc_html_e( 'Ajouter un lien', 'brio-guiseppe' ); ?>
					</button>
				</div>

				<!-- Colonne Services -->
				<div class="brio-footer-card">
					<label class="brio-footer-card__head">
						<?php esc_html_e( 'Titre de la colonne 2', 'brio-guiseppe' ); ?>
						<input type="text" name="services_title" value="<?php echo esc_attr( $s['services_title'] ); ?>" />
					</label>

					<h3><?php esc_html_e( 'Liens', 'brio-guiseppe' ); ?></h3>
					<div class="brio-footer-links" data-col="services">
						<?php foreach ( $s['services_links'] as $i => $link ) : ?>
							<div class="brio-footer-link-row">
								<input type="text" name="services_label[]" placeholder="<?php esc_attr_e( 'Libellé', 'brio-guiseppe' ); ?>" value="<?php echo esc_attr( $link['label'] ); ?>" />
								<input type="url"  name="services_url[]"   placeholder="https://…" value="<?php echo esc_attr( $link['url'] ); ?>" />
								<button type="button" class="button brio-footer-remove" aria-label="<?php esc_attr_e( 'Supprimer', 'brio-guiseppe' ); ?>">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="button button-secondary brio-footer-add" data-col="services">
						+ <?php esc_html_e( 'Ajouter un lien', 'brio-guiseppe' ); ?>
					</button>
				</div>

				<!-- Pages légales -->
				<div class="brio-footer-card brio-footer-card--legal">
					<h2><?php esc_html_e( 'Pages légales (barre du bas)', 'brio-guiseppe' ); ?></h2>

					<div class="brio-footer-legal-row">
						<input type="text" name="legal_privacy_label" placeholder="<?php esc_attr_e( 'Libellé', 'brio-guiseppe' ); ?>" value="<?php echo esc_attr( $s['legal_privacy_label'] ); ?>" />
						<input type="url"  name="legal_privacy_url"   placeholder="https://…" value="<?php echo esc_attr( $s['legal_privacy_url'] ); ?>" />
					</div>
					<div class="brio-footer-legal-row">
						<input type="text" name="legal_terms_label" placeholder="<?php esc_attr_e( 'Libellé', 'brio-guiseppe' ); ?>" value="<?php echo esc_attr( $s['legal_terms_label'] ); ?>" />
						<input type="url"  name="legal_terms_url"   placeholder="https://…" value="<?php echo esc_attr( $s['legal_terms_url'] ); ?>" />
					</div>
				</div>

			</div>

			<?php submit_button( __( 'Enregistrer', 'brio-guiseppe' ) ); ?>
		</form>
	</div>

	<style>
	.brio-footer-wrap { max-width: 1100px; }
	.brio-footer-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 16px; }
	.brio-footer-card--legal { grid-column: 1 / -1; }
	@media (max-width: 900px) { .brio-footer-grid { grid-template-columns: 1fr; } }
	.brio-footer-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 10px; padding: 18px 20px; }
	.brio-footer-card h2 { margin: 0 0 12px; font-size: 15px; text-transform: uppercase; letter-spacing: .05em; color: #475569; }
	.brio-footer-card h3 { margin: 14px 0 8px; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #64748b; }
	.brio-footer-card__head { display: block; font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 6px; }
	.brio-footer-card__head input { display: block; width: 100%; margin-top: 4px; padding: 6px 10px; border: 1px solid #d1d5db; border-radius: 6px; }
	.brio-footer-link-row,
	.brio-footer-legal-row { display: grid; grid-template-columns: 1fr 1.6fr 32px; gap: 8px; margin-bottom: 8px; align-items: center; }
	.brio-footer-legal-row { grid-template-columns: 1fr 1.6fr; }
	.brio-footer-link-row input,
	.brio-footer-legal-row input { padding: 7px 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 13px; width: 100%; }
	.brio-footer-remove { color: #dc2626; min-width: 32px; padding: 0 8px !important; }
	.brio-footer-add { margin-top: 4px; }
	</style>

	<script>
	( function () {
		var addBtns = document.querySelectorAll( '.brio-footer-add' );
		addBtns.forEach( function ( btn ) {
			btn.addEventListener( 'click', function () {
				var col  = btn.dataset.col;
				var list = btn.parentElement.querySelector( '.brio-footer-links' );
				var row  = document.createElement( 'div' );
				row.className = 'brio-footer-link-row';
				row.innerHTML =
					'<input type="text" name="' + col + '_label[]" placeholder="Libellé" />' +
					'<input type="url" name="' + col + '_url[]" placeholder="https://…" />' +
					'<button type="button" class="button brio-footer-remove">×</button>';
				list.appendChild( row );
			} );
		} );
		document.addEventListener( 'click', function ( e ) {
			if ( e.target.classList.contains( 'brio-footer-remove' ) ) {
				e.target.closest( '.brio-footer-link-row' ).remove();
			}
		} );
	} )();
	</script>
	<?php
}
