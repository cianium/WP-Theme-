<?php
/**
 * A single admin screen (Appearance → وضعیت راه‌اندازی) that answers the
 * one question a non-technical salon owner actually has: "is booking +
 * payment + SMS actually working?" — plus the last few SMS failures so
 * a broken API key doesn't fail silently forever.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function salon_theme_add_settings_page() {
	add_theme_page(
		__( 'وضعیت راه‌اندازی', 'salon-barbers' ),
		__( 'وضعیت راه‌اندازی', 'salon-barbers' ),
		'manage_options',
		'salon-theme-settings',
		'salon_theme_render_settings_page'
	);
}
add_action( 'admin_menu', 'salon_theme_add_settings_page' );

function salon_theme_render_settings_page() {
	$checks = salon_theme_setup_checks();
	$log    = array_reverse( get_option( 'salon_theme_sms_failures', array() ) );
	?>
	<div class="wrap salon-setup-status">
		<h1><?php esc_html_e( 'وضعیت راه‌اندازی سایت', 'salon-barbers' ); ?></h1>

		<table class="widefat striped" style="max-width:720px;margin-top:16px;">
			<tbody>
			<?php foreach ( $checks as $check ) : ?>
				<tr>
					<td style="width:28px;">
						<?php echo $check['ok'] ? '✅' : '⚠️'; ?>
					</td>
					<td>
						<strong><?php echo esc_html( $check['label'] ); ?></strong><br>
						<span class="description"><?php echo wp_kses_post( $check['description'] ); ?></span>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<h2 style="margin-top:32px;"><?php esc_html_e( 'خطاهای اخیر ارسال پیامک', 'salon-barbers' ); ?></h2>
		<?php if ( empty( $log ) ) : ?>
			<p><?php esc_html_e( 'در حال حاضر خطایی ثبت نشده است.', 'salon-barbers' ); ?></p>
		<?php else : ?>
			<table class="widefat striped" style="max-width:720px;">
				<thead>
				<tr>
					<th><?php esc_html_e( 'زمان', 'salon-barbers' ); ?></th>
					<th><?php esc_html_e( 'گیرنده', 'salon-barbers' ); ?></th>
					<th><?php esc_html_e( 'دلیل خطا', 'salon-barbers' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( array_slice( $log, 0, 20 ) as $entry ) : ?>
					<tr>
						<td><?php echo esc_html( $entry['time'] ); ?></td>
						<td dir="ltr"><?php echo esc_html( $entry['to'] ); ?></td>
						<td><?php echo esc_html( $entry['reason'] ); ?></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Everything that has to be true for the booking → payment → SMS chain
 * to actually work end to end.
 *
 * @return array<int, array{label:string, description:string, ok:bool}>
 */
function salon_theme_setup_checks(): array {
	$checks   = array();
	$checks[] = array(
		'label'       => __( 'ووکامرس فعال است', 'salon-barbers' ),
		'description' => __( 'بدون افزونه ووکامرس، فروشگاه و پرداخت رزرو کار نمی‌کند.', 'salon-barbers' ),
		'ok'          => class_exists( 'WooCommerce' ),
	);

	$has_gateway = class_exists( 'WooCommerce' ) && ! empty( WC()->payment_gateways()->get_available_payment_gateways() );
	$checks[]    = array(
		'label'       => __( 'حداقل یک درگاه پرداخت فعال است', 'salon-barbers' ),
		'description' => __( 'یک افزونه درگاه پرداخت ایرانی (مثلاً زرین‌پال برای ووکامرس) نصب و در تنظیمات ووکامرس → پرداخت فعال کنید.', 'salon-barbers' ),
		'ok'          => $has_gateway,
	);

	$checks[] = array(
		'label'       => __( 'حداقل یک خدمت قابل رزرو تعریف شده', 'salon-barbers' ),
		'description' => __( 'در صفحه ویرایش هر محصول، گزینه «قابل رزرو آنلاین» را فعال کنید.', 'salon-barbers' ),
		'ok'          => ! empty( salon_theme_get_bookable_services() ),
	);

	$checks[] = array(
		'label'       => __( 'کلید API پیامک تنظیم شده', 'salon-barbers' ),
		'description' => __( 'شخصی‌سازی → اطلاعات آرایشگاه → پیامک.', 'salon-barbers' ),
		'ok'          => (bool) get_theme_mod( 'salon_sms_api_key', '' ),
	);

	$checks[] = array(
		'label'       => __( 'موبایل آرایشگر برای دریافت پیامک نوبت تنظیم شده', 'salon-barbers' ),
		'description' => __( 'شخصی‌سازی → اطلاعات آرایشگاه → پیامک.', 'salon-barbers' ),
		'ok'          => (bool) get_theme_mod( 'salon_owner_phone', '' ),
	);

	$checks[] = array(
		'label'       => __( 'صفحه رزرو نوبت ساخته شده', 'salon-barbers' ),
		'description' => __( 'یک صفحه بسازید و از پنل ویژگی‌های صفحه، قالب «رزرو نوبت» را انتخاب کنید.', 'salon-barbers' ),
		'ok'          => (bool) get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'meta_key'       => '_wp_page_template',
				'meta_value'     => 'page-booking.php',
				'fields'         => 'ids',
			)
		),
	);

	return $checks;
}
