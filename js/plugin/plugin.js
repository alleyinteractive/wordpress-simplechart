jQuery( document ).ready(function() {
	if (jQuery('input[value="wordpress-simplechart/simplechart.php"]').length) {
		jQuery(
			'input[value="wordpress-simplechart/simplechart.php"]'
		)[0].disabled = true;
		jQuery(
			'input[value="wordpress-simplechart/simplechart.php"]'
		).css('cursor', 'default');
	}
});
