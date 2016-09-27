<?php
/**
 * Widget data API
 *
 * @package Simplechart
 */

class Simplechart_Request_Handler {

	/**
	 * ID of iframe request
	 *
	 * @var int
	 */
	private $_iframe_id = 0;

	public function __construct() {
		add_action( 'init', array( $this, 'add_rewrite_rule' ) );
		add_action( 'parse_request', array( $this, 'parse_request' ) );
	}

	public function add_rewrite_rule() {
		// JSON API
		add_rewrite_tag( '%simplechart-api%', '1' );
		add_rewrite_rule(
			'^simplechart/api/(\d+)/?',
			'index.php?p=$matches[1]&post_type=simplechart&simplechart-api=1',
			'top'
		);

		// iframe source page
		add_rewrite_tag( '%simplechart-iframe%', '1' );
		add_rewrite_rule(
			'^simplechart/iframe/(\d+)/?',
			'index.php?p=$matches[1]&post_type=simplechart&simplechart-iframe=1',
			'top'
		);
	}

	/**
	 * Parse requests for JSON or HTML page for iframe
	 *
	 * @var WP_Query $query	Current query
	 * @return none
	 */
	public function parse_request( $query ) {
		// must use one of our request query vars
		if ( empty( $query->query_vars['simplechart-api'] ) && empty( $query->query_vars['simplechart-iframe'] ) ) {
			return;
		}

		$id = empty( $query->query_vars['p'] ) ? 0 : absint( $query->query_vars['p'] );
		if ( ! empty( $query->query_vars['simplechart-api'] ) ) {
			$this->_handle_api_request( $id );
		} else {
			$this->_handle_iframe_request( $id );
		}
	}

	/**
	 * Render HTML page for use in iframe
	 *
	 * @var int $id WordPress ID for the simplechart post
	 * @return none
	 */
	private function _handle_iframe_request( $id ) {
		$this->_iframe_id = $id;
		require_once( Simplechart::instance()->get_plugin_dir( 'templates/amp-iframe-source.php' ) );
		exit();
	}

	/**
	 * Getter for current frame ID
	 *
	 * @return int ID
	 */
	public function frame_id() {
		return $this->_iframe_id;
	}

	/**
	 * Send JSON success and data, or failure when chart data is requested
	 *
	 * @var int $id WordPress ID for the simplechart post
	 * @return none
	 */
	private function _handle_api_request( $id ) {
		// Allows same-origin https CORS requests
		header( 'Access-Control-Allow-Origin: *' );

		// Validate post type
		if ( empty( $id ) || 'simplechart' !== get_post_type( $id ) ) {
			wp_send_json_error( array(
				'message' => sprintf( __( "Post ID %d is not in 'simplechart' post type", 'simplechart' ), $id ),
			) );
		}

		// Build array of save-chartData, etc. from post meta
		$response = array();
		foreach ( array( 'Data', 'Options', 'Metadata' ) as $key ) {
			$response[ strtolower( $key ) ] = get_post_meta( $id, 'save-chart' . $key, true );
		}
		wp_send_json_success( $response );
	}
}
