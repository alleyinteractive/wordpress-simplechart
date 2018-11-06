<?php
/*
Plugin Name: Simplechart
Plugin URI: https://github.com/alleyinteractive/wordpress-simplechart
Description: Create and render interactive charts in WordPress using Simplechart
Author: Dan Bowles, Drew Machat, Josh Kadis, Alley Interactive
Version: 0.5.35
Author URI: http://www.alleyinteractive.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class Simplechart {

	private static $instance;

	private $_plugin_dir_path = null;
	private $_plugin_dir_url = null;
	private $_admin_notices = array(
		'updated' => array(),
		'error' => array(),
	);
	private $_plugin_id = 'wordpress-simplechart/simplechart.php';

	// config vars that will eventually come from settings page
	private $_config = array(
		'web_app_iframe_src' => null,
		'vendor_js_url' => null,
		'web_app_js_url' => null,
		'webpack_public_path' => null,
		'widget_loader_url' => null,
		'menu_page_slug' => 'simplechart_app',
		'version' => '0.5.38',
		'app_version' => 'e5e9c28',
	);

	// startup
	private function __construct() {
		// Both of these will have trailing slash
		$this->_plugin_dir_path = plugin_dir_path( __FILE__ );
		$this->_plugin_dir_url = $this->_set_plugin_dir_url();

		$this->_init_modules();
		add_action( 'init', array( $this, 'action_init' ) );
	}

	/**
	* Static accessor
	*/
	public static function instance() {
		if ( ! is_object( self::$instance ) ) {
			self::$instance = new Simplechart;
		}
		return self::$instance;
	}

	/**
	 * deactivate plugin if it is active
	 */
	public function deactivate() {
		if ( is_plugin_active( $this->_plugin_id ) ) {
			deactivate_plugins( $this->_plugin_id );
		}
	}

	/**
	 * print admin notices, either 'updated' (green) or 'error' (red)
	 */
	public function admin_notices() {
		foreach ( $this->_admin_notices as $class => $notices ) {
			foreach ( $notices as $notice ) {
				printf( '<div class="%s"><p>%s</p></div>', esc_attr( $class ), esc_html( $notice ) );
			}
		}
	}

	/**
	 * get root url and path of plugin, whether loaded from plugins directory or in theme
	 */
	private function _set_plugin_dir_url() {

		// if running as regular plugin (i.e. inside wp-content/plugins/)
		if ( 0 === strpos( $this->_plugin_dir_path, WP_PLUGIN_DIR ) ) {
			$url = plugin_dir_url( __FILE__ );
		} elseif ( function_exists( 'wpcom_vip_get_loaded_plugins' ) && in_array( 'alley-plugins/simplechart', wpcom_vip_get_loaded_plugins(), true ) ) {
			// if running as VIP Classic™ plugin
			$url = plugins_url( '', __FILE__ );
		} else {
			// assume loaded directly by theme
			$path_relative_to_theme = str_replace( get_template_directory(), '', $this->_plugin_dir_path );
			$url = get_template_directory_uri() . $path_relative_to_theme;
		}
		return trailingslashit( $url );
	}

	/**
	 * config getter
	 */
	public function get_config( $key ) {
		return isset( $this->_config[ $key ] ) ? $this->_config[ $key ] : null;
	}

	/**
	 * LOAD MODULES
	 */
	private function _init_modules() {
		// setup simplechart post type
		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-post-type.php' );
		$this->post_type = new Simplechart_Post_Type;

		// setup save post stuff
		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-save.php' );
		$this->save = new Simplechart_Save;

		// load WP Media extension and initialize
		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-insert.php' );
		$this->insert = new Simplechart_Insert;

		// template rendering module
		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-template.php' );
		$this->template = new Simplechart_Template;

		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-request-handler.php' );
		$this->request_handler = new Simplechart_Request_Handler;

		// Handles checking for updates on Github
		require_once( $this->_plugin_dir_path . 'modules/class-simplechart-plugin-versions.php' );
		$this->plugin_versions = new Simplechart_Plugin_Versions;

		// WP-CLI commands
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once( $this->_plugin_dir_path . 'modules/class-simplechart-wp-cli.php' );
		}
	}

	/**
	 * on the 'init' action, do frontend or backend startup
	 */
	public function action_init() {
		// Allow constant or filter to force using localhost for JS files
		$use_localhost = ( defined( 'SIMPLECHART_USE_LOCALHOST' ) && SIMPLECHART_USE_LOCALHOST );

		/**
		 * Determine if we should load the Simplechart JS app from localhost or the plugin's copy
		 *
		 * @param bool $use_localhost Defaults to false unless set to true by a query var
		 */
		$use_localhost = apply_filters( 'simplechart_use_localhost', $use_localhost );

		// Git commit that the app was bundled from
		$commit_version = $this->get_config( 'app_version' );

		// Set URLs for JS app and widget
		if ( $use_localhost ) {
			$this->_config['webpack_public_path'] = 'http://localhost:8080/static/';
			$this->_config['vendor_js_url'] = $this->_config['webpack_public_path'] . 'vendor.js';
			$this->_config['web_app_js_url'] = $this->_config['webpack_public_path'] . 'app.js';
			$this->_config['widget_loader_url'] = $this->_config['webpack_public_path'] . 'widget.js';
		} else {
			$this->_config['webpack_public_path'] = $this->get_plugin_url( 'js/app/' );
			$this->_config['vendor_js_url'] = $this->get_plugin_url( sprintf( 'js/app/vendor.%s.js', $commit_version ) );
			$this->_config['web_app_js_url'] = $this->get_plugin_url( sprintf( 'js/app/app.%s.js', $commit_version ) );
			$this->_config['widget_loader_url'] = $this->get_plugin_url( sprintf( 'js/app/widget.%s.js', $commit_version ) );
		}

		// URL for menu page set up by Simplechart_Post_Type module
		$this->_config['web_app_iframe_src'] = admin_url( '/admin.php?page=' . $this->get_config( 'menu_page_slug' ) . '&noheader' );

		// Filters for app page and JS URLs
		$this->_config['webpack_public_path'] = apply_filters( 'simplechart_webpack_public_path', $this->_config['webpack_public_path'] );
		$this->_config['web_app_iframe_src'] = apply_filters( 'simplechart_web_app_iframe_src', $this->_config['web_app_iframe_src'] );
		$this->_config['vendor_js_url'] = apply_filters( 'simplechart_vendor_js_url', $this->_config['vendor_js_url'] );
		$this->_config['web_app_js_url'] = apply_filters( 'simplechart_web_app_js_url', $this->_config['web_app_js_url'] );
		$this->_config['widget_loader_url'] = apply_filters( 'simplechart_widget_loader_url', $this->_config['widget_loader_url'] );

		if ( is_admin() ) {
			$this->_admin_setup();
		}
	}

	/*
	 * setup /wp-admin functionality
	 */
	private function _admin_setup() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		load_plugin_textdomain( 'simplechart', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );
	}

	public function enqueue_admin_scripts() {
		if ( ! is_admin() ) {
			return;
		}
		wp_register_script( 'simplechart-plugin', $this->get_plugin_url( 'js/plugin/build/plugin.js' ), array( 'jquery' ), $this->_config['version'] );
		wp_register_script( 'simplechart-post-edit', $this->get_plugin_url( 'js/plugin/build/postEdit.js' ), array( 'jquery', 'underscore' ), $this->_config['version'] );
		wp_register_style( 'simplechart-style', $this->_plugin_dir_url . 'css/style.css', array(), $this->_config['version'] );
		wp_enqueue_script( 'simplechart-post-edit' );
		wp_enqueue_style( 'simplechart-style' );
		wp_enqueue_script( 'simplechart-plugin' );
	}

	/**
	 * Get URL of plugin directory, with optional path appended
	 *
	 * @param string $append Optional path to append to the plugin directory URL
	 * @return string URL
	 */
	public function get_plugin_url( $append = '' ) {
		// should already have trailing slash but just to be safe...
		return trailingslashit( $this->_plugin_dir_url ) . ltrim( $append, '/' );
	}

	/**
	 * Get absolute path to plugin directory, with optional path appended
	 *
	 * @param string $append Optional path to append to the plugin directory pth
	 * @return string Path
	 */
	public function get_plugin_dir( $append = '' ) {
		return trailingslashit( $this->_plugin_dir_path ) . ltrim( $append, '/' );
	}

	/**
	 * Clear rewrite rules so they get rebuilt the next time 'init' fires
	 *
	 * @return none
	 */
	public function clear_rules() {
		delete_option( 'rewrite_rules' );
	}
}
Simplechart::instance();

/**
 * Rebuild rewrite rules on de/activation
 */
register_activation_hook( __FILE__, array( Simplechart::instance(), 'clear_rules' ) );
register_deactivation_hook( __FILE__, array( Simplechart::instance(), 'clear_rules' ) );

/**
 * Helper Functions
 */
function simplechart_render_chart( $id ) {
	return Simplechart::instance()->template->render( $id );
}

/**
 * Confirm post type and that chart is either published or embedded in a preview
 *
 * @var int $id Post ID of chart
 * @return bool
 */
function simplechart_can_render( $id ) {
	return 'simplechart' === get_post_type( $id ) && ( 'publish' === get_post_status( $id ) || is_preview() );
}

function simplechart_inline_style_height( $id ) {
	return Simplechart::instance()->template->height_style( $id );
}
