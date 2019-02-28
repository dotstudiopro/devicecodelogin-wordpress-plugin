const gulp = require('gulp'),
	sass = require('gulp-sass'),
	sourcemaps = require('gulp-sourcemaps'),
	minifyjs = require('gulp-minify'),
	minifycss = require('gulp-clean-css'),
	rename = require('gulp-rename'),
	fs = require('fs-extra'),
	assets = process.cwd() + "/assets";

gulp.task('sass', function () {
	return gulp.src(assets + '/sass/**/*.scss')
		.pipe(sass({
			outputStyle: 'compressed'
		}).on('error', sass.logError))
		.pipe(rename(function (path) {
			path.extname = ".min" + path.extname;
		}))
		.pipe(gulp.dest(assets + '/css'));
});

gulp.task('sass-dev', function () {
	return gulp.src(assets + '/sass/**/*.scss')
		.pipe(sourcemaps.init())
		.pipe(sass().on('error', sass.logError))
		.pipe(sourcemaps.write())
		.pipe(gulp.dest(assets + '/css-dev'));
});

gulp.task('css', function () {
	return new Promise(function (resolve, reject) {
		try {
			// Check to see if we have any CSS that was put into the main CSS folder that is unminified (as far as we can tell)
			// and move it to our unminified folder, so we create a minified version for upload
			fs.readdirSync(assets + '/css').forEach(fileName => {
				const isDir = fs.existsSync(assets + '/css/' + fileName) && fs.lstatSync(assets + '/css/' + fileName).isDirectory();
				// Ensure that we are looking only at css files, not minified files, and not directories
				if (fileName.indexOf(".css") > -1 && fileName.indexOf(".min.css") < 0 && !isDir) {
					fs.moveSync(assets + '/css/' + fileName, assets + '/css/unminified/' + fileName)
				}
			});
			// Minify our files
			gulp.src(assets + '/css/unminified/*.css')
				.pipe(minifycss({
					level: 2
				}))
				.pipe(rename(function (path) {
					path.extname = ".min.css";
				}))
				.pipe(gulp.dest(assets + '/css'));
			// Remove the unminified versions that cleanCSS creates
			fs.readdirSync(assets + '/css').forEach(fileName => {
				const isDir = fs.existsSync(assets + '/css/' + fileName) && fs.lstatSync(assets + '/css/' + fileName).isDirectory();
				// Ensure that we are looking only at css files, not minified files, and not directories
				if (fileName.indexOf(".css") > -1 && fileName.indexOf(".min.css") < 0 && !isDir) {
					fs.unlinkSync(assets + '/css/' + fileName);
				}
			});
			resolve("Done");
		} catch(e) {
			reject(e.message);
		}
	});
});

gulp.task('js', function () {
	return new Promise(function (resolve, reject) {
		try {
			// Check to see if we have any JS that was put into the main JS folder that is unminified (as far as we can tell)
			// and move it to our unminified folder, so we create a minified version for upload
			fs.readdirSync(assets + '/js').forEach(fileName => {
				const isDir = fs.existsSync(assets + '/js/' + fileName) && fs.lstatSync(assets + '/js/' + fileName).isDirectory();
				// Ensure that we are looking only at css files, not minified files, and not directories
				if (fileName.indexOf(".js") > -1 && fileName.indexOf(".min.js") < 0 && !isDir) {
					fs.moveSync(assets + '/js/' + fileName, assets + '/js/unminified/' + fileName)
				}
			});
			gulp.src(assets + '/js/unminified/*.js')
				.pipe(minifyjs({
		        ext:{
		        	src: '-dsp-unminified.js',
		          min:'.min.js'
		        },
		        ignoreFiles: ['-min.js']
		    }))
				.pipe(gulp.dest(assets + '/js'))
				.on('end', () => {
					// Remove the unminified versions that cleanCSS creates
					fs.readdirSync(assets + '/js').forEach(fileName => {
						const isDir = fs.existsSync(assets + '/js/' + fileName) && fs.lstatSync(assets + '/js/' + fileName).isDirectory();
						// Ensure that we are looking only at our unminified js files
						if (fileName.indexOf("-dsp-unminified.js") > -1 && !isDir) {
							fs.unlinkSync(assets + '/js/' + fileName);
						}
					});
					resolve("Done");
				});
		} catch(e) {
			reject(e.message);
		}
	});
});