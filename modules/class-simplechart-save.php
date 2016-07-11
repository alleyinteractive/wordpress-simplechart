<?php

class Simplechart_Save {

	private $_default_img_type = 'png';
	private $_allowed_template_tags = array( 'nvd3', 'datamap', 'highchart' );
	private $_errors = array();
	private $_debug_messages = array();
	private $_show_debug_messages = false;
	private $_image_post_status = 'simplechart_image';

	function __construct() {
		add_action( 'save_post_simplechart', array( $this, 'save_post_action' ), 10, 1 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'init', array( $this, 'register_image_post_status' ), 10 );
		add_filter( 'wp_insert_attachment_data', array( $this, 'set_chart_image_status' ), 10, 2 );
	}

	public function register_image_post_status() {
		register_post_status( $this->_image_post_status, array(
			'label' => __( 'Chart Image', 'simplechart' ),
			'public' => false,
			'exclude_from_search' => true,
			'show_in_admin_all_list' => false,
			'show_in_admin_status_list' => false,
		) );
	}

	// use remove-add to prevent infinite loop
	function save_post_action( $post_id ) {

		// verify nonce
		if ( empty( $_POST['simplechart-nonce'] ) || ! wp_verify_nonce( $_POST['simplechart-nonce'], 'simplechart_save' ) ) {
			return;
		}

		// check user caps
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// only worry about the real post
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		remove_action( 'save_post_simplechart', array( $this, 'save_post_action' ), 10, 1 );
		$post = get_post( $post_id );
		$this->do_save_post( $post );
		add_action( 'save_post_simplechart', array( $this, 'save_post_action' ), 10, 1 );
	}

	function admin_notices() {
		// skip if not editing single post in Chart post type
		$screen = get_current_screen();
		if ( 'simplechart' !== $screen->post_type || 'post' !== $screen->base ) {
			return;
		}

		global $post;

		// print error messages
		$errors = maybe_unserialize( get_post_meta( $post->ID, 'simplechart-errors', true ) );
		if ( is_array( $errors ) ) {
			foreach ( $errors as $error ) {
				echo '<div class="error"><p>' . esc_html( $error ) . '</p></div>';
			}
		}

		// print debug messages
		$this->_show_debug_messages = apply_filters( 'simplechart_show_debug_messages', $this->_show_debug_messages );
		if ( $this->_show_debug_messages ) {
			$messages = maybe_unserialize( get_post_meta( $post->ID, 'simplechart-debug', true ) );
			if ( is_array( $messages ) ) {
				foreach ( $messages as $message ) {
					echo '<div class="update-nag"><p>' . esc_html( $message ) . '</p></div>';
				}
			}
		}

		// clear errors and debug messages
		delete_post_meta( $post->ID, 'simplechart-errors' );
		delete_post_meta( $post->ID, 'simplechart-debug' );
	}

	function do_save_post( $post ) {

		// delete featured image if post is NOT published but has a featured image
		if ( 'publish' !== get_post_status( $post->ID ) && has_post_thumbnail( $post->ID ) ) {
			wp_delete_attachment( get_post_thumbnail_id( $post->ID ), true );
		}

		// handle base64 image string if provided
		if ( ! empty( $_POST['simplechart-png-string'] ) ) {
			$this->_save_chart_image( $post, $_POST['simplechart-png-string'], $this->_default_img_type );
		}

		// sanitize and validate JSON formatting of chart data
		$json_data = $this->_validate_json( stripcslashes( $_POST['simplechart-data'] ) );
		if ( $json_data ) {
			update_post_meta( $post->ID, 'simplechart-data',  wp_slash( $json_data ) );
		}

		// save error messages
		if ( ! empty( $this->_errors ) ) {
			update_post_meta( $post->ID, 'simplechart-errors', $this->_errors );
		}

		// save debug messages
		if ( ! empty( $this->_debug_messages ) ) {
			update_post_meta( $post->ID, 'simplechart-debug', $this->_debug_messages, true );
		}

	}

	private function _save_chart_image( $post, $data_uri, $img_type ) {

		// make sure we have a post object
		if ( is_numeric( $post ) ) {
			$post = get_post( $post );
		}

		$perm_file_name = 'simplechart_' . $post->ID . '.' . $img_type;
		$temp_file_name = 'temp_' . $perm_file_name;

		// make sure we have valid base64 data then proceed
		$img_data = $this->_process_data_uri( $data_uri, $img_type );

		if ( is_wp_error( $img_data ) ) {
			$this->_errors = array_merge( $this->_errors, $img_data->get_error_messages() );
			return false;
		}

		// delete existing chart image if present
		// so we can upload the new one to the same URL
		if ( has_post_thumbnail( $post->ID ) ) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			$old_file_path = get_post_meta( $thumbnail_id, '_wp_attached_file', true );
			$this->_debug_messages[] = sprintf( __( 'Found post thumbnail at %s', 'simplechart' ), $old_file_path );
			wp_delete_attachment( $thumbnail_id, true );
		} else {
			$this->_debug_messages[] = __( 'No existing post thumbnail found', 'simplechart' );
		}

		// upload to temporary file location
		$temp_file = wp_upload_bits( $temp_file_name, null, base64_decode( $img_data ) );
		if ( is_wp_error( $temp_file ) ) {
			$this->_errors = array_merge( $this->_errors, $temp_file->get_error_messages() ); // translation handled inside wp_upload_bits()
			return false;
		}
		elseif ( false !== $temp_file['error'] ) {
			$this->_errors[] = $temp_file['error']; // translation handled inside wp_upload_bits()
			return false;
		}
		$this->_debug_messages[] = sprintf( __( 'wp_upload_bits() stored in %s', 'simplechart' ), $temp_file['file'] );

		// import to media library
		$desc = 'Chart: ' . sanitize_text_field( get_the_title( $post->ID ) );
		$this->_attachment_id = media_handle_sideload( array(
			'name' => $perm_file_name,
			'tmp_name' => $temp_file['file'],
		), $post->ID, $desc);
		$new_file_path = get_post_meta( $this->_attachment_id, '_wp_attached_file', true );
		$this->_debug_messages[] = sprintf( __( 'media_handle_sideload() to %s', 'simplechart' ), $new_file_path );
		$this->_debug_messages[] = $new_file_path === $old_file_path ? __( 'New file path matches old file path', 'simplechart' ) : __( 'New file path does NOT match old file path', 'simplechart' );

		if ( is_wp_error( $this->_attachment_id ) ) {
			$this->_errors = array_merge( $this->_errors, $this->_attachment_id->get_error_messages() );
			return false;
		}

		if ( is_wp_error( $updated ) ) {
			$this->_errors = array_merge( $this->_errors, $updated->get_error_messages() );
			return false;
		}

		// set as post featured image
		set_post_thumbnail( $post->ID, $this->_attachment_id );

		// delete the temporary file!
		if ( file_exists( $temp_file['file'] ) ) {
			unlink( $temp_file['file'] );
			$this->_debug_messages[] = sprintf( __( 'Deleted chart image %s', 'simplechart' ), $temp_file['file'] );
		} else {
			$this->_debug_messages[] = sprintf( __( '%s was already deleted', 'simplechart' ), $temp_file['file'] );
		}
	}

	public function set_chart_image_status( $data, $postarr ) {
		if ( 'simplechart' === get_post_type( $data['post_parent'] ) ) {
			$data['post_status'] = $this->_image_post_status;
		}
		return $data;
	}

	private function _process_data_uri( $data_uri, $img_type ) {
		$data_prefix = 'data:image/' . $img_type . ';base64,';

		// validate input format for data URI
		if ( 0 !== strpos( $data_uri, $data_prefix ) ) {
			$this->_errors[] = __( 'Incorrect data URI formatting', 'simplechart' );
		}

		// remove prefix to get base64 data
		$img_data = str_replace( $data_prefix, '', $data_uri );

		return $img_data;
	}

	/**
	 * HTML fragment to render the chart must be a single tag in one of the allowed tags, with no children
	 * use this instead of wp_kses_* because there are a large number of potential attributes for these tags
	 */
	function validate_template_fragment( $fragment ) {
		libxml_use_internal_errors( true );
		$el = simplexml_load_string( $fragment );

		if ( $el && in_array( $el->getName(), $this->_allowed_template_tags, true ) && 0 === count( $el->children() ) ) {
			return $fragment;
		} else {
			foreach ( libxml_get_errors() as $error ) {
				$this->_errors[] = sprintf( __( 'SimpleXML error in template fragment: %s', 'simplechart' ), $error->message );
			}
			libxml_clear_errors();
			return false;
		}
	}

	private function _validate_json( $data ) {
		// Make sure we have data
		if ( 'undefined' === $data ) {
			$this->_errors[] = "JS app set value of input to 'undefined'";
			return false;
		}
		// Attempt to validate JSON by decoding then re-encoding
		elseif ( $decoded = json_decode( $data ) ) {
			return json_encode( $decoded ); // returns a valid JSON string!
		}
		// Add error message
		elseif ( function_exists( 'json_last_error_msg' ) ) {
			$this->_errors[] = sprintf( __( 'JSON error: %s', 'simplechart' ), json_last_error_msg() );
			return false;
		}
		// Or just error code
		elseif ( function_exists( 'json_last_error' ) ) {
			$this->_errors[] = sprintf( __( 'JSON error code: %s', 'simplechart' ), json_last_error() );
			return false;
		}
		// Or catch-all error message
		else {
			$this->_errors[] = __( 'Attempted to save invalid JSON', 'simplechart' );;
			return false;
		}
	}
}
