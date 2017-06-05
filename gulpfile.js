const gulp = require( 'gulp' );
const i18n_calypso = require( 'i18n-calypso/cli' );
const tap = require( 'gulp-tap' );
const po2json = require( 'gulp-po2json' );
const wpPot = require( 'gulp-wp-pot' );
const sort = require( 'gulp-sort' );
const path = require( 'path' );
const globby = require( 'globby' );
const fs = require( 'fs' );
const config = require( './.config.json' );  // Local config

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
];

gulp.task( 'pot', [ 'pot:extract', 'pot:generate', 'pot:json' ] );

gulp.task( 'pot:json', done => {
	gulp.src( [ 'locale/*.po' ] )
		.pipe( po2json() )
		.pipe( gulp.dest( 'locale/json/' ) )
		.on( 'end', done );
} );

gulp.task( 'pot:extract', () => {
	globby( 'client/**/*.js' )
		.then( files => {
			let result = i18n_calypso( {
				projectName: 'Redirection',
				inputPaths: files,
//				output: 'redirection-strings.php',
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

gulp.task( 'pot:generate', function() {
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

const removeFromTarget = paths => {
	paths
		.map( item => {
			const relative = path.resolve( '..', path.relative( path.join( config.svn_target, '..' ), item ) );

			if ( ! fs.existsSync( relative ) ) {
				return relative;
			}

			return false;
		} )
		.filter( item => item )
		.map( item => {
			const relative = path.join( config.svn_target, '..', path.relative( '..', item ) );

			console.log( 'Removed: ' + relative );
			fs.unlinkSync( relative );
		} );
};

gulp.task( 'svn', function() {
	return gulp.src( SVN_SOURCE_FILES )
        .pipe( gulp.dest( config.svn_target ) )
		.on( 'end', function() {
			// Check which files are in the target but dont exist in the source
			globby( config.svn_target + '**' )
				.then( removeFromTarget );
		} );
} );
