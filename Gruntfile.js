module.exports = function (grunt) {

	'use strict';

	// Project configuration
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		addtextdomain: {
			options: {
				textdomain: 'fitet-monitor',
			},
			update_all_domains: { // todo controlla cambia in default
				options: {
					updateDomains: true
				},
				src: ['*.php', '**/*.php', '!\.git/**/*', '!bin/**/*', '!node_modules/**/*', '!tests/**/*']
			}
		},

		wp_readme_to_markdown: {
			default: {
				files: {
					'<%= pkg.name %>/README.md': 'readme.txt'
				}
			},
		},

		makepot: {
			default: {
				options: {
					domainPath: 'src/languages',
					exclude: ['\.git/*', 'bin/*', 'node_modules/*', 'tests/*'],
					mainFile: 'fitet-monitor.php',
					potFilename: 'fitet-monitor.pot',
					potHeaders: {
						poedit: true,
						'x-poedit-keywordslist': true
					},
					updatePoFiles: true,
					type: 'wp-plugin',
					updateTimestamp: true
				}
			}
		},
		po2mo: {
			files: {
				src: 'src/languages/fitet-monitor-it_IT.po',
				dest: 'src/languages/fitet-monitor-it_IT.mo',
			},
		},
		clean: {
			all: ["<%= pkg.name %>", 'dist'],
			js: ['<%= pkg.name %>/**/*.js', '!<%= pkg.name %>/**/*.min.js'],
			css: ['<%= pkg.name %>/**/*.css', '!<%= pkg.name %>/**/*.min.css'],
			pot: ['<%= pkg.name %>/**/*.po', '<%= pkg.name %>/**/*.pot'],
			tmp: ['<%= pkg.name %>'],
		},
		copy: {
			default: {
				expand: true,
				cwd: 'src',
				src: '**',
				dest: '<%= pkg.name %>/',
			},
		},
		uglify: {
			options: {
				mangle: {
					reserved: ['jQuery']
				},
				sourceMap: true,
			},
			default: {
				files: [{
					expand: true,
					cwd: '<%= pkg.name %>',
					src: ['**/*.js', '!**/*.min.js'],
					dest: '<%= pkg.name %>',
					rename: function (dst, src) {
						return dst + '/' + src.replace('.js', '.min.js');
					}
				}]
			}
		},
		cssmin: {
			options: {
				sourceMap: true,
			},
			default: {
				files: [{
					expand: true,
					cwd: '<%= pkg.name %>/',
					src: ['**/*.css', '!**/*.min.css'],
					dest: '<%= pkg.name %>',
					ext: '.min.css'
				}]
			}
		},
		zip: {
			'dist/<%= pkg.name %>.zip': ['<%= pkg.name %>/**']
		}
	});

	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('@floatwork/grunt-po2mo');
	grunt.loadNpmTasks('grunt-zip');


	grunt.registerTask('i18n', ['addtextdomain', 'makepot', 'po2mo']); // To run i18n you need to have gettext installed.
	grunt.registerTask('readme', ['wp_readme_to_markdown']);
	grunt.registerTask('assets', ['uglify', 'cssmin', 'clean:js', 'clean:css', 'clean:pot']);
	grunt.registerTask('build', ['clean:all', 'readme', 'copy', 'assets', 'zip', 'clean:tmp']);
	grunt.registerTask('build-with-i18n', ['i18n', 'build']);

	grunt.util.linefeed = '\n';

};


