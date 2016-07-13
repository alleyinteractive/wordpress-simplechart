<?php
/**
 * Meta box for Simplechart post type
 */

/**
 * Get JSON from post meta and validate by decoding then re-encoding
 *
 * @param string $key Post meta key
 * @return string JSON string
 */
function simplechart_get_validated_json_meta( $key ) {
	$raw_meta = get_post_meta( get_the_ID(), $key, true );
	return wp_json_encode( json_decode( $raw_meta ) );
}

$raw_data = get_post_meta( get_the_ID(), 'save-rawData', true );
$png_string = get_post_meta( get_the_ID(), 'save-previewImg', true );
$chart_data = simplechart_get_validated_json_meta( 'save-chartData' );
$chart_options = simplechart_get_validated_json_meta( 'save-chartOptions' );
$chart_metadata = simplechart_get_validated_json_meta( 'save-charMetadata' );
?>
<a class="button button-primary button-large" id="simplechart-launch" href="#"><?php esc_html_e( 'Launch Simplechart App', 'simplechart' ); ?></a>
<script>
	window.WPSimplechartBootstrap = {
		rawData: <?php echo wp_json_encode( $raw_data ); ?>,
		chartData: <?php echo $chart_data; // already used wp_json_encode() ?>,
		chartMetadata: <?php echo $chart_options; // already used wp_json_encode() ?>,
		chartOptions: <?php echo $chart_metadata; // already used wp_json_encode() ?>
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
