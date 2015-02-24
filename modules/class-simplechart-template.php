<?php

/*
 * frontend template rendering module
 */

class Simplechart_Template {

	function __construct(){
		add_shortcode( 'simplechart', array( $this, 'render_shortcode' ) );
		add_action( 'wp', array( $this, 'add_filter_post_content') );
		add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueues' ) );
	}

	public function frontend_enqueues(){
		if ( is_admin() ){
			return;
		}
		global $simplechart;
		$root = $simplechart->get_config( 'app_url_root' );
		wp_register_style( 'nvd3-css',	$root . '/bower_components/nvd3/nv.d3.min.css' );
		wp_register_style( 'simplechart', $simplechart->get_plugin_url() . 'css/style.css', array( 'nvd3-css' ), $simplechart->get_config( 'version' ) );
		wp_enqueue_style( 'simplechart' );
	}

	// do the shortcode
	public function render_shortcode( $attrs ){
		if ( empty( $attrs['id'] ) || ! is_numeric( $attrs['id'] ) ){
			return '';
		}

		$chart = get_post( intval( $attrs['id'] ) );

		if ( empty( $chart ) ){
			return '';
		}

		return $this->render( $chart->ID );
	}

	// render the chart from template
	public function render( $id ){
		global $simplechart;

		// only allow non-published charts if we're looking at a post preview
		if ( 'publish' !== get_post_status( $id ) && ! is_preview() ){
			return '';
		}

		// if we have a chart URL, use that in an iframe
		$chart_url = get_post_meta( $id, 'simplechart-chart-url', true );
		if ( ! empty( $chart_url ) ){
			return '<iframe id="simplechart-' . esc_attr( $id ) . '" class="simplechart-frame" src="' . esc_url( $chart_url ) . '"></iframe>';
		}

		// otherwise, support early versions of the plugin
		$json_data = get_post_meta( $id, 'simplechart-data', true );
		$template_html = get_post_meta( $id, 'simplechart-template', true );
		$image_fallback = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'large' );
		$template_format = file_get_contents( $simplechart->get_plugin_dir() . 'templates/template-partial.html' );

		$template_html = sprintf( $template_format,
			json_encode( json_decode( $json_data ) ),
			$simplechart->save->validate_template_fragment( $template_html ),
			esc_url( $simplechart->get_config( 'app_url_root' ) . $simplechart->get_config( 'loader_js_path' ) ),
			( ! $image_fallback ? '' : esc_url( $image_fallback[0] ) )
		);

		return $template_html;
	}

	// automatically render chart if looking at the chart's post
	public function add_filter_post_content(){
		if ( is_singular( 'simplechart' ) ){
			add_filter( 'the_content', array( $this, 'filter_insert_chart' ) );
		}
	}

	// prepend chart to post_content
	public function filter_insert_chart( $content ){
		global $post;

		$template_html = $this->render( $post->ID );

		return $template_html . $content;
	}

}
