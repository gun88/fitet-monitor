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
					'dist/README.md': 'readme.txt'
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
		phpdocs: {
			options: {
				phar: '/home/tpomante/Downloads/phpDocumentor.phar'
			},
			dist: {
				source: './src',
				destination: './docs',
				template: 'clean'
			}
		},
		clean: {
			all: ["dist"],
			js: ['dist/**/*.js', '!dist/**/*.min.js'],
			css: ['dist/**/*.css', '!dist/**/*.min.css'],
		},
		copy: {
			main: {
				expand: true,
				cwd: 'src',
				src: '**',
				dest: 'dist/',
			},
		},
		uglify: {
			options: {
				mangle: {
					reserved: ['jQuery']
				},
				sourceMap: true,
			},
			main: {
				files: [{
					expand: true,
					cwd: 'dist',
					src: ['**/*.js', '!**/*.min.js'],
					dest: 'dist',
					rename: function (dst, src) {
						let s = dst + '/' + src.replace('.js', '.min.js');
						console.log(s)
						return s;
					}
				}]
			}
		},
		cssmin: {
			options: {
				sourceMap: true,
			},
			main: {
				files: [{
					expand: true,
					cwd: 'dist/',
					src: ['**/*.css', '!**/*.min.css'],
					dest: 'dist',
					ext: '.min.css'
				}]
			}
		}
	});

	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
	grunt.loadNpmTasks('grunt-phpdocs');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('@floatwork/grunt-po2mo');

	grunt.registerTask('default', ['i18n', 'readme']);
	grunt.registerTask('i18n', ['addtextdomain', 'makepot', 'po2mo']); // To run i18n you need to have gettext installed.
	grunt.registerTask('readme', ['wp_readme_to_markdown', 'phpdocs']);
	grunt.registerTask('ciao', ['clean:all', 'copy', 'uglify', 'cssmin', 'clean:js', 'clean:css']);

	grunt.util.linefeed = '\n';

};


