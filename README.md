# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the Simplechart app inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data and settings/options are used to bootstrap redrawing the same chart.

### Installation for WordPress.com VIP sites (NOT including VIP Go)

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer) as a normal plugin in your development environment. It is loaded automatically as part of the platform on WordPress.com.
1. Request _read access_ to the `alley-plugins` VIP repository.
1. cd into `broadway/themes/vip` and:<br>`$ svn co https://vip-svn.wordpress.com/alley-plugins alley-plugins`
1. Add this line in your theme's `functions.php`:<br>`wpcom_vip_load_plugin( 'simplechart', 'alley-plugins' );`

### Installation for non-VIP or VIP Go sites

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer)
1. Install and activate the Simplechart plugin

### Usage

1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click "Send to WordPress" button
1. **Publish** the post in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Local Development

If you are working on the [Simplechart JS app](https://github.com/alleyinteractive/simplechart), you can load the main app and widget from `localhost:8080` instead of your WordPress site.

1. Use the query param `?sclocaldev=1`
1. Add `define( 'SIMPLECHART_USE_LOCALHOST', true );` to your `wp-config.php` file.
1. Set the `simplechart_use_localhost` filter to `true`

### Available WordPress filters

##### simplechart_web_app_iframe_src

Set the `src` attribute of the iframe for creating/editing charts in wp-admin. Defaults to menu page set up by `Simplechart_Post_Type::setup_iframe_page()`

##### simplechart_web_app_js_url

Set the URL of the main JS app for building a chart. Defaults to the local static file.

##### simplechart_widget_loader_url

Set the URL of the chart rendering widget. Defaults to the local static file.


##### simplechart_remove_mexp_default_services

Simplechart is integrated into the WordPress media manager using the [Media Explorer](https://github.com/Automattic/media-explorer) plugin, which adds the ability to embed from services like Twitter and YouTube. By default, Simplechart removes these other services - **except** on WordPress.com VIP (Classic™ only, not VIP Go), where Media Explorer is part of the platform. To force your desired behavior, use the `'simplechart_remove_mexp_default_services'` filter to return `true` or `false`.

Note for VIP Classic™ sites: Unless you use this filter to force a consistent value, the value of `'simplechart_remove_mexp_default_services'` will be `true` in your local development environment and `false` on WordPress.com VIP.

##### simplechart_show_debug_messages

Defaults to `false`. If `true`, the plugin will display extra debugging notices after you save a chart in WordPress admin.

##### simplechart_api_http_headers

Apply any headers to the request made to Simplechart's API before rendering a chart in a front-end template. Useful for dev sites protected by `.htaccess` passwords.

##### simplechart_widget_template

Use different markup structure when rendering a chart in a frontend template. The `.simplechart-*` classes are required to render the chart, title, caption, and credit.

##### simplechart_use_localhost

Defaults to false. If true, will load the app and widget from `localhost:8080`

##### simplechart_chart_options_override

Set defaults for NVD3. This is where you'd set a custom palette using the `color` as an array key.

##### simplechart_chart_default_metadata

Set default title, caption, or credit.
