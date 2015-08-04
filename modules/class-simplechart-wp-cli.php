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
		'simplechart-chart-url',
		'simplechart-chart-id',
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
		WP_CLI::line( sprintf( 'Starting import of %s posts from site %s', count( $post_ids ), $site_id ) );
		foreach ( $post_ids as $post_id ) {
			$post_object = $this->_extract_wpcom_post_object( $site_id, $post_id );
			if ( $post_object ) {
				$transformed = $this->_transform_from_wpcom_api( $post_object );
				$this->_load_post( $transformed );
			}
		}
		WP_CLI::success( sprintf( 'Processed %s posts from site %s', count( $post_ids ), $site_id ) );
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
			$warning = sprintf( "Failed to GET post %s from site %s\n", $post, $site );
			$warning .= implode( "\n", $response->get_error_messages() );
			WP_CLI::warning( $warning );
			return null;
		} elseif ( 200 !== $code = wp_remote_retrieve_response_code( $response ) ) {
			WP_CLI::warning( sprintf( 'Post %s from site %s returned error code %s', $post, $site, $code ) );
			return null;
		}

		$post_object = json_decode( wp_remote_retrieve_body( $response ), true );

		// make sure it's the right post type
		if ( $this->_post_type !== $post_object['type'] ) {
			WP_CLI::warning( sprintf( "Post %s from site %s does not have post_type '%s'", $post, $site, $this->_post_type ) );
			return null;
		}

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

		// loop through post metadata
		$post_meta = array();
		if ( ! empty( $post_object['metadata'] ) && is_array( $post_object['metadata'] ) ) {
			foreach ( $post_object['metadata']  as $meta ) {
				if ( in_array( $meta['key'], $this->_meta_keys, true ) ) {
					$post_meta[ $meta['key'] ] = $meta['value'];
				}
			}
		}

		// featured image
		$featured_image = null;
		if ( ! empty( $post_object['post_thumbnail' ] ) ) {
			$featured_image = array(
				'url' => $post_object['post_thumbnail' ]['URL'],
				'width' => $post_object['post_thumbnail' ]['width'],
				'height' => $post_object['post_thumbnail' ]['height'],
			);
		}

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
			WP_CLI::warning( 'Tried to insert post with no post_data' );
			return false;
		}

		// create the post
		$post_id = wp_insert_post( $data['post_data'] );
		if ( 0 === $post_id ) {
			return false;
		}

		// add the post metadata
		foreach ( $data['post_meta'] as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		// attach the featured image
		if ( $post_data['featured_image'] ) {
			$this->_attach_featured_image_to_post( $post_data['featured_image'], $post_id );
		}

		return true;
	}

	/**
	 * sideload featured image from URL and set as post thumbnail
	 * @var array $image Image URL, width, height
	 * @var int $post_id ID of Simplechart post
	 */
	private function _attach_featured_image_to_post( $image, $post_id ) {
		// https://codex.wordpress.org/Function_Reference/media_handle_sideload#Examples
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