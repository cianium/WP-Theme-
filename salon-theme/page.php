<?php
/**
 * Fallback for any Page that isn't assigned one of the dedicated
 * templates (about/booking/contact).
 *
 * @package Salon_Barbers
 */

get_header();
?>

<article class="section">
	<div class="container">
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<header class="section__header">
				<h1 class="section__title"><?php the_title(); ?></h1>
			</header>
			<div class="page-content">
				<?php the_content(); ?>
			</div>
			<?php
		endwhile;
		?>
	</div>
</article>

<?php
get_footer();
