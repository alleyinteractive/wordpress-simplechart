<?php
/**
 * Template for rendering Simplechart embeds
 */

$id = (int) Simplechart::instance()->template->current_id();

/**
 * Allow custom HTTP headers for the front-end API data request, e.g. basic auth on a staging site
 *
 * @param $headers array Defaults to empty array
 * @param int $id Post ID of current chart being rendered
 * @return array
 */
$http_headers = apply_filters( 'simplechart_api_http_headers', array(), $id );

/**
 * Text string to use while chart data is loading.
 * If none is provided, will use the JS app's default: "Loading"
 *
 * @param string|null $placeholder_text
 * @param int $id Post ID of chart being rendered
 * @return string|null
 */
$placeholder = apply_filters( 'simplechart_widget_placeholder_text', null, $id );

/**
 * Use a custom template for the Simplechart widget
 *
 * @param string|null $custom_template Null or string of HTML for template
 * @param int $id Post ID of chart being rendered
 * @return string|null
 */
$custom_template = apply_filters( 'simplechart_widget_template', null, $id );

// Chart must be published or embedded in a preview
if ( 'simplechart' === get_post_type( $id ) && ( 'publish' === get_post_status( $id ) || is_preview() ) ) : ?>
	<figure
		id='simplechart-widget-<?php echo absint( $id ); ?>'
		class='simplechart-widget'
		data-url='<?php echo esc_url( home_url( '/simplechart/api/' . $id . '/' ) ); ?>'
		data-headers='<?php echo wp_json_encode( $http_headers ); ?>'
		<?php if ( $placeholder ) : ?>
			data-placeholder = '<?php echo esc_attr( $placeholder ); ?>'
		<?php endif; ?>
	>
		<?php if ( $custom_template ) : ?>
			<?php echo wp_kses_post( $custom_template ); ?>
		<?php else : ?>
			<p class='simplechart-title'></p>
			<p class='simplechart-subtitle'></p>
			<p class='simplechart-caption'></p>
			<div
				class='simplechart-chart'
				style='<?php echo esc_attr( $this->height_style( $id ) ); ?>'
			></div>
			<p class='simplechart-credit'></p>
		<?php endif; ?>
	</figure>
	<script>
		if ( ! document.getElementById( 'simplechart-widget-js' ) ) {
			window.__simplechart_public_path__ = window.__simplechart_public_path__ ||
				<?php echo wp_json_encode( Simplechart::instance()->get_config( 'webpack_public_path' ) ); ?>;
		}
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3-annotation/2.2.5/d3-annotation.js"></script>
	<script>
		// We need d3 v3 for NVD3.  
		// We need D3 v4 for Annotations.
		window.d3v4 = d3;
	</script>
<?php
endif;

