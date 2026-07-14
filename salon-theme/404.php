<?php
/**
 * 404 error page.
 *
 * @package Salon_Barbers
 */

get_header();
?>

<div class="section error-404">
	<div class="container error-404__inner">
		<p class="section__eyebrow">۴۰۴</p>
		<h1 class="section__title"><?php esc_html_e( 'این صفحه پیدا نشد', 'salon-barbers' ); ?></h1>
		<p><?php esc_html_e( 'ممکن است آدرس اشتباه باشد یا صفحه جابه‌جا شده باشد.', 'salon-barbers' ); ?></p>

		<div class="error-404__actions">
			<a class="button button--accent" href="<?php echo esc_url( home_url( '/' ) ); ?>">
				<?php esc_html_e( 'بازگشت به خانه', 'salon-barbers' ); ?>
			</a>
			<a class="button button--outline" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
				<?php esc_html_e( 'رزرو نوبت', 'salon-barbers' ); ?>
			</a>
		</div>
	</div>
</div>

<?php
get_footer();
