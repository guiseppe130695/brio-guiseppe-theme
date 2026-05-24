<?php
/**
 * Leads — Custom Post Type + stockage des soumissions du formulaire landing.
 *
 * Chaque soumission du formulaire hero est enregistrée comme un post de type
 * `brio_lead` avec les champs nom, email, établissement, message, page source.
 * L'admin peut consulter, filtrer et supprimer les leads depuis WP Admin.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ── Register CPT ── */
function brio_register_lead_cpt() {
	register_post_type( 'brio_lead', [
		'labels' => [
			'name'               => __( 'Leads', 'brio-guiseppe' ),
			'singular_name'      => __( 'Lead', 'brio-guiseppe' ),
			'menu_name'          => __( 'Leads', 'brio-guiseppe' ),
			'all_items'          => __( 'Tous les leads', 'brio-guiseppe' ),
			'view_item'          => __( 'Voir le lead', 'brio-guiseppe' ),
			'search_items'       => __( 'Rechercher', 'brio-guiseppe' ),
			'not_found'          => __( 'Aucun lead trouvé.', 'brio-guiseppe' ),
			'not_found_in_trash' => __( 'Aucun lead dans la corbeille.', 'brio-guiseppe' ),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'menu_position'       => 25,
		'supports'            => [ 'title' ],
		'capability_type'     => 'post',
		'capabilities'        => [
			'create_posts' => 'do_not_allow', // no manual creation
		],
		'map_meta_cap'        => true,
	] );
}
add_action( 'init', 'brio_register_lead_cpt' );

/* ── Save lead to DB ── */
function brio_save_lead( $name, $email, $hotel, $message, $source_url = '' ) {
	$post_id = wp_insert_post( [
		'post_type'   => 'brio_lead',
		'post_title'  => sprintf( '%s — %s', sanitize_text_field( $name ), current_time( 'd/m/Y H:i' ) ),
		'post_status' => 'publish',
	] );

	if ( is_wp_error( $post_id ) ) {
		return false;
	}

	update_post_meta( $post_id, '_lead_name',       sanitize_text_field( $name ) );
	update_post_meta( $post_id, '_lead_email',      sanitize_email( $email ) );
	update_post_meta( $post_id, '_lead_hotel',      sanitize_text_field( $hotel ) );
	update_post_meta( $post_id, '_lead_message',    sanitize_textarea_field( $message ) );
	update_post_meta( $post_id, '_lead_source_url', esc_url_raw( $source_url ) );
	update_post_meta( $post_id, '_lead_status',     'new' );

	return $post_id;
}

/* ── Custom columns in admin list ── */
function brio_lead_columns( $cols ) {
	return [
		'cb'           => $cols['cb'],
		'lead_name'    => __( 'Nom', 'brio-guiseppe' ),
		'lead_email'   => __( 'Email', 'brio-guiseppe' ),
		'lead_hotel'   => __( 'Établissement', 'brio-guiseppe' ),
		'lead_message' => __( 'Message', 'brio-guiseppe' ),
		'lead_status'  => __( 'Statut', 'brio-guiseppe' ),
		'lead_source'  => __( 'Page source', 'brio-guiseppe' ),
		'date'         => __( 'Date', 'brio-guiseppe' ),
	];
}
add_filter( 'manage_brio_lead_posts_columns', 'brio_lead_columns' );

function brio_lead_column_content( $col, $post_id ) {
	switch ( $col ) {
		case 'lead_name':
			echo esc_html( get_post_meta( $post_id, '_lead_name', true ) );
			break;
		case 'lead_email':
			$email = get_post_meta( $post_id, '_lead_email', true );
			printf( '<a href="mailto:%s">%s</a>', esc_attr( $email ), esc_html( $email ) );
			break;
		case 'lead_hotel':
			echo esc_html( get_post_meta( $post_id, '_lead_hotel', true ) );
			break;
		case 'lead_message':
			echo esc_html( wp_trim_words( get_post_meta( $post_id, '_lead_message', true ), 12, '…' ) );
			break;
		case 'lead_status':
			$status = get_post_meta( $post_id, '_lead_status', true );
			$labels = [
				'new'       => [ __( 'Nouveau', 'brio-guiseppe' ),   '#2271b1', '#fff' ],
				'contacted' => [ __( 'Contacté', 'brio-guiseppe' ),  '#f0ad00', '#000' ],
				'closed'    => [ __( 'Fermé', 'brio-guiseppe' ),     '#46b450', '#fff' ],
			];
			[ $label, $bg, $color ] = $labels[ $status ] ?? $labels['new'];
			printf(
				'<span style="background:%s;color:%s;padding:2px 8px;border-radius:20px;font-size:12px">%s</span>',
				esc_attr( $bg ), esc_attr( $color ), esc_html( $label )
			);
			break;
		case 'lead_source':
			$url = get_post_meta( $post_id, '_lead_source_url', true );
			if ( $url ) {
				printf( '<a href="%s" target="_blank">%s</a>', esc_url( $url ), esc_html( wp_parse_url( $url, PHP_URL_PATH ) ) );
			}
			break;
	}
}
add_action( 'manage_brio_lead_posts_custom_column', 'brio_lead_column_content', 10, 2 );

/* ── Sortable columns ── */
function brio_lead_sortable_columns( $cols ) {
	$cols['lead_name']  = 'lead_name';
	$cols['lead_hotel'] = 'lead_hotel';
	return $cols;
}
add_filter( 'manage_edit-brio_lead_sortable_columns', 'brio_lead_sortable_columns' );

/* ── Detail meta box ── */
function brio_lead_detail_meta_box() {
	add_meta_box(
		'brio_lead_detail',
		__( 'Détail du lead', 'brio-guiseppe' ),
		'brio_lead_detail_render',
		'brio_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes_brio_lead', 'brio_lead_detail_meta_box' );

function brio_lead_detail_render( $post ) {
	$fields = [
		'_lead_name'       => __( 'Nom', 'brio-guiseppe' ),
		'_lead_email'      => __( 'Email', 'brio-guiseppe' ),
		'_lead_hotel'      => __( 'Établissement', 'brio-guiseppe' ),
		'_lead_message'    => __( 'Message', 'brio-guiseppe' ),
		'_lead_source_url' => __( 'Page source', 'brio-guiseppe' ),
	];
	echo '<table class="form-table">';
	foreach ( $fields as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );
		echo '<tr><th style="width:140px">' . esc_html( $label ) . '</th><td>';
		if ( $key === '_lead_email' ) {
			printf( '<a href="mailto:%s">%s</a>', esc_attr( $value ), esc_html( $value ) );
		} elseif ( $key === '_lead_source_url' ) {
			printf( '<a href="%s" target="_blank">%s</a>', esc_url( $value ), esc_html( $value ) );
		} elseif ( $key === '_lead_message' ) {
			echo '<p style="white-space:pre-wrap">' . esc_html( $value ) . '</p>';
		} else {
			echo esc_html( $value );
		}
		echo '</td></tr>';
	}
	echo '</table>';

	// Status changer
	$status = get_post_meta( $post->ID, '_lead_status', true ) ?: 'new';
	wp_nonce_field( 'brio_lead_status_save', 'brio_lead_status_nonce' );
	echo '<p style="margin-top:16px"><strong>' . esc_html__( 'Statut :', 'brio-guiseppe' ) . '</strong> ';
	echo '<select name="brio_lead_status">';
	foreach ( [ 'new' => __( 'Nouveau', 'brio-guiseppe' ), 'contacted' => __( 'Contacté', 'brio-guiseppe' ), 'closed' => __( 'Fermé', 'brio-guiseppe' ) ] as $val => $lbl ) {
		printf( '<option value="%s"%s>%s</option>', esc_attr( $val ), selected( $status, $val, false ), esc_html( $lbl ) );
	}
	echo '</select></p>';
}

/* ── Save status ── */
function brio_lead_save_status( $post_id ) {
	if (
		! isset( $_POST['brio_lead_status_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['brio_lead_status_nonce'] ) ), 'brio_lead_status_save' )
	) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
	if ( isset( $_POST['brio_lead_status'] ) ) {
		$allowed = [ 'new', 'contacted', 'closed' ];
		$status  = sanitize_text_field( wp_unslash( $_POST['brio_lead_status'] ) );
		if ( in_array( $status, $allowed, true ) ) {
			update_post_meta( $post_id, '_lead_status', $status );
		}
	}
}
add_action( 'save_post_brio_lead', 'brio_lead_save_status' );

/* ── Remove "Edit" publish box — leads are read-only ── */
function brio_lead_remove_publish_box() {
	remove_meta_box( 'submitdiv', 'brio_lead', 'side' );
}
add_action( 'admin_menu', 'brio_lead_remove_publish_box' );
