<?php

/**
 * Import Simplechart charts from a remote site via WP-CLI
 */
class Simplechart_WP_CLI extends WP_CLI_Command {

	/**
	 * @var string $_wpcom_api_url Templatized URL to retrieve a single post from the WordPress.com REST API
	 */
	private $_wpcom_api_url = 'https://public-api.wordpress.com/rest/v1.1/sites/{{site}}/posts/{{post}}';

	/**
	 * @var int|string|null $_author Author ID to assign imported posts to
	 */
	private $_author = null;

	/**
	 * @var string $_post_type The post type we want to work with
	 */
	private $_post_type = 'simplechart';

	/**
	 * @var array $_meta_keys List of post meta keys to set when creating a post
	 */
	private $_meta_keys = array(
		'simplechart-data',
		'simplechart-template',
		'simplechart-errors',
	);

	/**
	 * Imports Simplechart charts using the Jetpack/WPCOM REST API
	 *
	 * ## OPTIONS
	 *
	 * --site=<site_id>
	 * : WPCOM site ID
	 *
	 * --posts=<post_ids>
	 * : comma separated list of Simplechart post IDs on remote site
	 *
	 *
	 * [--author=<author>]
	 * : Assigns imported posts to a specific author ID or username
	 *
	 * ## EXAMPLES
	 *
	 * wp simplechart import_wpcom --site=123456 --posts=12,13,14 --author=6
	 *
	 * @synopsis --site=<site_id> --posts=<post_ids> [--author=<author_id>]
	 */
	function import_wpcom( $args, $assoc_args ) {
		$post_ids = explode( ',', $assoc_args['posts'] );
		$post_ids = array_map( 'absint', $post_ids );
		$site_id = absint( $assoc_args['site'] );
		$this->_set_author( $assoc_args );
		WP_CLI::line( sprintf( __( "Starting import of %s posts from site %s\n", 'simplechart' ), count( $post_ids ), $site_id ) );
		foreach ( $post_ids as $post_id ) {
			WP_CLI::line( sprintf( __( 'Processing post %s', 'simplechart' ), $post_id ) );
			$post_object = $this->_extract_wpcom_post_object( $site_id, $post_id );
			if ( $post_object ) {
				$transformed = $this->_transform_from_wpcom_api( $post_object );
				$this->_load_post( $transformed );
			}
			WP_CLI::line( '' );
		}
		WP_CLI::line( sprintf( __( 'Processed %s posts from site %s', 'simplechart' ), count( $post_ids ), $site_id ) );
	}

	/**
	 * Retrieve a single post from the WordPress.com REST API
	 * see https://developer.wordpress.com/docs/api/1.1/get/sites/%24site/posts/%24post_ID/#apidoc-response
	 * @param int $site WPCOM site ID
	 * @param int $post WPCOM post ID
	 * @return array Associative array of JSON response
	 */
	private function _extract_wpcom_post_object( $site, $post ) {
		// build API url
		$url = str_replace( '{{site}}', $site, $this->_wpcom_api_url );
		$url = str_replace( '{{post}}', $post, $url );

		// API request
		$response = wp_remote_get( $url );

		// validate response
		if ( is_wp_error( $response ) ) {
			$warning = sprintf( __( "Failed to GET post %s from site %s\n", 'simplechart' ), $post, $site );
			$warning .= implode( "\n", $response->get_error_messages() );
			WP_CLI::warning( $warning );
			return null;
		} elseif ( 200 !== $code = wp_remote_retrieve_response_code( $response ) ) {
			WP_CLI::warning( sprintf( __( 'Post %s from site %s returned error code %s', 'simplechart' ), $post, $site, $code ) );
			return null;
		}

		$post_object = json_decode( wp_remote_retrieve_body( $response ), true );

		// make sure json_decode worked
		if ( empty( $post_object ) ) {
			WP_CLI::warning( sprintf( __( 'Invalid JSON response from %s', 'simplechart' ), $url ) );
			return null;
		}

		// make sure it's the right post type
		if ( $this->_post_type !== $post_object['type'] ) {
			WP_CLI::warning( sprintf( __( "Post %s from site %s does not have post_type '%s'", 'simplechart' ), $post, $site, $this->_post_type ) );
			return null;
		}

		WP_CLI::success( sprintf( __( 'Retrieved post %s from site %s', 'simplechart' ), $post, $site ) );
		return $post_object;
	}

	/**
	 * Create a post in Simplechart post type using WPCOM REST API data
	 * @param array $post_object API data for the post
	 * @return array Post object transformed for our data loader
	 */
	private function _transform_from_wpcom_api( $post_object ) {
		// basic post info
		$post_data = array(
			'post_name' => $post_object['slug'],
			'post_title' => $post_object['title'],
			'post_status' => $post_object['status'],
			'post_type' => $this->_post_type,
			'post_date_gmt' => date( 'Y-m-d H:i:s', strtotime( $post_object['date'] ) ),
		);

		// author ID if we have one
		if ( $this->_author ) {
			$post_data['post_author'] = $this->_author;
		}

		// setup post metadata
		$post_meta = array(
			'_src_guid' => $post_data['guid'],
		);
		if ( ! empty( $post_object['metadata'] ) && is_array( $post_object['metadata'] ) ) {
			foreach ( $post_object['metadata']  as $meta ) {
				if ( in_array( $meta['key'], $this->_meta_keys, true ) ) {
					$post_meta[ $meta['key'] ] = $meta['value'];
				}
			}
		}

		// featured image URL
		$featured_image = ! empty( $post_object['featured_image'] ) ? $post_object['featured_image'] : null;

		return array(
			'post_data' => $post_data,
			'post_meta' => $post_meta,
			'featured_image' => $featured_image,
		);
	}

	/**
	 * create new post for the chart, using already transformed data
	 * @var array $data Post data for new chart
	 *			array 'post_data' Arguments to pass to wp_insert_post()
	 *			array 'post_meta' Key-value pairs to set as post meta
	 *			array 'featured_image' Optional array of url, width, height of image to import
	 * @return bool Success or failure
	 */
	private function _load_post( $data ) {
		if ( empty( $data['post_data'] ) ) {
			WP_CLI::warning( __( 'Cannot insert post with no post_data', 'simplechart' ) );
			return false;
		}

		// create the post
		$post_id = wp_insert_post( $data['post_data'] );
		if ( 0 === $post_id ) {
			WP_CLI::warning( __( 'Failed to insert post', 'simplechart' ) );
			return false;
		}

		// add the post metadata
		foreach ( $data['post_meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// attach the featured image
		if ( $data['featured_image'] ) {
			$this->_attach_featured_image_to_post( $data['featured_image'], $post_id, $data['post_data']['post_title'] );
		}

		return true;
	}

	/**
	 * sideload featured image from URL and set as post thumbnail
	 * @var string $image Image URL
	 * @var int $post_id ID of Simplechart post
	 * @var string $desc Description for image
	 */
	private function _attach_featured_image_to_post( $image, $post_id, $desc ) {
		$tmp = download_url( $image );
		if ( is_wp_error( $tmp ) ) {
			WP_CLI::warning( sprintf( __( 'Error downloading %s', 'simplechart' ), esc_url( $image ) ) );
			if ( file_exists( $tmp ) ) {
				@unlink( $tmp );
			}
			return;
		}

		$file_array = array(
			'tmp_name' => $tmp,
			'name' => basename( $image ),
		);
		$media_id = media_handle_sideload( $file_array, $post_id, $desc );

		if ( ! is_wp_error( $media_id ) ) {
			set_post_thumbnail( $post_id, $media_id );
			WP_CLI::success( sprintf( __( 'Set attachment %s as thumbnail for post %s', 'simplechart' ), $media_id, $post_id ) );
		} else {
			WP_CLI::warning( sprintf( __( 'Sideload failed: %s', 'simplechart' ), $media_id->get_error_message() ) );
		}
		if ( file_exists( $tmp ) ) {
			@unlink( $tmp );
		}
	}

	/**
	 * set author by ID or username to assign imported posts to
	 * @var array Assoc args from command
	 */
	private function _set_author( $assoc_args ) {
		// was an author provided?
		if ( empty( $assoc_args['author'] ) ) {
			return;
		}

		// check for author ID
		if ( is_numeric( $assoc_args['author'] ) ) {
			$user = get_user_by( 'id', absint( $assoc_args['author'] ) );
		}
		// check for author by slug
		else {
			$user = get_user_by( 'slug', $assoc_args['author'] );
		}

		// set author
		if ( ! empty( $user ) ) {
			$this->_author = $user->ID;
		}
		return;
	}
}
WP_CLI::add_command( 'simplechart', 'Simplechart_WP_CLI' );