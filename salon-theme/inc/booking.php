<?php
/**
 * Booking flow.
 *
 * Deliberately does NOT touch payment or card data directly. A booking
 * creates a normal WooCommerce order for the selected service and hands
 * off to WooCommerce's own checkout/payment URL, so whichever payment
 * gateway plugin is installed (e.g. a ZarinPal gateway for WooCommerce)
 * handles the actual charge under its own PCI-relevant safeguards. This
 * file only ever reserves a slot and reacts to the order's status.
 *
 * Single-resource assumption: the salon has one barber/chair, so a slot
 * is either free or taken regardless of which service was picked. If a
 * salon later has multiple barbers, `salon_theme_slot_is_taken()` is the
 * one place that needs to become resource-aware.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * How long a "pending_payment" appointment holds a slot before it's
 * treated as abandoned and the slot becomes bookable again.
 */
const SALON_BOOKING_HOLD_MINUTES = 15;

add_action( 'wp_ajax_salon_get_slots', 'salon_theme_ajax_get_slots' );
add_action( 'wp_ajax_nopriv_salon_get_slots', 'salon_theme_ajax_get_slots' );

add_action( 'wp_ajax_salon_submit_booking', 'salon_theme_ajax_submit_booking' );
add_action( 'wp_ajax_nopriv_salon_submit_booking', 'salon_theme_ajax_submit_booking' );

/**
 * AJAX: given a date + service, return the list of free time slots.
 */
function salon_theme_ajax_get_slots() {
	check_ajax_referer( 'salon_booking_nonce', 'nonce' );

	$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;

	if ( ! $date || ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) || ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'اطلاعات ارسالی نامعتبر است.', 'salon-barbers' ) ), 400 );
	}

	if ( ! salon_theme_is_bookable_product( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'این خدمت قابل رزرو آنلاین نیست.', 'salon-barbers' ) ), 400 );
	}

	try {
		$date_obj = new DateTimeImmutable( $date, wp_timezone() );
	} catch ( Exception $e ) {
		wp_send_json_error( array( 'message' => __( 'تاریخ نامعتبر است.', 'salon-barbers' ) ), 400 );
	}

	if ( $date_obj < new DateTimeImmutable( 'today', wp_timezone() ) ) {
		wp_send_json_success( array( 'slots' => array() ) );
	}

	$weekday_key = strtolower( $date_obj->format( 'l' ) );
	if ( in_array( $weekday_key, salon_theme_get_closed_weekdays(), true ) ) {
		wp_send_json_success( array( 'slots' => array() ) );
	}

	$duration = salon_theme_get_product_duration_minutes( $product_id );
	$slots    = salon_theme_generate_day_slots( $date_obj, $duration );

	$free = array_values(
		array_filter(
			$slots,
			static fn( $time ) => ! salon_theme_slot_is_taken( $date, $time )
		)
	);

	wp_send_json_success( array( 'slots' => $free ) );
}

/**
 * Build the full list of candidate slot start times (HH:MM) for a day,
 * from Customizer opening/closing hours and slot length. If the date is
 * today, slots already in the past are excluded.
 *
 * @return string[]
 */
function salon_theme_generate_day_slots( DateTimeImmutable $date, int $duration_minutes ): array {
	$opening = (int) get_theme_mod( 'salon_opening_hour', 10 );
	$closing = (int) get_theme_mod( 'salon_closing_hour', 21 );
	$step    = max( 5, (int) get_theme_mod( 'salon_slot_minutes', 30 ) );

	$slots = array();
	$cursor = $date->setTime( $opening, 0 );
	$end    = $date->setTime( $closing, 0 );
	$now    = new DateTimeImmutable( 'now', wp_timezone() );

	while ( $cursor->modify( "+{$duration_minutes} minutes" ) <= $end ) {
		if ( $cursor > $now ) {
			$slots[] = $cursor->format( 'H:i' );
		}
		$cursor = $cursor->modify( "+{$step} minutes" );
	}

	return $slots;
}

/**
 * Whether a given date+time is already reserved by a confirmed
 * appointment, or a pending-payment one still inside its hold window.
 */
function salon_theme_slot_is_taken( string $date, string $time ): bool {
	// _salon_created_gmt is stored via current_time( 'mysql', true ), i.e.
	// already in GMT — the cutoff we compare it against must be GMT too,
	// not the site's local timezone, or this silently misfires on any
	// site not set to UTC.
	$hold_cutoff_gmt = gmdate( 'Y-m-d H:i:s', time() - SALON_BOOKING_HOLD_MINUTES * MINUTE_IN_SECONDS );

	$query = new WP_Query(
		array(
			'post_type'      => 'salon_appointment',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => '_salon_date', 'value' => $date ),
				array( 'key' => '_salon_time', 'value' => $time ),
				array(
					'relation' => 'OR',
					array( 'key' => '_salon_status', 'value' => 'confirmed' ),
					array(
						'relation' => 'AND',
						array( 'key' => '_salon_status', 'value' => 'pending_payment' ),
						array( 'key' => '_salon_created_gmt', 'value' => $hold_cutoff_gmt, 'compare' => '>=', 'type' => 'DATETIME' ),
					),
				),
			),
		)
	);

	return $query->have_posts();
}

/**
 * AJAX: create the appointment + WooCommerce order, return the
 * checkout/payment URL for the browser to redirect to.
 */
function salon_theme_ajax_submit_booking() {
	check_ajax_referer( 'salon_booking_nonce', 'nonce' );

	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( array( 'message' => __( 'فروشگاه در دسترس نیست. لطفاً تلفنی تماس بگیرید.', 'salon-barbers' ) ), 500 );
	}

	$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
	$time       = isset( $_POST['time'] ) ? sanitize_text_field( wp_unslash( $_POST['time'] ) ) : '';
	$name       = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
	$phone_raw  = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';

	if ( ! $product_id || ! $date || ! preg_match( '/^\d{2}:\d{2}$/', $time ) || ! $name ) {
		wp_send_json_error( array( 'message' => __( 'همه فیلدها را کامل کنید.', 'salon-barbers' ) ), 400 );
	}

	$phone = salon_theme_normalize_ir_phone( $phone_raw );
	if ( ! $phone ) {
		wp_send_json_error( array( 'message' => __( 'شماره موبایل را درست وارد کنید (مثال: 09121234567)', 'salon-barbers' ) ), 400 );
	}

	if ( ! salon_theme_is_bookable_product( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'این خدمت قابل رزرو آنلاین نیست.', 'salon-barbers' ) ), 400 );
	}

	// Re-check availability right before writing, to close the race
	// between "load the slot list" and "submit the form".
	if ( salon_theme_slot_is_taken( $date, $time ) ) {
		wp_send_json_error( array( 'message' => __( 'این نوبت همین الان توسط شخص دیگری رزرو شد. لطفاً زمان دیگری انتخاب کنید.', 'salon-barbers' ) ), 409 );
	}

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'خدمت انتخاب‌شده یافت نشد.', 'salon-barbers' ) ), 404 );
	}

	// 1) Reserve the slot immediately by writing the appointment record
	//    before creating the order, so a concurrent request sees it.
	$appointment_id = wp_insert_post(
		array(
			'post_type'   => 'salon_appointment',
			'post_status' => 'publish',
			'post_title'  => sprintf( '%1$s — %2$s %3$s', $name, $date, $time ),
		),
		true
	);

	if ( is_wp_error( $appointment_id ) ) {
		wp_send_json_error( array( 'message' => __( 'ثبت نوبت با خطا مواجه شد.', 'salon-barbers' ) ), 500 );
	}

	update_post_meta( $appointment_id, '_salon_date', $date );
	update_post_meta( $appointment_id, '_salon_time', $time );
	update_post_meta( $appointment_id, '_salon_customer_name', $name );
	update_post_meta( $appointment_id, '_salon_customer_phone', $phone );
	update_post_meta( $appointment_id, '_salon_product_id', $product_id );
	update_post_meta( $appointment_id, '_salon_status', 'pending_payment' );
	update_post_meta( $appointment_id, '_salon_created_gmt', current_time( 'mysql', true ) );

	// 2) Create the WooCommerce order that will actually collect payment.
	try {
		$order = wc_create_order();
		$order->add_product( $product, 1 );
		$order->set_billing_first_name( $name );
		$order->set_billing_phone( $phone );
		$order->set_created_via( 'salon_booking' );
		$order->update_meta_data( '_salon_appointment_id', $appointment_id );
		$order->update_meta_data( '_salon_date', $date );
		$order->update_meta_data( '_salon_time', $time );
		$order->calculate_totals();
		$order->update_status( 'pending', __( 'در انتظار پرداخت رزرو نوبت.', 'salon-barbers' ) );
		$order->save();
	} catch ( Exception $e ) {
		wp_update_post( array( 'ID' => $appointment_id, 'post_status' => 'trash' ) );
		wp_send_json_error( array( 'message' => __( 'ایجاد سفارش با خطا مواجه شد. دوباره تلاش کنید.', 'salon-barbers' ) ), 500 );
		return;
	}

	update_post_meta( $appointment_id, '_salon_order_id', $order->get_id() );

	wp_send_json_success(
		array(
			'redirect' => $order->get_checkout_payment_url(),
		)
	);
}

/**
 * Whether a product is flagged as a bookable service (see the checkbox
 * added to Product data → General in inc/woocommerce-hooks.php).
 */
function salon_theme_is_bookable_product( int $product_id ): bool {
	return 'yes' === get_post_meta( $product_id, '_salon_bookable', true );
}

/**
 * Duration in minutes for a bookable product, falling back to the
 * global slot length if the product doesn't override it.
 */
function salon_theme_get_product_duration_minutes( int $product_id ): int {
	$duration = (int) get_post_meta( $product_id, '_salon_duration_minutes', true );
	return $duration > 0 ? $duration : max( 5, (int) get_theme_mod( 'salon_slot_minutes', 30 ) );
}

/**
 * All products currently flagged bookable, for the booking form's
 * service dropdown.
 *
 * @return WC_Product[]
 */
function salon_theme_get_bookable_services(): array {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}

	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_salon_bookable',
			'meta_value'     => 'yes',
		)
	);

	return array_filter( array_map( 'wc_get_product', $ids ) );
}
