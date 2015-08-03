<?php

/**
 * Import Simplechart charts from a remote site via WP-CLI
 */

class Simplechart_WP_CLI extends WP_CLI_Command {

	/**
	 * Imports Simplechart charts using the Jetpack/WPCOM REST API
	 *
	 * ## OPTIONS
	 *
	 * --site_id=<site_id>
	 * : WPCOM site ID
	 *
	 * --posts=<post_ids>
	 * : comma separated list of Simplechart post IDs on remote site
	 *
	 * ## EXAMPLES
	 *
	 * wp simplechart import_wpcom --site_id=123456 --posts=12,13,14
	 *
	 * @synopsis --site_id=<site_id> --posts=<post_ids>
	 */
	function import_wpcom( $args, $assoc_args ) {
		$post_ids = explode( ',', $assoc_args['posts'] );
		$post_ids = array_map( 'absint', $post_ids );

	}

}
WP_CLI::add_command( 'simplechart', 'Simplechart_WP_CLI' );