var gulp = require('gulp');
var sass = require('gulp-ruby-sass');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var autoprefixer = require('gulp-autoprefixer');

gulp.task('default', function() {
    return gulp.start('styles', 'watch');
});

gulp.task('watch', function() {
    gulp.watch('src/web/css/**/*.scss', ['styles']);
});

gulp.task('styles', function() {

  return sass('src/web/css/**/*.scss', {compass: true, sourcemap: false, style: 'expanded'})
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('src/web/css/'));

});