<?php
/**
 * Services grid: every product flagged "bookable" in WooCommerce, shown
 * with its price and a direct link into the booking form.
 *
 * @package Salon_Barbers
 */

$services = salon_theme_get_bookable_services();
if ( empty( $services ) ) {
	return;
}
?>
<section class="section services" id="services">
	<div class="container">
		<p class="section__eyebrow"><?php esc_html_e( 'خدمات', 'salon-barbers' ); ?></p>
		<h2 class="section__title"><?php esc_html_e( 'خدماتی که رزرو می‌کنید', 'salon-barbers' ); ?></h2>

		<div class="services__grid">
			<?php foreach ( $services as $service ) : ?>
				<article class="service-card">
					<h3 class="service-card__title"><?php echo esc_html( $service->get_name() ); ?></h3>
					<p class="service-card__price"><?php echo wp_kses_post( $service->get_price_html() ); ?></p>
					<p class="service-card__duration">
						<?php
						printf(
							/* translators: %d: duration in minutes */
							esc_html__( '%d دقیقه', 'salon-barbers' ),
							salon_theme_get_product_duration_minutes( $service->get_id() )
						);
						?>
					</p>
					<a
						class="button button--outline"
						href="<?php echo esc_url( add_query_arg( 'service', $service->get_id(), salon_theme_booking_page_url() ) ); ?>"
					>
						<?php esc_html_e( 'رزرو این خدمت', 'salon-barbers' ); ?>
					</a>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
