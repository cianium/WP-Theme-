<?php
/**
 * Template Name: درباره ما
 * Template Post Type: page
 *
 * Bio + address + map. Assign this template to a Page from the editor's
 * "Page Attributes" panel.
 *
 * @package Salon_Barbers
 */

get_header();

$barber_name = get_theme_mod( 'salon_barber_name', '' );
$bio         = get_theme_mod( 'salon_barber_bio', '' );
$avatar_id   = get_theme_mod( 'salon_barber_photo_id' );
$address     = get_theme_mod( 'salon_address', '' );
?>

<article class="section about-page">
	<div class="container about-page__inner">

		<div class="about-page__media">
			<?php if ( $avatar_id ) : ?>
				<?php echo wp_get_attachment_image( $avatar_id, 'large', false, array( 'class' => 'about-page__photo' ) ); ?>
			<?php endif; ?>
		</div>

		<div class="about-page__content">
			<p class="section__eyebrow"><?php esc_html_e( 'درباره ما', 'salon-barbers' ); ?></p>
			<h1 class="section__title"><?php echo esc_html( $barber_name ? $barber_name : get_the_title() ); ?></h1>

			<?php if ( $bio ) : ?>
				<div class="about-page__bio"><?php echo wp_kses_post( wpautop( $bio ) ); ?></div>
			<?php endif; ?>

			<?php
			// Allow the page's own editor content underneath the Customizer
			// bio, for anything that needs richer formatting than a
			// Customizer textarea allows.
			while ( have_posts() ) :
				the_post();
				the_content();
			endwhile;
			?>

			<div class="about-page__contact-block">
				<?php salon_theme_phone_link(); ?>
				<?php if ( $address ) : ?>
					<p class="about-page__address"><?php echo esc_html( $address ); ?></p>
				<?php endif; ?>
				<?php salon_theme_social_links(); ?>
			</div>
		</div>
	</div>

	<?php if ( salon_theme_has_map() ) : ?>
		<div class="about-page__map">
			<?php echo salon_theme_map_embed_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- pre-escaped, see function docblock. ?>
		</div>
	<?php endif; ?>

	<div class="container">
		<h2 class="section__title section__title--small"><?php esc_html_e( 'ساعات کاری', 'salon-barbers' ); ?></h2>
		<?php salon_theme_working_hours_list(); ?>
	</div>
</article>

<?php
get_footer();
