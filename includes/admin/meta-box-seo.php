<?php
/**
 * Meta Box — SEO (description override)
 *
 * Single field shared by every post type. Sits at the bottom of the editor
 * with `low` priority so template-specific meta boxes appear first.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_seo_register_meta_box() {
	foreach ( [ 'page', 'post' ] as $type ) {
		add_meta_box(
			'brio_seo_meta',
			__( 'SEO — Méta description', 'brio-guiseppe' ),
			'brio_seo_render_meta_box',
			$type,
			'normal',
			'low'
		);
	}
}
add_action( 'add_meta_boxes', 'brio_seo_register_meta_box' );

function brio_seo_render_meta_box( $post ) {
	wp_nonce_field( 'brio_seo_save', 'brio_seo_nonce' );
	$value = get_post_meta( $post->ID, '_brio_seo_description', true );
	?>
	<p class="brio-field">
		<label for="brio_seo_description"><strong><?php esc_html_e( 'Méta description (155–160 caractères)', 'brio-guiseppe' ); ?></strong></label><br />
		<textarea id="brio_seo_description"
		          name="brio_seo_description"
		          rows="3"
		          class="widefat"
		          maxlength="320"><?php echo esc_textarea( $value ); ?></textarea>
		<small><?php esc_html_e( 'Laissez vide pour utiliser un extrait automatique du contenu.', 'brio-guiseppe' ); ?></small>
	</p>
	<?php
}

function brio_seo_save_meta_box( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['brio_seo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_seo_nonce'] ) ), 'brio_seo_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$value = isset( $_POST['brio_seo_description'] )
		? sanitize_textarea_field( wp_unslash( $_POST['brio_seo_description'] ) )
		: '';

	update_post_meta( $post_id, '_brio_seo_description', $value );
}
add_action( 'save_post', 'brio_seo_save_meta_box' );
