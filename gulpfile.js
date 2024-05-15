require( 'dotenv' ).config();

const rootPath = './';

const gulp = require( 'gulp' );

const browserSync = require( 'browser-sync' ).create();

// Watch.
gulp.task( 'watch', function() {
	browserSync.init( {
		proxy: process.env.DEV_SERVER_URL,
		open: false,
	} );

	gulp.watch( rootPath + 'assets/**/*.css' ).on( 'change', browserSync.reload );
	gulp.watch( rootPath + 'assets/**/*.js' ).on( 'change', browserSync.reload );
	gulp.watch( rootPath + '**/**/*.php' ).on( 'change', browserSync.reload );
} );

// Tasks.
gulp.task( 'default', gulp.series( 'watch' ) );
