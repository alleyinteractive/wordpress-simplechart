/**
 * Installs the Simplechart WordPress plugin
 * To remove Git and other install files, add `--deploy-mode` when you run the script
 * e.g. `$ node simplechart-install.js --deploy-mode`
 * This makes it easier to deploy to hosts like Pantheon
 */

// Node dependencies
var Git = require('nodegit');
var fs = require('fs');
var path = require('path');
var rimraf = require('rimraf');

// repos needed
mediaExplorerRepo = 'https://github.com/Automattic/media-explorer.git';
simplechartRepo = 'https://github.com/alleyinteractive/simplechart.git';

// setup vars
var GITHUB_TOKEN;
var mediaExplorerPath;
var simplechartPath = __dirname + '/app';
var simplechartTmp = __dirname + '/_tmp_simplechart';

// install files to be deleted
var installFiles = [
  '.gitignore',
  'github_token.txt',
  'package.json'
];
var installDirs = [
  'node_modules',
  '.git'
];

/**
 * if requested by including argument `--deploy-mode`, delete .git and other files needed for install
 * this makes it easier to deploy to hosts like Pantheon, but harder to do local development
 */
function deleteInstallFiles() {
  if (process.argv.indexOf('--deploy-mode') === -1) {
    console.log('Install files NOT deleted')
    return;
  }
  console.log('Deleting install files:');
  installFiles.forEach(function(value) {
    console.log(value);
    fs.unlinkSync(value);
  });
  installDirs.forEach(function(value) {
    console.log(value + '/');
    rimraf.sync(value);
  });
}

/**
 * get path to the plugins directory where we want to install Media Explorer
 */
 function getMediaExplorerPath() {
  var pluginsDir = [];

  // if we are in the plugins/ directory already
  if ('plugins' === __dirname.split('/').reverse()[1]) {
    pluginsDir = __dirname.split('/');
    pluginsDir.pop();
  }
  // if we are somewhere else, i.e. a theme
  else {
    var wpContentFound = false;
    __dirname.split('/').forEach(function(value, key){
      // move upwards through directories until we get to wp-content/
      if (!wpContentFound) {
        pluginsDir.push(value);
      }
      if ('wp-content' == value) {
        wpContentFound = true;
      }
    });
    // then add plugins/
    pluginsDir.push('plugins');
  }

  // should now be an array of directories leading up to wp-content/plugins/
  pluginsDir.push('media-explorer');
  return pluginsDir.join('/');
 }

/**
 * required for Github HTTPS public repo cloning
 */
function githubHttpsCloneOptions() {
  var  cloneOptions = {};
  cloneOptions.remoteCallbacks = {
    certificateCheck: function() {
      return 1;
    }
  };
  return cloneOptions;
}

/**
 * pull down a fresh install of Simplechart web app no matter what
 */
function setupLocalSimplechart() {
  var  cloneOptions = githubHttpsCloneOptions();
  // used API token for private repo cloning
  cloneOptions.remoteCallbacks.credentials = function(url, userName) {
    return Git.Cred.userpassPlaintextNew(GITHUB_TOKEN, 'x-oauth-basic');
  }

  // delete existing stuff
  rimraf.sync(simplechartTmp);
  rimraf.sync(simplechartPath);

  console.log('Downloading Simplechart repo');
  Git.Clone.clone(simplechartRepo, simplechartTmp, cloneOptions).done(function(){
    // move the standalone web app then delete the temp folder
    console.log('Moving web app');
    fs.renameSync(simplechartTmp + '/client/pages', simplechartPath);
    console.log('Deleting temp folder');
    rimraf.sync(simplechartTmp);
    deleteInstallFiles();
    console.log('Setup complete!');
    process.exit(1);
  });
}

/**
 * install Media Explorer plugin if not already installed
 */
function installMediaExplorer(err, files) {
  if (typeof files === 'undefined' || files.length === 0) {
    console.log('Installing Media Exlorer plugin');
    var  cloneOptions = githubHttpsCloneOptions();
    Git.Clone.clone(mediaExplorerRepo, mediaExplorerPath, cloneOptions);
  }
  else {
    console.log('Media Explorer plugin already installed');
  }
}

// start by trying to get Github API token from github_token.txt
fs.readFile('github_token.txt', {encoding: 'utf8'}, function(err, data) {
  if (err) throw err;
  GITHUB_TOKEN = data;
  mediaExplorerPath = getMediaExplorerPath();
  fs.readdir(mediaExplorerPath, installMediaExplorer);
  setupLocalSimplechart();
});
