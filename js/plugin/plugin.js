jQuery( document ).ready(function() {
	const simplechartInput = jQuery('input[value="wordpress-simplechart/simplechart.php"]');
	if (simplechartInput.length) {
		simplechartInput.attr('disabled', true).css('cursor', 'default');
	}
});
