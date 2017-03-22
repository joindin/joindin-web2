'use strict';

module.exports = function(grunt) {
      grunt.loadNpmTasks('grunt-jsvalidate');
      grunt.loadNpmTasks('grunt-contrib-sass');

      grunt.initConfig({
        jsvalidate: {
          options:{
            globals: {},
            esprimaOptions: {},
            verbose: false
          },
          targetName:{
            files:{
              src:['web/js/**/*.js']
            }
          }
        },
        sass: {
          options:{
            'loadPath': 'sass',
            'style': 'compressed'
          },
          dist: {
              files: [{
                expand: true,
                cwd: 'scss',
                src: ['main.scss'],
                dest: 'web/css/',
                ext: '.css'
              }]
            }
        }
      });
};
