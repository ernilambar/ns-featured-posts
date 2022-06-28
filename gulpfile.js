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

// Replace.
var replace = require('gulp-replace');

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

// Version replace readme file.
gulp.task('version:readme', function() {
	return gulp.src( rootPath + 'readme.txt' )
	.pipe( replace(/Stable tag: (.+)/gm, 'Stable tag: ' + pkg.version) )
	.pipe( gulp.dest('.') );
});

// Version replace main file.
gulp.task('version:main', function() {
	return gulp.src( rootPath + pkg.main_file )
		.pipe( replace(/Version:\s?(.+)/gm, 'Version: ' + pkg.version) )
		.pipe( gulp.dest('.') );
});

// Version replace plugin class.
gulp.task('version:class', function() {
	return gulp.src( rootPath + 'includes/classes/class-ns-featured-posts.php' )
		.pipe( replace(/const VERSION = \'(.+)\'/gm, "const VERSION = '" + pkg.version + "'") )
		.pipe( gulp.dest('includes/classes/') );
});

// Tasks.
gulp.task( 'default', gulp.series('watch'));

gulp.task( 'version', gulp.series('version:readme', 'version:main', 'version:class'));

gulp.task( 'deploy', gulp.series('clean:deploy', 'copy:deploy'));
