<?php
/**
 * Fallback template — used for the blog posts index (if the site ever
 * publishes articles) and as WordPress's required catch-all. The salon's
 * real pages (home, about, booking, portfolio, shop) all have their own
 * dedicated templates.
 *
 * @package Salon_Barbers
 */

get_header();
?>

<div class="container section">
	<?php if ( have_posts() ) : ?>
		<header class="archive-header">
			<h1 class="archive-header__title"><?php esc_html_e( 'آخرین مطالب', 'salon-barbers' ); ?></h1>
		</header>

		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article <?php post_class( 'post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="post-card__thumb" href="<?php the_permalink(); ?>">
							<?php the_post_thumbnail( 'medium_large' ); ?>
						</a>
					<?php endif; ?>
					<h2 class="post-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<div class="post-card__excerpt"><?php the_excerpt(); ?></div>
				</article>
				<?php
			endwhile;
			?>
		</div>

		<?php the_posts_pagination(); ?>

	<?php else : ?>
		<p><?php esc_html_e( 'مطلبی یافت نشد.', 'salon-barbers' ); ?></p>
	<?php endif; ?>
</div>

<?php
get_footer();
