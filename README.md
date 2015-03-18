# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the Simplechart app inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data is used to bootstrap redrawing the same chart.

### Usage

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer) if you're not on WordPress.com
1. Install and activate Simplechart
1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click "Send to WordPress" button
1. Save the post in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Installation

To secure the postMessage interface between wp-admin and the the Simplechart JS app, there are a few different ways to configure the plugin depending on how your site is hosted.

#### Case #1: `http://*.wordpress.com` (https is cool, too)

Congratulations on being a WordPress.com VIP! `*.wordpress.com` domains are whitelisted.

Place the plugin in a directory inside your theme, e.g.  `mytheme/plugins/wordpress-simplechart`

In your theme's `functions.php`:
`require_once( __DIR__ . '/plugins/wordpress-simplechart/simplechart.php' );`

The Simplechart JS app will be loaded from the default URL:
`http://simplechart.io/#/simplechart`

#### Case #2: Any other domain

For non-VIP sites, the JS app and plugin must be on the same origin. You can load the WordPress plugin either from your `wp-content/plugins` directory or by requiring in your theme as above.

Then clone https://github.com/alleyinteractive/simplechart into a directory in your theme like `mytheme/plugins/wordpress-simplechart/app`

You might need to switch to the `gh-pages` branch

In your WordPress theme `functions.php`:
`define( 'SIMPLECHART_APP_URL_ROOT', get_template_directory_uri() . '/wordpress-simplechart/app' );`

#### Case #3: Local development

If you need to troubleshoot the cross-domain postMessage interface. Get Simplechart running locally on http://localhost:5000

Your WordPress install should be on one of these whitelisted domains:

* `http://simplechart.dev`,
* `http://simplechart.wp.dev`,
* `http://localhost`, (any port is okay)
* `http://vip.local`

Add this to your wp-config.php (or wp-config-local.php if that's how you roll):

`define( 'SIMPLECHART_APP_URL_ROOT', 'http://localhost:5000' );`