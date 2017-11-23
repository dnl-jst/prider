var gulp = require('gulp');
var sass = require('gulp-ruby-sass');
var cleanCSS = require('gulp-clean-css');

gulp.task('default', function() {
    return gulp.start('styles', 'watch');
});

gulp.task('watch', function() {
    gulp.watch('web/css/**/*.scss', ['styles']);
});

gulp.task('styles', function() {

  return sass('web/css/**/*.scss', {compass: true, sourcemap: false, style: 'expanded'})
    .pipe(cleanCSS({compatibility: 'ie8'}))
    .pipe(gulp.dest('web/css/'));

});