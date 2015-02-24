<?php

/*
 * frontend template rendering module
 */

class Simplechart_Template {

	function __construct(){
		add_shortcode( 'simplechart', array( $this, 'render_shortcode' ) );
		add_action( 'wp', array( $this, 'add_filter_post_content') );
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
