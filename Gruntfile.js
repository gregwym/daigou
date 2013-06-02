'use strict';

var runTask = require('./grunt/runTask');
var Path = require('path');

module.exports = function(grunt) {
  var TARGET = grunt.option('target') || 'dev';
  var DIR_TARGET = 'target/';
  var DIR_THEMES = 'wp-content/themes/';
  var DIR_PLUGINS = 'wp-content/plugins/';
  var DIR_BUILD = DIR_TARGET + TARGET + '/';
  var FILE_WP_CONFIG = 'src/wp-config.' + TARGET + '.php';

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    clean: [DIR_TARGET],

    compass: {
      'daigou-plugin': {
        options: {
          sassDir: 'src/' + DIR_PLUGINS + 'daigou-plugin/sass/',
          cssDir: DIR_BUILD + DIR_PLUGINS + 'daigou-plugin/css/',
          outputStyle: TARGET === 'dev' ? 'expand' : 'compressed'
        }
      }
    }
  });

  grunt.loadNpmTasks('grunt-contrib-clean');
  grunt.loadNpmTasks('grunt-contrib-copy');
  grunt.loadNpmTasks('grunt-contrib-compass');
  grunt.loadNpmTasks('grunt-update-submodules');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  grunt.registerTask('auto-build', function() {
    runTask('watch', {
      files: ['src/' + DIR_PLUGINS + 'daigou-plugin/**'],
      tasks: ['build-modified-files'],
      options: {
        nospawn: true
      }
    });
  });

  var modifiedFiles = [];
  grunt.registerTask('build-modified-files', function() {
    modifiedFiles.forEach(function(filePath) {
      var ext = Path.extname(filePath);

      switch (ext) {
        case '.scss':
          grunt.task.run('compass');
          break;
        case '.php':
        case '.js':
          var parts = filePath.split(Path.sep);
          var relativeParts = [];
          for (var i = parts.length - 1; i >= 0; i--) {
            var part = parts[i];

            if (part === 'src') {
              break;
            } else {
              relativeParts.unshift(part);
            }
          }

          var destPath = DIR_BUILD + relativeParts.join(Path.sep);
          grunt.log.writeln('copying');
          grunt.log.writeln(filePath);
          grunt.log.writeln(destPath);
          grunt.file.copy(filePath, destPath);
          break;
      }
    });
    modifiedFiles = [];
  });

  grunt.event.on('watch', function(_, filePath) {
    modifiedFiles.push(filePath);
  });

  grunt.registerTask('build', function() {

    var filesToCopy = [];

    // init and update git submodules
    grunt.task.run('update_submodules');

    // copy config files
    if (grunt.file.exists(FILE_WP_CONFIG)) {
      filesToCopy.push(
        { src: FILE_WP_CONFIG, dest: DIR_BUILD + 'wp-config.php' },
        { src: 'src/wp-salt.php', dest: DIR_BUILD + 'wp-salt.php' },
        { expand: true, cwd: 'src/', src: ['**/.htaccess'], dest: DIR_BUILD }
      );
    } else {
      grunt.fail.warn('You need to add ' + FILE_WP_CONFIG);
    }

    // copy wordpress, woocommerce, mystile, artificer
    if (!grunt.file.exists(DIR_BUILD)) {
      filesToCopy.push(
        { expand: true, cwd: 'wordpress/', src: ['**'], dest: DIR_BUILD },
        { expand: true, cwd: 'src/' + DIR_PLUGINS, src: ['woocommerce/**'], dest: DIR_BUILD + DIR_PLUGINS },
        { expand: true, cwd: 'src/' + DIR_THEMES, src: ['mystile/**', 'artificer/**'], dest: DIR_BUILD + DIR_THEMES }
      );
    }

    // copy PHP and static files
    filesToCopy.push(
      {
        expand: true,
        cwd: 'src/' + DIR_PLUGINS + 'daigou-plugin/',
        src: ['**/*.php', '**/*.gif', '**/*.jpg', '**/*.png'],
        dest: DIR_BUILD + DIR_PLUGINS + 'daigou-plugin/'
      }
    );

    // compile Sass
    grunt.task.run('compass');

    // compile JavaScript
    if (TARGET === 'dev') {
      filesToCopy.push(
        {
          expand: true,
          cwd: 'src/' + DIR_PLUGINS + 'daigou-plugin/js/',
          src: ['**/*.js'],
          dest: DIR_BUILD + DIR_PLUGINS + 'daigou-plugin/js/'
        }
      );
    } else {
      runTask('uglify', {
        options: {
          compress: true,
          report: 'min'
        },
        files: [{
          expand: true,
          cwd: 'src/' + DIR_PLUGINS + 'daigou-plugin/js/',
          src: ['**/*.js'],
          dest: DIR_BUILD + DIR_PLUGINS + 'daigou-plugin/js/'
        }]
      });
    }

    runTask('copy', {
      files: filesToCopy
    });
  });

  grunt.registerTask('default', ['build']);
};
