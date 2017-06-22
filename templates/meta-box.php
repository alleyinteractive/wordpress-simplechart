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

	/**
	 * Set custom default metadata.
	 *
	 * @param array $default_options Array of metadata. Possible keys are title, caption, credit
	 */
	$default_metadata = apply_filters( 'simplechart_chart_default_metadata', array() );
	$creating_chart = true;
} else {
	$default_options = null;
	$default_metadata = null;
	$creating_chart = false;
}
?>
<a class="button button-primary button-large" id="simplechart-launch" href="#"><?php esc_html_e( 'Launch Simplechart App', 'simplechart' ); ?></a>
<script>
	window.WPSimplechartBootstrap = {
		rawData: <?php echo simplechart_json_encode_meta( 'save-rawData' ); ?>,
		chartData: <?php echo simplechart_json_encode_meta( 'save-chartData' ); ?>,
		chartType: <?php echo simplechart_json_encode_meta( 'save-chartType' ); ?>,
		isNewChart: <?php echo wp_json_encode( $creating_chart ); ?>,
		<?php if ( ! $creating_chart ) : ?>
			chartMetadata: <?php echo simplechart_json_encode_meta( 'save-chartMetadata' ); ?>,
			chartOptions: <?php echo simplechart_json_encode_meta( 'save-chartOptions' ); ?>,
		<?php else : ?>
			chartMetadata: <?php echo wp_json_encode( $default_metadata ?: new stdClass() ); ?>,
			chartOptions: <?php echo wp_json_encode( $default_options ?: new stdClass() ); ?>,
		<?php endif; ?>
		<?php if ( defined( 'SIMPLECHART_GOOGLE_API_KEY' ) ) : ?>
			googleApiKey: <?php echo wp_json_encode( SIMPLECHART_GOOGLE_API_KEY ) ?>,
			googleSheetId: <?php echo simplechart_json_encode_meta( 'save-googleSheetId' ); ?>,
		<?php endif; ?>
	};
	window.WPSimplechartContainer = {
		appUrl: <?php echo wp_json_encode( Simplechart::instance()->get_config( 'web_app_iframe_src' ) ); ?>,
		closeModalMessage: <?php echo wp_json_encode( __( 'Close Modal', 'simplechart' ) ); ?>,
		confirmNoDataMessage:  <?php echo wp_json_encode( __( 'Confirming this message will proceed without saving changes. If you have made changes that you wish to save, cancel this message, proceed to the final step, and click the Save Chart button.', 'simplechart' ) ); ?>
	}
</script>

<?php if ( ! $creating_chart ) : ?>
	<h4 class="simplechart-preview-heading"><?php esc_html_e( 'Preview Embedded Chart', 'simplechart' ); ?></h4>
	<?php simplechart_render_chart( get_the_ID() ); ?>
<?php endif; ?>

<!-- hidden form fields for saving data received from Simplechart app -->
<?php foreach ( Simplechart::instance()->save->meta_field_names as $field ) : ?>
	<input
		id="save-<?php echo esc_attr( $field ); ?>"
		name="save-<?php echo esc_attr( $field ); ?>"
		type="hidden"
		value=""
	/>
<?php endforeach; ?>
<input type="hidden" id="save-height" name="save-height" value="" />
<!-- /Simplechart form fields -->
