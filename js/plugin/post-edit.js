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

		switch( messageType ) {
			case 'appReady':
				sendData();
				break;

			case 'closeApp':
				hideModal();
				break;

			case 'saveChart':
				hideModal();
				saveChart( evt.data.data );
				break;

			default:
				// nothing
		}
	}

	/**
	 * Send previously saved data to child window
	 */
	function sendData() {
		var childWindow = document.getElementById( 'simplechart-frame' );
		if ( ! childWindow || ! childWindow.contentWindow ) {
			throw new Error( 'Missing iframe#simplechart-frame' );
		}

		childWindow.contentWindow.postMessage( {
			data: parseBootstrapData(),
			messageType: WPSimplechartBootstrap.isNewChart ? 'bootstrap.new' : 'bootstrap.edit'
		}, '*' );
	}

	/**
	 * Build data object to send to chart editor window, parsing stringified JSON as needed
	 */
	function parseBootstrapData() {
		// WPSimplechartBootstrap defined in meta-box.php
		if ( ! window.WPSimplechartBootstrap ) {
			throw new Error( 'Missing window.WPSimplechartBootstrap' );
		}

		return Object.keys( window.WPSimplechartBootstrap ).reduce(function(data, key) {
			var toSend;
			try {
				toSend = JSON.parse( window.WPSimplechartBootstrap[ key ] );
			} catch( e ) {
				toSend = window.WPSimplechartBootstrap[ key ];
			}
			data[ key ] = toSend;
			return data;
		}, {} )
	}

	/**
	 * save individual elements of data from chart editor
	 */
	function saveChart( data ) {
		Object.keys( data ).forEach( function( key ) {
			saveToField( 'save-' + key, data[key] );
		} );
		handleSpecialCases( data );
		publishPost();
	}

	/**
	 * Receive new data from child window and set value of hidden input field
	 */
	function saveToField( fieldId, data ) {
		if ( 'string' !== typeof data ) {
			data = JSON.stringify( data );
		}

		document.getElementById( fieldId ).value = data;
	}

	/**
	 * Handle any special exceptions when receiving data from app
	 */
	function handleSpecialCases( data ) {
		// Save height to its own custom field
		document.getElementById( 'save-height' ).value = data.chartOptions.height;
	}

	/**
	 * Trigger publishing when data is received for a new post
	 */
	function publishPost() {
		if ( 'post-new.php' === window.location.pathname.split( '/' ).pop() ) {
			// make sure publish button exists in case user doesn't have publish capability
			var publishButton = document.getElementById( 'publish' );
			if ( publishButton ) {
				jQuery( publishButton ).click();
			}
		}
	}

	// GO GO GO
	init();
}

if ( 'undefined' !== typeof pagenow && 'simplechart' === pagenow ){
	jQuery( document ).ready( function() {
		WPSimplechartApp( jQuery );
	} );
}
