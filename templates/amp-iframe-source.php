<?php
/**
 * Template partial for source inside amp-iframe
 */

$id = (int) Simplechart::instance()->request_handler->frame_id();
if ( $id ) : ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<?php
		/**
		 * Add any additional styles, JS, or other markup here
		 *
		 * @var int $id ID of chart being rendered
		 */
		do_action( 'simplechart_iframe_head', $id );
		?>
	</head>
	<body>
		<?php
		// Make extra sure that we render the non-AMP embed inside the amp-iframe
		add_filter( 'simplechart_is_amp_page', '__return_false', 99 );
		Simplechart::instance()->template->render( $id );
		remove_filter( 'simplechart_is_amp_page', '__return_false', 99 );
		?>
		<?php
		/**
		 * Add any stuff before closing `</body>` tag
		 *
		 * @var int $id ID of chart being rendered
		 */
		do_action( 'simplechart_iframe_footer', $id );
		?>
	</body>
</html>
<?php
endif;
