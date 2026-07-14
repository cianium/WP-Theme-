<?php
/**
 * Single "نمونه کار" (portfolio) item: featured image, an optional
 * extra image gallery, and an optional video (YouTube/Aparat oEmbed,
 * or a direct mp4).
 *
 * @package Salon_Barbers
 */

get_header();

while ( have_posts() ) :
	the_post();

	$gallery_ids = salon_theme_get_portfolio_gallery_ids( get_the_ID() );
	$video_url   = get_post_meta( get_the_ID(), '_salon_video_url', true );
	?>

	<article class="section portfolio-single">
		<div class="container">
			<header class="section__header">
				<p class="section__eyebrow"><?php esc_html_e( 'نمونه کار', 'salon-barbers' ); ?></p>
				<h1 class="section__title"><?php the_title(); ?></h1>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="portfolio-single__cover">
					<?php the_post_thumbnail( 'large' ); ?>
				</div>
			<?php endif; ?>

			<?php if ( get_the_content() ) : ?>
				<div class="portfolio-single__content"><?php the_content(); ?></div>
			<?php endif; ?>

			<?php if ( $video_url ) : ?>
				<div class="portfolio-single__video">
					<?php
					$embed = wp_oembed_get( $video_url );
					if ( $embed ) {
						echo $embed; // phpcs:ignore WordPress.Security.EscapeOutput -- wp_oembed_get() returns sanitized provider markup.
					} elseif ( preg_match( '/\.mp4($|\?)/i', $video_url ) ) {
						printf(
							'<video controls preload="metadata" src="%s"></video>',
							esc_url( $video_url )
						);
					} else {
						printf(
							'<a class="text-link" href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
							esc_url( $video_url ),
							esc_html__( 'مشاهده ویدیو ←', 'salon-barbers' )
						);
					}
					?>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $gallery_ids ) ) : ?>
				<div class="portfolio-single__gallery">
					<?php foreach ( $gallery_ids as $image_id ) : ?>
						<?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'class' => 'portfolio-single__gallery-img', 'loading' => 'lazy' ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="portfolio-single__cta">
				<a class="button button--accent button--large" href="<?php echo esc_url( salon_theme_booking_page_url() ); ?>">
					<?php esc_html_e( 'شبیه این را می‌خواهم — رزرو نوبت', 'salon-barbers' ); ?>
				</a>
			</div>
		</div>
	</article>

	<?php
endwhile;

get_footer();
