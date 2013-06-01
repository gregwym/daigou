'use strict';

var runTask = require('./grunt/runTask');

var DIR_TARGET = 'target/';

module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    clean: [DIR_TARGET],

    build: {
      dev: {},
      stage: {},
      prod: {}
    }
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-update-submodules');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerMultiTask('build', function() {
    var DIR_BUILD = DIR_TARGET + this.target + '/';
    var DIR_BUILD_THEMES = DIR_BUILD + 'wp-content/themes/';
    var DIR_BUILD_PLUGINS = DIR_BUILD + 'wp-content/plugins/';
    var DIR_CONFIG = 'config/';
    var FILE_WP_CONFIG = DIR_CONFIG + 'wp-config.' + this.target + '.php';

    // init and update git submodules
    grunt.task.run('update_submodules');

    // copy wordpress, woocommerce, mystile
    if (!grunt.file.exists(DIR_BUILD)) {
      grunt.log.writeln('copying wordpress');
      runTask('copy', 
        { expand: true, cwd: 'wordpress/', src: ['**'], dest: DIR_BUILD }
      );
    }

    if (!grunt.file.exists(DIR_BUILD_PLUGINS + 'woocommerce/')) {
      grunt.log.writeln('copying woocommerce');
      runTask('copy', 
        { src: ['woocommerce/**'], dest: DIR_BUILD_PLUGINS }
      );
    }

    if (!grunt.file.exists(DIR_BUILD_THEMES + 'mystile')) {
      grunt.log.writeln('copying mystile');
      runTask('copy', 
        { src: ['mystile/**'], dest: DIR_BUILD_THEMES }
      );
    }

    // copy config files
    if (grunt.file.exists(FILE_WP_CONFIG)) {
      grunt.file.copy(FILE_WP_CONFIG, DIR_BUILD + 'wp-config.php');
    } else {
      grunt.fail.warn('You need to add ' + FILE_WP_CONFIG); 
    }

    // copy PHP 
    runTask('copy', { 
      expand: true,
      cwd: 'daigou-plugin/src/main/php/', 
      src: ['**/*.php'], 
      dest: DIR_BUILD_PLUGINS + 'daigou-plugin/'
    });

    // compile Sass
    runTask('compass', {
      options: {
        sassDir: 'daigou-plugin/src/main/sass/',
        cssDir: DIR_BUILD_PLUGINS + 'daigou-plugin/css/',
        outputStyle: this.target === 'dev' ? 'expand' : 'compressed'
      }
    });

    // compile JavaScript
    if (this.target === 'dev') {
      runTask('copy', { 
        expand: true,
        cwd: 'daigou-plugin/src/main/js/', 
        src: ['**/*.js'], 
        dest: DIR_BUILD_PLUGINS + 'daigou-plugin/js/'
      });
    } else {
      runTask('uglify', {
        options: {
          compress: true,
          report: 'min'
        },
        files: [{ 
          expand: true,
          cwd: 'daigou-plugin/src/main/js/', 
          src: ['**/*.js'], 
          dest: DIR_BUILD_PLUGINS + 'daigou-plugin/js/'
        }]
      });
    }

    // var tasks = this.data.tasks;
    // tasks = tasks || {};
    // Object.keys(tasks).forEach(function(taskName) {
    //   var params = tasks[taskName];
    //   if (params) {
    //     runTask(taskName, tasks[taskName]);
    //   } else {
    //     grunt.task.run(taskName);
    //   }
    // });
  });

  grunt.registerTask('default', ['build:dev']);
};
