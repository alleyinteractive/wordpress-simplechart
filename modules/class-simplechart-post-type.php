<?php

/*
 * This class manages what happens inside the "Chart" post type
 */

class Simplechart_Post_Type {

	private $_iframe_slug = 'simplechart_app';
	private $_app_cap = 'edit_posts';

	public function __construct(){
		add_action( 'init', array( $this, 'register_post_type' ) );
		add_action( 'after_setup_theme', array( $this, 'support_thumbnails' ) );
		add_action( 'init', array( $this, 'setup_iframe_page' ) );
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
		$chart_url = get_post_meta( $post->ID, 'simplechart-chart-url', true );
		$chart_id = get_post_meta( $post->ID, 'simplechart-chart-id', true );
		$app_url = Simplechart::instance()->get_config( 'web_app_iframe_src' );
		$assets_url = Simplechart::instance()->get_plugin_url() . 'app/';

		$html = sprintf( $meta_box_html,
			__( 'Launch Simplechart App', 'simplechart' ),
			__( 'Clear Simplechart Data', 'simplechart' ),
			esc_url( $app_url ),
			esc_url( $assets_url ),
			'simplechart-data',
			json_encode( json_decode( $json_data ) ), // escapes without converting " to &quot
			Simplechart::instance()->save->validate_template_fragment( $template_html ),
			__( 'Close Modal', 'simplechart' ),
			esc_attr( $nonce ),
			json_encode( json_decode( $json_data ) ),
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
		require_once( Simplechart::instance()->get_plugin_dir() . 'app/index.php' );
	}

	/**
	 * remove menu page from wp-admin nav since we're only creating it for the iframe when creating/editing a chart
	 */
	public function remove_menu_link( $menu_list ) {
		$index = array_search( $this->_iframe_slug, $menu_list, true );
		if ( false !== $index ) {
			unset( $menu_list[ $index ] );
		}
		return $menu_list;
	}

	/**
	 * getter for iframe src
	 * @return string path to use as src
	 */
	public function get_web_app_iframe_src() {
		return '/wp-admin/admin.php?page=' . $this->_iframe_slug . '&noheader#/simplechart';
	}

	/**
	 * use capability for this feature
	 */
	public function current_user_can() {
		return current_user_can( $this->_app_cap );
	}

}
