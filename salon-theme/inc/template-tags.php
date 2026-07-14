<?php
/**
 * Small, reusable display helpers so templates stay readable and don't
 * repeat esc_*/get_theme_mod boilerplate.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echo the salon's display phone number as a tel: link.
 */
function salon_theme_phone_link() {
	$phone = get_theme_mod( 'salon_phone', '' );
	if ( ! $phone ) {
		return;
	}
	$digits_only = preg_replace( '/[^0-9+]/', '', $phone );
	printf(
		'<a class="phone-link" href="tel:%1$s">%2$s</a>',
		esc_attr( $digits_only ),
		esc_html( $phone )
	);
}

/**
 * Echo social links (Instagram / Telegram / WhatsApp) as a list, only
 * including the ones the owner has actually filled in.
 */
function salon_theme_social_links() {
	$links = array(
		'instagram' => array( 'url' => get_theme_mod( 'salon_instagram', '' ), 'label' => __( 'اینستاگرام', 'salon-barbers' ) ),
		'telegram'  => array( 'url' => get_theme_mod( 'salon_telegram', '' ), 'label' => __( 'تلگرام', 'salon-barbers' ) ),
		'whatsapp'  => array( 'url' => get_theme_mod( 'salon_whatsapp', '' ), 'label' => __( 'واتساپ', 'salon-barbers' ) ),
	);

	$links = array_filter( $links, static fn( $link ) => ! empty( $link['url'] ) );
	if ( empty( $links ) ) {
		return;
	}

	echo '<ul class="social-links">';
	foreach ( $links as $key => $link ) {
		printf(
			'<li class="social-links__item social-links__item--%1$s"><a href="%2$s" target="_blank" rel="noopener noreferrer">%3$s</a></li>',
			esc_attr( $key ),
			esc_url( $link['url'] ),
			esc_html( $link['label'] )
		);
	}
	echo '</ul>';
}

/**
 * Weekday keys (English, lowercase) in the order Persian calendars
 * conventionally read a week: Saturday first.
 *
 * @return array<string,string> key => Persian label
 */
function salon_theme_weekday_labels() {
	return array(
		'saturday'  => 'شنبه',
		'sunday'    => 'یکشنبه',
		'monday'    => 'دوشنبه',
		'tuesday'   => 'سه‌شنبه',
		'wednesday' => 'چهارشنبه',
		'thursday'  => 'پنجشنبه',
		'friday'    => 'جمعه',
	);
}

/**
 * Render the working-hours list, marking closed days.
 */
function salon_theme_working_hours_list() {
	$closed  = salon_theme_get_closed_weekdays();
	$opening = (int) get_theme_mod( 'salon_opening_hour', 10 );
	$closing = (int) get_theme_mod( 'salon_closing_hour', 21 );

	echo '<ul class="working-hours">';
	foreach ( salon_theme_weekday_labels() as $key => $label ) {
		$is_closed = in_array( $key, $closed, true );
		printf(
			'<li class="working-hours__item%1$s"><span class="working-hours__day">%2$s</span><span class="working-hours__time">%3$s</span></li>',
			$is_closed ? ' working-hours__item--closed' : '',
			esc_html( $label ),
			$is_closed
				? esc_html__( 'تعطیل', 'salon-barbers' )
				: esc_html( sprintf( '%1$s تا %2$s', salon_theme_format_hour( $opening ), salon_theme_format_hour( $closing ) ) )
		);
	}
	echo '</ul>';
}

/**
 * Format a 24h integer hour as a short Persian-friendly time string.
 */
function salon_theme_format_hour( int $hour ): string {
	return sprintf( '%02d:00', $hour );
}

/**
 * Truthy check used in templates to decide whether to render the map.
 */
function salon_theme_has_map(): bool {
	return (bool) get_theme_mod( 'salon_map_embed', '' );
}

/**
 * Build a safe Google Maps <iframe> from the Customizer's map URL
 * setting. The Customizer control only accepts a plain URL string
 * (sanitize_text_field), so — unlike a "paste raw embed HTML" field —
 * there is no markup to sanitize: we build every tag ourselves and
 * only ever interpolate the URL through esc_url(). Safe to echo
 * directly, the same way core template tags like get_avatar() return
 * pre-escaped HTML.
 */
function salon_theme_map_embed_html(): string {
	$url = get_theme_mod( 'salon_map_embed', '' );
	if ( ! $url ) {
		return '';
	}

	return sprintf(
		'<iframe class="about-page__map-frame" src="%1$s" width="100%%" height="380" style="border:0;" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="%2$s"></iframe>',
		esc_url( $url ),
		esc_attr__( 'نقشه موقعیت آرایشگاه', 'salon-barbers' )
	);
}

/**
 * URL of whichever page uses page-booking.php, cached per request.
 * Falls back to the homepage if no page has been assigned that
 * template yet (e.g. right after theme activation).
 */
function salon_theme_booking_page_url(): string {
	static $url = null;
	if ( null !== $url ) {
		return $url;
	}

	$pages = get_posts(
		array(
			'post_type'      => 'page',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_key'       => '_wp_page_template',
			'meta_value'     => 'page-booking.php',
			'fields'         => 'ids',
		)
	);

	$url = $pages ? get_permalink( $pages[0] ) : home_url( '/' );
	return $url;
}

/**
 * Fallback menu shown only if no "primary" menu has been assigned yet,
 * so the header never renders empty for a freshly-activated theme.
 */
function salon_theme_default_menu() {
	$items = array(
		home_url( '/' )               => __( 'خانه', 'salon-barbers' ),
		salon_theme_booking_page_url() => __( 'رزرو نوبت', 'salon-barbers' ),
	);

	if ( class_exists( 'WooCommerce' ) ) {
		$items[ wc_get_page_permalink( 'shop' ) ] = __( 'فروشگاه', 'salon-barbers' );
	}

	$portfolio_archive = get_post_type_archive_link( 'salon_portfolio' );
	if ( $portfolio_archive ) {
		$items[ $portfolio_archive ] = __( 'نمونه کارها', 'salon-barbers' );
	}

	echo '<ul class="primary-navigation__list">';
	foreach ( $items as $url => $label ) {
		printf( '<li><a href="%1$s">%2$s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}
