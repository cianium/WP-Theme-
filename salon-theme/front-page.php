<?php
/**
 * The homepage. WordPress uses this automatically once Settings →
 * Reading is set to show a static front page.
 *
 * @package Salon_Barbers
 */

get_header();
?>

<?php get_template_part( 'template-parts/section-hero' ); ?>
<?php get_template_part( 'template-parts/section-services' ); ?>
<?php get_template_part( 'template-parts/section-about-preview' ); ?>
<?php get_template_part( 'template-parts/section-portfolio-preview' ); ?>
<?php get_template_part( 'template-parts/section-cta-booking' ); ?>

<?php
get_footer();
