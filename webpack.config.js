var path = require('path');

module.exports = {
  entry: {
    simplechartInsert: './js/plugin/src/simplechart-insert.js',
    postEdit: './js/plugin/src/post-edit.js',
    plugin: './js/plugin/src/plugin.js'
  },
  output: {
    filename: '[name].js',
    path: path.join(__dirname, 'js/plugin/build')
  },
	module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'eslint-loader',
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        loader: 'babel-loader',
        options: {
          presets: ['es2015'],
          plugins: ['transform-object-rest-spread'],
        },
      },
    ]
  }
};