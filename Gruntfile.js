module.exports = function(grunt) {
  'use strict';

  var path = require('path');

  var targetDirectory = 'target/';
  var themesDirectory = targetDirectory + 'wp-content/themes/';
  var pluginsDirectory = targetDirectory + 'wp-content/plugins/';

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    copy: {
      setup: {
        files: [
          { expand: true, cwd: 'wordpress/', src: ['**'], dest: targetDirectory },
          { src: ['woocommerce/**'], dest: pluginsDirectory },
          { src: ['mystile/**'], dest: themesDirectory }
        ]
      },

      build: {
        files: [
          { 
            expand: true,
            cwd: 'daigou-plugin/src/main/php/', 
            src: ['**/*.php'], 
            dest: pluginsDirectory + 'daigou-plugin/'
          },
          {
            expand: true,
            cwd: 'daigou-plugin/src/main/js/',
            src: ['**/*.js'],
            dest: pluginsDirectory + 'daigou-plugin/js/'
          }
        ]
      }
    },

    compass: {
      build: {
        options: {
          sassDir: 'daigou-plugin/src/main/sass/',
          cssDir: pluginsDirectory + 'daigou-plugin/css',
          outputStyle: 'expand'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-update-submodules');

  grunt.registerTask('setup', ['update_submodules', 'copy:setup']);
  grunt.registerTask('build:dev', ['copy:build', 'compass:build']);
  // TODO: write build for production
  grunt.registerTask('build:prod', ['build:dev']);
};
