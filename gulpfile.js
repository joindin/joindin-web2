var gulp = require('gulp');
var usemin = require('gulp-usemin');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var rename = require( 'gulp-rename' )
var rimraf = require( 'gulp-rimraf' )
var header = require( 'gulp-header' )

// order of operation: usemin, add-comments, remove-temp-file

gulp.task('default', ['remove-temp-file']);

gulp.task('usemin', function() {

    gulp.src('web/css/site.css')
        .pipe(rimraf(), { read: false });

    gulp.src('web/js/site.js')
        .pipe(rimraf(), { read: false });

    return gulp.src('app/templates/layout.html.twig')
        .pipe(usemin({
            assetsDir: 'web',
            css: [minifyCss(), 'concat'],
            js: [uglify(), 'concat']
        }))
        .pipe(gulp.dest('web'));
});

gulp.task('add-comments', ['usemin'], function() {
    gulp.src('web/js/site.js')
        .pipe(header("/* This file is generated — do not edit by hand! */\n"))
        .pipe(gulp.dest('web/js'));

    gulp.src('web/css/site.css')
        .pipe(header("/* This file is generated — do not edit by hand! */\n"))
        .pipe(gulp.dest('web/css'));
})

gulp.task('remove-temp-file', ['add-comments'], function() {
    return gulp.src('web/layout.html.twig', { read: false })
        .pipe(rimraf())
});

gulp.task('watch', ['default'], function() {
    gulp.watch('app/templates/layout.src.twig', ['default']);
    gulp.watch(['web/js/*.js', '!web/js/site.js'], ['default']);
    gulp.watch(['web/css/*.css', '!web/css/site.css'], ['default']);
});
