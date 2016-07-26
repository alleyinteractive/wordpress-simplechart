<?php
/**
 * Meta box for Simplechart post type
 */

/**
 * Get JSON from post meta and escape it
 *
 * @param string $key Post meta key
 * @param bool $is_json Defaults to true, indicates field is serialized JSON
 * @return string JSON string
 */
function simplechart_json_encode_meta( $key ) {
	$raw_meta = get_post_meta( get_the_ID(), $key, true );
	if ( empty( $raw_meta ) ) {
		return wp_json_encode( null );
	}
	return wp_json_encode( $raw_meta );
}


// Apply custom options when creating a new chart or saved options when editing an existing chart
$screen = get_current_screen();
if ( 'simplechart' === $screen->id && 'add' === $screen->action ) {
	/**
	 * Set custom default options for NVD3
	 *
	 * @param array $default_options Array of NVD3 options to pre-set
	 */
	$default_options = apply_filters( 'simplechart_chart_options_override', array() );
	$creating_chart = true;
} else {
	$default_options = null;
	$creating_chart = false;
}
?>
<a class="button button-primary button-large" id="simplechart-launch" href="#"><?php esc_html_e( 'Launch Simplechart App', 'simplechart' ); ?></a>
<script>
	window.WPSimplechartBootstrap = {
		rawData: <?php echo simplechart_json_encode_meta( 'save-rawData' ); ?>,
		chartData: <?php echo simplechart_json_encode_meta( 'save-chartData' ); ?>,
		chartMetadata: <?php echo simplechart_json_encode_meta( 'save-chartMetadata' ); ?>,
		<?php if ( ! $creating_chart ) : ?>
			chartOptions: <?php echo simplechart_json_encode_meta( 'save-chartOptions' ); ?>,
		<?php else : ?>
			chartOptions: <?php echo wp_json_encode( $default_options ); ?>
		<?php endif; ?>
	};
	window.WPSimplechartContainer = {
		appUrl: <?php echo wp_json_encode( Simplechart::instance()->get_config( 'web_app_iframe_src' ) ); ?>,
		closeModalMessage: <?php echo wp_json_encode( __( 'Close Modal', 'simplechart' ) ); ?>,
		confirmNoDataMessage:  <?php echo wp_json_encode( __( 'Confirming this message will proceed without saving changes. If you have made changes that you wish to save, cancel this message, proceed to the final step, and click the Save Chart button.', 'simplechart' ) ); ?>
	}
</script>
<input
	type="hidden"
	id="simplechart-nonce"
	name="simplechart-nonce"
	value="<?php echo esc_attr( wp_create_nonce( 'simplechart_save' ) ); ?>"
/>
<?php foreach ( Simplechart::instance()->save->meta_field_names as $field ) :?>
	<input type="hidden" id="save-<?php echo esc_attr( $field ); ?>" name="save-<?php echo esc_attr( $field ); ?>" value="" />
<?php endforeach; ?>
