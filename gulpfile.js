// Env.
require('dotenv').config()

// Config.
var rootPath = './';

// Gulp.
var gulp = require( 'gulp' );

// Gulp plugins.
var gulpPlugins = require( 'gulp-load-plugins' )();

// File system.
var fs = require('fs');

// Package.
var pkg = JSON.parse(fs.readFileSync('./package.json'));

// Delete.
var del = require('del');

// Browser sync.
var browserSync = require('browser-sync').create();

// Deploy files list.
var deploy_files_list = [
	'assets/**',
	'includes/**',
	'languages/**',
	'vendor/**',
	'readme.txt',
	pkg.main_file
];

// Watch.
gulp.task( 'watch', function() {
    browserSync.init({
        proxy: process.env.DEV_SERVER_URL,
        open: true
    });

    // Watch CSS files.
    gulp.watch( rootPath + '**/**/*.css' ).on('change',browserSync.reload);

    // Watch PHP files.
    gulp.watch( rootPath + '**/**/*.php' ).on('change',browserSync.reload);
});

// Clean deploy folder.
gulp.task('clean:deploy', function() {
    return del('deploy')
});

// Copy to deploy folder.
gulp.task('copy:deploy', function() {
	const { zip } = gulpPlugins;
	return gulp.src(deploy_files_list,{base:'.'})
	    .pipe(gulp.dest('deploy/' + pkg.name))
	    .pipe(zip(pkg.name + '.zip'))
	    .pipe(gulp.dest('deploy'))
});

// Tasks.
gulp.task( 'default', gulp.series('watch'));

gulp.task( 'deploy', gulp.series('clean:deploy', 'copy:deploy'));
