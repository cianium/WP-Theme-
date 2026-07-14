<?php
/**
 * Core theme setup: supports, menus, widget areas, image sizes.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme support and navigation menus.
 */
function salon_theme_setup() {
	// Translation ready, even though the primary audience is Persian.
	load_theme_textdomain( 'salon-barbers', SALON_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'customize-selective-refresh-widgets' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support(
		'automatic-feed-links'
	);

	// WooCommerce: declare support and opt in to product gallery features
	// rather than overriding its templates, which keeps us compatible
	// with WooCommerce core updates.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی', 'salon-barbers' ),
			'footer'  => __( 'منوی فوتر', 'salon-barbers' ),
		)
	);

	// Portfolio cover image: wide crop suited to before/after and studio shots.
	add_image_size( 'salon-portfolio-card', 640, 800, true );
	add_image_size( 'salon-hero', 1600, 900, true );
}
add_action( 'after_setup_theme', 'salon_theme_setup' );

/**
 * Register the footer widget area (address / hours / social links block).
 */
function salon_theme_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'فوتر', 'salon-barbers' ),
			'id'            => 'footer-1',
			'description'   => __( 'ابزارک‌های ستون فوتر سایت.', 'salon-barbers' ),
			'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h3 class="footer-widget__title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'salon_theme_widgets_init' );

/**
 * Limit excerpt length so archive/portfolio cards stay visually even.
 */
function salon_theme_excerpt_length( $length ) {
	return 20;
}
add_filter( 'excerpt_length', 'salon_theme_excerpt_length' );

function salon_theme_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'salon_theme_excerpt_more' );
