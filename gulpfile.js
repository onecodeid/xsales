const gulp = require('gulp');
const browserSync = require('browser-sync').create();
const minify = require('gulp-minify');

function scripts () {
  // return gulp.src('_modules/misc/*.js')
  //     .pipe(minify({
  //         ext: {
  //             min: '.min.js'
  //         },
  //         ignoreFiles: ['-min.js']
  //     }))
  //     .pipe(gulp.dest('_modules/_min'))
}
gulp.task('min-js', function() {
  // return gulp.src('_modules/master/*.js')
  //     .pipe(minify({
  //         ext: {
  //             min: '.min.js'
  //         },
  //         ignoreFiles: ['-min.js']
  //     }))
  //     .pipe(gulp.dest('_modules/_min'))
});

// Task to serve the Vue app using BrowserSync
function serve() {
  // Serve files from the root directory of your Vue project
//   browserSync.init({
//     server: {
//       baseDir: './',
//     },
//   });

  browserSync.init({
    proxy: 'localhost/hris/' // Update with your CodeIgniter app's local URL
  });

  // gulp.watch('_modules/misc/*.js', scripts); 
  // Watch for changes in Vue app files and reload the browser
  gulp.watch('**/*').on('change', browserSync.reload);
  
}

// Default task
exports.default = serve;
