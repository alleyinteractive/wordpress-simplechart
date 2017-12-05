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
	 * @param array $default_options Array of metadata. Possible keys are title, subtitle, caption, credit
	 */
	$default_metadata = apply_filters( 'simplechart_chart_default_metadata', array() );

	/**
	* Change any set truthy default that isn't a string to an
	* empty string so that we don't get weird default subtitles
	* when creating charts.
	*/
	if ( ! empty( $default_metadata['subtitle'] ) && 'string' !== gettype( $default_metadata['subtitle'] ) ) {
		$default_metadata['subtitle'] = '';
	}

	/**
	 * Enables the subtitle field, which is disabled by default in the chart editor app.
	 * Alternately, you can assign any truthy value to the 'subtitle' key in 'simplechart_chart_default_metadata'
	 *
	 * @param bool $enable_subtitle Whether to enable the subtitle field
	 */
	if ( ! isset( $default_metadata['subtitle'] ) && apply_filters( 'simplechart_enable_subtitle_field', false ) ) {
		$default_metadata['subtitle'] = '';
	}

	$creating_chart = true;
} else {
	$default_options = null;
	$default_metadata = null;
	$creating_chart = false;
}//end if

/**
 * If we're loading an existing chart and subtitles are enabled,
 * pull in the saved subtitle if it exists and add it to the metadata.
 * If the field is empty, just throw in a blank string.
 * If subtitles are disabled, do nothing and allow the existing metadata to go through.
 */
if ( ! $creating_chart && apply_filters( 'simplechart_enable_subtitle_field', false ) ) {
	$existing_subtitle = get_post_meta( get_the_ID(), 'save-chartSubtitle', true );
	if ( empty( $existing_subtitle ) ) {
		$existing_subtitle = '';
	}
	$loaded_metadata = json_decode( get_post_meta( get_the_ID(), 'save-chartMetadata', true ), true );
	if ( ! isset( $loaded_metadata['subtitle'] ) ) {
		$loaded_metadata['subtitle'] = $existing_subtitle;
	}
} else {
	$loaded_metadata = null;
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
			chartMetadata: <?php echo ! empty( $loaded_metadata ) ? wp_json_encode( $loaded_metadata ) : simplechart_json_encode_meta( 'save-chartMetadata' ); ?>,
			chartOptions: <?php echo simplechart_json_encode_meta( 'save-chartOptions' ); ?>,
		<?php else : ?>
			chartMetadata: <?php echo wp_json_encode( $default_metadata ?: '{}' ); ?>,
			chartOptions: <?php echo wp_json_encode( $default_options ?: '{}' ); ?>,
		<?php endif; ?>
		<?php if ( defined( 'SIMPLECHART_GOOGLE_API_KEY' ) ) : ?>
			googleApiKey: <?php echo wp_json_encode( SIMPLECHART_GOOGLE_API_KEY ); ?>,
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
		<?php if ( ! $creating_chart && 'chartSubtitle' === $field ) : ?>
			<?php /* Prevents subtitle from disappearing if post is resaved without launching JS app */ ?>
			value="<?php echo esc_attr( $existing_subtitle ); ?>"
		<?php else : ?>
			value=""
		<?php endif; ?>
	/>
<?php endforeach; ?>
<input type="hidden" id="save-height" name="save-height" value="" />
<!-- /Simplechart form fields -->
