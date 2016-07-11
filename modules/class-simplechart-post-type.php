<?php

/*
 * This class manages what happens inside the "Chart" post type
 */

class Simplechart_Post_Type {

	private $_app_cap = 'edit_posts';

	public function __construct(){
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'after_setup_theme', array( $this, 'support_thumbnails' ) );
		add_action( 'admin_menu', array( $this, 'setup_iframe_page' ) );
		add_filter( 'custom_menu_order', '__return_true' );
		add_filter( 'menu_order', array( $this, 'remove_menu_link' ), 999 );
	}

	public function support_thumbnails(){
		if ( ! current_theme_supports( 'post-thumbnails' ) ){
			add_theme_support( 'post-thumbnails', array( 'simplechart' ) );
		}
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
			'supports' => array( 'title', 'thumbnail' )
		);

		register_post_type( 'simplechart', $args );
	}

	public function render_meta_box( $post, $args ) {
		$plugin_dir_path = $args['args'][0];
		$json_data = $args['args'][1];
		$meta_box_html = file_get_contents( $plugin_dir_path . 'templates/meta-box.html' );
		$nonce = wp_create_nonce( 'simplechart_save' );
		$template_html = get_post_meta( $post->ID, 'simplechart-template', true );
		$app_url = Simplechart::instance()->get_config( 'web_app_iframe_src' );
		$assets_url = Simplechart::instance()->get_plugin_url( 'app/' );

		// escapes without converting " to &quot
		$validated_json_data = json_encode( json_decode( $json_data ) );

		$html = sprintf( $meta_box_html,
			__( 'Launch Simplechart App', 'simplechart' ),
			__( 'Clear Simplechart Data', 'simplechart' ),
			esc_url( $app_url ),
			esc_url( $assets_url ),
			'simplechart-data',
			$validated_json_data, // is printed in a script tag, so no htmlentities conversion
			Simplechart::instance()->save->validate_template_fragment( $template_html ),
			__( 'Close Modal', 'simplechart' ),
			__( 'Confirming this message will proceed without saving changes. If you have made changes that you wish to save, cancel this message, proceed to the final step, and click the Save Chart button.', 'simplechart' ),
			esc_attr( $nonce ),
			htmlentities( $validated_json_data, ENT_COMPAT ), // printed in a <input> value attr, so convert " to &quot
			esc_attr( $template_html ),
			esc_url( $chart_url ),
			esc_attr( $chart_id )
		);

		echo $html;
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
				if ( ! empty( $item[2] ) && $item[2] === Simplechart::instance()->get_config( 'menu_page_slug' ) ) {
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
