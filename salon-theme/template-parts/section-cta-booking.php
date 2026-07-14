<?php
/**
 * Closing call-to-action banner on the homepage.
 *
 * @package Salon_Barbers
 */
?>
<section class="cta-banner">
	<div class="container cta-banner__inner">
		<h2 class="cta-banner__title"><?php esc_html_e( 'وقت‌تان را همین حالا رزرو کنید', 'salon-barbers' ); ?></h2>
		<p class="cta-banner__text"><?php esc_html_e( 'رزرو آنلاین با پرداخت امن — بدون تماس، بدون معطلی.', 'salon-barbers' ); ?></p>
		<a class="button button--accent button--large" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
			<?php esc_html_e( 'رزرو نوبت', 'salon-barbers' ); ?>
		</a>
	</div>
</section>
