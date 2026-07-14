<?php
/**
 * Shared body for both archive-portfolio.php (the post type archive)
 * and taxonomy-portfolio_type.php (a single portfolio_type term) —
 * WordPress treats these as two different points in its template
 * hierarchy even though they render the same UI, so the shared markup
 * lives here once instead of being copy-pasted.
 *
 * @package Salon_Barbers
 */

$terms = get_terms( array( 'taxonomy' => 'portfolio_type', 'hide_empty' => true ) );
?>

<?php if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) : ?>
	<nav class="portfolio-filters" aria-label="<?php esc_attr_e( 'فیلتر نمونه کارها', 'salon-barbers' ); ?>">
		<a class="portfolio-filters__link<?php echo ! is_tax( 'portfolio_type' ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_post_type_archive_link( 'salon_portfolio' ) ); ?>">
			<?php esc_html_e( 'همه', 'salon-barbers' ); ?>
		</a>
		<?php foreach ( $terms as $term ) : ?>
			<a class="portfolio-filters__link<?php echo is_tax( 'portfolio_type', $term->term_id ) ? ' is-active' : ''; ?>" href="<?php echo esc_url( get_term_link( $term ) ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</a>
		<?php endforeach; ?>
	</nav>
<?php endif; ?>

<?php if ( have_posts() ) : ?>
	<div class="portfolio-grid">
		<?php
		while ( have_posts() ) :
			the_post();
			get_template_part( 'template-parts/card-portfolio-item' );
		endwhile;
		?>
	</div>
	<?php the_posts_pagination(); ?>
<?php else : ?>
	<p><?php esc_html_e( 'هنوز نمونه‌کاری ثبت نشده است.', 'salon-barbers' ); ?></p>
<?php endif; ?>
