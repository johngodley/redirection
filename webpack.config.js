/**
 * External dependencies
 */

const fs = require( 'fs' );
const path = require( 'path' );
const webpack = require( 'webpack' );
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
const pkg = require( './package.json' );
const TerserPlugin = require( 'terser-webpack-plugin' );
const MiniCSSExtractPlugin = require( 'mini-css-extract-plugin' );
const RtlCssPlugin = require( '@wordpress/scripts/plugins/rtlcss-webpack-plugin' );
const WebpackShellPluginNext = require( 'webpack-shell-plugin-next' );
const crypto = require( 'crypto' );

const versionHeader = md5 => `<?php

define( 'REDIRECTION_VERSION', '${ pkg.version }' );
define( 'REDIRECTION_BUILD', '${ md5 }' );
define( 'REDIRECTION_MIN_WP', '${ pkg.wordpress.supported }' );
`;

function generateVersion() {
	fs.readFile( path.resolve( __dirname, 'build/redirection.js' ), ( error, data ) => {
		const md5 = crypto
			.createHash( 'md5' )
			.update( data, 'utf8' )
			.digest( 'hex' );

		fs.writeFileSync( path.resolve( __dirname, 'build/redirection-version.php' ), versionHeader( md5 ) );
	} );
}

// Custom RTL CSS Plugin to use redirection naming
class CustomRtlCssPlugin extends RtlCssPlugin {
	processAssets = ( compilation, callback ) => {
		const rtlcss = require( 'rtlcss' );
		const chunks = Array.from( compilation.chunks );

		chunks.forEach( ( chunk ) => {
			const files = Array.from( chunk.files );
			files.filter( filename => path.extname( filename ) === '.css' ).forEach( ( filename ) => {
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

process.env.WP_NO_EXTERNALS = true;

const modified = {
	...defaultConfig,
	output: {
		...defaultConfig.output,
		filename: 'redirection.js',
	},
	externals: {
		'@wordpress/i18n': 'wp.i18n'
	},
	module: {
		...defaultConfig.module,
		rules: defaultConfig.module.rules.map( ( rule ) => {
			// Find the sass-loader and configure it to suppress deprecation warnings
			if ( rule && typeof rule === 'object' && rule.test && rule.test.toString().includes( 'scss' ) && Array.isArray( rule.use ) ) {
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
						if ( typeof loader === 'object' && loader && loader.loader && loader.loader.includes( 'sass-loader' ) ) {
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
		...defaultConfig.plugins.filter( ( plugin ) =>
			!( plugin instanceof MiniCSSExtractPlugin ) &&
			!( plugin instanceof DependencyExtractionWebpackPlugin ) &&
			!( plugin instanceof RtlCssPlugin )
		),
		new MiniCSSExtractPlugin( { filename: 'redirection.css' } ),
		new CustomRtlCssPlugin(),

		new webpack.DefinePlugin( {
			'process.env': { NODE_ENV: JSON.stringify( process.env.NODE_ENV || 'development' ) },
			REDIRECTION_VERSION: "'" + pkg.version + "'",
		} ),

		new WebpackShellPluginNext( {
			onBuildEnd: {
				scripts: [ generateVersion ],
				blocking: true,
				parallel: false
			},
		} )
	],
	resolve: {
		...defaultConfig.resolve,
		alias: {
			...defaultConfig.resolve.alias,
			'@wp-plugin-components': path.resolve( __dirname, 'src/wp-plugin-components' ),
			'@wp-plugin-lib': path.resolve( __dirname, 'src/wp-plugin-lib/' ),
			'lib': path.resolve( __dirname, 'src/lib/' ),
			'component': path.resolve( __dirname, 'src/component/' ),
			'state': path.resolve( __dirname, 'src/state/' ),
			'page': path.resolve( __dirname, 'src/page/' ),
			'app': path.resolve( __dirname, 'src/app/' ),
		}
	},
	optimization: {
		...defaultConfig.optimization,
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
		]
	}
};

module.exports = modified;