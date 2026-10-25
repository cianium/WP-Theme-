# Salon Barbers — RTL WordPress Theme Prototype

**Status:** public PHP/WordPress theme prototype; not verified as a production-ready booking or payment solution.

This repository contains `salon-theme/`, an RTL barbershop/salon theme with page templates, a portfolio and proposed integrations with WooCommerce for checkout and a separate SMS provider.

## Read the implementation guide

**[Theme documentation, prerequisites and installation](./salon-theme/README.md)**

## Repository layout

- `salon-theme/style.css` — theme header and styles
- `salon-theme/functions.php` — WordPress hooks and theme bootstrap
- `salon-theme/inc/` — supporting integration modules
- `salon-theme/page-booking.php` — booking page template
- `salon-theme/woocommerce.php` — WooCommerce integration template

## Local evaluation

1. Set up a local WordPress test installation with compatible PHP.
2. Copy `salon-theme/` into `wp-content/themes/`.
3. Follow the theme README for required plugins and configuration.
4. Test booking collisions, checkout completion, SMS callbacks, access permissions and mobile RTL behavior before real use.

**Important:** the repository's existing `.github/workflows/laravel.yml` is a Laravel workflow and is **not a valid quality gate for this WordPress theme**; its last run failed. Replace it with PHP syntax checks, WordPress coding standards and WordPress-specific tests.

## Payments and privacy

A theme must not collect raw bank card details. Use supported WooCommerce payment plugins, server-side confirmation and approved SMS credentials. Keep credentials outside Git and perform live integrations only in an isolated staging environment.

## License

No additional license is declared for this project. Clarify redistribution terms and third-party asset licenses before publishing reusable theme packages.
