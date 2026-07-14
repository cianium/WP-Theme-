<?php
/**
 * Homepage hero: the barber/salon's name, a one-line promise, and the
 * two actions everyone lands on this page wanting — book a slot, or see
 * the work first.
 *
 * @package Salon_Barbers
 */

$barber_name = get_theme_mod( 'salon_barber_name', get_bloginfo( 'name' ) );
$tagline     = get_bloginfo( 'description' );
?>
<section class="hero">
	<div class="hero__fade" aria-hidden="true"></div>
	<div class="container hero__inner">
		<p class="hero__eyebrow"><?php esc_html_e( 'آرایشگاه', 'salon-barbers' ); ?></p>
		<h1 class="hero__title"><?php echo esc_html( $barber_name ); ?></h1>
		<?php if ( $tagline ) : ?>
			<p class="hero__tagline"><?php echo esc_html( $tagline ); ?></p>
		<?php endif; ?>

		<div class="hero__actions">
			<a class="button button--accent button--large" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
				<?php esc_html_e( 'رزرو نوبت آنلاین', 'salon-barbers' ); ?>
			</a>
			<?php $portfolio_url = get_post_type_archive_link( 'salon_portfolio' ); ?>
			<?php if ( $portfolio_url ) : ?>
				<a class="button button--ghost button--large" href="<?php echo esc_url( $portfolio_url ); ?>">
					<?php esc_html_e( 'دیدن نمونه کارها', 'salon-barbers' ); ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</section>
