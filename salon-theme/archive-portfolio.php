<?php
/**
 * Archive of `salon_portfolio` -- the full portfolio gallery.
 *
 * @package Salon_Barbers
 */

get_header();
?>

<div class="section portfolio-archive">
	<div class="container">
		<header class="section__header">
			<p class="section__eyebrow"><?php esc_html_e( 'نمونه کارها', 'salon-barbers' ); ?></p>
			<h1 class="section__title"><?php esc_html_e( 'گالری کارها', 'salon-barbers' ); ?></h1>
		</header>

		<?php get_template_part( 'template-parts/portfolio-archive-content' ); ?>
	</div>
</div>

<?php
get_footer();
