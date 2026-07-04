const path = require( 'path' );
const webpack = require( 'webpack' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const pkg = require( './package.json' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const RtlCssPlugin = require( '@wordpress/scripts/plugins/rtlcss-webpack-plugin' );
const { BundleAnalyzerPlugin } = require( 'webpack-bundle-analyzer' );

function isDefaultCssPlugin( plugin ) {
	const pluginName = plugin?.constructor?.name;

	return (
		plugin instanceof MiniCSSExtractPlugin ||
		pluginName === MiniCSSExtractPlugin.name ||
		plugin instanceof RtlCssPlugin ||
		pluginName === RtlCssPlugin.name ||
		pluginName === 'RtlCSSPlugin'
	);
}

// Custom RTL CSS Plugin to use redirection naming
class CustomRtlCssPlugin extends RtlCssPlugin {
	processAssets = ( compilation, callback ) => {
		const rtlcss = require( 'rtlcss' );
		const chunks = Array.from( compilation.chunks );

		chunks.forEach( ( chunk ) => {
			const files = Array.from( chunk.files );
			files
				.filter( ( filename ) => path.extname( filename ) === '.css' )
				.forEach( ( filename ) => {
					const src = compilation.assets[ filename ].source();
					const dst = rtlcss.process( src );
					// Use custom naming: redirection.css -> redirection-rtl.css
					const dstFileName = filename.replace( /\.css$/, '-rtl.css' );

					compilation.assets[ dstFileName ] = new webpack.sources.RawSource( dst );
					chunk.files.add( dstFileName );
				} );
		} );

		callback();
	};
}

const modified = {
	...defaultConfig,
	output: {
		...defaultConfig.output,
		filename: 'redirection.js',
	},
	module: {
		...defaultConfig.module,
		rules: defaultConfig.module.rules.map( ( rule ) => {
			// Find the sass-loader and configure it to suppress deprecation warnings
			if (
				rule &&
				typeof rule === 'object' &&
				rule.test &&
				rule.test.toString().includes( 'scss' ) &&
				Array.isArray( rule.use )
			) {
				return {
					...rule,
					use: rule.use.map( ( loader ) => {
						// Handle both string and object loaders
						if ( typeof loader === 'string' && loader.includes( 'sass-loader' ) ) {
							return {
								loader,
								options: {
									sassOptions: {
										quietDeps: true,
										silenceDeprecations: [ 'import' ],
									},
								},
							};
						}
						if (
							typeof loader === 'object' &&
							loader &&
							loader.loader &&
							loader.loader.includes( 'sass-loader' )
						) {
							return {
								...loader,
								options: {
									...( loader.options || {} ),
									sassOptions: {
										...( loader.options?.sassOptions || {} ),
										quietDeps: true,
										silenceDeprecations: [ 'import' ],
									},
								},
							};
						}
						return loader;
					} ),
				};
			}
			return rule;
		} ),
	},
	ignoreWarnings: [
		{
			module: /\.scss$/,
			message: /Sass @import rules are deprecated/,
		},
	],
	plugins: [
		// Replace the default MiniCSSExtractPlugin and RtlCssPlugin with custom ones
		...defaultConfig.plugins.filter( ( plugin ) => ! isDefaultCssPlugin( plugin ) ),
		new MiniCSSExtractPlugin( { filename: 'redirection.css' } ),
		new CustomRtlCssPlugin(),

		new webpack.DefinePlugin( {
			'process.env': { NODE_ENV: JSON.stringify( process.env.NODE_ENV || 'development' ) },
			REDIRECTION_VERSION: "'" + pkg.version + "'",
		} ),
		// Add bundle analyzer when ANALYZE env var is set
		...( process.env.ANALYZE ? [ new BundleAnalyzerPlugin() ] : [] ),
	],
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			'@wp-plugin-components': path.resolve( __dirname, 'src/wp-plugin-components' ),
			'@wp-plugin-lib': path.resolve( __dirname, 'src/wp-plugin-lib/' ),
			lib: path.resolve( __dirname, 'src/lib/' ),
			component: path.resolve( __dirname, 'src/component/' ),
			state: path.resolve( __dirname, 'src/state/' ),
			page: path.resolve( __dirname, 'src/page/' ),
			app: path.resolve( __dirname, 'src/app/' ),
			types: path.resolve( __dirname, 'src/types/' ),
			stores: path.resolve( __dirname, 'src/stores/' ),
		},
	},
	optimization: {
		...defaultConfig.optimization,
		usedExports: true,
		sideEffects: true,
		minimize: true,
		minimizer: [
			new TerserPlugin( {
				parallel: true,
				terserOptions: {
					output: {
						comments: /translators:/i,
					},
					compress: {
						passes: 2,
					},
					mangle: {
						reserved: [ '__', '_n', '_nx', '_x' ],
					},
				},
				extractComments: {
					condition: true,
					banner: () => {
						return 'Redirection v' + pkg.version + ' - please refer to license.txt for license information';
					},
				},
			} ),
		],
	},
};

module.exports = modified;
