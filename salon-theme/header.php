<?php
/**
 * The header markup, shared by every template.
 *
 * @package Salon_Barbers
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'برو به محتوای اصلی', 'salon-barbers' ); ?></a>

<header class="site-header" id="masthead">
	<div class="site-header__inner container">
		<a class="site-branding" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span class="site-branding__name"><?php bloginfo( 'name' ); ?></span>
			<?php endif; ?>
		</a>

		<nav class="primary-navigation" id="primary-navigation" aria-label="<?php esc_attr_e( 'منوی اصلی', 'salon-barbers' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'primary-navigation__list',
					'fallback_cb'    => 'salon_theme_default_menu',
				)
			);
			?>
		</nav>

		<div class="site-header__actions">
			<?php if ( class_exists( 'WooCommerce' ) ) : ?>
				<a class="header-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'سبد خرید', 'salon-barbers' ); ?>">
					<span class="header-cart__count"><?php echo esc_html( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?></span>
					<?php esc_html_e( 'سبد خرید', 'salon-barbers' ); ?>
				</a>
			<?php endif; ?>

			<a class="button button--accent" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
				<?php esc_html_e( 'رزرو نوبت', 'salon-barbers' ); ?>
			</a>

			<button class="nav-toggle" id="nav-toggle" aria-controls="primary-navigation" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e( 'باز و بسته کردن منو', 'salon-barbers' ); ?></span>
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
				<span class="nav-toggle__bar"></span>
			</button>
		</div>
	</div>
</header>

<main id="primary" class="site-main">
