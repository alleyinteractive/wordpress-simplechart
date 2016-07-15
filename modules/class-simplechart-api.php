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
		if ( empty( $query->query_vars['simplechart-api'] ) || empty( $query->query_vars['p'] ) ) {
			return;
		}
		$id = absint( $query->query_vars['p'] );
		$post = get_post( $id );

		if ( empty( $post ) || 'simplechart' !== $post->post_type ) {
			wp_send_json_error( array(
				'message' => sprintf( __( "Post ID %d is not in 'simplechart' post type", 'simplechart' ), $id ),
			) );
		}

		$response = array();
		foreach ( array( 'Data', 'Options', 'Metadata' ) as $key ) {
			$meta = get_post_meta( $id, 'save-chart' . $key, true );
			// Fields are saved with esc_textarea() which uses htmlspecialchars(), so we decode here
			$meta = htmlspecialchars_decode( $meta, ENT_QUOTES );
			$response[ strtolower( $key ) ] = $meta;
		}

		wp_send_json_success( $response );
	}
}
