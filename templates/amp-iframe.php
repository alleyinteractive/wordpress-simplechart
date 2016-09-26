<?php
/**
 * Template partial for rendering as amp-iframe
 */

$id = (int) Simplechart::instance()->template->current_id();
$url = site_url( '/simplechart/iframe/' . $id . '/', 'https' );
$height = get_post_meta( $id, 'height', true );

// Chart must be published or embedded in a preview
if ( 'simplechart' === get_post_type( $id ) && ( 'publish' === get_post_status( $id ) || is_preview() ) ) : ?>
	<amp-iframe
		height=<?php echo absint( $height ); ?>
		sandbox="allow-scripts allow-same-origin"
		layout="fixed-height"
		frameborder="0"
		src="<?php echo esc_url( $url ); ?>"
	>
	</amp-iframe>
<?php endif;
