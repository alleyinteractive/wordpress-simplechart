<?php

/**
 * Simplechart WP_CLI commands
 *
 * @package simplechart
 * @see https://github.com/alleyinteractive/wordpress-simplechart
 */
WP_CLI::add_command( 'simplechart', 'WP_CLI_Simplechart' );

class WP_CLI_Simplechart extends WP_CLI_Command {

	protected $posts_per_batch = 50;
	protected $dry_run = false;
	protected $revert = false;
	protected $meta_sub_keys = array(
		'-data',
		'-template',
		'-png-string',
		'-errors',
		'-chart-url',
		'-chart-id',
		'-debug',
	);

	/**
	 * Migrates a batch of charts to Simplechart from the legacy Chartbuilder plugin
	 *
	 * ## OPTIONS
	 * --ids=<ids>
	 * : Comma-separated list of post IDs to migrate, or "all"
	 *
	 * [--dry-run]
	 * : Simulate migration without updating database
	 *
	 * [--revert]
	 * : Revert migration from Simplechart back to Chartbuilder
	 *
	 * @synopsis --ids=<ids> [--dry-run] [--revert]
	 *
	 * @subcommand migrate
	 */
	public function migrate_chartbuilder( $args, $assoc_args ) {
		$this->setup_migration_opts( $assoc_args );

		// Migrate all charts
		if ( 'all' === $assoc_args['ids'] ) {
			$this->loop_batch( array(
				'post_status' => 'any',
				'ignore_sticky_posts' => true,
				'posts_per_page' => $this->posts_per_batch,
				'post_type' => $this->post_type_from,
				'orderby' => 'ID',
			), 'migrate_single_post' );
		}
		// Migrate from specific list of IDs
		else {
			$ids = explode( ',', $assoc_args['ids'] );
			if ( count( $ids ) > $this->posts_per_batch ) {
				WP_CLI::error( sprintf( __( 'Max of %s ids can be specified, or use --ids=all', 'simplechart' ), $this->posts_per_batch ) );
			}
			foreach( $ids as $id ) {
				$this->migrate_single_post( absint( $id ) );
			}
		}
		WP_CLI::line( __( 'Migration complete', 'simplechart' ) );
	}

	/**
	 * Migrates a single chart to Simplechart from the legacy Chartbuilder plugin
	 *
	 * ## OPTIONS
	 * <id>
	 * : Single post ID to migrate
	 *
	 * [--dry-run]
	 * : Simulate migration without updating database
	 *
	 * [--revert]
	 * : Revert migration from Simplechart back to Chartbuilder
	 *
	 * @synopsis <id> [--dry-run] [--revert]
	 *
	 * @subcommand migrate-single
	 */
	public function migrate_single_post( $args, $assoc_args = false ) {
		// will be skipped if already set up, e.g. calling from $this->loop_batch
		$this->setup_migration_opts( $assoc_args );

		if ( is_object( $args ) && 'WP_Post' === get_class( $args ) ) {
			$post = $args;
		} else if ( ! empty( $args[0] ) && is_numeric( $args[0] ) ) {
			$post = get_post( $args[0] );
		} else if ( is_numeric( $args ) ) {
			$post = get_post( $args );
		} else {
			WP_CLI::error( __( '`migrate_single_post()` requires a numeric ID or a WP_Post object', 'simplechart' ) );
		}

		if ( empty( $post ) ) {
			WP_CLI::warning( sprintf( __( 'Could not locate post %s', 'simplechart' ), strval( $post ) ) );
			return;
		}

		// confirm post type
		if ( $this->post_type_from !== $post->post_type ) {
			WP_CLI::warning( sprintf( __( 'Attempted to migrate post %s from invalid post_type "%s"', 'simplechart' ), $post->ID, $post->post_type ) );
			return;
		}

		// confirm status
		if ( wp_is_post_revision( $post->ID ) || 'auto-draft' === $post->post_status || 'inherit' === $post->post_status ) {
			WP_CLI::warning( sprintf( __( 'Attempted to migrate post %s with invalid post_status "%s"', 'simplechart' ), $post->ID, $post->post_status ) );
			return;
		}

		WP_CLI::line( sprintf( __( 'Migrating post %s', 'simplechart' ), $post->ID ) );

		if ( ! $this->dry_run ) {
			// migrate to new post type
			$id = wp_update_post( array(
				'ID' => $post->ID,
				'post_type' => $this->post_type_to,
			), true );

			// check error
			if ( is_wp_error( $id ) ) {
				WP_CLI::warning( sprintf( __( 'Error updating post %s: %s', 'simplechart' ), $post->ID, $id->get_error_message() ) );
				return;
			}
		} else {
			$id = $post->ID;
		}

		// migrate post meta
		$migrated_keys = array();
		foreach( $this->meta_sub_keys as $sub_key ) {
			$value = get_post_meta( $id, $this->meta_prefix_from . $sub_key, true );
			// skip empty keys
			if ( empty( $value ) ) {
				continue;
			}
			if ( ! $this->dry_run ) {
				update_post_meta( $id, $this->meta_prefix_to . $sub_key, $value );
				delete_post_meta( $id, $this->meta_prefix_from . $sub_key );
			}
			$migrated_keys[] = $this->meta_prefix_from . $sub_key;
		}

		// URL decode chart title and other metadata, will respect $this->dry_run if true
		$this->fix_metadata( $id, false, false );

		if ( ! $this->dry_run ) {
			// update migration metadata
			$migration_key_prefix = ! $this->revert ? '_migrated_to_' : '_reverted_to_';
			update_post_meta( $id, $migration_key_prefix . $this->post_type_to, current_time( 'mysql' ) );
		}

		$is_dry_run = $this->dry_run ? '[dry run] ' : '';
		WP_CLI::success( sprintf( __( '%sMigrated post %s with meta keys: %s', 'simplechart' ), $is_dry_run, $id, implode( ', ', $migrated_keys ) ) );
	}

	/**
	 * URL-decodes chart metadata (title,etc)
	 *
	 * ## OPTIONS
	 * <ids>
	 * : Comma-separated post ids
	 *
	 * [--dry-run]
	 * : Simulate migration without updating database
	 *
	 * @synopsis <id> [--dry-run]
	 *
	 * @subcommand fix-metadata
	 */
	public function fix_metadata( $args, $assoc_args = false, $verbose = true ) {
		// will be skipped if already set up, e.g. calling from $this->loop_batch
		$this->setup_migration_opts( $assoc_args );

		if ( is_int( $args ) || is_numeric( $args ) ) {
			$ids = array( absint( $args ) );
		} else if ( is_array( $args ) ) {
			$ids = explode( ',', $args[0] );
		} else {
			$ids = explode( ',', $args );
		}

		$source_key = ! $this->dry_run ? $this->meta_prefix_to . '-data' : $this->meta_prefix_from . '-data';

		foreach ( $ids as $id ) {
			if ( $verbose ) {
				WP_CLI::line( sprintf( __( 'Updating post meta for %s', 'simplechart' ), $id ) );
			}

			$value = get_post_meta( $id, $source_key, true );
			if ( empty( $value ) ) {
				WP_CLI::warning( sprintf( __( 'Post %s: post meta empty for key %s', 'simplechart' ), $id, $source_key ) );
				continue;
			}
			$data = json_decode( $value, true );
			if ( ! empty( $data ) && empty( $data['meta'] ) ) {
				WP_CLI::warning( sprintf( __( 'Post %s: could not locate data.meta object', 'simplechart' ), $id ) );
				continue;
			}
			if ( ! $this->dry_run ) {
				foreach ( $data['meta'] as $key => $value ) {
					$data['meta'][ $key ] = rawurldecode( $value );
				}

				$value = update_post_meta( $id, $source_key, json_encode( $data ) );
			}
		}
	}

	/**
	 * get a list of posts and loop through it, using $method_name as a callback for each found post
	 */
	protected function loop_batch( $query_args, $method_name ) {
		if ( method_exists( $this, $method_name ) ) {
			$callback = array( $this, $method_name );
		} else {
			WP_CLI::error( sprintf( __( 'Invalid batch callback: %s', 'simplechart' ), $method_name ) );
		}

		$finished_batch = false;
		if ( empty( $query_args['paged'] ) ) {
			$query_args['paged'] = 1;
		}

		while ( ! $finished_batch ) {
			// get batch of posts
			$posts = get_posts( $query_args );
			if ( empty( $posts ) ) {
				WP_CLI::warning( sprintf( __( 'Empty `get_posts()` for page %s', 'simplechart' ), $query_args['paged'] ) );
				$finished_batch = true;
				continue;
			}

			// loop through batch
			WP_CLI::line( sprintf( __( "\nProcessing page %s (%s posts)\n---------------------\n", 'simplechart' ), $query_args['paged'], count( $posts ) ) );
			foreach ( $posts as $post ) {
				call_user_func( $callback, $post );
			}

			$query_args['paged']++;
			if ( count( $posts ) < $this->posts_per_batch ) {
				$finished_batch = true;
			}
		}
	}

	/**
	 * sets up migration options like dry-run, and revert, etc
	 */
	protected function setup_migration_opts( $assoc_args ) {
		// already set up
		if ( ! empty( $this->meta_prefix_to ) ) {
			return;
		}

		$this->dry_run = isset( $assoc_args['dry-run'] ) || isset( $assoc_args['dry_run'] );
		$this->revert = isset( $assoc_args['revert'] );

		if ( ! $this->revert ) {
			$this->meta_prefix_to = 'simplechart';
			$this->post_type_to = 'simplechart';
			$this->meta_prefix_from = 'chartbuilder';
			$this->post_type_from = 'chartbuilder_chart';
		} else {
			$this->meta_prefix_to = 'chartbuilder';
			$this->post_type_to = 'chartbuilder_chart';
			$this->meta_prefix_from = 'simplechart';
			$this->post_type_from = 'simplechart';
		}
	}
}
