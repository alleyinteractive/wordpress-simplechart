<?php
/**
 * Template partial for rendering as amp-iframe
 */

$id = (int) Simplechart::instance()->template->current_id();
$url = site_url( '/simplechart/iframe/' . $id . '/', 'https' );
$height = get_post_meta( $id, 'height', true ) ?: 400;

if ( simplechart_can_render( $id ) ) : ?>
	<amp-iframe
		height=<?php echo absint( $height ); ?>
		sandbox="allow-scripts"
		layout="fixed-height"
		frameborder="0"
		src="<?php echo esc_url( $url ); ?>"
	>
		<?php echo apply_filters( 'simplechart_amp_iframe_placeholder', $id, ''); ?>
	</amp-iframe>
<?php endif;
