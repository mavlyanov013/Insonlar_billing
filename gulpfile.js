/*
 * @link      http://www.activemedia.uz/
 * @copyright Copyright (c) 2017. ActiveMedia Solutions LLC
 * @author    Rustam Mamadaminov <rmamdaminov@gmail.com>
 */
var gulp = require('gulp');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var minifyCSS = require('gulp-minify-css');

gulp.task('js', function () {
    return gulp.src([
        './frontend/assets/app/vendor/popper/popper.min.js',
        './frontend/assets/app/vendor/bootstrap/js/bootstrap.min.js',
        './frontend/assets/app/js/jquery.formatter.min.js',
        './frontend/assets/app/js/custom.js'
    ])
        .pipe(concat('app.js'))
        .pipe(gulp.dest('./frontend/assets/app/js'))
        .pipe(uglify())
        .pipe(rename('app.min.js'))
        .pipe(gulp.dest('./frontend/assets/app/js'));
});

gulp.task('css', function () {
    return gulp.src([
        './frontend/assets/app/vendor/bootstrap/css/bootstrap.min.css',
        './frontend/assets/app/css/fontello.css',
        './frontend/assets/app/css/fontello-ie7.css',
        './frontend/assets/app/css/theme.css',
        './frontend/assets/app/css/custom-style.css'
    ])
        .pipe(concat('app.css'))
        .pipe(gulp.dest('./frontend/assets/app/css'))
        .pipe(minifyCSS())
        .pipe(rename('app.min.css'))
        .pipe(gulp.dest('./frontend/assets/app/css'));
});

gulp.task('build', ['js', 'css']);

gulp.task('watch', function () {
    gulp.watch('./frontend/assets/app/css/*.css', ['css']);
    gulp.watch('./frontend/assets/app/js/*.js', ['js']);
});

gulp.task('default', ['watch']);
