/**
 * On the plugins page, make sure we don't accidentally
 * try to update Simplechart as if it was hosted on WP.org
 */
jQuery( document ).ready( function( $ ) {
	if ( 'plugins' !== window.pagenow ) {
		return;
	}

	var simplechartInput = $( 'input[value="wordpress-simplechart/simplechart.php"]' );
	var disabledForBulkActions = false;

	/**
	 * Uncheck and disable Simplechart for Bulk Actions on Plugins page
	 */
	function _disableForBulkActions() {
		if ( simplechartInput.length ) {
			simplechartInput.attr( { disabled: true, checked: false } ).css( 'cursor', 'default' );
			disabledForBulkActions = true;
		}
	}

	/**
	 * Enable Simplechart for Bulk Actions on Plugins page
	 *
	 * @param bool checked Whether it should be checked
	 */
	function _enableForBulkActions( checked ) {
		if ( simplechartInput.length ) {
			simplechartInput.attr( { disabled: false, checked: checked } ).css( 'cursor', 'pointer' );
			disabledForBulkActions = false;
		}
	}

	if ( simplechartInput.length ) {

		// Make sure Simplechart is unchecked if Update is selected as Bulk Action
		$( '.bulkactions [name^="action"]' ).on( 'change', function( evt ) {
			if ( 'update-selected' === $( evt.target ).val() ) {
				// If Update is selected, uncheck and disable
				_disableForBulkActions()
			} else if ( disabledForBulkActions ) {
				// If changing from Update to another Bulk Action,
				// un-disable and fallback to "check all" checkbox value
				var allChecked = 'undefined' !== typeof $( '#cb-select-all-1' ).attr( 'checked' );
				_enableForBulkActions( allChecked );
			}
		} );

		// Make extra sure Simplechart is unchecked when applying bulk action to Update
		$( '.bulkactions [type="submit"]' ).on( 'click', function( evt ) {
			var select = $( evt.target ).siblings( 'select' ).first();
			if ( select.length && 'update-selected' === select.val() ) {
				_disableForBulkActions();
			}
		} );
	}
});
