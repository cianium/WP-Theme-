/**
 * Binds the "salon-multicheck" checkbox groups (currently just the
 * closed-weekdays control) to their Customizer setting as an array.
 *
 * WordPress core's data-customize-setting-link only supports a single
 * scalar value per input, so a group of checkboxes representing one
 * array-valued setting needs this small amount of manual wiring.
 */
( function ( wp, jQuery ) {
	'use strict';

	if ( ! wp || ! wp.customize || ! jQuery ) {
		return;
	}

	wp.customize.bind( 'ready', function () {
		jQuery( '.salon-multicheck' ).each( function () {
			var $group = jQuery( this );
			var settingId = $group.data( 'setting-id' );
			if ( ! settingId ) {
				return;
			}

			var setting = wp.customize( settingId );
			if ( ! setting ) {
				return;
			}

			$group.on( 'change', '.salon-multicheck__input', function () {
				var checked = [];
				$group.find( '.salon-multicheck__input:checked' ).each( function () {
					checked.push( jQuery( this ).val() );
				} );
				setting.set( checked );
			} );
		} );
	} );
} )( window.wp, window.jQuery );
