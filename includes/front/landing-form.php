<?php
/**
 * Landing Page — Traitement du formulaire de contact.
 *
 * Reçoit le POST du formulaire hero, valide, envoie un email à l'admin
 * et redirige vers la même page avec ?contact=success.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_landing_contact_handler() {
	if ( ! isset( $_POST['brio_landing_contact_nonce'] )
		|| ! wp_verify_nonce( $_POST['brio_landing_contact_nonce'], 'brio_landing_contact' )
	) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ), '', [ 'response' => 403 ] );
	}

	$name     = sanitize_text_field( $_POST['lf_name']    ?? '' );
	$email    = sanitize_email(      $_POST['lf_email']   ?? '' );
	$hotel    = sanitize_text_field( $_POST['lf_hotel']   ?? '' );
	$message  = sanitize_textarea_field( $_POST['lf_message'] ?? '' );
	$redirect = esc_url_raw( $_POST['redirect_to'] ?? home_url( '/' ) );

	if ( ! $name || ! is_email( $email ) ) {
		wp_safe_redirect( add_query_arg( 'contact', 'error', $redirect ) );
		exit;
	}

	$to      = get_option( 'admin_email' );
	$subject = sprintf( __( '[Brio Guiseppe] Nouveau lead : %s', 'brio-guiseppe' ), $name );
	$body    = sprintf(
		"Prénom : %s\nEmail : %s\nÉtablissement : %s\n\nMessage :\n%s",
		$name, $email, $hotel, $message
	);
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	];

	wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( add_query_arg( 'contact', 'success', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_brio_landing_contact', 'brio_landing_contact_handler' );
add_action( 'admin_post_brio_landing_contact',        'brio_landing_contact_handler' );
