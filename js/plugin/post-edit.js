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
		savedChart = false,
		inputEl,
		imgInputEl;

	/**
	 * Set scope var values and build modal element
	 */
	function init() {
		appUrl = WPSimplechartBootstrap.appUrl.toString();
		confirmNoDataMessage = WPSimplechartBootstrap.confirmNoDataMessage.toString();
		closeModalMessage = WPSimplechartBootstrap.closeModalMessage.toString();
		inputEl = document.getElementById( WPSimplechartBootstrap.postmetaKey );
		imgInputEl = document.getElementById( 'simplechart-png-string' );

		window.addEventListener('message', onReceiveMessage );
		$( '#simplechart-clear' ).click( clearInputEl );
		$( '#simplechart-launch' ).click( openModal );

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

		// add lister to close modal now that it's in DOM
		$( '#simplechart-close' ).click( function( e ) {
			e.preventDefault();
			if ( savedChart || confirm( confirmNoDataMessage ) ) {
				hideModal();
				savedChart = false;
			}
		} );
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
	 * Clear hidden fields that store app data after postMessage from child frame
	 */
	function clearInputEl( e ) {
		e.preventDefault();
		inputEl.setAttribute('value', '');
		imgInputEl.setAttribute('value', '');
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
		switch (messageType) {
			case 'appReady':
				sendData();
				break;

			case 'saveData':
				receiveData( evt.data.data );
				break;
		}
	}

	/**
	 * Send previously saved data to child window
	 */
	function sendData() {
		if( WPSimplechartBootstrap.data ) {
			document.getElementById( 'simplechart-frame' ).contentWindow.postMessage({
				data: WPSimplechartBootstrap.data,
				messageType: 'bootstrapAppData'
			}, '*' );
		}
	}

	/**
	 * Receive new data from child window
	 */
	function receiveData( data ) {
		if ( data.previewImg ) {
			imgInputEl.value = data.previewImg;
			delete data.previewImg;
		}

		inputEl.value = JSON.stringify( data );
	}

	// GO GO GO
	init();
}

if ( typeof pagenow !== 'undefined' && pagenow === 'simplechart' ){
	jQuery(document).ready(function(){
		WPSimplechartApp( jQuery )
	});
}
