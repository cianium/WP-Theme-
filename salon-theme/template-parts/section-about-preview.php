<?php
/**
 * Short bio teaser on the homepage, linking through to the full About
 * page for address + map.
 *
 * @package Salon_Barbers
 */

$bio         = get_theme_mod( 'salon_barber_bio', '' );
$barber_name = get_theme_mod( 'salon_barber_name', '' );

if ( ! $bio && ! $barber_name ) {
	return;
}

$about_page = get_posts(
	array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'meta_key'       => '_wp_page_template',
		'meta_value'     => 'page-about.php',
		'fields'         => 'ids',
	)
);
$about_url = $about_page ? get_permalink( $about_page[0] ) : '';
?>
<section class="section about-preview">
	<div class="container about-preview__inner">
		<div class="about-preview__media">
			<?php
			$avatar_id = get_theme_mod( 'salon_barber_photo_id' );
			if ( $avatar_id ) {
				echo wp_get_attachment_image( $avatar_id, 'medium_large', false, array( 'class' => 'about-preview__photo' ) );
			}
			?>
		</div>
		<div class="about-preview__content">
			<p class="section__eyebrow"><?php esc_html_e( 'درباره ما', 'salon-barbers' ); ?></p>
			<?php if ( $barber_name ) : ?>
				<h2 class="section__title"><?php echo esc_html( $barber_name ); ?></h2>
			<?php endif; ?>
			<?php if ( $bio ) : ?>
				<div class="about-preview__bio"><?php echo wp_kses_post( wpautop( wp_trim_words( $bio, 55 ) ) ); ?></div>
			<?php endif; ?>
			<?php if ( $about_url ) : ?>
				<a class="text-link" href="<?php echo esc_url( $about_url ); ?>"><?php esc_html_e( 'بیشتر بدانید و آدرس ما را ببینید ←', 'salon-barbers' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</section>
