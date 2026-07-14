<?php
/**
 * The footer markup, shared by every template.
 *
 * @package Salon_Barbers
 */
?>
</main><!-- #primary -->

<footer class="site-footer" id="colophon">
	<div class="site-footer__fade" aria-hidden="true"></div>
	<div class="container site-footer__grid">

		<div class="site-footer__col">
			<span class="site-branding__name"><?php bloginfo( 'name' ); ?></span>
			<?php $bio = get_theme_mod( 'salon_barber_bio', '' ); ?>
			<?php if ( $bio ) : ?>
				<p class="site-footer__bio"><?php echo wp_kses_post( wp_trim_words( $bio, 24 ) ); ?></p>
			<?php endif; ?>
			<?php salon_theme_social_links(); ?>
		</div>

		<div class="site-footer__col">
			<h3 class="footer-widget__title"><?php esc_html_e( 'تماس', 'salon-barbers' ); ?></h3>
			<?php salon_theme_phone_link(); ?>
			<?php $address = get_theme_mod( 'salon_address', '' ); ?>
			<?php if ( $address ) : ?>
				<p class="site-footer__address"><?php echo esc_html( $address ); ?></p>
			<?php endif; ?>
		</div>

		<div class="site-footer__col">
			<h3 class="footer-widget__title"><?php esc_html_e( 'ساعات کاری', 'salon-barbers' ); ?></h3>
			<?php salon_theme_working_hours_list(); ?>
		</div>

		<?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
			<div class="site-footer__col">
				<?php dynamic_sidebar( 'footer-1' ); ?>
			</div>
		<?php endif; ?>

	</div>

	<div class="site-footer__bottom container">
		<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
