<?php
/**
 * Landing Page — Traitement du formulaire de contact.
 *
 * Validation stricte côté serveur avant tout traitement :
 *   - Nonce WordPress
 *   - Honeypot anti-bot
 *   - Rate limiting (1 soumission / IP / 60 s via transient)
 *   - Regex sur chaque champ
 *   - Longueurs min/max
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

function brio_landing_contact_handler() {

	/* 1 — Nonce */
	if (
		! isset( $_POST['brio_landing_contact_nonce'] ) ||
		! wp_verify_nonce(
			sanitize_text_field( wp_unslash( $_POST['brio_landing_contact_nonce'] ) ),
			'brio_landing_contact'
		)
	) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ), '', [ 'response' => 403 ] );
	}

	/* 2 — Honeypot (champ caché que les bots remplissent) */
	if ( ! empty( $_POST['lf_website'] ) ) {
		wp_die( esc_html__( 'Requête invalide.', 'brio-guiseppe' ), '', [ 'response' => 403 ] );
	}

	/* 3 — Rate limiting : 1 soumission par IP par minute (ignoré pour les admins) */
	$ip       = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0' ) );
	$rate_key = 'brio_lf_rate_' . md5( $ip );
	if ( ! current_user_can( 'manage_options' ) ) {
		if ( get_transient( $rate_key ) ) {
			wp_die( esc_html__( 'Trop de tentatives. Merci de patienter une minute.', 'brio-guiseppe' ), '', [ 'response' => 429 ] );
		}
		set_transient( $rate_key, 1, 60 );
	}

	/* 4 — Récupération brute */
	$raw_name    = wp_unslash( $_POST['lf_name']    ?? '' );
	$raw_email   = wp_unslash( $_POST['lf_email']   ?? '' );
	$raw_hotel   = wp_unslash( $_POST['lf_hotel']   ?? '' );
	$raw_message = wp_unslash( $_POST['lf_message'] ?? '' );
	$redirect    = esc_url_raw( wp_unslash( $_POST['redirect_to'] ?? home_url( '/' ) ) );

	/* 5 — Validation & regex */
	$errors = [];

	// Nom : lettres, espaces, tirets, apostrophes — 2 à 60 car.
	$name = sanitize_text_field( $raw_name );
	if ( empty( $name ) || strlen( $name ) < 2 || strlen( $name ) > 60 ) {
		$errors[] = 'name_length';
	} elseif ( ! preg_match( '/^[\p{L}\s\'\-\.]+$/u', $name ) ) {
		$errors[] = 'name_format';
	}

	// Email : format RFC + longueur max 254
	$email = sanitize_email( $raw_email );
	if ( ! is_email( $email ) || strlen( $email ) > 254 ) {
		$errors[] = 'email_invalid';
	}
	// Bloquer les domaines jetables les plus courants
	$blocked_domains = [ 'mailinator.com', 'guerrillamail.com', 'tempmail.com', 'throwam.com', 'yopmail.com' ];
	$email_domain    = strtolower( substr( strrchr( $email, '@' ), 1 ) );
	if ( in_array( $email_domain, $blocked_domains, true ) ) {
		$errors[] = 'email_disposable';
	}

	// Établissement : optionnel, mais si rempli : 2–100 car., pas de HTML
	$hotel = sanitize_text_field( $raw_hotel );
	if ( ! empty( $hotel ) && ( strlen( $hotel ) < 2 || strlen( $hotel ) > 100 ) ) {
		$errors[] = 'hotel_length';
	}
	if ( ! empty( $hotel ) && preg_match( '/<[^>]+>/', $hotel ) ) {
		$errors[] = 'hotel_html';
	}

	// Message : optionnel, max 2000 car., pas de balises HTML
	$message = sanitize_textarea_field( $raw_message );
	if ( ! empty( $message ) && strlen( $message ) > 2000 ) {
		$errors[] = 'message_too_long';
	}
	if ( ! empty( $message ) && preg_match( '/<[^>]+>/', $message ) ) {
		$errors[] = 'message_html';
	}

	// Bloquer les patterns spam courants dans le message
	if ( ! empty( $message ) && preg_match( '/\b(viagra|casino|crypto|bitcoin|loan|forex|seo service)\b/i', $message ) ) {
		$errors[] = 'message_spam';
	}

	if ( ! empty( $errors ) ) {
		wp_safe_redirect( add_query_arg( [
			'contact' => 'error',
			'lf_err'  => implode( ',', $errors ),
		], $redirect ) );
		exit;
	}

	/* 6 — Envoi email */
	$to      = get_option( 'admin_email' );
	$subject = sprintf( '[Brio Guiseppe] Nouveau lead : %s', $name );
	$body    = sprintf(
		"Prénom : %s\nEmail : %s\nÉtablissement : %s\n\nMessage :\n%s\n\nIP : %s",
		$name, $email, $hotel, $message, $ip
	);
	$headers = [
		'Content-Type: text/plain; charset=UTF-8',
		sprintf( 'Reply-To: %s <%s>', $name, $email ),
	];
	wp_mail( $to, $subject, $body, $headers );

	/* 7 — Sauvegarde en DB */
	brio_save_lead( $name, $email, $hotel, $message, $redirect );

	wp_safe_redirect( add_query_arg( 'contact', 'success', $redirect ) );
	exit;
}
add_action( 'admin_post_nopriv_brio_landing_contact', 'brio_landing_contact_handler' );
add_action( 'admin_post_brio_landing_contact',        'brio_landing_contact_handler' );
