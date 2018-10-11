'use strict';
var
  gulp = require('gulp'),
  watch = require('gulp-watch'), // Rebuild only changed files
  plumber = require('gulp-plumber'), // Protect from exit on error
  prefixer = require('gulp-autoprefixer'),
  sass = require('gulp-sass'),
  sourcemaps = require('gulp-sourcemaps'),
  concat = require('gulp-concat'),
	spritesmith = require('gulp.spritesmith'),
	imagemin = require('gulp-imagemin'),
  uglify = require('gulp-uglify'),
	buffer = require('vinyl-buffer'),
	merge = require('merge-stream'),
	svgSprite = require('gulp-svg-sprite'),
	size = require('gulp-size'),
  shell = require('gulp-shell'),
  argv = require('yargs').argv,
  csscomb = require('gulp-csscomb'),
  browserSync = require("browser-sync"),
  reload = browserSync.reload,
  config = require('./configs/config_' + argv.env + '.js'),
  themePath = config.theme_path;

var 
  path = {
  	sprite: {
  		src_png: themePath + 'src/isrc/*.png',
  		dst_img: themePath + 'css/',
  		dst_scss: themePath + 'src/csrc/common/',
  		svg: 'css/img/sprite.svg',
  	},
  	styles: {
  		css: themePath + 'css/',
  		scss: themePath + 'src/**/*.scss',
  		scss_input: themePath + 'src/csrc/styles.scss'
  	},
  	js: {
  		src: themePath + 'src/**/*.js',
  		dst: themePath + 'js/'
  	},
  	template: themePath + '**/*.twig'
  };

gulp.task('webserver', ['css', 'js'], function () {
    browserSync.init({
        proxy: config.site_name,
        open: false
    });
});
 
gulp.task('sprite:png', function () {
  var spriteData = gulp.src(path.sprite.src_png)
  .pipe(spritesmith({
    imgName: 'img/sprite.png',
    cssName: '_sprite.scss',
    algorithm: 'binary-tree',
	padding: 2
  }));
  var imgStream = spriteData.img
  .pipe(buffer())
  .pipe(imagemin())
  .pipe(gulp.dest(path.sprite.dst_img));
  var cssStream = spriteData.css
  .pipe(gulp.dest(path.sprite.dst_scss));
  return merge(imgStream, cssStream);
});

gulp.task('sprite:svg', function () {
	return gulp.src(path.sprite.src)
		.pipe(svgSprite({
			shape: {
				spacing: {
					padding: 2
				}
			},
			mode: {
				css: {
					dest: "./",
					layout: "vertical",
					sprite: path.sprite.svg,
					bust: false,
					render: {
						scss: {
							dest: path.sprite.scss,
							template: path.sprite.tpl
						}
					}
				}
			},
			variables: {
				mapname: "icons"
			}
		}))
		.pipe(gulp.dest(themePath));
});

gulp.task('css', function () {
  gulp.src(path.styles.scss_input)
    .pipe(plumber())
    .pipe(sourcemaps.init())
    .pipe(sass()).on('error', sass.logError)
    .pipe(prefixer())
    // .pipe(csscomb())
    .pipe(sourcemaps.write('/'))
    .pipe(gulp.dest(path.styles.css))
    .pipe(reload({stream: true}));
});

gulp.task('js', function () {
  var min = argv.min;

  var process = gulp.src(path.js.src)
  .pipe(plumber())
  .pipe(sourcemaps.init())
  .pipe(concat('scripts.js'));

  if (min) {
    process
			.pipe(uglify())
  }

  process
		.pipe(sourcemaps.write('/'))
    .pipe(gulp.dest(path.js.dst))
    .pipe(reload({stream: true}));
});

gulp.task('clearcache', shell.task([
	'cd ' + themePath + ' && drush cr'
]));

gulp.task('reload', ['clearcache'], function () {
    reload();
});

gulp.task('watch', ['webserver'], function () {
  watch([path.styles.scss], function (event, cb) {
    gulp.start('css');
  });
  watch([path.sprite.src_png], function (event, cb) {
    gulp.start('sprite:png');
  });
  watch([path.js.src], function (event, cb) {
    gulp.start('js');
  });
  watch([path.template], function (event, cb) {
    gulp.start('reload');
  });
});

gulp.task('default', ['watch', 'css']);