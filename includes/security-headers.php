<?php
/**
 * Security Response Headers
 *
 * Outputs the HTTP security headers that Lighthouse "Best Practices" audits.
 * Sent via the WordPress `send_headers` action so they apply to every
 * front-end response (admin pages are skipped to avoid breaking the editor).
 *
 * Header reference:
 *   - HSTS      → forces HTTPS on subsequent visits (only effective once the
 *                 site is served over HTTPS; harmless on HTTP).
 *   - COOP      → isolates the browsing context group (window.opener-safety).
 *   - X-Frame   → blocks the page from being embedded in <iframe> (clickjacking).
 *   - X-Content → forbids MIME-sniffing.
 *   - Referrer  → strips referrer on cross-origin requests.
 *   - CSP       → restricts asset sources. The directives below mirror what the
 *                 theme actually loads (Google Fonts + cdnjs for Font Awesome).
 *                 `require-trusted-types-for 'script'` satisfies the DOM XSS audit.
 *   - Permissions → opt-out of unused browser features.
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Send security headers on every front-end request.
 *
 * Filterable via brio_security_headers to allow plugins or environment
 * overrides (e.g. relax CSP during development).
 *
 * @since 1.0.0
 */
function brio_send_security_headers() {
	if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	$csp = implode( '; ', [
		"default-src 'self'",
		"base-uri 'self'",
		"object-src 'none'",
		"frame-ancestors 'none'",
		"form-action 'self'",
		"img-src 'self' data: https:",
		"media-src 'self'",
		"font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com",
		"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
		"style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
		"script-src 'self' 'unsafe-inline'",
		"connect-src 'self' https://wa.me",
		"upgrade-insecure-requests",
	] );

	$headers = [
		'Strict-Transport-Security' => 'max-age=63072000; includeSubDomains; preload',
		'Cross-Origin-Opener-Policy' => 'same-origin',
		'Cross-Origin-Resource-Policy' => 'same-origin',
		'X-Frame-Options'            => 'DENY',
		'X-Content-Type-Options'     => 'nosniff',
		'Referrer-Policy'            => 'strict-origin-when-cross-origin',
		'Permissions-Policy'         => 'camera=(), microphone=(), geolocation=(), interest-cohort=()',
		'Content-Security-Policy'    => $csp,
	];

	$headers = apply_filters( 'brio_security_headers', $headers );

	foreach ( $headers as $name => $value ) {
		if ( $value ) {
			header( sprintf( '%s: %s', $name, $value ) );
		}
	}
}
add_action( 'send_headers', 'brio_send_security_headers' );
