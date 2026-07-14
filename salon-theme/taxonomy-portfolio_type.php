<?php
/**
 * Archive for a single portfolio_type term (e.g. "کوتاهی مردانه",
 * "رنگ مو"). Shares markup with archive-portfolio.php via
 * template-parts/portfolio-archive-content.php.
 *
 * @package Salon_Barbers
 */

get_header();

$term = get_queried_object();
?>

<div class="section portfolio-archive">
	<div class="container">
		<header class="section__header">
			<p class="section__eyebrow"><?php esc_html_e( 'نمونه کارها', 'salon-barbers' ); ?></p>
			<h1 class="section__title"><?php echo esc_html( $term->name ); ?></h1>
			<?php if ( $term->description ) : ?>
				<p class="section__description"><?php echo esc_html( $term->description ); ?></p>
			<?php endif; ?>
		</header>

		<?php get_template_part( 'template-parts/portfolio-archive-content' ); ?>
	</div>
</div>

<?php
get_footer();
