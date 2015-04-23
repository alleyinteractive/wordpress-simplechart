# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the Simplechart app inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data is used to bootstrap redrawing the same chart.

### Installation on WordPress.com VIP

1. Place the plugin in a directory inside your theme, e.g.  `mytheme/inc/wordpress-simplechart`
1. Follow the **Local app setup commands** steps below
1. Add this line to your theme's `functions.php` to "activate" the plugin:

````
require_once( get_template_directory() . '/inc/wordpress-simplechart/simplechart.php' );
````

### Installation on non-VIP sites

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer)
1. Install and activate the Simplechart plugin
1. Follow the **Local app setup commands** steps below

### Usage

1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click "Send to WordPress" button
1. Save the post in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Local app setup commands

Do this from the root of the `wordpress-simplechart` plugin directory, either in your theme or in `wp-plugins`:

````
$ git clone git@github.com:alleyinteractive/simplechart.git
$ mv simplechart/client/pages/ app
$ rm -rf simplechart
````

### Available filters

##### simplechart_web_app_url

URL of the Simplechart web app, e.g.
````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app
````

##### simplechart_loader_js_url

URL of the JS file used to render charts on the front-end, e.g.
````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app/assets/widget/loader.js
````

##### simplechart_web_app_iframe_src

Set the `src` attribute of the iframe for creating/editing charts in wp-admin. Defaults to root-relative for postMessage security reasons, e.g.
````
/wp-content/plugins/wordpress-simplechart/app/#/simplechart
````