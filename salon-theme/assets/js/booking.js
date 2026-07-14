/**
 * Booking form behavior: loads free time slots for the chosen service +
 * date, then submits the booking and redirects to WooCommerce's
 * checkout/payment URL for the resulting order.
 *
 * Depends on the `salonBooking` object localized in inc/enqueue.php.
 */
( function () {
	'use strict';

	if ( typeof window.salonBooking === 'undefined' ) {
		return;
	}

	var config = window.salonBooking;

	document.addEventListener( 'DOMContentLoaded', function () {
		var form = document.getElementById( 'booking-form' );
		if ( ! form ) {
			return;
		}

		var serviceField = document.getElementById( 'booking-service' );
		var dateField = document.getElementById( 'booking-date' );
		var timeField = document.getElementById( 'booking-time' );
		var messageEl = document.getElementById( 'booking-form-message' );
		var submitBtn = form.querySelector( '.booking-form__submit' );

		serviceField.addEventListener( 'change', maybeLoadSlots );
		dateField.addEventListener( 'change', maybeLoadSlots );
		form.addEventListener( 'submit', handleSubmit );

		function maybeLoadSlots() {
			var serviceId = serviceField.value;
			var date = dateField.value;

			resetTimeField();

			if ( ! serviceId || ! date ) {
				return;
			}

			var weekday = getWeekdayKey( date );
			if ( config.closedDays.indexOf( weekday ) !== -1 ) {
				setTimeFieldMessage( config.i18n.noSlots );
				return;
			}

			timeField.disabled = true;
			timeField.innerHTML = '<option value="">' + escapeHtml( config.i18n.submitting ) + '</option>';

			postForm( 'salon_get_slots', { product_id: serviceId, date: date } )
				.then( function ( response ) {
					if ( ! response.success || ! response.data.slots || ! response.data.slots.length ) {
						setTimeFieldMessage( config.i18n.noSlots );
						return;
					}
					populateTimeField( response.data.slots );
				} )
				.catch( function () {
					setTimeFieldMessage( config.i18n.genericError );
				} );
		}

		function resetTimeField() {
			timeField.disabled = true;
			timeField.innerHTML = '<option value="">' + escapeHtml( config.i18n.selectService ) + '</option>';
		}

		function setTimeFieldMessage( text ) {
			timeField.disabled = true;
			timeField.innerHTML = '<option value="">' + escapeHtml( text ) + '</option>';
		}

		function populateTimeField( slots ) {
			var html = '<option value="">' + escapeHtml( '— انتخاب کنید —' ) + '</option>';
			slots.forEach( function ( slot ) {
				html += '<option value="' + escapeHtml( slot ) + '">' + escapeHtml( toPersianDigits( slot ) ) + '</option>';
			} );
			timeField.innerHTML = html;
			timeField.disabled = false;
		}

		function handleSubmit( event ) {
			event.preventDefault();
			hideMessage();

			var phone = form.phone.value.trim();
			if ( ! /^0?9\d{9}$/.test( phone.replace( /\s/g, '' ) ) ) {
				showMessage( config.i18n.invalidPhone, true );
				return;
			}

			if ( ! form.service.value || ! form.date.value || ! form.time.value || ! form.name.value.trim() ) {
				showMessage( config.i18n.genericError, true );
				return;
			}

			setSubmitting( true );

			postForm( 'salon_submit_booking', {
				product_id: form.service.value,
				date: form.date.value,
				time: form.time.value,
				name: form.name.value.trim(),
				phone: phone,
			} )
				.then( function ( response ) {
					if ( response.success && response.data.redirect ) {
						window.location.href = response.data.redirect;
						return;
					}
					setSubmitting( false );
					showMessage( ( response.data && response.data.message ) || config.i18n.genericError, true );
				} )
				.catch( function () {
					setSubmitting( false );
					showMessage( config.i18n.genericError, true );
				} );
		}

		function setSubmitting( isSubmitting ) {
			submitBtn.disabled = isSubmitting;
			submitBtn.setAttribute( 'aria-busy', isSubmitting ? 'true' : 'false' );
		}

		function showMessage( text, isError ) {
			messageEl.textContent = text;
			messageEl.hidden = false;
			messageEl.classList.toggle( 'is-error', !! isError );
		}

		function hideMessage() {
			messageEl.hidden = true;
			messageEl.textContent = '';
		}

		function postForm( action, data ) {
			var body = new URLSearchParams( Object.assign( { action: action, nonce: config.nonce }, data ) );
			return fetch( config.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: body.toString(),
			} ).then( function ( res ) {
				return res.json();
			} );
		}

		function getWeekdayKey( isoDate ) {
			var days = [ 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday' ];
			var parts = isoDate.split( '-' ).map( Number );
			var d = new Date( parts[ 0 ], parts[ 1 ] - 1, parts[ 2 ] );
			return days[ d.getDay() ];
		}

		function toPersianDigits( str ) {
			var persian = [ '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ];
			return String( str ).replace( /[0-9]/g, function ( digit ) {
				return persian[ Number( digit ) ];
			} );
		}

		function escapeHtml( str ) {
			var div = document.createElement( 'div' );
			div.textContent = str;
			return div.innerHTML;
		}
	} );
} )();
