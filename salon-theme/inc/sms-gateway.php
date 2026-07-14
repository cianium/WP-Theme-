<?php
/**
 * SMS gateway.
 *
 * Default implementation targets Kavenegar (the most common Iranian SMS
 * API), configured entirely from Customizer settings. To use a different
 * provider (Melipayamak, Ghasedak, etc.) without touching booking.php,
 * hook the `salon_theme_send_sms` filter — return true/false yourself
 * and this file's default sender will be skipped.
 *
 * IMPORTANT: this requires a real Kavenegar account and API key. Without
 * one, salon_theme_send_sms() safely no-ops and logs the reason instead
 * of throwing — a booking must never fail just because SMS is
 * unconfigured.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Send an SMS. Returns true on a confirmed provider success, false
 * otherwise. Never throws — booking flow must survive SMS failures.
 *
 * @param string $to      Destination phone number, Iranian local format (09xxxxxxxxx).
 * @param string $message Message body.
 */
function salon_theme_send_sms( string $to, string $message ): bool {
	/**
	 * Let a different provider fully take over. Return a bool (not null)
	 * from your callback to short-circuit the built-in Kavenegar sender.
	 */
	$override = apply_filters( 'salon_theme_send_sms', null, $to, $message );
	if ( is_bool( $override ) ) {
		return $override;
	}

	$api_key = get_theme_mod( 'salon_sms_api_key', '' );
	$sender  = get_theme_mod( 'salon_sms_sender_line', '' );

	if ( ! $api_key || ! $sender ) {
		salon_theme_log_sms_failure( $to, 'کلید API یا شماره خط ارسال در تنظیمات وارد نشده است.' );
		return false;
	}

	$to = salon_theme_normalize_ir_phone( $to );
	if ( ! $to ) {
		salon_theme_log_sms_failure( $to, 'شماره موبایل نامعتبر است.' );
		return false;
	}

	$endpoint = sprintf( 'https://api.kavenegar.com/v1/%s/sms/send.json', rawurlencode( $api_key ) );

	$response = wp_remote_post(
		$endpoint,
		array(
			'timeout' => 10,
			'body'    => array(
				'receptor' => $to,
				'sender'   => $sender,
				'message'  => $message,
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		salon_theme_log_sms_failure( $to, $response->get_error_message() );
		return false;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	// Kavenegar returns HTTP 200 with a return.status of 200 on success.
	$provider_ok = ( 200 === $code ) && isset( $body['return']['status'] ) && 200 === (int) $body['return']['status'];

	if ( ! $provider_ok ) {
		$reason = $body['return']['message'] ?? ( 'HTTP ' . $code );
		salon_theme_log_sms_failure( $to, $reason );
		return false;
	}

	return true;
}

/**
 * Normalize a phone number to Kavenegar's expected local format
 * (09xxxxxxxxx). Returns '' if it doesn't look like a valid Iranian
 * mobile number.
 */
function salon_theme_normalize_ir_phone( string $phone ): string {
	$digits = preg_replace( '/\D/', '', $phone );

	if ( str_starts_with( $digits, '0098' ) ) {
		$digits = '0' . substr( $digits, 4 );
	} elseif ( str_starts_with( $digits, '98' ) && 12 === strlen( $digits ) ) {
		$digits = '0' . substr( $digits, 2 );
	} elseif ( str_starts_with( $digits, '9' ) && 10 === strlen( $digits ) ) {
		$digits = '0' . $digits;
	}

	return (bool) preg_match( '/^09\d{9}$/', $digits ) ? $digits : '';
}

/**
 * Log an SMS failure without ever surfacing it to the customer. Visible
 * to admins via WP_DEBUG_LOG, and mirrored to a small option so the
 * owner can see recent failures from wp-admin without server access.
 */
function salon_theme_log_sms_failure( string $to, string $reason ): void {
	$entry = array(
		'time'   => current_time( 'mysql' ),
		'to'     => $to,
		'reason' => $reason,
	);

	if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( sprintf( '[salon-theme SMS] failed to %s: %s', $to, $reason ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
	}

	$log   = get_option( 'salon_theme_sms_failures', array() );
	$log[] = $entry;
	// Keep only the most recent 50 entries so the option can't grow forever.
	$log = array_slice( $log, -50 );
	update_option( 'salon_theme_sms_failures', $log, false );
}
