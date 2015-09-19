'use strict';

var gulp = require( 'gulp' );

var SVN_SOURCE = [
    './**',

    '!package.json',
    '!phpunit.xml',
    '!gulpfile.js',
    '!node_modules/**',
    '!tests/**',
    '!bin/**',
    '!node_modules',
    '!tests',
    '!bin'
];

var SVN_SOURCE2 = [
    '*/**',

    '!package.json',
    '!phpunit.xml',
    '!gulpfile.js',
    '!node_modules/**',
    '!tests/**',
    '!bin/**',
    '!node_modules',
    '!tests',
    '!bin'
];

// Based on gulp-deleted, but with 'force: true'
function cleaner( dest, destPatterns ) {
    var path, through, glob, _, del;

    glob = require("glob-all");

    through = require("through");

    path = require("path");

    _ = require("underscore");

    del = require("del");

    var repl = /\\/g

    if (destPatterns === undefined) return through(function write(data) {this.emit('data', data)},function end () {this.emit('end')});

    var srcFiles, destFiles, files, onEnd, onFile;
    files = [];
    srcFiles = [];

    destFiles = glob.sync( destPatterns, {cwd: path.join(process.cwd(), dest) } );

    onFile = function(file) {
        srcFiles.push(file.path);
        this.push(file);
        return files.push(file);
    };

    onEnd = function() {
        for (var i = srcFiles.length -1, l = -1; i > l; i--){
            srcFiles[i] = srcFiles[i].replace(repl, "/").substr(process.cwd().length + 1);
            if (srcFiles[i] == '') {
                srcFiles.splice(i,1);
            }
        }

        //compare source and destination files and delete any missing in source at destination
        var deletedFiles = _.difference(destFiles, srcFiles);

        _.each(deletedFiles, function(item, index) {
            deletedFiles[index] = path.join(process.cwd(), dest,  deletedFiles[index]);
            del.sync(deletedFiles[index], { force: true });
        } );

        return this.emit("end");
    };

    return through(onFile, onEnd);
}

gulp.task( 'pot', function() {
    var wpPot = require( 'gulp-wp-pot' );
    var sort = require( 'gulp-sort' );

    return gulp.src( [ '**/*.php' ] )
        .pipe( sort() )
        .pipe( wpPot( {
            domain: 'redirection',
            destFile: 'redirection.pot',
            package: 'Redirection',
            bugReport: 'https://wordpress.org/plugins/redirection/'
        } ) )
        .pipe( gulp.dest( 'locale' ) );
} );

gulp.task( 'svn', function() {
    var config = require( './.config.json' );  // Local config
    var deleted = require( 'gulp-deleted' );

    return gulp.src( SVN_SOURCE )
        .pipe( cleaner( config.svn_relative, SVN_SOURCE2 ) )
        .pipe( gulp.dest( config.svn_target ) );
} );
