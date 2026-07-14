<?php
/**
 * WooCommerce's catch-all template. It intentionally does almost
 * nothing: WooCommerce itself decides which template part to render
 * (shop grid, single product, cart, checkout…) inside
 * woocommerce_content(), and inc/woocommerce-hooks.php has already
 * replaced the default content wrapper with one that matches this
 * theme's header/footer structure.
 *
 * @package Salon_Barbers
 */

get_header();
woocommerce_content();
get_footer();
