<?php
/**
 * Salon Barbers theme bootstrap.
 *
 * Every real feature lives in its own file under /inc so this file stays
 * a simple, readable table of contents. Keep it that way — do not add
 * feature code directly here.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Block direct access.
}

define( 'SALON_THEME_VERSION', '1.0.0' );
define( 'SALON_THEME_DIR', get_template_directory() );
define( 'SALON_THEME_URI', get_template_directory_uri() );

/**
 * Required modules. Order matters: setup before enqueue, custom post
 * types before anything that queries them, WooCommerce hooks last
 * since they depend on post types + SMS gateway both being ready.
 */
$salon_theme_modules = array(
	'inc/setup.php',
	'inc/enqueue.php',
	'inc/customizer.php',
	'inc/template-tags.php',
	'inc/custom-post-types.php',
	'inc/sms-gateway.php',
	'inc/booking.php',
	'inc/woocommerce-hooks.php',
	'inc/admin-page.php',
);

foreach ( $salon_theme_modules as $salon_theme_module ) {
	$path = SALON_THEME_DIR . '/' . $salon_theme_module;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}
unset( $salon_theme_modules, $salon_theme_module, $path );
