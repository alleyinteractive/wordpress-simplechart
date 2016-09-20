<?php
/**
 * Template for rendering Simplechart embeds
 */

$id = (int) Simplechart::instance()->template->current_id();

// Make sure we have a valid chart
if ( 'simplechart' !== get_post_type( $id ) ) {
	return '';
}

// Only allow non-published charts unless we're looking at a post preview
if ( 'publish' !== get_post_status( $id ) && ! is_preview() ) {
	return '';
}

/**
 * Allow custom HTTP headers for the front-end API data request, e.g. basic auth on a staging site
 *
 * @param $headers array Defaults to empty array
 * @param int $id Post ID of current chart being rendered
 * @return array
 */
$http_headers = apply_filters( 'simplechart_api_http_headers', array(), $id );

?>
	<figure
		id='simplechart-widget-<?php echo absint( $id ); ?>'
		class='simplechart-widget'
		data-url='<?php echo esc_url( home_url( '/simplechart/api/' . $id . '/' ) ); ?>'
		data-headers='<?php echo wp_json_encode( $http_headers ); ?>'
		<?php if ( $placeholder = apply_filters( 'simplechart_widget_placeholder_text', null ) ) : ?>
			data-placeholder = '<?php echo esc_attr( $placeholder ); ?>'
		<?php endif; ?>
	>
		<?php
		/**
		 * Use a custom template for the Simplechart widget
		 *
		 * @param string|null $custom_template Null or string of HTML for template
		 * @param int $id Post ID of chart being rendered
		 */
		if ( $custom_template = apply_filters( 'simplechart_widget_template', null, $id ) ) : ?>
			<?php echo wp_kses_post( $custom_template ); ?>
		<?php else : ?>
			<p class='simplechart-title'></p>
			<p class='simplechart-caption'></p>
			<div
				class='simplechart-chart'
				style='<?php echo esc_attr( $this->height_style( $id ) ); ?>'
			></div>
			<p class='simplechart-credit'></p>
		<?php endif; ?>
	</figure>
	<script>
		<?php // Load Simplechart widget JS asynchronously if not already loaded ?>
		if ( ! document.getElementById( 'simplechart-widget-js' ) ) {
			var scriptEl = document.createElement( 'script' ),
				chartEl = document.getElementById( 'simplechart-widget-<?php echo esc_attr( $id ); ?>' );

			scriptEl.id = 'simplechart-widget-js';
			scriptEl.src = <?php echo wp_json_encode( Simplechart::instance()->get_config( 'widget_loader_url' ) ); ?>;
			chartEl.parentNode.appendChild( scriptEl );
		}
	</script>