const entry = {
	"script": {
		import: './plg_radicalmart_wishboxcdekorderstatusupdater/es6/script.es6',
		filename: 'script.js',
	},
};

const webpackConfig = require('./webpack.config.js');
const publicPath = '../media';
const production = webpackConfig(entry, publicPath);
const development = webpackConfig(entry, publicPath, 'development');

module.exports = [production, development]