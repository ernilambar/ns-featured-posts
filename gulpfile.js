// Env.
require('dotenv').config();

// Config.
var rootPath = './';

// Gulp.
var gulp = require('gulp');

// Browser sync.
var browserSync = require('browser-sync').create();

// Watch.
gulp.task('watch', function() {
    browserSync.init({
			proxy: process.env.DEV_SERVER_URL,
			open: true
    });

    gulp.watch(rootPath + 'assets/**/*.css').on('change', browserSync.reload);
    gulp.watch(rootPath + 'assets/**/*.js').on('change', browserSync.reload);
    gulp.watch(rootPath + '**/**/*.php').on('change', browserSync.reload);
});

// Tasks.
gulp.task( 'default', gulp.series('watch'));
