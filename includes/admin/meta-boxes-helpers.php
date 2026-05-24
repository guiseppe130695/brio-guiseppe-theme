<?php
/**
 * Meta Box Helpers
 *
 * Reusable rendering and saving primitives for the per-template meta boxes
 * (landing, page annexe, outils). Each section meta box uses these helpers
 * so that fields stay visually and behaviorally consistent across templates.
 *
 * Storage convention: each field is saved as its own post_meta key with the
 * pattern `_brio_{template}_{section}_{field}` (underscore prefix hides the
 * meta from the default Custom Fields UI).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build the canonical meta key for a field.
 *
 * @since 1.0.0
 *
 * @param string $template Template slug (landing|page|outils).
 * @param string $section  Section slug (hero|cta|...).
 * @param string $field    Field slug (title|subtitle|...).
 * @return string
 */
function brio_meta_key( $template, $section, $field ) {
	return sprintf( '_brio_%s_%s_%s', $template, $section, $field );
}

/**
 * Read a meta value with a default fallback.
 *
 * Used by the front-end data providers so empty fields gracefully fall back
 * to the design copy instead of rendering an empty section.
 *
 * @since 1.0.0
 *
 * @param int    $post_id  Post being rendered.
 * @param string $template Template slug.
 * @param string $section  Section slug.
 * @param string $field    Field slug.
 * @param mixed  $default  Fallback when meta is empty.
 * @return mixed
 */
function brio_meta_get( $post_id, $template, $section, $field, $default = '' ) {
	$value = get_post_meta( $post_id, brio_meta_key( $template, $section, $field ), true );

	if ( '' === $value || null === $value || false === $value ) {
		return $default;
	}

	return $value;
}

/**
 * Render a text input row.
 *
 * @since 1.0.0
 *
 * @param string $name  HTML name attribute (also the data key in $_POST).
 * @param string $label Visible label.
 * @param string $value Current value.
 */
function brio_field_text( $name, $label, $value ) {
	?>
	<div class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<input type="text"
		       id="<?php echo esc_attr( $name ); ?>"
		       name="<?php echo esc_attr( $name ); ?>"
		       value="<?php echo esc_attr( $value ); ?>"
		       class="widefat" />
	</div>
	<?php
}

/**
 * Render a textarea row.
 *
 * @since 1.0.0
 *
 * @param string $name  HTML name attribute.
 * @param string $label Visible label.
 * @param string $value Current value.
 * @param int    $rows  Number of visible rows.
 */
function brio_field_textarea( $name, $label, $value, $rows = 4 ) {
	?>
	<div class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<textarea id="<?php echo esc_attr( $name ); ?>"
		          name="<?php echo esc_attr( $name ); ?>"
		          rows="<?php echo (int) $rows; ?>"
		          class="widefat"><?php echo esc_textarea( $value ); ?></textarea>
	</div>
	<?php
}

/**
 * Render a URL input row.
 *
 * @since 1.0.0
 */
function brio_field_url( $name, $label, $value ) {
	?>
	<div class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<input type="url"
		       id="<?php echo esc_attr( $name ); ?>"
		       name="<?php echo esc_attr( $name ); ?>"
		       value="<?php echo esc_url( $value ); ?>"
		       class="widefat"
		       placeholder="https://" />
	</div>
	<?php
}

/**
 * Render an image picker (stores the attachment URL as a string).
 *
 * Compact layout: thumbnail + URL input + button on one line.
 */
function brio_field_image( $name, $label, $value ) {
	?>
	<div class="brio-field brio-field--image">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<div class="brio-image-row">
			<span class="brio-img-thumb<?php echo $value ? '' : ' is-empty'; ?>">
				<?php if ( $value ) : ?>
					<img src="<?php echo esc_url( $value ); ?>" alt="" class="brio-img-preview" />
				<?php else : ?>
					<span class="dashicons dashicons-format-image" aria-hidden="true"></span>
				<?php endif; ?>
			</span>
			<input type="url"
			       id="<?php echo esc_attr( $name ); ?>"
			       name="<?php echo esc_attr( $name ); ?>"
			       value="<?php echo esc_url( $value ); ?>"
			       class="widefat brio-image-url"
			       placeholder="https://…/image.jpg" />
			<button type="button" class="button brio-image-upload" data-target="<?php echo esc_attr( $name ); ?>">
				<?php esc_html_e( 'Choisir…', 'brio-guiseppe' ); ?>
			</button>
		</div>
	</div>
	<?php
}

/**
 * Render the standard section header block (Surtitre + Titre [+ Description]).
 *
 * @param int    $post_id  Post being edited.
 * @param string $template Template slug.
 * @param string $section  Section slug.
 * @param array  $args {
 *     @type bool $overline    Whether to include the overline field. Default true.
 *     @type bool $heading     Whether to include the heading field. Default true.
 *     @type bool $description Whether to include the description field. Default false.
 * }
 */
function brio_section_header( $post_id, $template, $section, $args = [] ) {
	$args = wp_parse_args( $args, [
		'overline'    => true,
		'heading'     => true,
		'description' => false,
	] );
	echo '<div class="brio-block"><h4 class="brio-block__title">' . esc_html__( 'En-tête de section', 'brio-guiseppe' ) . '</h4>';
	if ( $args['overline'] ) {
		brio_field_text( "brio_{$template}_{$section}_overline", __( 'Surtitre', 'brio-guiseppe' ), brio_meta_get( $post_id, $template, $section, 'overline' ) );
	}
	if ( $args['heading'] ) {
		brio_field_text( "brio_{$template}_{$section}_heading", __( 'Titre', 'brio-guiseppe' ), brio_meta_get( $post_id, $template, $section, 'heading' ) );
	}
	if ( $args['description'] ) {
		brio_field_textarea( "brio_{$template}_{$section}_description", __( 'Description', 'brio-guiseppe' ), brio_meta_get( $post_id, $template, $section, 'description' ) );
	}
	echo '</div>';
}

/**
 * Render a grouped CTA (label + URL) as a single visual unit.
 *
 * @param int    $post_id  Post being edited.
 * @param string $template Template slug.
 * @param string $section  Section slug.
 * @param array  $args {
 *     @type string $label_field Field slug for the label. Default 'cta_label'.
 *     @type string $url_field   Field slug for the URL. Default 'cta_url'.
 *     @type string $title       Block title. Default "Bouton".
 * }
 */
function brio_field_cta( $post_id, $template, $section, $args = [] ) {
	$args = wp_parse_args( $args, [
		'label_field' => 'cta_label',
		'url_field'   => 'cta_url',
		'title'       => __( 'Bouton', 'brio-guiseppe' ),
	] );
	$label_name = "brio_{$template}_{$section}_{$args['label_field']}";
	$url_name   = "brio_{$template}_{$section}_{$args['url_field']}";
	$label_val  = brio_meta_get( $post_id, $template, $section, $args['label_field'] );
	$url_val    = brio_meta_get( $post_id, $template, $section, $args['url_field'] );
	?>
	<div class="brio-block brio-block--cta">
		<h4 class="brio-block__title"><?php echo esc_html( $args['title'] ); ?></h4>
		<div class="brio-cta-row">
			<div class="brio-field">
				<label for="<?php echo esc_attr( $label_name ); ?>"><?php esc_html_e( 'Libellé', 'brio-guiseppe' ); ?></label>
				<input type="text" id="<?php echo esc_attr( $label_name ); ?>" name="<?php echo esc_attr( $label_name ); ?>" value="<?php echo esc_attr( $label_val ); ?>" class="widefat" />
			</div>
			<div class="brio-field">
				<label for="<?php echo esc_attr( $url_name ); ?>"><?php esc_html_e( 'Lien (URL)', 'brio-guiseppe' ); ?></label>
				<input type="url" id="<?php echo esc_attr( $url_name ); ?>" name="<?php echo esc_attr( $url_name ); ?>" value="<?php echo esc_url( $url_val ); ?>" class="widefat" placeholder="https://" />
			</div>
		</div>
	</div>
	<?php
}

/**
 * Open a collapsible repeater wrapper.
 *
 * @param string $title Block title (e.g. "Programmes").
 * @param int    $count Number of items (for the counter chip).
 */
function brio_repeater_open( $title, $count ) {
	?>
	<div class="brio-block brio-block--repeater">
		<h4 class="brio-block__title">
			<?php echo esc_html( $title ); ?>
			<span class="brio-chip"><?php echo (int) $count; ?></span>
		</h4>
		<div class="brio-accordion" data-accordion>
	<?php
}

function brio_repeater_close() {
	echo '</div></div>';
}

/**
 * Render a single accordion item. Title shown when collapsed is derived from
 * $preview_value (live-updated via JS when its input changes).
 *
 * Usage:
 *   brio_repeater_item_open( "Programme", 3, "preview-input-name", $preview_value );
 *   ...fields...
 *   brio_repeater_item_close();
 *
 * @param string $label     Static label (e.g. "Programme").
 * @param int    $index     1-based index.
 * @param string $watch     Input name whose value drives the live preview.
 * @param string $preview   Current value to show when collapsed.
 * @param string $empty_txt Text when preview is empty.
 */
function brio_repeater_item_open( $label, $index, $watch, $preview, $empty_txt = '' ) {
	$empty_txt = $empty_txt ?: __( 'vide', 'brio-guiseppe' );
	$display   = $preview !== '' ? $preview : '— ' . $empty_txt . ' —';
	?>
	<details class="brio-acc-item">
		<summary class="brio-acc-summary">
			<span class="brio-acc-caret dashicons dashicons-arrow-right" aria-hidden="true"></span>
			<span class="brio-acc-index"><?php echo (int) $index; ?></span>
			<span class="brio-acc-label"><?php echo esc_html( $label ); ?></span>
			<span class="brio-acc-preview" data-watch="<?php echo esc_attr( $watch ); ?>" data-empty="<?php echo esc_attr( '— ' . $empty_txt . ' —' ); ?>"><?php echo esc_html( $display ); ?></span>
		</summary>
		<div class="brio-acc-body">
	<?php
}

function brio_repeater_item_close() {
	echo '</div></details>';
}

/**
 * Render a JSON textarea (used for simple repeaters: features, FAQs, etc.).
 *
 * Value is stored as a JSON-encoded string. The front-end decodes it back to
 * an array. Empty / invalid JSON falls back to an empty array.
 *
 * @since 1.0.0
 *
 * @param string $name        HTML name attribute.
 * @param string $label       Visible label.
 * @param string $value       Current raw JSON value.
 * @param string $placeholder Example JSON shown when empty.
 */
function brio_field_json( $name, $label, $value, $placeholder = '' ) {
	?>
	<div class="brio-field brio-field--json">
		<label for="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $label ); ?></label>
		<small class="brio-field__hint"><?php esc_html_e( 'Format JSON. Voir le placeholder pour la structure attendue.', 'brio-guiseppe' ); ?></small>
		<textarea id="<?php echo esc_attr( $name ); ?>"
		          name="<?php echo esc_attr( $name ); ?>"
		          rows="8"
		          class="widefat code"
		          placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	</div>
	<?php
}

/**
 * Decode a JSON-encoded meta value to an array (safe).
 *
 * @since 1.0.0
 *
 * @param string $raw     Raw JSON string from post meta.
 * @param array  $default Fallback when missing / invalid.
 * @return array
 */
function brio_meta_json_decode( $raw, $default = [] ) {
	if ( empty( $raw ) ) {
		return $default;
	}
	$decoded = json_decode( $raw, true );
	if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
		return $default;
	}
	return $decoded;
}

/**
 * Verify nonce + autosave + capability before persisting meta box data.
 *
 * Returns true when the current request is a legitimate save we should act
 * on, false otherwise. Centralized so each save handler is one-liner safe.
 *
 * @since 1.0.0
 *
 * @param int    $post_id Post being saved.
 * @param string $nonce   Nonce action name (matches wp_nonce_field()).
 * @return bool
 */
function brio_meta_can_save( $post_id, $nonce_field, $nonce_action = '' ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	// Accept our meta box nonce (classic editor) OR the native WP nonce (block editor).
	$action = $nonce_action ?: $nonce_field;
	if ( isset( $_POST[ $nonce_field ] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce_field ] ) ), $action ) ) {
		return true;
	}
	if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-post_' . $post_id ) ) {
		return true;
	}
	return false;
}

/**
 * Persist a single field from $_POST to post meta, with a sanitizer.
 *
 * @since 1.0.0
 *
 * @param int      $post_id   Post being saved.
 * @param string   $template  Template slug.
 * @param string   $section   Section slug.
 * @param string   $field     Field slug (also the $_POST key name).
 * @param string   $sanitizer One of: text, textarea, url, json.
 */
function brio_meta_save_field( $post_id, $template, $section, $field, $sanitizer = 'text' ) {
	$post_key = sprintf( 'brio_%s_%s_%s', $template, $section, $field );
	$meta_key = brio_meta_key( $template, $section, $field );

	if ( ! isset( $_POST[ $post_key ] ) ) {
		return;
	}

	$raw = wp_unslash( $_POST[ $post_key ] );

	switch ( $sanitizer ) {
		case 'textarea':
			$clean = sanitize_textarea_field( $raw );
			break;
		case 'url':
			$clean = esc_url_raw( $raw );
			break;
		case 'json':
			$decoded = json_decode( $raw, true );
			if ( JSON_ERROR_NONE === json_last_error() && is_array( $decoded ) ) {
				$clean = wp_json_encode( $decoded );
			} else {
				$clean = '';
			}
			break;
		case 'text':
		default:
			$clean = sanitize_text_field( $raw );
			break;
	}

	update_post_meta( $post_id, $meta_key, $clean );
}

/**
 * Restrict a meta box to pages using a given page template.
 *
 * Use as: add_action( 'add_meta_boxes_page', function( $post ) {
 *     if ( ! brio_meta_box_applies( $post, 'template-landing.php' ) ) { return; }
 *     add_meta_box( ... );
 * } );
 *
 * @since 1.0.0
 *
 * @param WP_Post $post     Current post object.
 * @param string  $template Page template filename (e.g. "template-landing.php").
 * @return bool
 */
function brio_meta_box_applies( $post, $template ) {
	if ( ! $post || 'page' !== $post->post_type ) {
		return false;
	}
	$current = get_page_template_slug( $post->ID );
	return $current === $template;
}

/**
 * Enqueue the WP media uploader on page-edit screens that use our templates.
 *
 * @since 1.0.0
 */
function brio_meta_admin_assets( $hook ) {
	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}
	wp_enqueue_media();

	/* ── Tabs + grid CSS ── */
	wp_add_inline_style( 'wp-admin', '
/* ---- Brio meta tabs (vertical) ---- */
#brio_landing_sections .inside,
#brio_landing_sections .postbox-header + .inside { margin: 0; padding: 0; }

.brio-tabs {
	font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", sans-serif;
	color: #1d2327;
	background: #f6f7f7;
}
.brio-tabs--vertical {
	display: grid;
	grid-template-columns: 248px 1fr;
	min-height: 560px;
}

/* ── Vertical nav ── */
.brio-tabs__nav {
	background: #fff;
	border-right: 1px solid #e5e5e7;
	padding: 14px 10px;
	display: flex;
	flex-direction: column;
	gap: 2px;
	align-self: stretch;
	position: sticky;
	top: 46px; /* WP admin bar */
}
.brio-tabs__nav-head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 4px 10px 12px;
	margin-bottom: 4px;
	border-bottom: 1px solid #f0f0f1;
}
.brio-tabs__nav-title {
	font-size: 10px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: .9px;
	color: #8c8f94;
}
.brio-tabs__nav-count {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 20px;
	height: 18px;
	padding: 0 6px;
	background: #f0f0f1;
	color: #50575e;
	border-radius: 9px;
	font-size: 10px;
	font-weight: 700;
}

.brio-tabs__btn {
	display: grid;
	grid-template-columns: 32px 1fr auto;
	align-items: center;
	gap: 10px;
	padding: 9px 10px;
	border: 1px solid transparent;
	border-radius: 6px;
	background: transparent;
	color: #50575e;
	font-size: 13px;
	text-align: left;
	cursor: pointer;
	transition: background .12s, color .12s, border-color .12s, transform .12s;
	position: relative;
}
.brio-tabs__btn:hover {
	background: #f6f7f7;
	color: #1d2327;
}
.brio-tabs__btn:focus-visible {
	outline: 2px solid var(--wp-admin-theme-color, #2271b1);
	outline-offset: 1px;
}
.brio-tabs__btn.is-active {
	background: color-mix(in srgb, var(--wp-admin-theme-color, #2271b1) 8%, #fff);
	color: var(--wp-admin-theme-color-darker-20, #135e96);
	border-color: color-mix(in srgb, var(--wp-admin-theme-color, #2271b1) 25%, #fff);
	box-shadow: 0 1px 0 color-mix(in srgb, var(--wp-admin-theme-color, #2271b1) 6%, transparent);
}
.brio-tabs__btn.is-active::before {
	content: "";
	position: absolute;
	left: -10px;
	top: 8px;
	bottom: 8px;
	width: 3px;
	border-radius: 0 3px 3px 0;
	background: var(--wp-admin-theme-color, #2271b1);
}

.brio-tabs__icon-wrap {
	width: 32px;
	height: 32px;
	border-radius: 6px;
	background: #f6f7f7;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	color: #646970;
	transition: background .12s, color .12s;
	flex-shrink: 0;
}
.brio-tabs__btn:hover .brio-tabs__icon-wrap { background: #eef0f1; color: #1d2327; }
.brio-tabs__btn.is-active .brio-tabs__icon-wrap {
	background: var(--wp-admin-theme-color, #2271b1);
	color: #fff;
	box-shadow: 0 1px 2px color-mix(in srgb, var(--wp-admin-theme-color, #2271b1) 35%, transparent);
}
.brio-tabs__icon.dashicons { font-size: 18px; width: 18px; height: 18px; line-height: 1; }

.brio-tabs__text { display: flex; flex-direction: column; min-width: 0; line-height: 1.25; }
.brio-tabs__label { font-weight: 600; font-size: 13px; color: inherit; }
.brio-tabs__btn.is-active .brio-tabs__label { color: var(--wp-admin-theme-color-darker-20, #135e96); }
.brio-tabs__hint {
	font-size: 11px;
	font-weight: 400;
	color: #8c8f94;
	margin-top: 1px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
.brio-tabs__btn.is-active .brio-tabs__hint { color: var(--wp-admin-theme-color, #2271b1); opacity: .75; }

.brio-tabs__dot {
	width: 7px;
	height: 7px;
	border-radius: 50%;
	background: #46b450;
	box-shadow: 0 0 0 2px rgba(70, 180, 80, .15);
	flex-shrink: 0;
}

/* ── Body / panel ── */
.brio-tabs__body { background: #f6f7f7; min-width: 0; }

.brio-tabs__panel { padding: 0; }
.brio-tabs__panel[hidden] { display: none; }

.brio-panel-head {
	display: flex;
	align-items: center;
	gap: 14px;
	padding: 20px 28px;
	background: #fff;
	border-bottom: 1px solid #e5e5e7;
	position: sticky;
	top: 46px;
	z-index: 1;
}
.brio-panel-head__icon.dashicons {
	width: 36px;
	height: 36px;
	font-size: 22px;
	line-height: 36px;
	color: var(--wp-admin-theme-color, #2271b1);
	background: color-mix(in srgb, var(--wp-admin-theme-color, #2271b1) 10%, #fff);
	border-radius: 8px;
	text-align: center;
	flex-shrink: 0;
}
.brio-panel-head__title { margin: 0; font-size: 16px; font-weight: 600; color: #1d2327; line-height: 1.2; }
.brio-panel-head__hint  { margin: 2px 0 0; font-size: 12px; color: #8c8f94; }

.brio-panel-body { padding: 24px 28px; }

.brio-tabs__heading { display: none; }

/* ── Responsive fallback (WP mobile breakpoint = 782px) ── */
@media (max-width: 782px) {
	.brio-tabs--vertical { grid-template-columns: 1fr; }
	.brio-tabs__nav {
		position: static;
		border-right: none;
		border-bottom: 1px solid #e5e5e7;
		flex-direction: row;
		overflow-x: auto;
		padding: 10px;
		gap: 6px;
	}
	.brio-tabs__nav-head { display: none; }
	.brio-tabs__btn {
		flex: 0 0 auto;
		grid-template-columns: 24px auto;
	}
	.brio-tabs__hint, .brio-tabs__dot { display: none; }
	.brio-tabs__btn.is-active::before { display: none; }
	.brio-panel-head { padding: 14px 16px; position: static; }
	.brio-panel-body { padding: 16px; }
}

/* ---- Block (En-tête / Items / Bouton wrappers) ---- */
.brio-block {
	margin: 0 0 22px;
	padding: 0;
}
.brio-block__title {
	margin: 0 0 10px;
	padding: 0 0 6px;
	font-size: 11px;
	font-weight: 600;
	color: #646970;
	text-transform: uppercase;
	letter-spacing: .6px;
	border-bottom: 1px solid #f0f0f1;
	display: flex;
	align-items: center;
	gap: 8px;
}
.brio-chip {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	min-width: 18px;
	height: 18px;
	padding: 0 6px;
	background: #f0f0f1;
	color: #50575e;
	border-radius: 9px;
	font-size: 10px;
	font-weight: 700;
	text-transform: none;
	letter-spacing: 0;
}

/* ---- Field ---- */
.brio-field { margin: 0 0 12px; }
.brio-field > label {
	display: block;
	font-size: 12px;
	font-weight: 500;
	color: #50575e;
	margin-bottom: 4px;
}
.brio-field__hint {
	display: block;
	color: #8c8f94;
	font-size: 11px;
	margin: -2px 0 4px;
}
.brio-field input.widefat,
.brio-field textarea.widefat {
	border-radius: 3px;
	border-color: #c3c4c7;
	box-shadow: inset 0 1px 2px rgba(0,0,0,.04);
	transition: border-color .1s, box-shadow .1s;
}
.brio-field input.widefat:focus,
.brio-field textarea.widefat:focus { border-color: var(--wp-admin-theme-color, #2271b1); box-shadow: 0 0 0 1px var(--wp-admin-theme-color, #2271b1); outline: none; }

/* paired fields (Prix + Devise, Libellé + URL bouton…) */
.brio-pair {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
	gap: 12px;
	margin-bottom: 12px;
}
.brio-pair .brio-field { margin-bottom: 0; }

/* ---- CTA block ---- */
.brio-block--cta {
	background: #fafafa;
	border: 1px solid #f0f0f1;
	border-radius: 4px;
	padding: 12px 14px;
}
.brio-block--cta .brio-block__title { border: none; padding: 0; margin-bottom: 8px; }
.brio-cta-row {
	display: grid;
	grid-template-columns: minmax(140px, 1fr) minmax(220px, 2fr);
	gap: 10px;
}
.brio-cta-row .brio-field { margin: 0; }

/* ---- Image picker (compact, one row) ---- */
.brio-field--image .brio-image-row {
	display: grid;
	grid-template-columns: 60px 1fr auto;
	gap: 8px;
	align-items: center;
}
.brio-img-thumb {
	width: 60px;
	height: 60px;
	border: 1px solid #dcdcde;
	border-radius: 3px;
	background: #fff;
	display: flex;
	align-items: center;
	justify-content: center;
	overflow: hidden;
}
.brio-img-thumb.is-empty { background: #f6f7f7; color: #c3c4c7; }
.brio-img-thumb .dashicons { font-size: 24px; width: 24px; height: 24px; }
.brio-img-preview { width: 100%; height: 100%; object-fit: cover; display: block; }

/* ---- Accordion repeater ---- */
.brio-accordion { display: flex; flex-direction: column; gap: 4px; }
.brio-acc-item {
	border: 1px solid #dcdcde;
	border-radius: 4px;
	background: #fff;
}
.brio-acc-item[open] { background: #fafafa; border-color: #c3c4c7; }
.brio-acc-summary {
	list-style: none;
	cursor: pointer;
	padding: 9px 12px;
	display: flex;
	align-items: center;
	gap: 10px;
	font-size: 13px;
	color: #1d2327;
	user-select: none;
}
.brio-acc-summary::-webkit-details-marker { display: none; }
.brio-acc-summary:hover { background: #f6f7f7; }
.brio-acc-item[open] > .brio-acc-summary { border-bottom: 1px solid #f0f0f1; }
.brio-acc-caret {
	font-size: 16px;
	width: 16px;
	height: 16px;
	color: #8c8f94;
	transition: transform .15s;
}
.brio-acc-item[open] > .brio-acc-summary .brio-acc-caret { transform: rotate(90deg); }
.brio-acc-index {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	width: 22px;
	height: 22px;
	background: #f0f0f1;
	color: #50575e;
	border-radius: 50%;
	font-size: 11px;
	font-weight: 600;
	flex-shrink: 0;
}
.brio-acc-item[open] > .brio-acc-summary .brio-acc-index {
	background: var(--wp-admin-theme-color, #2271b1);
	color: #fff;
}
.brio-acc-label {
	color: #50575e;
	font-weight: 500;
}
.brio-acc-preview {
	color: #1d2327;
	font-weight: 400;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	flex: 1;
	min-width: 0;
}
.brio-acc-preview:empty::before { content: "— vide —"; color: #a7aaad; font-style: italic; }
.brio-acc-body { padding: 14px; }
	' );

	/* ── Tabs JS + media uploader ── */
	wp_add_inline_script( 'jquery-core', "
(function(){
	document.addEventListener('DOMContentLoaded', function(){

		/* --- Tabs --- */
		document.querySelectorAll('.brio-tabs__nav').forEach(function(nav){
			nav.addEventListener('click', function(e){
				var btn = e.target.closest('.brio-tabs__btn');
				if (!btn) return;
				var wrap = nav.closest('.brio-tabs');
				wrap.querySelectorAll('.brio-tabs__btn').forEach(function(b){ b.classList.remove('is-active'); b.setAttribute('aria-selected','false'); });
				wrap.querySelectorAll('.brio-tabs__panel').forEach(function(p){ p.classList.remove('is-active'); p.hidden = true; });
				btn.classList.add('is-active');
				btn.setAttribute('aria-selected','true');
				var panel = wrap.querySelector('#' + btn.dataset.tab);
				if (panel) { panel.classList.add('is-active'); panel.hidden = false; }
			});
		});

		/* --- Accordion live preview --- */
		document.querySelectorAll('.brio-acc-preview[data-watch]').forEach(function(prev){
			var name = prev.dataset.watch;
			var empty = prev.dataset.empty || '— vide —';
			var input = document.querySelector('[name=\"' + name + '\"]');
			if (!input) return;
			input.addEventListener('input', function(){
				prev.textContent = input.value ? input.value : empty;
			});
		});

		/* --- Accordion: single-open within a group --- */
		document.querySelectorAll('[data-accordion]').forEach(function(group){
			group.addEventListener('toggle', function(e){
				var item = e.target;
				if (item.tagName !== 'DETAILS' || !item.open) return;
				group.querySelectorAll('details[open]').forEach(function(other){
					if (other !== item) other.open = false;
				});
			}, true);
		});

		/* --- Media uploader --- */
		document.addEventListener('click', function(e){
			var trigger = e.target.closest('.brio-image-upload');
			if (!trigger) return;
			e.preventDefault();
			var target = trigger.dataset.target;
			var frame = wp.media({ title: 'Choisir une image', multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				var input = document.querySelector('input[name=\"' + target + '\"]');
				if (!input) return;
				input.value = att.url;
				var wrap = input.closest('.brio-field--image');
				var thumb = wrap ? wrap.querySelector('.brio-img-thumb') : null;
				if (thumb) {
					thumb.classList.remove('is-empty');
					thumb.innerHTML = '<img src=\"' + att.url + '\" alt=\"\" class=\"brio-img-preview\" />';
				}
			});
			frame.open();
		});
	});
})();
	" );
}
add_action( 'admin_enqueue_scripts', 'brio_meta_admin_assets' );
