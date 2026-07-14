<?php
/**
 * WooCommerce integration:
 *  - Adds a "bookable service" checkbox + duration field to the product
 *    editor, which is how salon_theme_get_bookable_services() knows
 *    which products belong in the booking form.
 *  - Sends the two booking SMS messages (to the owner, to the customer)
 *    exactly once, when an order backing an appointment is actually paid.
 *  - Frees the slot again if a booking order is cancelled or fails.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* ---------------------------------------------------------------------
 * Content wrapper: replace WooCommerce's default <div id="primary">
 * <main id="main">…</main></div> wrapper with a plain container, since
 * header.php already opens the theme's own <main id="primary"> and
 * footer.php closes it — using both would duplicate that id/element.
 * This is the standard approach for a custom (non-Storefront) theme,
 * per the WooCommerce theme developer handbook.
 * ------------------------------------------------------------------- */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', 'salon_theme_wc_wrapper_start', 10 );
add_action( 'woocommerce_after_main_content', 'salon_theme_wc_wrapper_end', 10 );

function salon_theme_wc_wrapper_start() {
	echo '<div class="container section woocommerce-page-wrap">';
}

function salon_theme_wc_wrapper_end() {
	echo '</div>';
}

/* ---------------------------------------------------------------------
 * Product editor: "bookable service" fields.
 * ------------------------------------------------------------------- */

function salon_theme_product_booking_fields() {
	echo '<div class="options_group">';

	woocommerce_wp_checkbox(
		array(
			'id'          => '_salon_bookable',
			'label'       => __( 'قابل رزرو آنلاین', 'salon-barbers' ),
			'description' => __( 'این محصول به‌عنوان یک «خدمت» در فرم رزرو نوبت نمایش داده شود.', 'salon-barbers' ),
		)
	);

	woocommerce_wp_text_input(
		array(
			'id'                => '_salon_duration_minutes',
			'label'             => __( 'مدت انجام (دقیقه)', 'salon-barbers' ),
			'desc_tip'          => true,
			'description'       => __( 'اگر خالی بماند، طول پیش‌فرض نوبت از تنظیمات ظاهری استفاده می‌شود.', 'salon-barbers' ),
			'type'              => 'number',
			'custom_attributes' => array( 'min' => '5', 'step' => '5' ),
		)
	);

	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'salon_theme_product_booking_fields' );

function salon_theme_save_product_booking_fields( $post_id ) {
	$bookable = isset( $_POST['_salon_bookable'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_salon_bookable', $bookable );

	if ( isset( $_POST['_salon_duration_minutes'] ) ) {
		update_post_meta( $post_id, '_salon_duration_minutes', absint( $_POST['_salon_duration_minutes'] ) );
	}
}
add_action( 'woocommerce_process_product_meta', 'salon_theme_save_product_booking_fields' );

/* ---------------------------------------------------------------------
 * Order lifecycle → appointment status + SMS.
 * ------------------------------------------------------------------- */

/**
 * Fires on both 'processing' and 'completed' because different Iranian
 * gateway plugins land on different statuses after a successful
 * payment. `_salon_sms_sent` guards against sending twice if an order
 * passes through both.
 */
function salon_theme_order_paid( int $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	if ( 'yes' === $order->get_meta( '_salon_sms_sent' ) ) {
		return;
	}

	$appointment_id = (int) $order->get_meta( '_salon_appointment_id' );
	if ( ! $appointment_id ) {
		return; // Not a booking order — a regular shop order, nothing to do.
	}

	update_post_meta( $appointment_id, '_salon_status', 'confirmed' );

	$date         = get_post_meta( $appointment_id, '_salon_date', true );
	$time         = get_post_meta( $appointment_id, '_salon_time', true );
	$name         = get_post_meta( $appointment_id, '_salon_customer_name', true );
	$phone        = get_post_meta( $appointment_id, '_salon_customer_phone', true );
	$product_id   = (int) get_post_meta( $appointment_id, '_salon_product_id', true );
	$service_name = $product_id ? get_the_title( $product_id ) : __( 'خدمت', 'salon-barbers' );

	$owner_phone = get_theme_mod( 'salon_owner_phone', '' );
	if ( $owner_phone ) {
		$owner_message = sprintf(
			/* translators: 1: customer name, 2: service name, 3: date, 4: time, 5: customer phone */
			__( "نوبت جدید ثبت و پرداخت شد.\nمشتری: %1\$s\nخدمت: %2\$s\nتاریخ: %3\$s ساعت %4\$s\nتماس: %5\$s", 'salon-barbers' ),
			$name,
			$service_name,
			$date,
			$time,
			$phone
		);
		salon_theme_send_sms( $owner_phone, $owner_message );
	}

	if ( $phone ) {
		$customer_message = sprintf(
			/* translators: 1: service name, 2: date, 3: time */
			__( "نوبت شما برای %1\$s در تاریخ %2\$s ساعت %3\$s با موفقیت ثبت و پرداخت شد. منتظر شما هستیم.", 'salon-barbers' ),
			$service_name,
			$date,
			$time
		);
		salon_theme_send_sms( $phone, $customer_message );
	}

	$order->update_meta_data( '_salon_sms_sent', 'yes' );
	$order->save();
}
add_action( 'woocommerce_order_status_processing', 'salon_theme_order_paid' );
add_action( 'woocommerce_order_status_completed', 'salon_theme_order_paid' );

/**
 * If a booking order is cancelled, refunded, or fails, release the slot
 * so someone else can take it.
 */
function salon_theme_order_release_slot( int $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$appointment_id = (int) $order->get_meta( '_salon_appointment_id' );
	if ( ! $appointment_id ) {
		return;
	}

	update_post_meta( $appointment_id, '_salon_status', 'cancelled' );
}
add_action( 'woocommerce_order_status_cancelled', 'salon_theme_order_release_slot' );
add_action( 'woocommerce_order_status_failed', 'salon_theme_order_release_slot' );
add_action( 'woocommerce_order_status_refunded', 'salon_theme_order_release_slot' );

/**
 * Keep the shop focused on real retail products in nav/search — the
 * "service" products backing bookings still need their own page (the
 * booking form links to them for price display) but don't need to
 * clutter the general shop grid twice next to the booking flow.
 * Left as an opt-in filter rather than forced, since some salons will
 * want services listed in the shop too.
 */
function salon_theme_maybe_hide_services_from_shop( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! is_shop() ) {
		return;
	}
	if ( ! apply_filters( 'salon_theme_hide_services_from_shop', false ) ) {
		return;
	}
	$meta_query   = (array) $q->get( 'meta_query' );
	$meta_query[] = array(
		'key'     => '_salon_bookable',
		'value'   => 'yes',
		'compare' => '!=',
	);
	$q->set( 'meta_query', $meta_query );
}
add_action( 'pre_get_posts', 'salon_theme_maybe_hide_services_from_shop' );
