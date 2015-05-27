'use strict';

module.exports = function(grunt) {  
      grunt.loadNpmTasks('grunt-jsvalidate');

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
        }
      });

};

