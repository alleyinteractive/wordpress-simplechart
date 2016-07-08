# Simplechart for WordPress

Simplechart lets anyone quickly create interactive data visualizations that are easy to embed in any webpage.

### Technical overview

The plugin sets up a custom post type for Charts and launches the Simplechart app inside an iframe. After the user creates a chart through the JS app in the iframe, all the info needed to recreate it (data and settings/options) is sent via postMessage back to the parent page. Then it gets saved in postmeta when the WordPress post is saved.

When the post is rendered on the front end, this same data and settings/options are used to bootstrap redrawing the same chart.

Two Javascript files are required to render charts in page templates. `loader.js` goes first and sets up some configuration stuff, then it loads `app.js` which draws the chart.

### Installation for WordPress.com VIP themes

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer) as a normal plugin in your development environment. It is loaded automatically as part of the platform on WordPress.com.
1. Request _read access_ to the `alley-plugins` VIP repository.
1. cd into `broadway/themes/vip` and:<br>`$ svn co https://vip-svn.wordpress.com/alley-plugins alley-plugins`
1. Add this line in your theme's `functions.php`:<br>`wpcom_vip_load_plugin( 'simplechart', 'alley-plugins' );`

### Installation for non-VIP themes

1. Install and activate [Media Explorer](https://github.com/Automattic/media-explorer)
1. Install and activate the Simplechart plugin

### Usage

1. Your WP Admin area should now have a custom post type for Charts.
1. Click the "Launch Simplechart App" button to create a new chart.
1. When you're happy with your new chart, click "Send to WordPress" button
1. Save the post in WordPress
1. You can now embed the Chart in any post by selecting it from the Charts section in the Media Manager, which will drop a shortcode into the post content.

### Update script for Simplechart web app

In the unlikely event that you need to update the version of the app that lives in the plugin, do this from the root of the `wordpress-simplechart` plugin directory, either in your theme or in `wp-plugins`:

````
$ npm install
$ node simplechart-update.js [--token=<token>] [--deploy-mode=<deploy-mode>] [--branch=<branch>]
````

The command accepts these optional arguments:

`--token=<your GitHub token>` A [GitHub access token](https://github.com/settings/tokens) is **required**. If you do not pass it with the `--token` argument, you can store it in a text file `github_token.txt` in this directory and the script will pick it up from there.

`--deploy-mode=<deploy-mode>` deletes Git files, Node modules, and other stuff not necessary for deploying _and updating_ the plugin. **You probably do not want to use this!** Note that `--deploy-mode` does not require a value, but you can specify `--deploy-mode=vip`. This will skip the check for the Media Explorer plugin, which is part of the platform on WordPress.com VIP.

`--branch=<branch>` allows you to checkout a specific branch of the Simplechart repo before updating the WordPress plugin. Defaults to `master`.

### Available WordPress filters

##### simplechart_widget_dir_url

URL of _directory_ containing the Simplechart front-end rendering widget. *Note* that this directory should contain

```
|-- loader.js
|-- nv.d3.min.css
|-- js
    |-- app.js
```

Defaults to:

````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app/assets/widget/
````

##### simplechart_widget_loader_url

_Full URL_ of the `loader.js` used to render charts on the front-end. Overrides the default `loader.js` URL (see below) _and_ the `simplechart_widget_dir_url` filter. This can be useful for development if you want to load `loader.js` and `js/app.js` from two different hosts.
````
http://www.mysite.com/wp-content/plugins/wordpress-simplechart/app/assets/widget/loader.js
````

##### simplechart_web_app_iframe_src

Set the `src` attribute of the iframe for creating/editing charts in wp-admin. Defaults to root-relative for postMessage security reasons, e.g.
````
/wp-content/plugins/wordpress-simplechart/app/#/simplechart
````
##### simplechart_remove_mexp_default_services

Simplechart is integrated into the WordPress media manager using the [Media Explorer](https://github.com/Automattic/media-explorer) plugin, which adds the ability to embed from services like Twitter and YouTube. By default, Simplechart removes these other services - **except** on WordPress.com VIP, where Media Explorer is part of the platform. To force your desired behavior, use the `'simplechart_remove_mexp_default_services'` filter to return `true` or `false`.

Note for VIP sites: Unless you use this filter to force a consistent value, the value of `'simplechart_remove_mexp_default_services'` will be `true` in your local development environment and `false` on WordPress.com VIP.

##### simplechart_show_debug_messages

Defaults to `false`. If `true`, the plugin will display extra debugging notices after you save a chart in WordPress admin.