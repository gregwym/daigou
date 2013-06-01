'use strict';

var grunt = require('grunt');

var PREFIX = '-runTask-';
var id = 0;

module.exports = function(taskName, params) {
	var targetName = PREFIX + id++;
	grunt.config([taskName, targetName], params);
	grunt.task.run(taskName + ':' + targetName);
};
