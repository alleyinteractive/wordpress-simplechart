# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the [Simplechart JS app](https://github.com/alleyinteractive/simplechart) inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data and settings/options are used to bootstrap redrawing the same chart.

### Installation

1. Install and activate the Simplechart plugin
	1. See [theme setup](https://github.com/alleyinteractive/wordpress-simplechart/wiki/Theme-Setup) tips

### Usage

1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click the green "Publish" button to store the chart in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Local Development

The Simplechart Dev Mode plugin in the [dev site repo](https://github.com/alleyinteractive/simplechart-dev-site) is the easiest way to work locally.

There are instructions for local development on the [JS application](https://github.com/alleyinteractive/simplechart-dev-site#local-js-app-development) or the [WordPress plugin](https://github.com/alleyinteractive/simplechart-dev-site#local-wordpress-plugin-development).

Additionally, you can load the Simplechart JS files from `localhost:8080` by:

1. Adding `define( 'SIMPLECHART_USE_LOCALHOST', true );` to your `wp-config.php` file.
1. Setting the `simplechart_use_localhost` filter to `true`

### AMP Considerations

The plugin is compatible with [AMP](https://www.ampproject.org/) pages using the `amp-iframe` [element](https://github.com/ampproject/amphtml/blob/master/extensions/amp-iframe/amp-iframe.md).

Determining when to render the AMP version is handled automatically if you're using the offical [WP AMP plugin](https://wordpress.org/plugins/amp/). If not, you'll need to use the `simplechart_is_amp_page` filter, like:

```
add_action( 'wp', function() {
	if ( my_check_if_this_is_an_amp_page() ) {
		add_filter( 'simplechart_is_amp_page', '__return_true' );
	}
} );
```

AMP requires that the source document of the `amp-iframe` be served over https. _If your site does not support https_, you should disable Simplechart embeds on AMP pages with this snippet:

```
add_filter( 'simplechart_disable_amp', '__return_true' );
```

Additionally, two actions fire while rendering the source document of the `<amp-iframe>`.

`simplechart_iframe_head` and `simplechart_iframe_footer` fire inside the `<head>` and before the closing `</body>` tag, and take the chart's WordPress ID as their only parameter. You can use these actions to add your own custom CSS or JS to the AMP embed.

Use the `simplechart_amp_iframe_placeholder` action to render any markup you need inside the AMP `placeholder` [element](https://github.com/ampproject/amphtml/blob/master/extensions/amp-iframe/amp-iframe.md#iframe-with-placeholder).

### Available WordPress actions and filters

##### simplechart_web_app_iframe_src

Set the `src` attribute of the iframe for creating/editing charts in wp-admin. Defaults to menu page set up by `Simplechart_Post_Type::setup_iframe_page()`

##### simplechart_webpack_public_path

URL of the directory where Webpack assets live. Used for loading chunks and other assets. [More info](https://webpack.github.io/docs/configuration.html#output-publicpath).

##### simplechart_vendor_js_url

Set the URL of the JS bundle container vendor libraries. Defaults to the local static file.

##### simplechart_web_app_js_url

Set the URL of the main JS app for building a chart. Defaults to the local static file.

##### simplechart_widget_loader_url

Set the URL of the chart rendering widget. Defaults to the local static file.

##### simplechart_show_debug_messages

Defaults to `false`. If `true`, the plugin will display extra debugging notices after you save a chart in WordPress admin.

##### simplechart_api_http_headers

Apply any headers to the request made to Simplechart's API before rendering a chart in a front-end template. Useful for dev sites protected by `.htaccess` passwords.

##### simplechart_widget_template

Use different markup structure when rendering a chart in a frontend template. The `.simplechart-*` classes are required to render the chart, title, subtitle, caption, and credit.

##### simplechart_widget_placeholder_text

Text string to use while chart data is loading. If none is provided, will use the JS app's default `Loading`.

##### simplechart_use_localhost

Defaults to false. If true, will load the app and widget from `localhost:8080`

##### simplechart_chart_options_override

Set defaults for NVD3. This is where you'd set a custom palette using the `color` as an array key.

##### simplechart_chart_default_metadata

Set default title, subtitle, caption, or credit.

##### simplechart_is_amp_page

See [AMP considerations](#amp-considerations).

##### simplechart_disable_amp

See [AMP considerations](#amp-considerations).

##### simplechart_iframe_head

See [AMP considerations](#amp-considerations).

##### simplechart_iframe_footer

See [AMP considerations](#amp-considerations).

##### simplechart_amp_iframe_placeholder

See [AMP considerations](#amp-considerations).
