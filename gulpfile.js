const gulp = require( 'gulp' );
const i18n_calypso = require( 'i18n-calypso/cli' );
const po2json = require( 'gulp-po2json' );
const wpPot = require( 'gulp-wp-pot' );
const sort = require( 'gulp-sort' );
const path = require( 'path' );
const globby = require( 'globby' );
const fs = require( 'fs' );
const zip = require( 'gulp-zip' );
const config = require( './.config.json' ); // Local config
const pkg = require( './package.json' );

const SVN_SOURCE_FILES = [
	'./**',
	'!node_modules/**',
	'!node_modules',
	'!bin/**',
	'!bin',
	'!client/**',
	'!client',
	'!tests/**',
	'!tests',
	'!yarn.lock',
	'!package.json',
	'!gulpfile.js',
	'!postcss.config.js',
	'!README.md',
	'!phpunit.xml',
	'!webpack.config.js',
	'!package-lock.json',
];

gulp.task( 'pot', [ 'pot:extract', 'pot:generate', 'pot:json' ] );

gulp.task( 'pot:json', done => {
	gulp.src( [ 'locale/*.po' ] )
		.pipe( po2json() )
		.pipe( gulp.dest( 'locale/json/' ) )
		.on( 'end', done );
} );

gulp.task( 'pot:extract', () => {
	globby( [ 'client/**/*.js', '!client/lib/polyfill/index.js' ] )
		.then( files => {
			let result = i18n_calypso( {
				projectName: 'Redirection',
				inputPaths: files,
				// output: 'redirection-strings.php',
				phpArrayName: 'redirection_strings',
				format: 'PHP',
				textdomain: 'redirection',
				keywords: [ 'translate', '__' ]
			} );

			// There's a bug where it doesnt escape $ correctly - do it here
			result = result.replace( /\$(.*?)\$/g, '%%$1%%' );
			result = result.replace( /%%/g, '\\$' );

			fs.writeFileSync( 'redirection-strings.php', result, 'utf8' );
		} );
} );

gulp.task( 'pot:generate', () => {
	const pot = {
		domain: 'redirection',
		destFile: 'redirection.pot',
		'package': 'Redirection',
		bugReport: 'https://wordpress.org/plugins/redirection/'
	};

	return gulp.src( [ '**/*.php' ] )
		.pipe( sort() )
		.pipe( wpPot( pot ) )
		.pipe( gulp.dest( 'locale/redirection.pot' ) );
} );

const removeFromTarget = ( paths, rootPath ) => {
	paths
		.map( item => {
			const relative = path.resolve( '..', path.relative( path.join( rootPath, '..' ), item ) );

			if ( ! fs.existsSync( relative ) ) {
				return relative;
			}

			return false;
		} )
		.filter( item => item )
		.map( item => {
			const relative = path.join( rootPath, '..', path.relative( '..', item ) );

			/* eslint-disable no-console */
			console.log( 'Removed: ' + relative );
			fs.unlinkSync( relative );
		} );
};

const copyPlugin = ( target, cb ) => gulp.src( SVN_SOURCE_FILES )
	.pipe( gulp.dest( target ) )
	.on( 'end', () => {
		// Check which files are in the target but dont exist in the source
		globby( target + '**' )
			.then( paths => {
				removeFromTarget( paths, target );
			} );

		if ( cb ) {
			cb();
		}
	} );

gulp.task( 'svn', () => copyPlugin( config.svn_target ) );

gulp.task( 'export', () => {
	const zipTarget = path.resolve( config.export_target, '..' );
	const zipName = 'redirection-' + pkg.version + '.zip';

	console.log( 'Exporting: ' + zipName );

	return copyPlugin( config.export_target, () => {
		return gulp.src( config.export_target + '/**' )
			.pipe( zip( zipName ) )
			.pipe( gulp.dest( zipTarget ) );
	} );
} );
