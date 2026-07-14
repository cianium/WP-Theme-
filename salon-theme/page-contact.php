<?php
/**
 * Template Name: تماس با ما
 * Template Post Type: page
 *
 * @package Salon_Barbers
 */

get_header();
?>

<article class="section contact-page">
	<div class="container contact-page__inner">

		<div class="contact-page__details">
			<p class="section__eyebrow"><?php esc_html_e( 'تماس با ما', 'salon-barbers' ); ?></p>
			<h1 class="section__title"><?php the_title(); ?></h1>

			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>

			<ul class="contact-list">
				<?php if ( get_theme_mod( 'salon_phone', '' ) ) : ?>
					<li><span class="contact-list__label"><?php esc_html_e( 'تلفن', 'salon-barbers' ); ?></span><?php salon_theme_phone_link(); ?></li>
				<?php endif; ?>
				<?php $address = get_theme_mod( 'salon_address', '' ); ?>
				<?php if ( $address ) : ?>
					<li><span class="contact-list__label"><?php esc_html_e( 'آدرس', 'salon-barbers' ); ?></span><?php echo esc_html( $address ); ?></li>
				<?php endif; ?>
			</ul>

			<h2 class="section__title section__title--small"><?php esc_html_e( 'ساعات کاری', 'salon-barbers' ); ?></h2>
			<?php salon_theme_working_hours_list(); ?>

			<?php salon_theme_social_links(); ?>

			<a class="button button--accent" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
				<?php esc_html_e( 'رزرو نوبت آنلاین', 'salon-barbers' ); ?>
			</a>
		</div>

		<?php if ( salon_theme_has_map() ) : ?>
			<div class="contact-page__map">
				<?php echo salon_theme_map_embed_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- pre-escaped, see function docblock. ?>
			</div>
		<?php endif; ?>

	</div>
</article>

<?php
get_footer();
