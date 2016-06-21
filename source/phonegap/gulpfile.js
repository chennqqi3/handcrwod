var gulp = require('gulp');
var gutil = require('gulp-util');
var bower = require('bower');
var concat = require('gulp-concat');
var sass = require('gulp-sass');
var minifyCss = require('gulp-minify-css');
var rename = require('gulp-rename');
var sh = require('shelljs');
var jshint = require('gulp-jshint');
var sourcemaps = require('gulp-sourcemaps');

var paths = {
  sass: ['./scss/**/*.scss'],
  vendorjs: [
    './bower_components/jquery/dist/jquery.min.js',
    './bower_components/sjcl/sjcl.js',
	  './bower_components/angular-route/angular-route.min.js',
	  './bower_components/momentjs/min/moment.min.js',
	  './bower_components/moment-timezone/builds/moment-timezone-with-data-2010-2020.min.js',
	  './bower_components/angular-moment/angular-moment.min.js',
	  './bower_components/Autolinker.js/dist/Autolinker.min.js',
    './bower_components/angular-scroll/angular-scroll.min.js',
    './bower_components/ionic-datepicker/dist/ionic-datepicker.bundle.min.js',
    './bower_components/ionic-timepicker/dist/ionic-timepicker.bundle.min.js'
  ]
};

gulp.task('default', ['watch']);

gulp.task('sass', function(done) {
  gulp.src('./scss/ionic.app.scss')
    .pipe(sass({
      errLogToConsole: true
    }))
    .pipe(gulp.dest('./www/css/'))
    .pipe(minifyCss({
      keepSpecialComments: 0
    }))
    .pipe(rename({ extname: '.min.css' }))
    .pipe(gulp.dest('./www/css/'))
    .on('end', done);
});

gulp.task('vendor-js', function() {
  return gulp.src(paths.vendorjs)
    //.pipe(sourcemaps.init())
    .pipe(concat('vendor.js'))
    //only uglify if gulp is ran with '--type production'
    //.pipe(gutil.env.type === 'production' ? uglify() : gutil.noop()) 
    //.pipe(sourcemaps.write())
    .pipe(gulp.dest('./www/js/'));
});

gulp.task('watch', function() {
  gulp.watch(paths.sass, ['sass']);
});

gulp.task('install', ['git-check'], function() {
  return bower.commands.install()
    .on('log', function(data) {
      gutil.log('bower', gutil.colors.cyan(data.id), data.message);
    });
});

gulp.task('git-check', function(done) {
  if (!sh.which('git')) {
    console.log(
      '  ' + gutil.colors.red('Git is not installed.'),
      '\n  Git, the version control system, is required to download Ionic.',
      '\n  Download git here:', gutil.colors.cyan('http://git-scm.com/downloads') + '.',
      '\n  Once git is installed, run \'' + gutil.colors.cyan('gulp install') + '\' again.'
    );
    process.exit(1);
  }
  done();
});
