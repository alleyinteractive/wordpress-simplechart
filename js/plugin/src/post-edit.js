/* eslint-disable */
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
				sendToApp(
					WPSimplechartBootstrap.isNewChart ? 'bootstrap.new' : 'bootstrap.edit',
					parseBootstrapData()
				);
				break;

			case 'closeApp':
				hideModal();
				break;

			case 'saveChart':
				saveChart( evt.data.data );
				break;

			default:
				// nothing
		}
	}

	/**
	 * Send previously saved data to child window
	 */
	function sendToApp( messageType, messageData ) {
		var childWindow = document.getElementById( 'simplechart-frame' );
		if ( ! childWindow || ! childWindow.contentWindow ) {
			throw new Error( 'Missing iframe#simplechart-frame' );
		}

		childWindow.contentWindow.postMessage( {
			messageType: messageType,
			data: messageData
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
			// If subtitle is set, rip it out and save it separately
			if ('chartMetadata' === key) {
				if ('undefined' !== typeof data[key].subtitle) {
					saveToField('save-chartSubtitle', data[key].subtitle);
					delete data[key].subtitle;
				} else {
					saveToField('save-chartSubtitle', false);
				}
			}
			saveToField( 'save-' + key, data[key] );
		} );

		// Save height to its own custom field
		document.getElementById( 'save-height' ).value = data.chartOptions.height;

		setPostTitleField( data );

		// auto-publish if we are creating a new chart
		if ( addingNewChart() ) {
			publishPost();
		} else {
			updateWidget( data );
			hideModal();
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
	function setPostTitleField( data ) {
		// Update post_title field if needed
		var postTitleField = document.querySelector( 'input[name="post_title"]' );
		if ( data.chartMetadata.title ) {
			postTitleField.value = data.chartMetadata.title;
			// hides placeholder text
			document.getElementById( 'title-prompt-text' ).className = 'screen-reader-text';
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
		if ( shouldPostPublish() ) {
			// make extra super sure publish button exists as expected
			var publishButton = document.querySelector( '#simplechart-save input#publish' );
			if ( publishButton ) {
				$( publishButton ).click();
				sendToApp( 'cms.isSaving', null );
			} else {
				hideModal();
				addNotice( 'success', 'Your chart is ready! Click Publish to continue.' );
			}
		} else {
			// Only hide the modal if publishing is blocked for some reason,
			// e.g. a missing post_title
			hideModal();
		}
	}

	/**
	 * Trigger an update on the embedded chart widget
	 *
	 * @param obj data Data received from app
	 */
	function updateWidget( data ) {
		var widgetUpdate = new CustomEvent( 'widgetData', {
			detail: {
				data: data.chartData,
				options: data.chartOptions,
				metadata: data.chartMetadata
			}
		} );
		document.getElementById( getWidgetId() ).dispatchEvent( widgetUpdate );
	}

	/**
	 * Get expected widget ID
	 */
	function getWidgetId() {
		var postId = /post=(\d+)/.exec(window.location.search)[1];
		return 'simplechart-widget-' + postId;
	}

	// GO GO GO
	init();
}

if ( 'undefined' !== typeof pagenow && 'simplechart' === pagenow ){
	jQuery( document ).ready( function() {
		WPSimplechartApp( jQuery );
	} );
}
