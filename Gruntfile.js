module.exports = function (grunt) {

	'use strict';

	// Project configuration
	grunt.initConfig({

		pkg: grunt.file.readJSON('package.json'),

		addtextdomain: {
			options: {
				textdomain: 'fitet-monitor',
			},
			default: {
				options: {
					updateDomains: true
				},
				src: ['*.php', '**/*.php', '!\.git/**/*', '!**/simple_html_dom.php', '!bin/**/*', '!node_modules/**/*', '!tests/**/*']
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
					exclude: ['\.git/*', 'bin/*', 'node_modules/*', 'tests/*', 'src/admin/includes/class-fitet-monitor-wp-table.php'],
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
			dist: ['<%= pkg.name %>', 'dist'],
			js: ['<%= pkg.name %>/**/*.js', '!<%= pkg.name %>/**/*.min.js'],
			css: ['<%= pkg.name %>/**/*.css', '!<%= pkg.name %>/**/*.min.css'],
			pot: ['<%= pkg.name %>/**/*.po', '<%= pkg.name %>/**/*.pot'],
			tmp: ['<%= pkg.name %>'],
			doc: ['docs']
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
		},
		replace: {
			version: {
				options: {
					patterns: [
						{
							match: /0.0.0-DEV/g,
							replacement: '<%= pkg.version %>'
						}
					]
				},
				files: [
					{
						expand: true,
						flatten: true,
						src: ['<%= pkg.name %>/<%= pkg.name %>.php'],
						dest: '<%= pkg.name %>'
					}
				]
			}
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
	grunt.loadNpmTasks('grunt-exec');
	grunt.loadNpmTasks("grunt-replace");


	// To run i18n you need to have gettext installed.
	// https://www.gnu.org/software/gettext/
	grunt.registerTask('i18n', ['addtextdomain', 'makepot', 'po2mo']);

	grunt.registerTask('readme', ['wp_readme_to_markdown']);
	grunt.registerTask('assets', ['uglify', 'cssmin', 'clean:js', 'clean:css', 'clean:pot']);

	grunt.registerTask('build', ['clean:dist', 'readme', 'copy', 'indexes', 'replace:version', 'assets', 'zip', 'clean:tmp']);

	grunt.registerTask('build-full', ['clean', 'i18n', 'build']);

	grunt.registerTask('indexes', function () {
		// adding index.php files to prevent direct access to directories

		const basedir = grunt.config.data.pkg.name; // <%= pkg.version %>
		const fs = require('fs'), path = require('path');

		const walkDirectories = (dir, callback) => {
			const files = fs.readdirSync(dir);
			files.forEach((file) => {
				const filepath = path.join(dir, file);
				const stats = fs.statSync(filepath);
				if (stats.isDirectory()) {
					callback(filepath);
					walkDirectories(filepath, callback);
				}
			});
		};
		walkDirectories(basedir, function (filepath) {
			const indexFile = filepath + '/index.php';
			fs.writeFileSync(indexFile, '<?php // Silence is golden');
		})

	});


	grunt.util.linefeed = '\n';

};


