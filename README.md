# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the Simplechart app inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data is used to bootstrap redrawing the same chart.

### Installation on WordPress.com VIP

1. Place the plugin in a directory inside your theme, e.g.  `mytheme/inc/wordpress-simplechart`
1. Add this line to your theme's `functions.php` to "activate" the plugin:

````
require_once( get_template_directory() . '/inc/wordpress-simplechart/simplechart.php' );
````

### Installation on non-VIP sites

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer)
1. Install and activate the Simplechart plugin

### Usage

1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click "Send to WordPress" button
1. Save the post in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Update script for Simplechart web app

Do this from the root of the `wordpress-simplechart` plugin directory, either in your theme or in `wp-plugins`:

````
$ npm install
$ node simplechart-update.js
````

This script will download the Simplechart web app to your local copy of the WordPress plugin. It **requires** a [GitHub access token](https://github.com/settings/tokens), which you can pass to the script with the command line argument `--token=<your GitHub token>` or by putting it in a file `github_token.txt`

If you want to install the plugin for deployment to Pantheon or other Git-based hosts, there is a `--deploy-mode` flag. Use cautiously, as it will delete the plugin's Git files and other stuff necessary for reinstallation. If you think you might be doing local development, **do not use this option!**

### Available WordPress filters

##### simplechart_web_app_url

URL of the Simplechart web app. This is used to locate the `assets/widget/loader.js` script (unless overridden by the `'simplechart_loader_js_url'` filter) and then by `loader.js` to find `assets/widget/js/app.js`.
````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app
````

##### simplechart_loader_js_url

URL of the JS file used to render charts on the front-end. Override the default location of `loader.js` by providing the full URL of the script.
````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app/assets/widget/loader.js
````

##### simplechart_web_app_iframe_src

Set the `src` attribute of the iframe for creating/editing charts in wp-admin. Defaults to root-relative for postMessage security reasons, e.g.
````
/wp-content/plugins/wordpress-simplechart/app/#/simplechart
````