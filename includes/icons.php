<?php
/**
 * Inline SVG Icon Library
 *
 * Replaces the Font Awesome CDN bundle (80 KB CSS + 150 KB woff2 fonts +
 * 1 render-blocking request + cdnjs preconnect + CSP entry) with a tiny
 * set of inline SVGs covering exactly the icons this theme uses.
 *
 * Each icon is a 24×24 viewBox path so callers can size via CSS
 * (`.icon { width: 1em; height: 1em }` or explicit `font-size`).
 *
 * Usage:
 *   echo brio_icon( 'phone' );
 *   echo brio_icon( 'check-circle', [ 'class' => 'home-foo__icon' ] );
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Catalogue of available icons → SVG path data (24×24 viewBox).
 *
 * Source: Font Awesome 6 Free (CC BY 4.0). Single-path version of each
 * icon we currently render. Add new icons here as needed.
 *
 * @since 1.0.0
 *
 * @return array<string, string> Slug => SVG path "d" attribute value.
 */
function brio_icon_paths() {
	return apply_filters( 'brio_icon_paths', [
		// fa-phone — solid receiver, used in header.php contact block.
		'phone'        => 'M19.4 13.3l-4.3-1.8a.9.9 0 0 0-1.1.3l-1.9 2.3a14.4 14.4 0 0 1-6.9-6.9l2.3-1.9a.9.9 0 0 0 .3-1.1L5.9 0a.9.9 0 0 0-1-.5l-4 .9a.9.9 0 0 0-.7.9c0 12.7 10.3 23 23 23a.9.9 0 0 0 .9-.7l.9-4a.9.9 0 0 0-.5-1Z',
		// fa-pencil — used in Philosophy feature #1.
		'pencil'       => 'M21.5 6.5 17.5 2.5a2 2 0 0 0-2.8 0L3 14.2V21h6.8L21.5 9.3a2 2 0 0 0 0-2.8ZM9 19H5v-4l9-9 4 4-9 9Z',
		// fa-chart-column — used in Philosophy feature #2.
		'chart-column' => 'M3 3v18h18v-2H5V3H3Zm6 13h2v-6H9v6Zm4 0h2V8h-2v8Zm4 0h2V12h-2v4Z',
		// fa-circle-check — used in Philosophy feature #3.
		'check-circle' => 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1.3 14.3-4.4-4.4 1.4-1.4 3 3 6.6-6.6 1.4 1.4-8 8Z',
		// fa-circle — used as bullet in Pricing includes list (8 px size).
		'dot'          => 'M12 4a8 8 0 1 0 0 16 8 8 0 0 0 0-16Z',
		// fa-user — used in Blog post meta (author).
		'user'         => 'M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Zm0 2c-4.4 0-8 2.7-8 6v2h16v-2c0-3.3-3.6-6-8-6Z',
		// fa-calendar — used in Blog post meta (date).
		'calendar'     => 'M7 2v2H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-3V2h-2v2H9V2H7Zm13 8v10H4V10h16Z',
		// fa-map-marker-alt — used in footer contact column.
		'map-marker'   => 'M12 2a8 8 0 0 0-8 8c0 5.4 7 11.5 7.3 11.8a1 1 0 0 0 1.4 0c.3-.3 7.3-6.4 7.3-11.8a8 8 0 0 0-8-8Zm0 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6Z',
		// fa-envelope — used in footer contact column.
		'envelope'     => 'M22 6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6Zm-2 0-8 5-8-5h16Zm0 12H4V8l8 5 8-5v10Z',
		// fa-linkedin-in — used in footer social row.
		'linkedin'     => 'M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2ZM8.3 18.3H5.7v-8h2.6v8Zm-1.3-9.1a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3ZM18.3 18.3h-2.6v-3.9c0-1 0-2.1-1.3-2.1s-1.5 1-1.5 2v4h-2.6v-8h2.5v1.1c.4-.7 1.2-1.3 2.5-1.3 2.7 0 3.1 1.7 3.1 4v4.2Z',
		// fa-chevron-down — used in nav dropdown indicator.
		'chevron-down' => 'M12 16 4 8l1.4-1.4L12 13.2l6.6-6.6L20 8l-8 8Z',
	] );
}

/**
 * Render an inline SVG icon.
 *
 * @since 1.0.0
 *
 * @param string $name  Icon slug (see brio_icon_paths()).
 * @param array  $attrs Optional HTML attributes (class, style, aria-label, …).
 * @return string SVG markup, or empty string if the icon is unknown.
 */
function brio_icon( $name, $attrs = [] ) {
	$paths = brio_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	// Merge defaults — width:1em/height:1em lets callers size via font-size.
	$attrs = array_merge(
		[
			'class'       => 'icon',
			'width'       => '1em',
			'height'      => '1em',
			'fill'        => 'currentColor',
			'aria-hidden' => 'true',
			'focusable'   => 'false',
			'viewBox'     => '0 0 24 24',
		],
		$attrs
	);

	$attr_str = '';
	foreach ( $attrs as $key => $value ) {
		if ( false === $value || null === $value ) {
			continue;
		}
		$attr_str .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	return sprintf(
		'<svg xmlns="http://www.w3.org/2000/svg"%s><path d="%s" /></svg>',
		$attr_str,
		esc_attr( $paths[ $name ] )
	);
}
