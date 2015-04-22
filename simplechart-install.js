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
var mediaExplorerPath = __dirname + '/../media-explorer';
var simplechartPath = __dirname + '/app';
var simplechartTmp = __dirname + '/_tmp_simplechart';

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

  console.log('Downloading Simplechart repo to ' + simplechartTmp);
  Git.Clone(simplechartRepo, simplechartTmp, cloneOptions).then(function(repo) {
    // move the standalone web app then delete the temp folder
    console.log('Moving web app to ' + simplechartApp);
    fs.renameSync(simplechartTmp + '/client/pages', simplechartApp);
    console.log('Deleting ' + simplechartTmp);
    rimraf.sync(simplechartTmp);
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
    Git.Clone(mediaExplorerRepo, mediaExplorerPath, cloneOptions)
      .then(function(repo) {
        setupLocalSimplechart();
      });
  }
  else {
    console.log('Media Explorer plugin already installed');
    setupLocalSimplechart();
  }
}

// start by trying to get Github API token from github_token.txt
fs.readFile('github_token.txt', {encoding: 'utf8'}, function(err, data) {
  if (err) throw err;
  GITHUB_TOKEN = data;
  fs.readdir(mediaExplorerPath, installMediaExplorer);
});
