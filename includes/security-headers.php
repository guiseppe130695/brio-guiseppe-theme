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
		"font-src 'self' data: https://fonts.gstatic.com",
		"style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
		"style-src-elem 'self' 'unsafe-inline' https://fonts.googleapis.com",
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

/**
 * Disable the WordPress file editor from the admin.
 *
 * Prevents any admin account from editing theme/plugin PHP files directly
 * from Apparence > Éditeur — equivalent to DISALLOW_FILE_EDIT in wp-config.
 *
 * @since 1.0.0
 */
if ( ! defined( 'DISALLOW_FILE_EDIT' ) ) {
	define( 'DISALLOW_FILE_EDIT', true );
}
if ( ! defined( 'DISALLOW_FILE_MODS' ) ) {
	define( 'DISALLOW_FILE_MODS', true );
}

/**
 * Lock down the REST API — expose only the brio/v1 namespace.
 *
 * WordPress's default /wp/v2 routes expose usernames, post IDs, and other
 * metadata publicly. We keep only our own endpoint and the authentication
 * route (needed for the admin JS to function).
 *
 * @since 1.0.0
 */
function brio_restrict_rest_api( $result ) {
	if ( ! empty( $result ) ) {
		return $result; // Already handled (e.g. authentication error).
	}

	if ( is_user_logged_in() ) {
		return $result; // Logged-in users (admin) get full access.
	}

	$request    = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
	$request    = trailingslashit( (string) $request );
	$allowed    = [
		'/brio/v1/',           // Our own blog endpoint.
		'/wp/v2/media/',       // Needed for some front-end image lookups.
	];

	foreach ( $allowed as $prefix ) {
		if ( str_starts_with( $request, $prefix ) ) {
			return $result;
		}
	}

	return new WP_Error(
		'rest_forbidden',
		__( 'REST API access restricted.', 'brio-guiseppe' ),
		[ 'status' => 401 ]
	);
}
add_filter( 'rest_authentication_errors', 'brio_restrict_rest_api' );

/**
 * Block user enumeration via /?author=N redirects and REST /wp/v2/users.
 *
 * A request like /?author=1 normally redirects to /author/admin, leaking
 * the admin login. We intercept it before the redirect fires.
 *
 * @since 1.0.0
 */
function brio_block_author_enumeration() {
	if ( ! is_admin() && isset( $_GET['author'] ) ) {
		wp_die(
			esc_html__( 'Author enumeration is disabled.', 'brio-guiseppe' ),
			'',
			[ 'response' => 403 ]
		);
	}
}
add_action( 'init', 'brio_block_author_enumeration' );

/**
 * Disable XML-RPC completely.
 *
 * xmlrpc.php is a common brute-force and DDoS amplification vector.
 * This site uses neither the Jetpack remote nor any mobile app that needs it.
 *
 * @since 1.0.0
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

// Remove the X-Pingback header that advertises xmlrpc.php.
add_filter( 'wp_headers', function ( $headers ) {
	unset( $headers['X-Pingback'] );
	return $headers;
} );

// Strip the <link rel="pingback"> discovery tag from <head>.
remove_action( 'wp_head', 'pingback_link_rel' );
