const path = require( 'path' );
const webpack = require( 'webpack' );

// PostCSS plugins
const cssnext = require( 'postcss-cssnext' );
const postcssFocus = require( 'postcss-focus' );
const postcssReporter = require( 'postcss-reporter' );

const isProduction = () => process.env.NODE_ENV === 'production';
const getDevUrl = 'http://localhost:3312/';
const pkg = require( './package.json' );

const config = {
	entry: [
		path.join( __dirname, 'client', 'index.js' ),
	],
	output: {
		path: path.join( __dirname ),
		filename: 'redirection.js',
		chunkFilename: 'redirection-[name]-[chunkhash].js',
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader?cacheDirectory',
			},
			{
				test: /\.json$/,
				loader: 'json-loader'
			},
			{
				test: /\.scss$/,
				exclude: /node_modules/,
				use: [
					'style-loader',
					'css-loader',
					'postcss-loader',
					'sass-loader',
				]
			},
			{
				test: [
					path.resolve( __dirname, 'node_modules/redbox-react' ),
				],
				use: 'null-loader'
			}
		]
	},
	resolve: {
		extensions: [ '.js', '.jsx', '.json', '.scss', '.css' ],
		modules: [ path.resolve( __dirname, 'client' ), 'node_modules' ],
	},
	plugins: [
		new webpack.DefinePlugin( {
			'process.env': { NODE_ENV: JSON.stringify( process.env.NODE_ENV || 'development' ) },
			REDIRECTION_VERSION: "'" + pkg.version + "'",
		} ),
		new webpack.LoaderOptionsPlugin( {
			options: {
				postcss: [
					postcssFocus(),
					cssnext( {
						browsers: [ 'last 2 versions', 'IE > 10' ],
					} ),
					postcssReporter( {
						clearMessages: true
					} ),
				]
			}
		} ),
	],
	watchOptions: {
		ignored: [ /node_modules/ ],
	},
};

if ( isProduction() ) {
	config.plugins.push( new webpack.optimize.UglifyJsPlugin( { compress: { warnings: false, drop_console: true, dead_code: true, unused: true, drop_debugger: true } } ) );
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) );
	config.plugins.push( new webpack.optimize.ModuleConcatenationPlugin() );
	config.module.rules.push( { test: /\.js$/, loader: 'webpack-remove-debug' } );
} else {
	config.output.publicPath = getDevUrl;
	config.devtool = 'inline-source-map';
	config.entry.unshift( 'webpack/hot/only-dev-server' );
	config.entry.unshift( 'webpack-dev-server/client?' + getDevUrl );
	config.entry.unshift( 'react-hot-loader/patch' );
	config.plugins.push( new webpack.NamedModulesPlugin() );
	config.devServer = {
		historyApiFallback: {
			index: '/'
		},
		contentBase: path.resolve( __dirname ),
		publicPath: getDevUrl,
		headers: { 'Access-Control-Allow-Origin': '*' },
		hot: true,
		stats: {
			colors: true,
			hash: false,
			version: true,
			timings: true,
			assets: true,
			chunks: false,
			modules: false,
			reasons: false,
			children: false,
			source: false,
			errors: true,
			errorDetails: true,
			warnings: false,
			publicPath: false
		}
	};
}

module.exports = config;
