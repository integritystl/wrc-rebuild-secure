var gulp  = require('gulp');
var sass  = require('gulp-sass');
var csso = require('gulp-csso');
var sourcemaps  = require('gulp-sourcemaps');
var plumber = require('gulp-plumber');
var concat = require('gulp-concat');
var autoprefixer = require('autoprefixer');
var postcss      = require('gulp-postcss');
var bourbon = require('node-bourbon').includePaths;
var neat = require('node-neat').includePaths;

var build = './wp-content/themes/wrcgroup-secure/';


gulp.task('sass', function () {
  gulp.src('./wp-content/themes/wrcgroup-secure/sass/style.scss')
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass({
      includePaths: [].concat(bourbon, neat),
    }).on('error', sass.logError))
    .pipe(postcss([ autoprefixer("last 2 versions", "ie 10", "ios 10" ) ]))
    .pipe(sourcemaps.write())
    .pipe(gulp.dest(build));
});

// UPDATE THIS after reviewing JS that's being included in site from old site
gulp.task('concat', function() {
    return gulp.src([
        './wp-content/themes/wrcgroup-secure/js/fastclick.js',
        './wp-content/themes/wrcgroup-secure/js/jquery.sidr.min.js',
        './wp-content/themes/wrcgroup-secure/js/main.js',
        ])
        .pipe(sourcemaps.init())
        .pipe(concat('wrcgroup-secure.js'))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest( './wp-content/themes/wrcgroup-secure/js/'));
});

gulp.task('default', ['sass', 'concat'], function () {
  gulp.watch('./wp-content/themes/wrcgroup-secure/sass/**/*.scss', ['sass']);
//  gulp.watch('./src/wp-content/themes/wrcgroup-secure/js/**/*.js', ['concat']);
});
