jQuery(document).ready( function() {
	jQuery(document).on( 'click', '.simplechart-nag .notice-dismiss', function() {
		jQuery.ajax({
			url: ajaxurl,
			data: {
				action: 'simplechart_dismiss_nag'
			}
		});
	});
});
