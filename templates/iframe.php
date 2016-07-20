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
  </head>
  <body>
    <div id='app'></div>
    <script src="<?php echo esc_url( Simplechart::instance()->get_config( 'web_app_js_url' ) ); ?>"></script>
  </body>
</html>

<?php die(); ?>
