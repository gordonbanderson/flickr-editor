let mix = require('laravel-mix');
let fs = require('fs');

const Path = require('path');

const ENV = process.env.NODE_ENV;

// Import the core config
const webpackConfig = require('@silverstripe/webpack-config');
const {
	resolveJS,
	externalJS,
	moduleJS,
	pluginJS,
	moduleCSS,
	pluginCSS,
} = webpackConfig;

const PATHS = {
	MODULES: 'node_modules',
	FILES_PATH: '../',
	ROOT: Path.resolve(),
	SRC: Path.resolve('client/src'),
	DIST: Path.resolve('client/dist'),
};

const externals = externalJS(ENV, PATHS);
delete externals.reactstrap;

// drop console.log in prod
if (mix.inProduction()) {
	mix.options({
		terser: {
			terserOptions: {
				compress: {
					drop_console: true
				}
			}
		}
	});
}


mix.webpackConfig({
	node: {
		fs: "empty",
		child_process: "empty",
		net: "empty",
		module: "empty",
		fsevents: "empty"
	},
	output: {
		jsonpFunction: 'jsonpFunction'
	},

	target: "web",
	module: {
		rules: [
			{
				test: /\.(jsx|js|vue)$/,
				loader: 'eslint-loader',
				enforce: 'pre',
				exclude: /(node_modules)/,
				options: {
					formatter: require('eslint-friendly-formatter')
				}
			},
			{
				test: /\.tsx?$/,
				loader: "ts-loader",
				exclude: /node_modules/
			}
		]
	},
	resolve: {
		extensions: ['*', '.js', '.jsx', '.vue', '.ts', '.tsx'],
	},
});

mix.sass('client/src/css/flickr.scss', 'dist/client/css')
	.sass('admin/client/src/css/flickredit.scss', 'dist/admin/client/css')
	.sass('admin/client/src/css/flickrreact.scss', 'dist/admin/client/css')

	.js('admin/client/src/js/flickredit.js', 'dist/admin/client/js')
	.react('admin/client/src/js/components/app.js', 'dist/admin/client/js')

	.sourceMaps()

// this renders the app not running
	/*
	.extract(['apollo-boost', 'apollo-cache', 'apollo-cache-inmemory', 'apollo-client',
		'apollo-link', 'apollo-link-error', 'apollo-link-http', 'apollo-link-http-common',
		'apollo-utilities' ], `dist/admin/client/js/thirdpartyvendor.js`)
*/
/*
apollo-boost
apollo-cache
apollo-cache-inmemory
apollo-client
apollo-link
apollo-link-error
apollo-link-http
apollo-link-http-common
apollo-utilities


	.copyFile('admin/client/src/image/spinner.gif', 'dist/admin/client/img')
*/

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

//mix.js('src/app.js', 'dist/').sass('src/app.scss', 'dist/');

/*
mix.sass('src/scss/main.scss', 'dist/css')
    .options({
        postCss: [
            require('postcss-css-variables')(),
            require('postcss-discard-comments')({
                removeAll: true
            })
        ],
        fileLoaderDirs: {
            images: 'dist/img',
            fonts: 'dist/fonts'
        }
    })

    // bootstrap
    .sass('src/scss/bootstrap.scss', 'dist/css/bootstrap4.css')
    .options({
        postCss: [
            require('postcss-css-variables')()
        ]
    })

    .autoload({ jquery: ['$', 'window.jQuery', 'jQuery'] })

.js('node_modules/bootstrap/dist/js/bootstrap.js', 'dist/js/bootstrap4.js')

    .scripts([
            './node_modules/timeago/jquery.timeago.js',
            './node_modules/owl.carousel/dist/owl.carousel.js',
            './node_modules/lazysizes/lazysizes.js',
            './node_modules/photoswipe/dist/photoswipe.js',
            './node_modules/photoswipe/dist/photoswipe-ui-default.js',
            './node_modules/chart.js/dist/Chart.js'
    ],
        'dist/js/vendor.js')

    .js([
        './src/js/jquery.photoswipe/src/jquery.photoswipe.js',
            './src/js/initialise/init.js'
    ],
        'dist/js/main.js'
    )


    .copy('src/img/logo.png', 'dist/img/furniture/logo.png')
    .copy('src/img/noun_Cricket_13117.svg', 'dist/img/furniture/b1.svg')
    .copy('src/img/noun_Cricket_5360.svg', 'dist/img/furniture/b2.svg')
    .copy('src/img/sponsor-tailend.jpg', 'dist/img/furniture/sponsor-tailend.jpg')
    .copy('src/img/sponsor-angus-soft-fruits.gif', 'dist/img/furniture/sponsor-angus-soft-fruits.gif')
    .copy('src/img/cms-edit-logo.png', 'dist/img/furniture/cms-edit-logo.png')
    .copy('./dist/img/vendor/photoswipe/src/css/default-skin/default-skin.svg',
        '../../public/dist/img/vendor/photoswipe/src/css/default-skin/default-skin.svg')

*/
   // .sourceMaps(true, 'source-map')

// Full API
// mix.js(src, output);
// mix.react(src, output); <-- Identical to mix.js(), but registers React Babel compilation.
// mix.preact(src, output); <-- Identical to mix.js(), but registers Preact compilation.
// mix.coffee(src, output); <-- Identical to mix.js(), but registers CoffeeScript compilation.
// mix.ts(src, output); <-- TypeScript support. Requires tsconfig.json to exist in the same folder as webpack.mix.js
// mix.extract(vendorLibs);
// mix.sass(src, output);
// mix.less(src, output);
// mix.stylus(src, output);
// mix.postCss(src, output, [require('postcss-some-plugin')()]);
// mix.browserSync('my-site.test');
// mix.combine(files, destination);
// mix.babel(files, destination); <-- Identical to mix.combine(), but also includes Babel compilation.
// mix.copy(from, to);
// mix.copyDirectory(fromDir, toDir);
// mix.minify(file);
// mix.sourceMaps(); // Enable sourcemaps
// mix.version(); // Enable versioning.
// mix.disableNotifications();
// mix.setPublicPath('path/to/public');
// mix.setResourceRoot('prefix/for/resource/locators');
// mix.autoload({}); <-- Will be passed to Webpack's ProvidePlugin.
// mix.webpackConfig({}); <-- Override webpack.config.js, without editing the file directly.
// mix.babelConfig({}); <-- Merge extra Babel configuration (plugins, etc.) with Mix's default.
// mix.then(function () {}) <-- Will be triggered each time Webpack finishes building.
// mix.dump(); <-- Dump the generated webpack config object to the console.
// mix.extend(name, handler) <-- Extend Mix's API with your own components.
// mix.options({
//   extractVueStyles: false, // Extract .vue component styling to file, rather than inline.
//   globalVueStyles: file, // Variables file to be imported in every component.
//   processCssUrls: true, // Process/optimize relative stylesheet url()'s. Set to false, if you don't want them touched.
//   purifyCss: false, // Remove unused CSS selectors.
//   terser: {}, // Terser-specific options. https://github.com/webpack-contrib/terser-webpack-plugin#options
//   postCss: [] // Post-CSS options: https://github.com/postcss/postcss/blob/master/docs/plugins.md
// });
