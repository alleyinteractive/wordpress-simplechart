<?php

/*
 * This class manages what happens inside the "Chart" post type
 */

class Simplechart_Post_Type {

	private $_app_cap = 'edit_posts';

	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'admin_menu', array( $this, 'setup_iframe_page' ) );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'remove_menu_link' ), 999 );
		add_filter( 'enter_title_here', array( $this, 'enter_title_here' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices_placeholder' ) );
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
		add_action( 'wp_print_scripts', array( $this, 'disable_autosave' ) );
	}

	/**
	 * Prevent autosaving since it doesn't save any of the chart data need
	 * and blocks our custom Publish/Update button
	 */
	public function disable_autosave() {
		if ( 'simplechart' === get_post_type() ) {
			wp_deregister_script( 'autosave' );
		}
	}

	public function enter_title_here( $text ) {
		if ( 'simplechart' === get_post_type() ) {
			$text = __( 'Enter WordPress internal identifier', 'simplechart' );
		}
		return $text;
	}

	public function admin_notices_placeholder() {
		if ( 'simplechart' === get_post_type() ) {
			echo '<div id="simplechart-admin-notices"></div>';
		}
	}

	public function action_add_meta_boxes() {
		remove_meta_box( 'submitdiv', 'simplechart', 'side' );

		add_meta_box( 'simplechart-save',
			__( 'Save Chart', 'simplechart' ),
			array( $this, 'render_submit_button' ),
			'simplechart',
			'side',
			'default'
		);

		add_meta_box( 'simplechart-preview',
			__( 'Simplechart', 'simplechart' ),
			array( $this, 'render_meta_box' ),
			'simplechart',
			'normal',
			'default'
		);
	}

	public function render_submit_button() {
		global $post;
		if ( in_array( $post->post_status, array( 'pending', 'publish', 'draft' ) ) ) {
			$button_text = __( 'Update', 'simplechart' );
		} else {
			$button_text = __( 'Publish', 'simplechart' );
		}
		submit_button( $button_text, 'primary', 'publish', false );
		wp_nonce_field( 'simplechart_save', 'simplechart-nonce' );
	}

	public function register_post_type() {
		$args = array(
			'labels' => array(
				'name' => esc_html__( 'Charts', 'simplechart' ),
				'singular_name' => esc_html__( 'Chart', 'simplechart' ),
				'plural_name' => esc_html__( 'All Charts', 'simplechart' ),
				'add_new' => esc_html__( 'Add New', 'simplechart' ),
				'add_new_item' => esc_html__( 'Add New Chart', 'simplechart' ),
				'edit_item' => esc_html__( 'Edit Chart', 'simplechart' ),
				'new_item' => esc_html__( 'New Chart', 'simplechart' ),
				'view_item' => esc_html__( 'View Chart', 'simplechart' ),
				'search_items' => esc_html__( 'Search Charts', 'simplechart' ),
				'not_found' => esc_html__( 'No charts found', 'simplechart' ),
				'not_found_in_trash' => esc_html__( 'No charts found in Trash', 'simplechart' ),
			),

			// external publicness
			'public' => true,
			'exclude_from_search' => true,
			'publicly_queryable' => true,

			// wp-admin publicness
			'show_in_nav_menus' => false,
			'show_ui' => true,

			// just below Media
			//'menu_position' => 11,

			// enable single pages without permalink for checking template rendering
			'rewrite' => false,
			'has_archive' => false,

			'menu_icon' => 'dashicons-chart-pie',
			'supports' => array( 'title' ),
		);

		register_post_type( 'simplechart', $args );
	}

	public function render_meta_box( $post, $args ) {
		require_once( Simplechart::instance()->get_plugin_dir( 'templates/meta-box.php' ) );
	}

	public function setup_iframe_page() {
		add_menu_page( 'Simplechart App', 'Simplechart App', $this->_app_cap, 'simplechart_app', array( $this, 'render_iframe_page' ) );
	}

	public function render_iframe_page() {
		require_once( Simplechart::instance()->get_plugin_dir() . 'templates/iframe.php' );
	}

	/**
	 * remove menu page from wp-admin nav since we're only creating it for the iframe when creating/editing a chart
	 */
	public function remove_menu_link( $menu_list ) {

		// remove from flat array of slugs for menu order
		$menu_list = $this->_remove_slug_from_menu_order( $menu_list );

		// remove from global 'default' menu order
		global $default_menu_order;
		$default_menu_order = $this->_remove_slug_from_menu_order( $default_menu_order );

		// remove from global menu
		global $menu;
		if ( ! empty( $menu ) && is_array( $menu ) ) {
			foreach ( $menu as $index => $item ) {
				if ( ! empty( $item[2] ) && Simplechart::instance()->get_config( 'menu_page_slug' ) === $item[2] ) {
					unset( $menu[ $index ] );
					$menu = array_values( $menu );
					break;
				}
			}
		}

		return $menu_list;
	}

	private function _remove_slug_from_menu_order( $array ) {
		if ( ! empty( $array ) && is_array( $array ) ) {
			$index = array_search( Simplechart::instance()->get_config( 'menu_page_slug' ), $array, true );
			if ( false !== $index ) {
				unset( $array[ $index ] );
				$array = array_values( $array );
			}
		}
		return $array;
	}

	/**
	 * use capability for this feature
	 */
	public function current_user_can() {
		return current_user_can( $this->_app_cap );
	}
}
