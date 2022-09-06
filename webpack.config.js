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
    plugins: [
        // Replace the default MiniCSSExtractPlugin with a custom one that doesn't externalise React
        ...defaultConfig.plugins.filter( ( plugin ) => !( plugin instanceof MiniCSSExtractPlugin ) && !( plugin instanceof DependencyExtractionWebpackPlugin ) ),
        new MiniCSSExtractPlugin( { filename: 'redirection.css' } ),

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
            '@wp-plugin-lib': path.resolve( __dirname, 'src/wp-plugin-lib/' )
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