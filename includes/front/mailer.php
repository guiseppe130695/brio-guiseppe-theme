<?php
/**
 * Mailer — Resend API
 *
 * Remplace l'envoi SMTP natif de wp_mail() par un appel HTTP à l'API Resend.
 * Aucun mot de passe email n'est stocké — seulement une clé API révocable.
 *
 * Configuration dans wp-config.php :
 *
 *   define( 'BRIO_RESEND_API_KEY', 're_xxxxxxxxxxxx' );
 *   define( 'BRIO_RESEND_FROM',    'Brio Guiseppe <noreply@tondomaine.com>' );
 *
 * Si BRIO_RESEND_API_KEY n'est pas défini, wp_mail() continue à fonctionner
 * normalement (fallback PHP mail).
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Intercept wp_mail() et déléguer à l'API Resend.
 *
 * Le filtre `pre_wp_mail` court-circuite tout le pipeline PHPMailer/SMTP
 * quand il retourne true. On retourne null si la clé n'est pas configurée
 * pour laisser WordPress utiliser son envoi natif.
 *
 * @param null|bool $return  null = laisser WP gérer ; true = envoi intercepté.
 * @param array     $atts    Tableau passé par wp_mail() : to, subject, message, headers, attachments.
 * @return null|true
 */
add_filter( 'pre_wp_mail', function( $return, $atts ) {

	if ( ! defined( 'BRIO_RESEND_API_KEY' ) || empty( BRIO_RESEND_API_KEY ) ) {
		return null;
	}

	$from = defined( 'BRIO_RESEND_FROM' ) ? BRIO_RESEND_FROM : 'noreply@' . wp_parse_url( home_url(), PHP_URL_HOST );

	/* Normalise les destinataires en tableau */
	$to = is_array( $atts['to'] ) ? $atts['to'] : [ $atts['to'] ];
	$to = array_map( 'sanitize_email', array_map( 'trim', $to ) );
	$to = array_filter( $to );

	if ( empty( $to ) ) {
		return null;
	}

	/* Détecte si le contenu est HTML */
	$is_html = false;
	$headers = $atts['headers'] ?? [];
	if ( is_string( $headers ) ) {
		$headers = explode( "\n", str_replace( "\r\n", "\n", $headers ) );
	}

	$reply_to = '';
	foreach ( $headers as $header ) {
		if ( stripos( $header, 'content-type' ) !== false && stripos( $header, 'text/html' ) !== false ) {
			$is_html = true;
		}
		if ( stripos( $header, 'reply-to:' ) !== false ) {
			$reply_to = trim( str_ireplace( 'reply-to:', '', $header ) );
		}
	}

	$body = [
		'from'    => $from,
		'to'      => array_values( $to ),
		'subject' => $atts['subject'],
	];

	if ( $is_html ) {
		$body['html'] = $atts['message'];
	} else {
		$body['text'] = $atts['message'];
	}

	if ( $reply_to ) {
		$body['reply_to'] = $reply_to;
	}

	$response = wp_remote_post( 'https://api.resend.com/emails', [
		'timeout' => 10,
		'headers' => [
			'Authorization' => 'Bearer ' . BRIO_RESEND_API_KEY,
			'Content-Type'  => 'application/json',
		],
		'body' => wp_json_encode( $body ),
	] );

	if ( is_wp_error( $response ) ) {
		/* Laisse WP logger l'erreur et tente le fallback natif */
		error_log( '[Brio Mailer] Resend API error: ' . $response->get_error_message() );
		return null;
	}

	$code = wp_remote_retrieve_response_code( $response );
	if ( $code < 200 || $code >= 300 ) {
		error_log( '[Brio Mailer] Resend API HTTP ' . $code . ': ' . wp_remote_retrieve_body( $response ) );
		return null;
	}

	return true;

}, 10, 2 );
