<?php
/**
 * The booking form itself. All availability + submission logic runs in
 * assets/js/booking.js against the AJAX handlers in inc/booking.php —
 * this template only renders the static shell and a plain <select> of
 * bookable services so it still degrades gracefully with JS disabled
 * (the phone-call fallback below covers that case).
 *
 * @package Salon_Barbers
 */

$services = salon_theme_get_bookable_services();
$preselected = isset( $_GET['service'] ) ? absint( $_GET['service'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only UI convenience, not a state change.
?>

<div class="booking-form-wrap">
	<?php if ( empty( $services ) ) : ?>

		<p class="booking-form__empty">
			<?php esc_html_e( 'در حال حاضر خدمتی برای رزرو آنلاین تعریف نشده است. لطفاً تلفنی با ما تماس بگیرید:', 'salon-barbers' ); ?>
			<?php salon_theme_phone_link(); ?>
		</p>

	<?php else : ?>

		<form class="booking-form" id="booking-form" novalidate>
			<div class="booking-form__field">
				<label for="booking-service"><?php esc_html_e( 'خدمت', 'salon-barbers' ); ?></label>
				<select id="booking-service" name="service" required>
					<option value=""><?php esc_html_e( '— انتخاب کنید —', 'salon-barbers' ); ?></option>
					<?php foreach ( $services as $service ) : ?>
						<option value="<?php echo esc_attr( $service->get_id() ); ?>" <?php selected( $preselected, $service->get_id() ); ?>>
							<?php echo esc_html( $service->get_name() . ' — ' . wp_strip_all_tags( $service->get_price_html() ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="booking-form__field">
				<label for="booking-date"><?php esc_html_e( 'تاریخ', 'salon-barbers' ); ?></label>
				<input type="date" id="booking-date" name="date" required min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>">
			</div>

			<div class="booking-form__field">
				<label for="booking-time"><?php esc_html_e( 'ساعت', 'salon-barbers' ); ?></label>
				<select id="booking-time" name="time" required disabled>
					<option value=""><?php esc_html_e( 'ابتدا خدمت و تاریخ را انتخاب کنید', 'salon-barbers' ); ?></option>
				</select>
			</div>

			<div class="booking-form__field">
				<label for="booking-name"><?php esc_html_e( 'نام و نام خانوادگی', 'salon-barbers' ); ?></label>
				<input type="text" id="booking-name" name="name" required autocomplete="name">
			</div>

			<div class="booking-form__field">
				<label for="booking-phone"><?php esc_html_e( 'شماره موبایل', 'salon-barbers' ); ?></label>
				<input type="tel" id="booking-phone" name="phone" required autocomplete="tel" placeholder="09121234567" dir="ltr">
			</div>

			<p class="booking-form__message" id="booking-form-message" role="alert" hidden></p>

			<button type="submit" class="button button--accent button--large booking-form__submit">
				<?php esc_html_e( 'ادامه و پرداخت', 'salon-barbers' ); ?>
			</button>

			<p class="booking-form__note">
				<?php esc_html_e( 'برای تکمیل رزرو به درگاه پرداخت آنلاین منتقل می‌شوید. بلافاصله پس از پرداخت، نوبت شما پیامک می‌شود.', 'salon-barbers' ); ?>
			</p>
		</form>

	<?php endif; ?>

	<p class="booking-form__phone-fallback">
		<?php esc_html_e( 'ترجیح می‌دهید تلفنی رزرو کنید؟', 'salon-barbers' ); ?>
		<?php salon_theme_phone_link(); ?>
	</p>
</div>
