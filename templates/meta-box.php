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
function simplechart_json_encode_meta( $key, $is_json = true ) {
	$raw_meta = get_post_meta( get_the_ID(), $key, true );

	if ( empty( $raw_meta ) ) {
		return wp_json_encode( null );
	}

	// Fields are saved with esc_textarea() which uses htmlspecialchars(), so we decode here
	$raw_meta = htmlspecialchars_decode( $raw_meta, ENT_QUOTES );

	// Decoding then encoding escapes and validates serialized JSON
	if ( $is_json ) {
		$raw_meta = json_decode( $raw_meta );
	}

	return wp_json_encode( $raw_meta );
}
?>
<a class="button button-primary button-large" id="simplechart-launch" href="#"><?php esc_html_e( 'Launch Simplechart App', 'simplechart' ); ?></a>
<script>
	window.WPSimplechartBootstrap = {
		rawData: <?php echo simplechart_json_encode_meta( 'save-rawData', false ); ?>,
		chartData: <?php echo simplechart_json_encode_meta( 'save-chartData' ); ?>,
		chartMetadata: <?php echo simplechart_json_encode_meta( 'save-chartMetadata' ); ?>,
		chartOptions: <?php echo simplechart_json_encode_meta( 'save-chartOptions' ); ?>
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
