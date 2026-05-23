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
	<p class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br />
		<input type="text"
		       id="<?php echo esc_attr( $name ); ?>"
		       name="<?php echo esc_attr( $name ); ?>"
		       value="<?php echo esc_attr( $value ); ?>"
		       class="widefat" />
	</p>
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
	<p class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br />
		<textarea id="<?php echo esc_attr( $name ); ?>"
		          name="<?php echo esc_attr( $name ); ?>"
		          rows="<?php echo (int) $rows; ?>"
		          class="widefat"><?php echo esc_textarea( $value ); ?></textarea>
	</p>
	<?php
}

/**
 * Render a URL input row.
 *
 * @since 1.0.0
 */
function brio_field_url( $name, $label, $value ) {
	?>
	<p class="brio-field">
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label><br />
		<input type="url"
		       id="<?php echo esc_attr( $name ); ?>"
		       name="<?php echo esc_attr( $name ); ?>"
		       value="<?php echo esc_url( $value ); ?>"
		       class="widefat"
		       placeholder="https://" />
	</p>
	<?php
}

/**
 * Render an image picker (stores the attachment URL as a string).
 *
 * Uses the native WordPress media frame via the brio-meta-box admin script.
 *
 * @since 1.0.0
 */
function brio_field_image( $name, $label, $value ) {
	?>
	<p class="brio-field brio-field--image">
		<label><strong><?php echo esc_html( $label ); ?></strong></label><br />
		<input type="url"
		       id="<?php echo esc_attr( $name ); ?>"
		       name="<?php echo esc_attr( $name ); ?>"
		       value="<?php echo esc_url( $value ); ?>"
		       class="widefat brio-image-url"
		       placeholder="https://…/image.jpg" />
		<button type="button" class="button brio-image-upload" data-target="<?php echo esc_attr( $name ); ?>">
			<?php esc_html_e( 'Choisir une image', 'brio-guiseppe' ); ?>
		</button>
		<?php if ( $value ) : ?>
			<br /><img src="<?php echo esc_url( $value ); ?>" alt="" style="max-width:160px;height:auto;margin-top:6px;" />
		<?php endif; ?>
	</p>
	<?php
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
	<p class="brio-field brio-field--json">
		<label for="<?php echo esc_attr( $name ); ?>"><strong><?php echo esc_html( $label ); ?></strong></label>
		<br />
		<small><?php esc_html_e( 'Format JSON. Voir le placeholder pour la structure attendue.', 'brio-guiseppe' ); ?></small>
		<textarea id="<?php echo esc_attr( $name ); ?>"
		          name="<?php echo esc_attr( $name ); ?>"
		          rows="8"
		          class="widefat code"
		          placeholder="<?php echo esc_attr( $placeholder ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
	</p>
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
function brio_meta_can_save( $post_id, $nonce ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return false;
	}
	if ( ! isset( $_POST[ $nonce ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $nonce ] ) ), $nonce ) ) {
		return false;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return false;
	}
	return true;
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
	wp_add_inline_script(
		'jquery-core',
		"jQuery(function($){
			$(document).on('click','.brio-image-upload',function(e){
				e.preventDefault();
				var target = $(this).data('target');
				var frame = wp.media({ title:'Choisir une image', multiple:false });
				frame.on('select', function(){
					var att = frame.state().get('selection').first().toJSON();
					$('input[name=\"'+target+'\"]').val(att.url).trigger('change');
				});
				frame.open();
			});
		});"
	);
}
add_action( 'admin_enqueue_scripts', 'brio_meta_admin_assets' );
