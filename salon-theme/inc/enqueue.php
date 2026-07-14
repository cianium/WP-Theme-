<?php
/**
 * Styles and scripts.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue front-end assets.
 */
function salon_theme_enqueue_assets() {
	// Vazirmatn: a single Persian/Latin variable family covers the whole
	// type scale (weights 100–900), which avoids pairing a Latin display
	// face with a Persian body face — mixed pairings rarely read as
	// intentional in RTL layouts. Self-host this in production; see
	// README for instructions. Loaded with font-display:swap via the
	// stylesheet's @font-face, so no external request is required once
	// self-hosted.
	wp_enqueue_style(
		'salon-fonts',
		SALON_THEME_URI . '/assets/css/fonts.css',
		array(),
		SALON_THEME_VERSION
	);

	wp_enqueue_style(
		'salon-theme-style',
		SALON_THEME_URI . '/assets/css/style.css',
		array( 'salon-fonts' ),
		SALON_THEME_VERSION
	);

	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_style(
			'salon-woocommerce',
			SALON_THEME_URI . '/assets/css/woocommerce.css',
			array( 'salon-theme-style' ),
			SALON_THEME_VERSION
		);
	}

	wp_enqueue_script(
		'salon-main',
		SALON_THEME_URI . '/assets/js/main.js',
		array(),
		SALON_THEME_VERSION,
		true
	);

	if ( is_page_template( 'page-booking.php' ) ) {
		wp_enqueue_script(
			'salon-booking',
			SALON_THEME_URI . '/assets/js/booking.js',
			array(),
			SALON_THEME_VERSION,
			true
		);

		wp_localize_script(
			'salon-booking',
			'salonBooking',
			array(
				'ajaxUrl'      => admin_url( 'admin-ajax.php' ),
				'nonce'        => wp_create_nonce( 'salon_booking_nonce' ),
				'openingHour'  => (int) get_theme_mod( 'salon_opening_hour', 10 ),
				'closingHour'  => (int) get_theme_mod( 'salon_closing_hour', 21 ),
				'slotMinutes'  => (int) get_theme_mod( 'salon_slot_minutes', 30 ),
				'closedDays'   => salon_theme_get_closed_weekdays(),
				'i18n'         => array(
					'selectService'  => __( 'ابتدا یک خدمت را انتخاب کنید', 'salon-barbers' ),
					'noSlots'        => __( 'برای این روز نوبت خالی موجود نیست', 'salon-barbers' ),
					'submitting'     => __( 'در حال ثبت نوبت…', 'salon-barbers' ),
					'genericError'   => __( 'مشکلی پیش آمد. لطفاً دوباره تلاش کنید یا تماس بگیرید.', 'salon-barbers' ),
					'invalidPhone'   => __( 'شماره موبایل را درست وارد کنید (مثال: 09121234567)', 'salon-barbers' ),
				),
			)
		);
	}
}
add_action( 'wp_enqueue_scripts', 'salon_theme_enqueue_assets' );

/**
 * Admin-side polish: nothing heavy, just a note in the customizer/editor
 * that the SMS + payment integrations need real credentials.
 */
function salon_theme_admin_enqueue( $hook ) {
	if ( 'appearance_page_salon-theme-settings' !== $hook ) {
		return;
	}
	wp_enqueue_style( 'salon-admin', SALON_THEME_URI . '/assets/css/admin.css', array(), SALON_THEME_VERSION );
}
add_action( 'admin_enqueue_scripts', 'salon_theme_admin_enqueue' );
