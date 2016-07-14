<?php
/**
 * Widget data API
 *
 * @package Simplechart
 */

class Simplechart_API {

	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_action( 'parse_request', array( $this, 'handle_api_request' ) );
	}

	public function add_rewrite_rule() {
		add_rewrite_tag( '%simplechart-api%', '1' );
		add_rewrite_rule(
			'^simplechart/api/(\d+)/?',
			'index.php?p=$matches[1]&post_type=simplechart&simplechart-api=1',
			'top'
		);
	}

	public function handle_api_request( $query ) {
		if (
			empty( $query->query_vars['simplechart-api'] ) || empty( $query->query_vars['p'] ) ) {
			return;
		}
		$id = intval( $query->query_vars['p'] );
		$post = get_post( $id );

		if ( empty( $post ) || 'simplechart' !== $post->post_type ) {
			wp_send_json_error( array(
				'message' => sprintf( __( "Post ID %d is not in 'simplechart' post type", 'simplechart' ), $id ),
			) );
		}

		wp_send_json_success( array(
			'data' => get_post_meta( $id, 'save-chartData', true ),
			'options' => get_post_meta( $id, 'save-chartOptions', true ),
			'metadata' => get_post_meta( $id, 'save-chartMetadata', true ),
		) );
	}
}
