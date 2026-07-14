<?php
/**
 * Customizer: everything a salon owner needs to edit without touching
 * code — contact info, bio, working hours, and the SMS/booking
 * credentials the booking flow depends on.
 *
 * @package Salon_Barbers
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function salon_theme_customize_register( WP_Customize_Manager $wp_customize ) {

	/* ---------------------------------------------------------------
	 * Panel: Salon info
	 * ------------------------------------------------------------- */
	$wp_customize->add_panel(
		'salon_info_panel',
		array(
			'title'    => __( 'اطلاعات آرایشگاه', 'salon-barbers' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_section(
		'salon_contact_section',
		array(
			'title' => __( 'تماس و آدرس', 'salon-barbers' ),
			'panel' => 'salon_info_panel',
		)
	);

	$contact_fields = array(
		'salon_phone'        => array( 'label' => __( 'شماره تماس نمایشی', 'salon-barbers' ), 'default' => '' ),
		'salon_whatsapp'     => array( 'label' => __( 'شماره واتساپ (اختیاری)', 'salon-barbers' ), 'default' => '' ),
		'salon_address'      => array( 'label' => __( 'آدرس کامل', 'salon-barbers' ), 'default' => '', 'type' => 'textarea' ),
		'salon_map_embed'    => array( 'label' => __( 'کد Embed نقشه گوگل (iframe src)', 'salon-barbers' ), 'default' => '' ),
		'salon_instagram'    => array( 'label' => __( 'آدرس اینستاگرام', 'salon-barbers' ), 'default' => '' ),
		'salon_telegram'     => array( 'label' => __( 'آدرس تلگرام', 'salon-barbers' ), 'default' => '' ),
	);

	foreach ( $contact_fields as $id => $field ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $field['default'],
				'sanitize_callback' => 'sanitize_text_field',
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $field['label'],
				'section' => 'salon_contact_section',
				'type'    => isset( $field['type'] ) ? $field['type'] : 'text',
			)
		);
	}

	$wp_customize->add_section(
		'salon_bio_section',
		array(
			'title' => __( 'درباره ما', 'salon-barbers' ),
			'panel' => 'salon_info_panel',
		)
	);

	$wp_customize->add_setting(
		'salon_barber_name',
		array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		)
	);
	$wp_customize->add_control(
		'salon_barber_name',
		array(
			'label'   => __( 'نام آرایشگر / سالن', 'salon-barbers' ),
			'section' => 'salon_bio_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting(
		'salon_barber_bio',
		array(
			'default'           => '',
			'sanitize_callback' => 'wp_kses_post',
		)
	);
	$wp_customize->add_control(
		'salon_barber_bio',
		array(
			'label'   => __( 'بیوگرافی', 'salon-barbers' ),
			'section' => 'salon_bio_section',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'salon_barber_photo_id',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'salon_barber_photo_id',
			array(
				'label'    => __( 'عکس آرایشگر / سالن', 'salon-barbers' ),
				'section'  => 'salon_bio_section',
				'mime_type' => 'image',
			)
		)
	);

	/* ---------------------------------------------------------------
	 * Section: Working hours (drives the booking slot generator)
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'salon_hours_section',
		array(
			'title' => __( 'ساعات کاری و نوبت‌دهی', 'salon-barbers' ),
			'panel' => 'salon_info_panel',
		)
	);

	$wp_customize->add_setting( 'salon_opening_hour', array( 'default' => 10, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		'salon_opening_hour',
		array(
			'label'       => __( 'ساعت شروع کار (۰ تا ۲۳)', 'salon-barbers' ),
			'section'     => 'salon_hours_section',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 23 ),
		)
	);

	$wp_customize->add_setting( 'salon_closing_hour', array( 'default' => 21, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		'salon_closing_hour',
		array(
			'label'       => __( 'ساعت پایان کار (۰ تا ۲۳)', 'salon-barbers' ),
			'section'     => 'salon_hours_section',
			'type'        => 'number',
			'input_attrs' => array( 'min' => 0, 'max' => 23 ),
		)
	);

	$wp_customize->add_setting( 'salon_slot_minutes', array( 'default' => 30, 'sanitize_callback' => 'absint' ) );
	$wp_customize->add_control(
		'salon_slot_minutes',
		array(
			'label'   => __( 'طول هر نوبت (دقیقه)', 'salon-barbers' ),
			'section' => 'salon_hours_section',
			'type'    => 'select',
			'choices' => array( 15 => '۱۵', 20 => '۲۰', 30 => '۳۰', 45 => '۴۵', 60 => '۶۰' ),
		)
	);

	$wp_customize->add_setting(
		'salon_closed_days',
		array(
			'default'           => array( 'friday' ),
			'sanitize_callback' => 'salon_theme_sanitize_closed_days',
		)
	);
	$wp_customize->add_control(
		new Salon_Multicheck_Control(
			$wp_customize,
			'salon_closed_days',
			array(
				'label'   => __( 'روزهای تعطیل هفتگی', 'salon-barbers' ),
				'section' => 'salon_hours_section',
				'choices' => array(
					'saturday'  => __( 'شنبه', 'salon-barbers' ),
					'sunday'    => __( 'یکشنبه', 'salon-barbers' ),
					'monday'    => __( 'دوشنبه', 'salon-barbers' ),
					'tuesday'   => __( 'سه‌شنبه', 'salon-barbers' ),
					'wednesday' => __( 'چهارشنبه', 'salon-barbers' ),
					'thursday'  => __( 'پنجشنبه', 'salon-barbers' ),
					'friday'    => __( 'جمعه', 'salon-barbers' ),
				),
			)
		)
	);

	/* ---------------------------------------------------------------
	 * Section: SMS gateway credentials
	 * ------------------------------------------------------------- */
	$wp_customize->add_section(
		'salon_sms_section',
		array(
			'title'       => __( 'پیامک (اطلاع‌رسانی نوبت)', 'salon-barbers' ),
			'panel'       => 'salon_info_panel',
			'description' => __( 'برای فعال شدن پیامک باید در یکی از سرویس‌دهنده‌های پیامک (مثل کاوه‌نگار، ملی‌پیامک یا قاصدک) ثبت‌نام کنید و کلید API را اینجا وارد کنید. بدون این کلید، پیامک ارسال نمی‌شود و فقط در گزارش خطا ثبت می‌گردد.', 'salon-barbers' ),
		)
	);

	$wp_customize->add_setting( 'salon_sms_api_key', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'salon_sms_api_key',
		array(
			'label'   => __( 'کلید API کاوه‌نگار', 'salon-barbers' ),
			'section' => 'salon_sms_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting( 'salon_sms_sender_line', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'salon_sms_sender_line',
		array(
			'label'   => __( 'شماره خط ارسال پیامک', 'salon-barbers' ),
			'section' => 'salon_sms_section',
			'type'    => 'text',
		)
	);

	$wp_customize->add_setting( 'salon_owner_phone', array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
	$wp_customize->add_control(
		'salon_owner_phone',
		array(
			'label'       => __( 'موبایل آرایشگر (مقصد پیامک نوبت جدید)', 'salon-barbers' ),
			'section'     => 'salon_sms_section',
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'salon_theme_customize_register' );

/**
 * Sanitize the closed-days multicheck: only accept known weekday keys.
 */
function salon_theme_sanitize_closed_days( $value ) {
	$valid = array( 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday' );
	if ( ! is_array( $value ) ) {
		return array();
	}
	return array_values( array_intersect( $value, $valid ) );
}

/**
 * Helper used by inc/enqueue.php and inc/booking.php.
 *
 * @return string[] Lowercase English weekday keys, e.g. ['friday'].
 */
function salon_theme_get_closed_weekdays() {
	$days = get_theme_mod( 'salon_closed_days', array( 'friday' ) );
	return is_array( $days ) ? $days : array();
}

/**
 * Minimal multicheck Customizer control (WordPress core has no built-in
 * checkbox-group control). Kept intentionally small — this is not a
 * general-purpose control library, just enough for the closed-days field.
 *
 * WP_Customize_Control only exists when the Customizer manager has been
 * bootstrapped (customize.php, the preview iframe, or a customize_save
 * request) — NOT on a normal front-end page load. Declaring a class
 * that extends it unconditionally would fatal-error every regular
 * visit, since this file is required on every request via
 * functions.php. Guarding with class_exists() is the standard WordPress
 * pattern for this.
 */
if ( class_exists( 'WP_Customize_Control' ) ) {
	class Salon_Multicheck_Control extends WP_Customize_Control {
		public $type = 'salon_multicheck';

		public function render_content() {
			if ( empty( $this->choices ) ) {
				return;
			}
			$values = (array) $this->value();
			?>
			<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<div class="salon-multicheck" data-setting-id="<?php echo esc_attr( $this->id ); ?>">
				<?php foreach ( $this->choices as $key => $label ) : ?>
					<label style="display:block;margin-bottom:4px;">
						<input type="checkbox" class="salon-multicheck__input" value="<?php echo esc_attr( $key ); ?>" <?php checked( in_array( $key, $values, true ) ); ?> />
						<?php echo esc_html( $label ); ?>
					</label>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
}

/**
 * The multicheck control above has no native way to aggregate several
 * checkboxes into one array-valued setting — WordPress core's
 * data-customize-setting-link only binds a single scalar value, so we
 * bind it ourselves with a small script in the Customizer controls pane.
 */
function salon_theme_customize_controls_enqueue() {
	wp_enqueue_script(
		'salon-customizer-controls',
		SALON_THEME_URI . '/assets/js/customizer-controls.js',
		array( 'jquery', 'customize-controls' ),
		SALON_THEME_VERSION,
		true
	);
}
add_action( 'customize_controls_enqueue_scripts', 'salon_theme_customize_controls_enqueue' );
