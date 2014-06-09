var gulp = require('gulp');
var usemin = require('gulp-usemin');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');
var rename = require( 'gulp-rename' )
var clean = require( 'gulp-clean' )

gulp.task('default', ['usemin', 'rename', 'clean']);

gulp.task('usemin', function() {
    return gulp.src('./app/templates/layout.src.twig')
        .pipe(usemin({
            assetsDir: './web',
            css: [minifyCss(), 'concat'],
            js: [uglify(), 'concat']
        }))
        .pipe(gulp.dest('./web'));
});

gulp.task('rename', ['usemin'], function() {
    return gulp.src('web/layout.src.twig')
        .pipe(rename("layout.html.twig"))
        .pipe(gulp.dest('app/templates'));
});

gulp.task('clean', ['rename'], function() {
    return gulp.src('web/layout.src.twig')
        .pipe(clean())
});

gulp.task('watch', ['default'], function() {
    gulp.watch('app/templates/layout.src.twig', ['default']);
    gulp.watch(['web/js/*.js', '!web/js/site.js'], ['default']);
    gulp.watch(['web/css/*.css', '!web/css/site.css'], ['default']);
});
