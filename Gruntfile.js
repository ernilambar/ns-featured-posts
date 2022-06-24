module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON( 'package.json' ),

		wp_deploy: {
			deploy: {
				options: {
					plugin_slug: '<%= pkg.name %>',
					plugin_main_file: '<%= pkg.main_file %>',
					svn_user: 'rabmalin',
					build_dir: 'deploy/<%= pkg.name %>',
					assets_dir: '.wordpress-org',
					deploy_trunk: true,
					deploy_tag: true
				},
			}
		},
	});

	grunt.loadNpmTasks('grunt-wp-deploy');

	grunt.registerTask('wpdeploy', ['wp_deploy']);
};
