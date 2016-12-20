<?php

/*
 * This class manages when we show alerts to upgrade
 */

class Simplechart_Plugin_Versions {

	private $_simplechart_releases_url = 'https://api.github.com/repos/alleyinteractive/wordpress-simplechart/releases';

	public function __construct() {
		add_action( 'admin_init', array( $this, 'simplechart_check_for_new_version' ) );
		add_action( 'wp_ajax_simplechart_dismiss_nag', array( $this, 'simplechart_dismiss_nag' ) );
	}

	public function simplechart_check_for_new_version() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( false === ( $value = get_transient( 'simplechart_plugin_version_remote' ) ) ) {
			$this->update_simplechart_remote_version();
			return;
		}

		if ( version_compare( Simplechart::instance()->get_config( 'version' ), $value, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'show_simplechart_version_nag' ) );
		}
	}

	public function show_simplechart_version_nag() {
		if ( ! get_option( 'simplechart_nag_dismissed', false ) ) {
			$class = 'simplechart-nag notice notice-warning is-dismissible';
			$message = __( 'There\'s a Simplechart update waiting for you. ', 'simplechart' );
			$url = esc_url( 'https://github.com/alleyinteractive/wordpress-simplechart' );
			$cta = __( 'Update now.', 'simplechart' ) . '</a>';
			printf( '<div class="%1$s"><p>%2$s <a href="%3$s">%4$s</a></p></div>', $class, $message, $url, $cta );
		}
	}

	public function update_simplechart_remote_version() {
		if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
			$response = vip_safe_wp_remote_get( $this->_simplechart_releases_url );
		} else {
			$response = wp_remote_get( $this->_simplechart_releases_url );
		}
		if ( is_wp_error( $response ) ) {
			return;
		}
		$body = wp_remote_retrieve_body( $response );
		$json = json_decode( $body, true );
		if ( null === $json ) {
			return;
		}
		if ( ! empty( $json[0]['tag_name'] ) ) {
			delete_option( 'simplechart_nag_dismissed' );
			set_transient( 'simplechart_plugin_version_remote', $json[0]['tag_name'], DAY_IN_SECONDS );
		}
	}

	public function simplechart_dismiss_nag() {
		update_option( 'simplechart_nag_dismissed', true );
		wp_die();
	}

}
