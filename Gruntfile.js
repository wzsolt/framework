module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        sass: {
            target: {
                files: {
                    'web/Public/assets/css/minton.style.css':       'web/Resources/Sass/minton.main.scss',
                    'web/Public/assets/css/minton-dark.style.css':  'web/Resources/Sass/minton.dark.scss',
                }
            }
        },

        cssmin: {
            options: {
                sourceMap: true,
            },
            target: {
                files: {
                    'web/Public/assets/css/minton.style.min.css':       'web/Public/assets/css/minton.style.css',
                    'web/Public/assets/css/minton-dark.style.min.css':  'web/Public/assets/css/minton-dark.style.css',
                }
            }
        },

        concat: {
            options: {
                separator: ';'
            },

            app_minton: {
                src: [
                    'web/Resources/Sass/minton/js/layout.js',
                    'web/Resources/Sass/minton/js/waves.js',
                    'web/Resources/Sass/minton/js/app.js'
                ],
                dest: 'web/Public/assets/js/app-minton.js'
            },

            admin: {
                src: [
                    'web/Public/vendor/jquery-toast-plugin/jquery.toast.js',
                    'web/Resources/Scripts/admin.js',
                    'web/Resources/Scripts/tables.js',
                    'web/Resources/Scripts/chunk-uploader.js'
                ],
                dest: 'web/Public/assets/js/admin.js'
            }

            /*
            assets: {
                src: [
                    'web/Resources/Scripts/course.js'
                ],
                'dest': 'web/Public/assets/js/course.js'
            }
            */
        },

        uglify: {
            options: {
                sourceMap: true
            },
            admin: {
                files: {
                    'web/Public/assets/js/app-minton.min.js':   ['<%= concat.app_minton.dest %>'],
                    'web/Public/assets/js/admin.min.js':        ['<%= concat.admin.dest %>'],
                    'web/Public/assets/js/dictionary.min.js':   ['web/Resources/Scripts/dictionary.js'],
                    'web/Public/assets/js/calendar.min.js':     ['web/Resources/Scripts/calendar.js'],
                    'web/Public/assets/js/charts.min.js':       ['web/Resources/Scripts/charts.js'],
                    'web/Public/assets/js/camo.min.js':         ['web/Resources/Scripts/camo.js']
                }
            }
        },

        obfuscator: {
            options: {
                //banner: '// obfuscated with grunt-contrib-obfuscator.\n',
                //debugProtection: true,
                //debugProtectionInterval: true,
                //domainLock: ['www.example.com']
            },
            task1: {
                options: {
                    // options for each sub task
                },
                files: {
                    'web/Public/assets/js/app-minton.min.js':   ['<%= concat.app_minton.dest %>'],

                    'web/Public/assets/js/admin.min.js':        ['<%= concat.admin.dest %>'],
                    'web/Public/assets/js/dictionary.min.js':   ['web/Resources/Scripts/dictionary.js'],
                    'web/Public/assets/js/calendar.min.js':     ['web/Resources/Scripts/calendar.js'],
                    'web/Public/assets/js/charts.min.js':       ['web/Resources/Scripts/charts.js'],
                    'web/Public/assets/js/camo.min.js':         ['web/Resources/Scripts/camo.js']

                    //'web/Public/assets/js/cbt.min.js':          ['web/Resources/Scripts/cbt.js'],
                    //'web/Public/assets/js/course.min.js':       ['web/Resources/Scripts/course.js']
                }
            }
        },

        svgstore: {
            options: {
                prefix : 'svg-', // This will prefix each ID
                svg: {
                    // will be added as attributes to the resulting SVG
                    xmlns: 'http://www.w3.org/2000/svg'
                }
            },
            default : {
                files: {
                    'web/Public/images/sprite.svg': ['web/Resources/Svg/*.svg']
                }
            }
        },

        watch: {
            css: {
                files: '/web/Resources/Sass/**/*.scss',
                tasks: ['dart-sass', 'cssmin']
            },
            js: {
                files: '/web/Resources/Scripts/**/*.js',
                tasks: ['concat', 'obfuscator']
            }
        }

    });

    //grunt.loadNpmTasks('grunt-dart-sass');
    grunt.loadNpmTasks('grunt-contrib-sass');
        // sudo gem install sass

    //grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-concat-css');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-svgstore');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-obfuscator');

    grunt.registerTask('watch', ['watch']);
    grunt.registerTask('svg', ['svgstore']);
    grunt.registerTask('css', ['sass', 'cssmin']);
    //grunt.registerTask('js', ['concat', 'uglify']);
    grunt.registerTask('js', ['concat', 'obfuscator']);

    grunt.registerTask('build', ['dart-sass', 'cssmin', 'uglify']);
    grunt.registerTask('default', ['dart-sass', 'cssmin', 'uglify', 'svgstore']);

};