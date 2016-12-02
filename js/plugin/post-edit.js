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

	// do not update directly!
	var __postShouldPublish = true;

	function setPostShouldPublish( newValue ) {
		__postShouldPublish = newValue;
	}

	function shouldPostPublish() {
		// use !! to always return boolean
		return !! __postShouldPublish;
	}

	/**
	 * True if adding a new chart; false if editing a chart
	 */
	function addingNewChart() {
		return 'post-new.php' === window.location.pathname.split( '/' ).pop();
	}

	/**
	 * Set scope var values and build modal element
	 */
	function init() {
		appUrl = window.WPSimplechartContainer.appUrl.toString();
		confirmNoDataMessage = window.WPSimplechartContainer.confirmNoDataMessage.toString();
		closeModalMessage = window.WPSimplechartContainer.closeModalMessage.toString();
		window.addEventListener( 'message', onReceiveMessage );
		renderModal();
	}

	/**
	 * Renders the app iframe modal in its open state
	 */
	function renderModal() {
		// create modal elements and append to <body>
		modalElements.container = modalElements.container.replace( '{{iframeSrc}}', appUrl);
		modalElements.container = modalElements.container.replace( '{{closeModal}}', closeModalMessage);
		$( 'body' ).append( modalElements.container + modalElements.backdrop );

		// Listen for click to open modal
		$( '#simplechart-launch' ).click( openModal );

		// Open modal if creating new chart
		if ( addingNewChart() ) {
			openModal();
		}
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

		// auto-publish if we are creating a new chart
		if ( addingNewChart() ) {
			publishPost();
		}
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

		// Update post_title field if needed
		var postTitleField = document.querySelector( 'input[name="post_title"]' );
		if ( data.chartMetadata.title ) {
			postTitleField.value = data.chartMetadata.title;
		} else if ( ! postTitleField.value ) {
			addNotice( 'error', 'Please enter a WordPress internal identifier.' );
			setPostShouldPublish( false );
		}
	}

	/**
	 * Add a notification in side the container created during the admin_notices hook
	 * https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
	 *
	 * @param string noticeType Should notice-error, notice-warning, notice-success, or notice-info
	 * @param string message Text-only, no HTML
	 * @return none
	 */
	function addNotice( noticeType, message ) {
		var container = document.getElementById( 'simplechart-admin-notices' );
		if ( ! container ) {
			return;
		}

		var notice = document.createElement( 'div' );
		notice.className = 'notice is-dismissable notice-' + noticeType;
		var content = document.createElement( 'p' );
		content.innerText = message;
		notice.appendChild( content );
		container.appendChild( notice );
	}

	/**
	 * Trigger publishing when data is received for a new post
	 */
	function publishPost() {
		if ( postShouldPublish() ) {
			// make sure publish button exists in case user doesn't have publish capability
			var publishButton = document.getElementById( 'publish' );
			if ( publishButton ) {
				$( publishButton ).click();
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
