<?php

/*
 * frontend template rendering module
 */

class Simplechart_Template {

	function __construct() {
		add_shortcode( 'simplechart', array( $this, 'render_shortcode' ) );
		add_action( 'wp', array( $this, 'add_filter_post_content' ) );
	}

	// do the shortcode
	public function render_shortcode( $attrs ) {
		if ( empty( $attrs['id'] ) || ! is_numeric( $attrs['id'] ) ) {
			return '';
		}

		$chart = get_post( intval( $attrs['id'] ) );

		if ( empty( $chart ) ) {
			return '';
		}

		ob_start();
		$this->render( $chart->ID );
		return ob_get_clean();
	}

	// render the chart from template
	public function render( $id ) {

		// only allow non-published charts unless we're looking at a post preview
		if ( 'publish' !== get_post_status( $id ) && ! is_preview() ) {
			return '';
		}

		if ( 'simplechart' !== get_post_type( intval( $id ) ) ) {
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
			<div
				id='simplechart-widget-<?php echo absint( $id ); ?>'
				class='simplechart-widget'
				data-url='<?php echo esc_url( home_url( '/simplechart/api/' . $id . '/' ) ); ?>'
				data-headers='<?php echo wp_json_encode( $http_headers ); ?>'
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
					<h3 class='simplechart-title'></h3>
					<h4 class='simplechart-caption'></h4>
					<div class='simplechart-chart'></div>
					<p class='simplechart-credit'></p>
				<?php endif; ?>
			</div>
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
		<?php
	}

	// automatically render chart if looking at the chart's own post
	public function add_filter_post_content() {
		if ( ! is_admin() && is_singular( 'simplechart' ) ) {
			//add_filter( 'the_content', array( $this, 'filter_insert_chart' ) );
		}
	}

	// prepend chart to post_content
	public function filter_insert_chart( $content ) {
		global $post;

		$template_html = $this->render( $post->ID );

		return $template_html . $content;
	}
}
