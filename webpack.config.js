/*
    ./webpack.config.js
*/
const path = require('path');
module.exports = {
  entry: ['whatwg-fetch', './src/wp-content/themes/wrcgroup-secure/js/react_document_table/src/index.js'],
  output: {
    path: path.resolve('src/wp-content/themes/wrcgroup-secure/js/react_document_table/dist/'),
    filename: 'react_document_table.js',
    publicPath: 'src/wp-content/themes/wrcgroup-secure/js/react_document_table/dist/'
  },
  module: {
    loaders: [
      { test: /\.js$/, loader: 'babel-loader', exclude: /node_modules/ },
      { test: /\.jsx$/, loader: 'babel-loader', exclude: /node_modules/ }
    ]
  }
}