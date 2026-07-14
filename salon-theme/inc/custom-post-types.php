<?php
/**
 * Custom post types:
 *  - salon_portfolio: public, for the "نمونه کار" gallery (photos + video).
 *  - salon_appointment: private, internal record of each booking used to
 *    block double-booked slots. Not meant to be browsed publicly.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function salon_theme_register_post_types() {

	register_post_type(
		'salon_portfolio',
		array(
			'labels'              => array(
				'name'          => __( 'نمونه کارها', 'salon-barbers' ),
				'singular_name' => __( 'نمونه کار', 'salon-barbers' ),
				'add_new_item'  => __( 'افزودن نمونه کار جدید', 'salon-barbers' ),
				'edit_item'     => __( 'ویرایش نمونه کار', 'salon-barbers' ),
				'all_items'     => __( 'همه نمونه کارها', 'salon-barbers' ),
				'menu_name'     => __( 'نمونه کارها', 'salon-barbers' ),
			),
			'public'               => true,
			'has_archive'          => true,
			'rewrite'              => array( 'slug' => 'portfolio' ),
			'menu_icon'            => 'dashicons-camera',
			'supports'             => array( 'title', 'editor', 'thumbnail' ),
			'show_in_rest'         => true,
			'capability_type'      => 'post',
		)
	);

	register_taxonomy(
		'portfolio_type',
		'salon_portfolio',
		array(
			'labels'            => array(
				'name'          => __( 'نوع خدمت', 'salon-barbers' ),
				'singular_name' => __( 'نوع خدمت', 'salon-barbers' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'portfolio-type' ),
			'show_in_rest'      => true,
		)
	);

	register_post_type(
		'salon_appointment',
		array(
			'labels'          => array(
				'name'          => __( 'نوبت‌ها', 'salon-barbers' ),
				'singular_name' => __( 'نوبت', 'salon-barbers' ),
				'all_items'     => __( 'همه نوبت‌ها', 'salon-barbers' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'show_in_menu'    => true,
			'menu_icon'       => 'dashicons-calendar-alt',
			'supports'        => array( 'title' ),
			'capability_type' => 'post',
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'salon_theme_register_post_types' );

/**
 * Make the appointment list table useful at a glance: date, time,
 * customer, service, status, linked order — instead of the default
 * title-only view.
 */
function salon_theme_appointment_columns( $columns ) {
	$new = array(
		'cb'               => $columns['cb'],
		'appointment_date' => __( 'تاریخ', 'salon-barbers' ),
		'appointment_time' => __( 'ساعت', 'salon-barbers' ),
		'customer'         => __( 'مشتری', 'salon-barbers' ),
		'service'          => __( 'خدمت', 'salon-barbers' ),
		'status'           => __( 'وضعیت', 'salon-barbers' ),
		'order'            => __( 'سفارش', 'salon-barbers' ),
	);
	return $new;
}
add_filter( 'manage_salon_appointment_posts_columns', 'salon_theme_appointment_columns' );

function salon_theme_appointment_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'appointment_date':
			echo esc_html( get_post_meta( $post_id, '_salon_date', true ) );
			break;
		case 'appointment_time':
			echo esc_html( get_post_meta( $post_id, '_salon_time', true ) );
			break;
		case 'customer':
			printf(
				'%1$s<br><span dir="ltr">%2$s</span>',
				esc_html( get_post_meta( $post_id, '_salon_customer_name', true ) ),
				esc_html( get_post_meta( $post_id, '_salon_customer_phone', true ) )
			);
			break;
		case 'service':
			$product_id = (int) get_post_meta( $post_id, '_salon_product_id', true );
			echo $product_id ? esc_html( get_the_title( $product_id ) ) : '—';
			break;
		case 'status':
			$status = get_post_meta( $post_id, '_salon_status', true );
			echo esc_html( salon_theme_appointment_status_label( $status ) );
			break;
		case 'order':
			$order_id = (int) get_post_meta( $post_id, '_salon_order_id', true );
			if ( $order_id && function_exists( 'wc_get_order' ) ) {
				printf(
					'<a href="%1$s">#%2$d</a>',
					esc_url( admin_url( 'post.php?post=' . $order_id . '&action=edit' ) ),
					$order_id
				);
			} else {
				echo '—';
			}
			break;
	}
}
add_action( 'manage_salon_appointment_posts_custom_column', 'salon_theme_appointment_column_content', 10, 2 );

/**
 * Human-readable label for an appointment status key.
 */
function salon_theme_appointment_status_label( string $status ): string {
	$labels = array(
		'pending_payment' => __( 'در انتظار پرداخت', 'salon-barbers' ),
		'confirmed'        => __( 'تأیید شده', 'salon-barbers' ),
		'cancelled'        => __( 'لغو شده', 'salon-barbers' ),
	);
	return $labels[ $status ] ?? $status;
}

/* ---------------------------------------------------------------------
 * Portfolio meta box: extra gallery images + optional video embed URL.
 * ------------------------------------------------------------------- */

function salon_theme_add_portfolio_meta_box() {
	add_meta_box(
		'salon_portfolio_media',
		__( 'رسانه‌های تکمیلی (گالری و ویدیو)', 'salon-barbers' ),
		'salon_theme_render_portfolio_meta_box',
		'salon_portfolio',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'salon_theme_add_portfolio_meta_box' );

function salon_theme_render_portfolio_meta_box( $post ) {
	wp_nonce_field( 'salon_portfolio_media_save', 'salon_portfolio_media_nonce' );

	$gallery_ids = get_post_meta( $post->ID, '_salon_gallery_ids', true );
	$video_url   = get_post_meta( $post->ID, '_salon_video_url', true );
	?>
	<p>
		<label for="salon_gallery_ids"><strong><?php esc_html_e( 'شناسه تصاویر گالری (با کاما جدا کنید)', 'salon-barbers' ); ?></strong></label><br>
		<input type="text" class="widefat" id="salon_gallery_ids" name="salon_gallery_ids" value="<?php echo esc_attr( $gallery_ids ); ?>" placeholder="123,124,125">
		<span class="description"><?php esc_html_e( 'شناسه رسانه‌ها را از کتابخانه رسانه (Media Library) بردارید. در نسخه بعدی می‌توان این بخش را به یک انتخاب‌گر تصویری ارتقا داد.', 'salon-barbers' ); ?></span>
	</p>
	<p>
		<label for="salon_video_url"><strong><?php esc_html_e( 'آدرس ویدیو (آپارات، یوتیوب یا لینک مستقیم mp4)', 'salon-barbers' ); ?></strong></label><br>
		<input type="url" class="widefat" id="salon_video_url" name="salon_video_url" value="<?php echo esc_attr( $video_url ); ?>" placeholder="https://www.aparat.com/v/xxxxxxx">
	</p>
	<?php
}

function salon_theme_save_portfolio_meta_box( $post_id ) {
	if ( ! isset( $_POST['salon_portfolio_media_nonce'] ) ||
		! wp_verify_nonce( $_POST['salon_portfolio_media_nonce'], 'salon_portfolio_media_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['salon_gallery_ids'] ) ) {
		$ids = array_filter( array_map( 'absint', explode( ',', wp_unslash( $_POST['salon_gallery_ids'] ) ) ) );
		update_post_meta( $post_id, '_salon_gallery_ids', implode( ',', $ids ) );
	}

	if ( isset( $_POST['salon_video_url'] ) ) {
		update_post_meta( $post_id, '_salon_video_url', esc_url_raw( wp_unslash( $_POST['salon_video_url'] ) ) );
	}
}
add_action( 'save_post_salon_portfolio', 'salon_theme_save_portfolio_meta_box' );

/**
 * Get the portfolio gallery image IDs as an int array.
 *
 * @return int[]
 */
function salon_theme_get_portfolio_gallery_ids( int $post_id ): array {
	$raw = get_post_meta( $post_id, '_salon_gallery_ids', true );
	if ( ! $raw ) {
		return array();
	}
	return array_filter( array_map( 'absint', explode( ',', $raw ) ) );
}
