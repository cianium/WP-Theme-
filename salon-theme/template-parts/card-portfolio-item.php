<?php
/**
 * A single portfolio card. Expects the loop to already be positioned
 * (the_post() called by the parent template).
 *
 * @package Salon_Barbers
 */

$has_video = (bool) get_post_meta( get_the_ID(), '_salon_video_url', true );
?>
<article <?php post_class( 'portfolio-card' ); ?>>
	<a class="portfolio-card__link" href="<?php the_permalink(); ?>">
		<div class="portfolio-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'salon-portfolio-card' ); ?>
			<?php else : ?>
				<div class="portfolio-card__placeholder" aria-hidden="true"></div>
			<?php endif; ?>
			<?php if ( $has_video ) : ?>
				<span class="portfolio-card__video-badge"><?php esc_html_e( 'ویدیو', 'salon-barbers' ); ?></span>
			<?php endif; ?>
		</div>
		<h3 class="portfolio-card__title"><?php the_title(); ?></h3>
	</a>
</article>
