<?php
/**
 * Latest portfolio pieces on the homepage, linking to the full archive.
 *
 * @package Salon_Barbers
 */

$portfolio_query = new WP_Query(
	array(
		'post_type'      => 'salon_portfolio',
		'posts_per_page' => 6,
		'post_status'    => 'publish',
	)
);

if ( ! $portfolio_query->have_posts() ) {
	return;
}

$archive_url = get_post_type_archive_link( 'salon_portfolio' );
?>
<section class="section portfolio-preview">
	<div class="container">
		<div class="section__header-row">
			<div>
				<p class="section__eyebrow"><?php esc_html_e( 'نمونه کارها', 'salon-barbers' ); ?></p>
				<h2 class="section__title"><?php esc_html_e( 'کارهای اخیر', 'salon-barbers' ); ?></h2>
			</div>
			<?php if ( $archive_url ) : ?>
				<a class="text-link" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'همه نمونه کارها ←', 'salon-barbers' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="portfolio-grid">
			<?php
			while ( $portfolio_query->have_posts() ) :
				$portfolio_query->the_post();
				get_template_part( 'template-parts/card-portfolio-item' );
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
</section>
