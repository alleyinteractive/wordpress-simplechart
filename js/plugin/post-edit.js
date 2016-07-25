/**
 * Open, close, and communicate with the iframe containing the app
 */

function WPSimplechartApp( $ ) {
	// setup scoped vars
	var appUrl,
		modalElements = {
			container : '<div id="simplechart-modal"><iframe id="simplechart-frame" src="{{iframeSrc}}"></iframe></div>',
			backdrop : '<div id="simplechart-backdrop"></div>'
		},
		confirmNoDataMessage = '',
		closeModalMessage = '',
		savedChart = false;

	/**
	 * Set scope var values and build modal element
	 */
	function init() {
		appUrl = window.WPSimplechartContainer.appUrl.toString();
		confirmNoDataMessage = window.WPSimplechartContainer.confirmNoDataMessage.toString();
		closeModalMessage = window.WPSimplechartContainer.closeModalMessage.toString();
		window.addEventListener( 'message', onReceiveMessage );
		renderOpenModal();
	}

	/**
	 * Renders the app iframe modal in its open state
	 */
	function renderOpenModal() {
		// create modal elements and append to <body>
		modalElements.container = modalElements.container.replace( '{{iframeSrc}}', appUrl);
		modalElements.container = modalElements.container.replace( '{{closeModal}}', closeModalMessage);
		$( 'body' ).append( modalElements.container + modalElements.backdrop );
		$( '#simplechart-launch' ).click( openModal );
	}

	/**
	 * Reopens already-rendered modal
	 */
	function openModal() {
		$( '#simplechart-backdrop, #simplechart-modal' ).show();
	}

	/**
	 * Hide modal
	 */
	function hideModal() {
		$( '#simplechart-backdrop, #simplechart-modal' ).hide();
	}

	/**
	 * Extract messageType string when a postMessage is received
	 */
	function getMessageType( evt ) {
		// confirm same-origin or http(s)://localhost:8080
		if ( evt.origin !== window.location.origin && !/https?:\/\/localhost:8080/.test( evt.origin ) ) {
			return false;
		}

		var messageType;
		try {
			messageType = evt.data.messageType;
		} catch( err ) {
			throw err;
		}

		return messageType;
	}

	/**
	 * adds listeners for specific messageType from child window
	 */
	function onReceiveMessage( evt ) {
		var messageType = getMessageType( evt );
		if ( ! messageType ) {
			return;
		}

		switch( true ) {
			case 'appReady' === messageType:
				sendData();
				break;

			case 'closeApp' === messageType:
				hideModal();
				break;

			case 0 === messageType.indexOf( 'save-' ):
				receiveData( messageType, evt.data.data );
				break;

			default:
				// nothing
		}
	}

	/**
	 * Send previously saved data to child window
	 */
	function sendData() {
		if ( ! window.WPSimplechartBootstrap ) {
			throw new Error( 'Missing window.WPSimplechartBootstrap' );
		}

		var childWindow = document.getElementById( 'simplechart-frame' );
		if ( ! childWindow || ! childWindow.contentWindow ) {
			throw new Error( 'Missing iframe#simplechart-frame' );
		}

		Object.keys( window.WPSimplechartBootstrap ).forEach( function( key ) {
			sendDataKeyMessage( childWindow.contentWindow, key );
		} );
	}

	/**
	 * Send specific value from bootstrap data via postMessage
	 */
	function sendDataKeyMessage( toWindow, key ) {
		if( window.WPSimplechartBootstrap[ key ] ) {

			// Convert JSON string to JSON object
			var toSend;
			try {
				toSend = JSON.parse( window.WPSimplechartBootstrap[ key ] );
			} catch( e ) {
				toSend = window.WPSimplechartBootstrap[ key ];
			}
			toWindow.postMessage( {
				data: toSend,
				messageType: 'bootstrap.' + key
			}, '*' );
		}
	}

	/**
	 * Receive new data from child window and set value of hidden input field
	 */
	function receiveData( messageType, data ) {
		if ( 'string' !== typeof data ) {
			data = JSON.stringify( data );
		}

		document.getElementById( messageType ).value = data;
	}

	// GO GO GO
	init();
}

if ( 'undefined' !== typeof pagenow && 'simplechart' === pagenow ){
	jQuery( document ).ready( function() {
		WPSimplechartApp( jQuery );
	} );
}
