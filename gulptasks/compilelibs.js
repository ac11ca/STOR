const gulp = require('gulp');

gulp.task('compilelibs',['delete'], function () {
   
   gulp
    .src(['./node_modules/bootstrap/dist/**/*'])
    .pipe(gulp.dest('./web/dist/lib/bootstrap'));

   gulp
    .src(['./node_modules/jquery/dist/**/*'])
    .pipe(gulp.dest('./web/dist/lib/jquery'));

   gulp
    .src(['./node_modules/moment/min/moment.min.js'])
    .pipe(gulp.dest('./web/dist/lib/moment'));

   gulp
    .src(['./node_modules/moment-timezone/builds/moment-timezone-with-data-2010-2020.min.js'])
    .pipe(gulp.dest('./web/dist/lib/moment-timezone'));

    gulp
    .src(['./node_modules/ckeditor/**/*'])
    .pipe(gulp.dest('./web/dist/lib/ckeditor/'));

    gulp
    .src(['./node_modules/smoothstate/src/**/*'])
    .pipe(gulp.dest('./web/dist/lib/smoothstate/'));

    gulp
    .src(['./node_modules/chart.js/dist/**/*'])
    .pipe(gulp.dest('./web/dist/lib/chart.js/'));

    return true;
});
