<?php
/**
 * Brio Guiseppe Theme Functions
 *
 * @package Brio_Guiseppe
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cache-busting toggle for development.
 * Set to true to append a timestamp to asset versions (forces browser reload).
 */
define( 'JU_DEV_MODE', true );

/**
 * Load theme files.
 */
require_once get_theme_file_path( '/includes/theme-data.php' );
require_once get_theme_file_path( '/includes/front/enqueue.php' );
require_once get_theme_file_path( '/includes/setup.php' );
require_once get_theme_file_path( '/includes/custom-nav-walker.php' );
require_once get_theme_file_path( '/includes/widgets.php' );

/**
 * Register theme hooks.
 */
add_action( 'wp_enqueue_scripts', 'ju_enqueue' );
add_action( 'after_setup_theme',  'ju_setup_theme' );
add_action( 'widgets_init',       'ju_widgets' );
