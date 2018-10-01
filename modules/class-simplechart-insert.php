<?php

function simplechart_init( $services ) {

	if ( class_exists( 'Simplechart_Insert' ) ) {
		$service = new Simplechart_Insert;
	}
	return $service;
}

class Simplechart_Insert {
	/**
	 * Constructor.
	 *
	 * Creates the Backbone view template.
	 */
	public function __construct() {
		require_once( plugin_dir_path( __FILE__ ) . 'class-simplechart-insert-template.php' );
		$this->set_template( new Simplechart_Insert_Template );

		// Add actions to WP hooks
		add_action( 'print_media_templates', array( $this, 'action_print_media_templates' ) );
		add_action( 'wp_ajax_simplechart_request', array( $this, 'ajax_request' ) );
		add_action( 'wp_enqueue_media', array( $this, 'action_enqueue_media' ) );
	}

	/**
	 * Process an AJAX request and output the resulting JSON.
	 *
	 * @action wp_ajax_simplechart_request
	 * @return null
	 */
	public function ajax_request() {

		if ( ! isset( $_POST['_nonce'] )
			or ! wp_verify_nonce( sanitize_key( $_POST['_nonce'] ), 'simplechart_request' )
			) {
			die( '-1' );
		}

		$request = wp_parse_args(
			stripslashes_deep( $_POST ),
			array(
				'params'  => array(),
				'tab'     => null,
				'min_id'  => null,
				'max_id'  => null,
				'page'    => 1,
			)
		);
		$request['page'] = absint( $request['page'] );
		$request['user_id'] = absint( get_current_user_id() );

		$response = $this->request( $request );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error(
				array(
					'error_code'    => $response->get_error_code(),
					'error_message' => $response->get_error_message(),
				)
			);

		} else if ( is_array( $response ) ) {
			wp_send_json_success( $response );
		} else {
			wp_send_json_success( false );
		}
	}

	/**
	 * Handles the AJAX request and returns an appropriate response. This should be used, for example, to perform an API request to the service provider and return the results.
	 *
	 * @param array $request The request parameters.
	 * @return array|bool An array should be returned on success, boolean false should be returned if there are no results to show.
	 */
	public function request( array $request ) {

		// You'll want to handle connection errors to your service here. Look at the Twitter and YouTube implementations for how you could do this.

		// Create the response for the API
		$response = array();
		$items = array();

		$query_args = array(
			'post_type' => 'simplechart',
		);

		// pagination
		if ( isset( $request['page'] ) && absint( $request['page'] ) > 1 ) {
			$query_args['paged'] = absint( $request['page'] );
		}

		// search query
		if ( isset( $request['params']['q'] ) ) {
			$query_args['s'] = sanitize_text_field( $request['params']['q'] );
		}

		$simplechart_query = new WP_Query( $query_args );

		if ( $simplechart_query->have_posts() ) {
			while ( $simplechart_query->have_posts() ) {
				$simplechart_query->the_post();
				global $post;

				$thumb = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), array( 150, 150 ) );
				$thumb_url = isset( $thumb[0] ) ? $thumb[0] : '';
				$item = array();
				$item['date'] = date( 'g:i A - j M y', intval( get_the_time( 'U' ) - $request['tz_off'] ) );
				$item['id'] = absint( $post->ID );
				$item['content'] = esc_html( get_the_title() );
				$item['img'] = esc_url( $thumb_url );

				// Add status like ' - Draft' if chart is not yet published
				$status = get_post_status( $post->ID );
				if ( 'publish' === $status || ! $status ) {
					$status = esc_html__( 'Published', 'simplechart' );
				} else {
					$status = esc_html( ucfirst( $status ) );
				}
				$item['status'] = $status;

				$items[] = $item;
			}
		} else {
			return false;
		}

		$response['items'] = $items;
		$response['meta'] = array(
			'min_id' => reset( $items )['id'],
		);

		return $response;
	}

	/**
	 * Load the Backbone templates for each of our registered services.
	 *
	 * @action print_media_templates
	 * @return null
	 */
	public function action_print_media_templates() {
		$template = $this->get_template();
		if ( ! $template ) {
			return;
		}

		foreach ( array( 'search', 'item' ) as $t ) {
			$id = sprintf(
				'simplechart-insert-%s-all',
				esc_attr( $t )
			);

			$template->before_template( $id );
			call_user_func( array( $template, $t ), $id, 'all' );
			$template->after_template();
		}

		$id = sprintf( 'simplechart-insert-thumbnail' );

		$template->before_template( $id );
		call_user_func( array( $template, 'thumbnail' ), $id );
		$template->after_template();
	}

	/**
	 * Enqueue and localise the JS and CSS we need for the media manager.
	 *
	 * @action enqueue_media
	 * @return null
	 */
	public function action_enqueue_media() {
		$simplechart = array(
			'_nonce'    => wp_create_nonce( 'simplechart_request' ),
			'base_url'  => untrailingslashit( Simplechart::instance()->get_plugin_url() ),
			'admin_url' => untrailingslashit( admin_url() ),
		);

		wp_enqueue_script(
			'simplechart-insert',
			Simplechart::instance()->get_plugin_url( 'js/plugin/build/simplechartInsert.js' ),
			array( 'jquery' ),
			Simplechart::instance()->get_config( 'version' )
		);

		wp_enqueue_style(
			'simplechart-insert',
			Simplechart::instance()->get_plugin_url( 'css/simplechart-insert.css' ),
			array(),
			Simplechart::instance()->get_config( 'version' )
		);

		wp_localize_script(
			'simplechart-insert',
			'simplechart',
			$simplechart
		);
	}

	/**
	 * Sets the template object.
	 *
	 * @return null
	 */
	final public function set_template( Simplechart_Insert_Template $template ) {

		$this->template = $template;

	}

	/**
	 * Returns the template object for this service.
	 *
	 * @return Template|null A Template object,
	 * or null if a template isn't set.
	 */
	final public function get_template() {

		return $this->template;

	}
}
