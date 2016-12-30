<?php

/*
 * This class manages when we show alerts to upgrade
 */

class Simplechart_Plugin_Versions {

	/**
	 * @var string URL for API requests to get new releases info
	 */
	private $_simplechart_releases_url = 'https://api.github.com/repos/alleyinteractive/wordpress-simplechart/releases';

	/**
	 * @var string URL for Sumplechart repo
	 */
	private $_simplechart_repository_url = 'https://github.com/alleyinteractive/wordpress-simplechart/';

	/**
	 * @var string latest remote version of the plugin
	 */
	private $_latest_plugin_version;

	/**
	 * @var string URL pointing to the latest release ZIP
	 */
	private $_latest_plugin_zip_url;

	/**
	 * @var string Title of the latest release
	 */
	private $_latest_plugin_update_name;

	/**
	 * @var string Description of the latest release
	 */
	private $_latest_plugin_update_description;

	/**
	 * @var string Date/time of the last plugin update
	 */
	private $_latest_plugin_last_updated;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_for_new_version' ) );
		add_filter( 'plugins_api_result', array( $this, 'mock_plugins_api' ), 10, 3 );
	}

	/**
	 * @return boolean True/false whether a Simplechart update is avaialble
	 */
	private function update_available() {
		return version_compare( Simplechart::instance()->get_config( 'version' ), $this->_latest_plugin_version, '<' );
	}

	/**
	 * Compares remote version transient to current version checking for update
	 */
	public function check_for_new_version() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( false === ( $this->_latest_plugin_version = get_transient( 'simplechart_plugin_version_remote' ) ) ) {
			$this->update_simplechart_remote_metadata();
			return;
		}

		if ( false === ( $this->_latest_plugin_zip_url = get_transient( 'simplechart_plugin_zip_url_remote' ) ) ) {
			$this->_latest_plugin_zip_url = $this->_simplechart_repository_url;
		}

		if ( false === ( $this->_latest_plugin_update_name = get_transient( 'simplechart_plugin_update_name' ) ) ) {
			$this->_latest_plugin_update_name = __( 'Latest Update', 'simplechart' );
		}

		if ( false === ( $this->_latest_plugin_update_description = get_transient( 'simplechart_plugin_update_body' ) ) ) {
			$this->_latest_plugin_update_description = __( 'See more on Github.', 'simplechart' );
		}

		$this->_latest_plugin_last_updated = get_transient( 'simplechart_plugin_update_last_updated' );

		if ( $this->update_available() ) {
			add_filter( 'site_transient_update_plugins', array( $this, 'extend_filter_update_plugins' ) );
		}
	}

	/**
	 * Add Simplechart to the plugin updates transient when appropraite
	 * @param  object          $update_plugins Contains plugin update info
	 * @return object          $update_plugins Contains plugin update info, plus Simplechart
	 */
	public function extend_filter_update_plugins( $update_plugins ) {
		if ( ! is_object( $update_plugins ) ) {
			return $update_plugins;
		}

		if ( ! isset( $update_plugins->response ) || ! is_array( $update_plugins->response ) ) {
			$update_plugins->response = array();
		}

		$update_plugins->response['wordpress-simplechart/simplechart.php'] = (object) array(
			'slug'         => 'wordpress-simplechart',
			'new_version'  => $this->_latest_plugin_version,
			'url'          => $this->_simplechart_repository_url,
		);
		return $update_plugins;
	}

	/**
	 * Intercept the plugins API result and return Simplechart remote information
	 * @param object|WP_Error $res    Response object or WP_Error.
	 * @param string          $action The type of information being requested from the Plugin Install API.
	 * @param object          $args   Plugin API arguments.
	 * @return object         $args   Mock response object for Simplechart
	 */
	public function mock_plugins_api( $res, $action, $args ) {
		if ( 'plugin-information' !== $action && 'wordpress-simplechart' !== $args->slug ) {
			return $res;
		}
		$copy_generated = sprintf(
			__( 'Ask an administrator to replace your existing Simplechart plugin directory with <a href="%1$s">the ZIP of %2$s via Github</a>', 'simplechart' ),
			esc_url( $this->_latest_plugin_zip_url ),
			esc_html( $this->_latest_plugin_version )
		);
		$description = sprintf(
			__( '<small>%1$s</small><h1>%2$s</h1>%3$s', 'simplechart' ),
			$copy_generated,
			esc_html( $this->_latest_plugin_update_name ),
			esc_html( $this->_latest_plugin_update_description )
		);

		return (object) array(
			'name'         => __( 'Simplechart', 'simplechart' ),
			'banners'      => array(
				'high'       => null,
				'low'        => null,
			),
			'external'     => $this->_simplechart_repository_url,
			'homepage'     => $this->_simplechart_repository_url,
			'slug'         => 'wordpress-simplechart',
			'version'      => $this->_latest_plugin_version,
			'last_updated' => $this->_latest_plugin_last_updated,
			'sections'     => array(
				'changelog'  => $description,
			),
		);
	}

	/**
	 * Update the transients for Simplechart remote version
	 */
	public function update_simplechart_remote_metadata() {
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$response = vip_safe_wp_remote_get( $this->_simplechart_releases_url );
		} else {
			$response = wp_remote_get( $this->_simplechart_releases_url );
		}

		if ( is_wp_error( $response ) ) {
			return;
		}

		if( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
			return;
		}

		$json = json_decode( $body, true );

		if ( null === $json ) {
			return;
		}

		if ( ! empty( $json[0]['tag_name'] ) ) {
			set_transient( 'simplechart_plugin_version_remote', $json[0]['tag_name'], DAY_IN_SECONDS );
			set_transient( 'simplechart_plugin_zip_url_remote', $json[0]['zipball_url'], DAY_IN_SECONDS );
			set_transient( 'simplechart_plugin_update_name', $json[0]['name'], DAY_IN_SECONDS );
			set_transient( 'simplechart_plugin_update_last_updated', $json[0]['published_at'], DAY_IN_SECONDS );
			set_transient( 'simplechart_plugin_update_body', $json[0]['body'], DAY_IN_SECONDS );
		}
	}

}
