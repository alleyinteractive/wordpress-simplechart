<?php
/**
 * Template partial for rendering as amp-iframe
 */

$id = (int) Simplechart::instance()->template->current_id();
$url = site_url( '/simplechart/iframe/' . $id . '/', 'https' );
$height = get_post_meta( $id, 'height', true );

if ( simplechart_can_render( $id ) ) : ?>
	<amp-iframe
		height=<?php echo absint( $height ); ?>
		sandbox="allow-scripts"
		layout="fixed-height"
		frameborder="0"
		src="<?php echo esc_url( $url ); ?>"
	>
		<div placeholder>
			<?php do_action( 'simplechart_amp_iframe_placeholder', $id ); ?>
		</div>
	</amp-iframe>
<?php endif;
