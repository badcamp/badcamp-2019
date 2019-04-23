'use strict';
var gulp = require('gulp');
var sass = require('gulp-sass');
var sourcemaps = require('gulp-sourcemaps');
var autoprefixer = require('gulp-autoprefixer');
var plumber = require('gulp-plumber');
var stripCssComments = require('gulp-strip-css-comments');
var uglify = require('gulp-uglifyjs');
var cssmin = require('gulp-cssmin');
var livereload = require('gulp-livereload');
var imagemin = require('gulp-imagemin');
var pngquant = require('imagemin-pngquant');
var sassGlob = require('gulp-sass-glob');
var spawn = require('child_process').spawn;

var sass_config = {
    includePaths: [
        'node_modules/breakpoint-sass/stylesheets/',
        'node_modules/singularitygs/stylesheets/',
        'node_modules/modularscale-sass/stylesheets',
        'node_modules/compass-mixins/lib/',
        'node_modules/susy/sass/',
        'node_modules/breakpoint-sass/stylesheets',
        'node_modules/compass-sass-mixins/lib/',
        'node_modules/foundation-sites/scss/util/util',
        'node_modules/foundation-sites/scss/foundation',
        'node_modules/foundation-sites/scss/util/color',
        'node_modules/motion-ui/src'
    ],
    errLogToConsole: true,
    outputStyle: 'expanded'
};

gulp.task('imagemin', function (done) {
    gulp.src('./images/**/*')
        .pipe(imagemin({
            progressive: true,
            svgoPlugins: [{removeViewBox: false}],
            use: [pngquant()]
        }))
        .pipe(gulp.dest('./images'));
    done();
});

gulp.task('breakout', function(done){
    gulp.src('./sass/**/*.scss')
        .pipe(plumber())
        .pipe(sassGlob())
        .pipe(sass(sass_config).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 7', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(gulp.dest('./css/individual'));


    gulp.src([
        './node_modules/foundation-sites/dist/js/**/*.js',
        './lib/*.js'
    ])
    .pipe(gulp.dest('./js/individual'));

    done();
});

gulp.task('sass', function (done) {
    gulp.src(['./sass/**/*.scss', './sass/**/**/*.scss'])
        .pipe(plumber())
        .pipe(sassGlob())
        .pipe(sourcemaps.init())
        .pipe(sass(sass_config).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version', 'safari 5', 'ie 7', 'ie 8', 'ie 9', 'opera 12.1', 'ios 6', 'android 4'))
        .pipe(stripCssComments({preserve: false}))
        .pipe(sourcemaps.write('.'))
        .pipe(cssmin())
        .pipe(gulp.dest('./css'));
    done();
});

gulp.task('uglify', function(done) {
    gulp.src([
        './node_modules/foundation-sites/dist/js/foundation.js',
        './lib/*.js'
    ])
        .pipe(uglify('main.js'))
        .pipe(gulp.dest('./js'));
    done();
});

gulp.task('cc', function(done) {
    var cmd = spawn('/usr/local/bin/drush', ['--root="/var/www/web/"', 'cc', 'css-js'], {stdio: 'inherit', shell: '/bin/bash'});
    cmd.on('close', function (code) {
        console.log('my-task exited with code ' + code);
        done(code);
    });
});

gulp.task('watch', gulp.parallel('sass', 'uglify', function(done){
    livereload.listen();
    gulp.watch('./sass/**/*.scss', { usePolling: true }, gulp.series('sass'));
    gulp.watch('./lib/*.js', { usePolling: true }, gulp.series('uglify'));
    gulp.watch(['./css/styles.css', './**/*.twig', './js/*.js'], { usePolling: true }, function (files){
        livereload.changed(files)
    });
    done();
}));

gulp.task('default', gulp.series('sass', 'uglify', function(done){
    done();
}));
