<?php

/*
 * frontend template rendering module
 */

class Simplechart_Template {

	/**
	 * ID of chart being rendered
	 * @var int
	 */
	private $_id = 0;

	/**
	 * Is current page AMP?
	 *
	 * @var bool
	 */
	private $_is_amp = false;


	function __construct() {
		add_shortcode( 'simplechart', array( $this, 'render_shortcode' ) );
		add_action( 'wp', array( $this, 'add_filter_post_content' ) );

		// Hook into AMP plugin action
		add_action( 'pre_amp_render_post', function() {
			$this->_is_amp = true;
		} );
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

		// Set object property so it can be retrieved later by template file
		$this->_id = $id;

		/**
		 * Set this to true for AMP pages to render as `amp-iframe`. This is handled automatically if
		 * you're using the offical WP AMP plugin (https://wordpress.org/plugins/amp/)
		 *
		 * @param bool $amp
		 * @return bool
		 */
		$this->_is_amp = apply_filters( 'simplechart_is_amp_page', $this->_is_amp );

		/**
		 * Set this to true to disable Simplechart entirely on AMP pages
		 *
		 * @param bool $disable_amp Defaults to false
		 * @return bool
		 */
		$disable_amp = apply_filters( 'simplechart_disable_amp', false );

		if ( ! $this->_is_amp ) {
			require_once( Simplechart::instance()->get_plugin_dir( 'templates/embed.php' ) );
		} else if ( ! $disable_amp ) {
			require_once( Simplechart::instance()->get_plugin_dir( 'templates/amp-iframe.php' ) );
		}
	}

	/**
	 * Get ID of chart currently being rendered
	 *
	 * @return int $id
	 */
	public function current_id() {
		return $this->_id;
	}

	/**
	 * Get string for height style attribute based on post meta
	 *
	 * @param int $id Post ID for the chart
	 * @return string 'height: XXXpx' or empty string if no height found
	 */
	public function height_style( $id ) {
		$height = get_post_meta( $id, 'height', true );
		if ( empty( $height ) ) {
			return '';
		}
		return 'height:' . absint( $height ) . 'px;';
	}

	// automatically render chart if looking at the chart's own post
	public function add_filter_post_content() {
		if ( ! is_admin() && is_singular( 'simplechart' ) ) {
			add_filter( 'the_content', array( $this, 'filter_insert_chart' ) );
		}
	}

	// prepend chart to post_content
	public function filter_insert_chart( $content ) {
		global $post;

		$template_html = $this->render( $post->ID );

		return $template_html . $content;
	}
}
