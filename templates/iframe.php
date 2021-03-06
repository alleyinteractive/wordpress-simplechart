<?php if ( ! Simplechart::instance()->post_type->current_user_can() ) {
	die( esc_html__( 'Insufficient user capability', 'simplechart' ) );
} ?>

<!DOCTYPE html>
<html>
	<head lang="en">
		<meta charset="UTF-8">
		<title>Simplechart</title>
		<style>
			body {
			margin: 0;
			padding: 0;
			font-family: -apple-system, BlinkMacSystemFont,
				"Segoe UI", "Roboto", "Oxygen", "Ubuntu", "Cantarell",
				"Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif;
			}
			html, body, #app {
				height: 100%;
			}
		</style>
		<script>
			/**
			 * Give React Dev Tools access to iframe
			 */
			if ( window.parent && window.parent.__REACT_DEVTOOLS_GLOBAL_HOOK__ ) {
				window.__REACT_DEVTOOLS_GLOBAL_HOOK__ = window.parent.__REACT_DEVTOOLS_GLOBAL_HOOK__;
			}
			/**
			 * Webpack public path for asset loading
			 */
			 window.__simplechart_public_path__ = <?php echo wp_json_encode( Simplechart::instance()->get_config( 'webpack_public_path' ) ); ?>;
		</script>
	</head>
	<body>
	<div id='app'></div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/d3-annotation/2.2.5/d3-annotation.js"></script>
	<script>
		// We need d3 v3 for NVD3.  
		// We need D3 v4 for Annotations.
		window.d3v4 = d3;
	</script>
	<script src="<?php echo esc_url( Simplechart::instance()->get_config( 'vendor_js_url' ) ); ?>"></script>
	<script src="<?php echo esc_url( Simplechart::instance()->get_config( 'web_app_js_url' ) ); ?>"></script>
	</body>
</html>

<?php die(); ?>
