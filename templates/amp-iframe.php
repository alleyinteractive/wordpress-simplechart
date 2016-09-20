<?php
/**
 * Template partial for rendering as amp-iframe
 */

$id = (int) Simplechart::instance()->template->current_id();

// Chart must be published or embedded in a preview
if ( 'simplechart' === get_post_type( $id ) && ( 'publish' === get_post_status( $id ) || is_preview() ) ) : ?>
	<amp-iframe
		width="300"
		height="300"
		sandbox="allow-scripts allow-same-origin"
		layout="responsive"
		frameborder="0"
		src="https://brookings.alley.dev/?simplechart=test-black-group-a"
	>
	</amp-iframe>
<?php endif;
