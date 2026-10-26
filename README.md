<div align="center">

# Salon Barbers

**An RTL WordPress theme prototype for barbershops and salons**

**WordPress · PHP · WooCommerce · Persian / RTL**

[![WordPress Theme CI](https://github.com/cianium/WP-Theme-/actions/workflows/wordpress-ci.yml/badge.svg)](https://github.com/cianium/WP-Theme-/actions/workflows/wordpress-ci.yml)
![Status](https://img.shields.io/badge/Status-Prototype-F2B544)
![Theme version](https://img.shields.io/badge/Theme-1.0.0-2271B1)
![GitHub Release](https://img.shields.io/badge/GitHub%20Release-Not%20published-6B7280)

</div>

> **Release status:** This is a public source prototype, **not a verified production booking/payment system**. The theme header declares version `1.0.0`, but the repository currently has **no GitHub Releases**. The most recent GitHub Actions PHP syntax check passed; WordPress/WooCommerce runtime, concurrency, payment-provider and SMS integrations have not been independently validated.

## Overview

Salon Barbers is a custom, mobile-oriented **right-to-left WordPress theme** for a single-chair barbershop or salon. It provides page templates, a service/booking interface, a portfolio content type, WooCommerce-oriented commerce hooks, and a configurable SMS integration.

The application code is contained in **[`salon-theme/`](salon-theme/)**. The root directory is a repository wrapper, **not** a directly installable WordPress theme folder.

## Features in the codebase

| Area | Included implementation | Important limitation |
| :-- | :-- | :-- |
| **Persian / RTL website** | Home, About, Contact, Booking, portfolio templates and navigation | Verify on an actual WordPress installation and multiple device sizes |
| **Portfolio** | Dedicated custom post type, gallery metadata and optional video links | Gallery media selection currently relies on manual media IDs |
| **Services & products** | WooCommerce product fields for bookable services and durations | WooCommerce must be installed and configured |
| **Appointment flow** | Date/slot selection, pending reservation and WooCommerce order creation | **Single barber/chair model**; concurrency and payment flows require testing |
| **Payments** | WooCommerce checkout handoff and order-status hooks | **No built-in bank gateway**; use a trusted, compatible WooCommerce gateway plugin |
| **SMS notifications** | Kavenegar-based sender and an override filter | Requires real provider credentials; success/delivery must be tested |
| **Administration** | WordPress Customizer settings and a setup/status page | Not a standalone SaaS/admin backend |

These are source-level features, not certification of a live business deployment.

## Requirements

- **WordPress 6.4+** and **PHP 8.0+**, as declared in the theme header (not a verified compatibility matrix).
- **WooCommerce** for the intended services, checkout and storefront workflows.
- A maintained WooCommerce **payment gateway plugin** for any real transactions.
- A configured SMS service such as **Kavenegar** if notifications are needed.

## Installation in a staging WordPress site

1. Create an isolated WordPress installation with test users and no real customer data.
2. Copy **only** the `salon-theme/` folder to `wp-content/themes/salon-theme/`, or package that folder as a theme ZIP.
3. Activate **Salon Barbers** under **Appearance → Themes**.
4. Install WooCommerce and configure test products/services.
5. Configure business hours, contact details and optional provider credentials using the theme Customizer.
6. Create the Booking, About and Contact pages and assign the appropriate page templates.
7. Run end-to-end tests on checkout, rejected/duplicate payments, slot collisions and SMS failure behavior before any public launch.

See the detailed **[Persian setup and booking documentation](salon-theme/README.md)** for theme-specific steps and supported options.

## Repository layout

```text
WP-Theme-/
├── README.md                    Repository overview and release status
├── .github/workflows/
│   └── wordpress-ci.yml         PHP syntax and theme-structure checks
└── salon-theme/                 WordPress theme folder
    ├── style.css                WordPress theme metadata (version 1.0.0)
    ├── functions.php             Theme module bootstrap
    ├── front-page.php            Home page
    ├── page-booking.php          Appointment page
    ├── woocommerce.php           WooCommerce template wrapper
    ├── inc/
    │   ├── booking.php          Slots and order creation
    │   ├── woocommerce-hooks.php Order status / booking integration
    │   ├── sms-gateway.php      SMS provider integration
    │   └── ...                  Customizer, setup and display helpers
    ├── template-parts/           Reusable theme components
    └── assets/                   CSS, JavaScript and fonts
```

## Quality and release gates

[**WordPress Theme CI**](https://github.com/cianium/WP-Theme-/actions/workflows/wordpress-ci.yml) currently checks required theme files and validates PHP syntax with PHP 8.2. This is valuable **static validation only**: it does not install WordPress, exercise WooCommerce, run booking concurrency tests, or prove that a payment or SMS was delivered.

Before publishing a GitHub Release or deploying for a real business:

- Confirm compatibility with the actual WordPress, WooCommerce and PHP versions.
- Test nonce validation, permissions and booking slot collisions, including simultaneous submissions.
- Verify payment completion/cancellation/refund transitions and avoid duplicate notifications.
- Verify provider error handling and safe storage of SMS/payment credentials.
- Test keyboard and screen-reader navigation, Persian RTL/mobile layouts and performance.
- Prepare an installable theme ZIP, a screenshot, a changelog and an exact tagged commit.

**Release history:** There are currently **no GitHub Releases**. The `Version: 1.0.0` value in `salon-theme/style.css` is a theme metadata field, not a published or tested release.

## Licensing and attribution

The theme header in [`salon-theme/style.css`](salon-theme/style.css) **declares “GNU General Public License v2 or later”** and attributes its starting point to **Underscores / Automattic**. However, there is **no standalone LICENSE file** in the checked repository tree, and the theme's `License URI: LICENSE` currently points to a missing file. Before redistribution, add the appropriate license text and review the licenses of fonts, icons and other assets. Do not infer third-party asset rights from the theme header alone.

## Maintainer

Developed by [Kian Khayat](https://github.com/cianium). For setup details, limitations and extension points, start with the [theme README (فارسی)](salon-theme/README.md).

---

<div align="center">

**Salon Barbers — a WordPress theme prototype with a clear path to production hardening.**

</div>
