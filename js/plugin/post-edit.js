/**
 * Open, close, and communicate with the iframe containing the app
 */

function WPSimplechartApp( $ ) {
	// setup scoped vars
	var appUrl,
		modalElements = {
			container : '<div id="simplechart-modal"><a id="simplechart-close" href="#">{{closeModal}}</a><iframe id="simplechart-frame" src="{{iframeSrc}}"></iframe></div>',
			backdrop : '<div id="simplechart-backdrop"></div>'
		},
		chartData = null,
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
		window.addEventListener('message', onReceiveMessage );
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

		// add listenters to open/close modal now that it's in DOM
		$( '#simplechart-close' ).click( function( e ) {
			e.preventDefault();
			if ( savedChart || confirm( confirmNoDataMessage ) ) {
				hideModal();
				savedChart = false;
			}
		} );
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
	function getMessageType(evt) {
		// confirm same-origin or http(s)://localhost:8080
		if ( evt.origin !== window.location.origin && !/https?:\/\/localhost:8080/.test(evt.origin )) {
			return false;
		}

		try {
			var messageType = evt.data.messageType;
		} catch(err) {
			throw err;
		}

		return messageType;
	}

	/**
	 * adds listeners for specific messageType from child window
	 */
	function onReceiveMessage(evt) {
		var messageType = getMessageType( evt );
		if ( ! messageType ) {
			return;
		}

		if ( 'appReady' === messageType ) {
			sendData();
		} else if ( 0 === messageType.indexOf( 'save-' ) ) {
			receiveData( messageType, evt.data.data );
		}
	}

	/**
	 * Send previously saved data to child window
	 */
	function sendData() {
		if( window.WPSimplechartBootstrap.rawData ) {
			document.getElementById( 'simplechart-frame' ).contentWindow.postMessage({
				data: window.WPSimplechartBootstrap.rawData,
				messageType: 'bootstrap.rawData'
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
		}

		document.getElementById( messageType ).value = data;
	}

	// GO GO GO
	init();
}

if ( typeof pagenow !== 'undefined' && pagenow === 'simplechart' ){
	jQuery(document).ready(function(){
		WPSimplechartApp( jQuery )
	});
}
