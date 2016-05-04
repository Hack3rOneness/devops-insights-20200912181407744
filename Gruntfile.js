module.exports = function(grunt) {
  grunt.initConfig({
    env: {
      release: {
        NODE_ENV: 'production'
      }
    },
    eslint: {
      options: {
        force: true
      },
      dist: {
        src: ['src/static/js/**/*.js']
      }
    },
    flow: {
      watch: {
        src: 'src/static/js/**/*.js',
        options: {
          server: true
        }
      }
    },
    // babel: {
    //   options: {
    //     presets: [
    //       "es2015",
    //       "react"
    //     ]
    //   },
    //   dist: {
    //     src: 'src/static/js/app.js',
    //     dest: 'src/static/build/app-babel.js'
    //   }
    // },
    browserify: {
      options: {
        browserifyOptions: {
          debug: true
        },
        transform: [
          [
            'babelify', {
              presets: [
                'es2015',
                'react'
              ]
            }
          ]
        ]
      },
      dist: {
        src: 'src/static/js/app.js',
        dest: 'src/static/build/app-browserify.js'
      }
    },
    uglify: {
      dist: {
        src: 'src/static/build/app-browserify.js',
        dest: 'src/static/dist/js/app.js'
      }
    },
    watch: {
      files: ['src/static/js/**/*.js'],
      tasks: ['default']
    },
    copy: {
      browserify: {
        src: 'src/static/build/app-browserify.js',
        dest: 'src/static/dist/js/app.js'
      }
    }
  });

  grunt.loadNpmTasks('grunt-babel');
  grunt.loadNpmTasks('grunt-browserify');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-jshint');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-env');
  grunt.loadNpmTasks('grunt-eslint');
  //grunt.loadNpmTasks('grunt-flow-type-check');
  grunt.loadNpmTasks('grunt-flow');
  grunt.loadNpmTasks('grunt-force-task');

  grunt.registerTask('default', ['force:eslint', 'flow', 'browserify', 'copy:browserify']);
  grunt.registerTask('release', ['env:release', 'eslint', 'flow', 'browserify', 'uglify']);
};
