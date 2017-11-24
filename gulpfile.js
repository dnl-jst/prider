var gulp = require('gulp');
var sass = require('gulp-sass');

gulp.task('default', function() {
    return gulp.start('styles');
});

gulp.task('watch', function() {
    gulp.watch('web/css/**/*.scss', ['styles']);
});

gulp.task('styles', function() {
  return gulp.src('web/css/**/*.scss')
    .pipe(sass({outputStyle: 'compressed'}).on('error', sass.logError))
    .pipe(gulp.dest('web/css/'));
});