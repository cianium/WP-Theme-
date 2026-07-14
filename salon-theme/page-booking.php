<?php
/**
 * Template Name: رزرو نوبت
 * Template Post Type: page
 *
 * @package Salon_Barbers
 */

get_header();
?>

<article class="section booking-page">
	<div class="container booking-page__inner">
		<header class="section__header">
			<p class="section__eyebrow"><?php esc_html_e( 'رزرو آنلاین', 'salon-barbers' ); ?></p>
			<h1 class="section__title"><?php the_title(); ?></h1>
			<?php
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>
		</header>

		<?php get_template_part( 'template-parts/booking-form' ); ?>
	</div>
</article>

<?php
get_footer();
