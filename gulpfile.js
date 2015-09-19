'use strict';

var gulp = require( 'gulp' );
var wpPot = require( 'gulp-wp-pot' );
var sort = require( 'gulp-sort' );

gulp.task( 'pot', function() {
    return gulp.src( [ '*.php', 'actions/*.php', 'fileio/*.php', 'matches/*.php', 'models/*.php', 'modules/*.php', 'view/*.php' ] )
        .pipe( sort() )
        .pipe( wpPot( {
            domain: 'redirection',
            destFile: 'redirection.pot',
            package: 'Redirection',
            bugReport: 'https://wordpress.org/plugins/redirection/'
        } ) )
        .pipe(gulp.dest( 'locale' ) );
} );
