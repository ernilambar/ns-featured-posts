module.exports = function(grunt) {
	'use strict';

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

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

		replace : {
			readme: {
				options: {
					patterns: [
						{
							match: /Stable tag:\s?(.+)/gm,
							replacement: 'Stable tag: <%= pkg.version %>'
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['readme.txt'], dest: './'
					}
				]
			},
			main: {
				options: {
					patterns: [
						{
							match: /Version:\s?(.+)/gm,
							replacement: 'Version: <%= pkg.version %>'
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['<%= pkg.main_file %>'], dest: './'
					}
				]
			},
			class: {
				options: {
					patterns: [
						{
							match: /const VERSION = \'(.+)\'/gm,
							replacement: "const VERSION = '<%= pkg.version %>'"
						}
					]
				},
				files: [
					{
						expand: true, flatten: true, src: ['includes/classes/class-ns-featured-posts.php'], dest: 'includes/classes/'
					}
				]
			}
		}
	});

	grunt.loadNpmTasks('grunt-wp-deploy');
	grunt.loadNpmTasks('grunt-replace');

	grunt.registerTask('wpdeploy', ['wp_deploy']);
	grunt.registerTask('version', ['replace:readme', 'replace:main', 'replace:class']);
};
