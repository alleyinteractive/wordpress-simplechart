var Git = require('nodegit');
var fs = require('fs');
var path = require('path');
var rimraf = require('rimraf');

var mediaExplorerPath = '../media-explorer';
var simpleChartPath = 'app';

/**
 * do a fresh intall of local simplechart app no matter what
 */
function setupLocalSimplechart() {
	// required for Github HTTPS repo cloning
	var GITHUB_TOKEN = '22b250143d3a6d1d20d81aa448362ee7225476ef';
	var	cloneOptions = {};
	cloneOptions.remoteCallbacks = {
		certificateCheck: function() { return 1; },
		credentials: function(url, userName) {
			return Git.Cred.userpassPlaintextNew(GITHUB_TOKEN, 'x-oauth-basic');
		}
	};

	var tmpLocation = __dirname + '/_tmp_simplechart';
	console.log('Installing Simplechart web app in ' + tmpLocation );
	Git.Clone('https://github.com/alleyinteractive/simplechart.git', tmpLocation, cloneOptions )
		.then( function(repo){
			console.log( 'Moving web app to ' + __dirname + '/app' );
			fs.renameSync( tmpLocation + '/client/pages', __dirname + '/app' );
			rimraf( tmpLocation, function(err){
				if (err) throw err;
				process.exit(1);
			});
		});
}

/**
 * install Media Explorer plugin if not already installed
 */
function installMediaExplorer(err, files) {
	// required for Github HTTPS public repo cloning
	var	cloneOptions = {};
	cloneOptions.remoteCallbacks = {
		certificateCheck: function() { return 1; }
	};

	if ( typeof files === 'undefined' || files.length === 0 ) {
		console.log( 'Installing Media Exlorer' );
		Git.Clone('https://github.com/Automattic/media-explorer.git', __dirname + '/' + mediaExplorerPath, cloneOptions )
			.then( function(repo){
				setupLocalSimplechart();
			});
	} else {
		console.log( 'Media Explorer already installed' );
		setupLocalSimplechart();
	}
}

// install Media Explorer
fs.readdir( mediaExplorerPath, installMediaExplorer );