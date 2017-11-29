var gulp = require('gulp');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var changed = require('gulp-changed');

var stylePaths = [
  'web/css/**/*.scss'
];

var jsPaths = [
  'node_modules/jquery/dist/jquery.js',
  'node_modules/popper.js/dist/umd/popper.js',
  'node_modules/bootstrap/dist/js/bootstrap.js'
];

gulp.task('default', function() {
    return gulp.start('styles', 'scripts', 'fonts');
});

gulp.task('watch', function() {
    gulp.watch(stylePaths.concat(jsPaths), ['styles', 'scripts', 'fonts']);
});

gulp.task('styles', function() {
  return gulp.src(stylePaths)
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(concat('styles.css'))
    .pipe(gulp.dest('web/build/'));
});

gulp.task('scripts', function() {
  return gulp.src(jsPaths)
    .pipe(concat('scripts.js'))
    .pipe(uglify())
    .pipe(gulp.dest('web/build/'));
});

gulp.task('fonts', function() {
  return gulp.src([
    'node_modules/font-awesome/fonts/*'
  ])
    .pipe(changed('web/build/fonts/'))
    .pipe(gulp.dest('web/build/fonts/'));
});