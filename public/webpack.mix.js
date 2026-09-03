const mix = require('laravel-mix')
const path = require('path')
/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */





mix.webpackConfig(webpack => {
    return {
      output: {
       chunkFilename: "js/chunks/[name].js",
       publicPath: "/"
      },
      plugins: [
        new webpack.ProvidePlugin({
            $: 'jquery',
            jQuery: 'jquery',
            'window.jQuery': 'jquery'
        }) // ,
        // new webpack.IgnorePlugin({ resourceRegExp: /^\.\/locale$/, contextRegExp: /moment$/ })
      ],
      resolve: {
        alias: {
            //adding react and react-dom may not be necessary for you but it did fix some issues in my setup.
            'react' : path.resolve('node_modules/react'),
            'react-dom' : path.resolve('node_modules/react-dom'),

            'components' : path.resolve('resources/js/src/components'),
            'pages' : path.resolve('resources/js/src/pages'),
            'themes' : path.resolve('resources/js/src/themes'),
            'layouts' : path.resolve('resources/js/src/layouts'),
            'hooks' : path.resolve('resources/js/src/hooks'),
        },
      }// ,
     //  optimization: {
     //   runtimeChunk: 'single',
     //   splitChunks: {
     //    chunks: 'all',
     //    maxInitialRequests: Infinity,
     //    minSize: 0,
     //    cacheGroups: {
     //      reactVendor: {
     //        test: /[\\/]node_modules[\\/](react|react-dom)[\\/]/,
     //        name: "js/reactvendor"
     //      },
     //      formik: {
     //        test: /[\\/]node_modules[\\/](formik)[\\/]/,
     //        name: "js/formikvendor"
     //      },
     //      utilityVendor: {
     //        test: /[\\/]node_modules[\\/](lodash|moment|moment-timezone)[\\/]/,
     //        name: "js/utilityVendor"
     //      },
     //      bootstrapVendor: {
     //        test: /[\\/]node_modules[\\/](react-bootstrap)[\\/]/,
     //        name: "js/bootstrapVendor"
     //      }
     //    },
     //  },
     // }
    }
});

// mix.autoload({
//     jquery: ['$', 'window.jQuery']
// })

mix.js('resources/js/vendors/admin-lte-core.js', 'public/js/vendors').js('resources/js/bootstrap.js', 'public/js')
mix.sass('resources/sass/vendors/admin-lte-core.scss', 'public/css/vendors').sass('resources/sass/app.scss', 'public/css')

mix.js('resources/js/app.js', 'public/js').react().extract(['react', 'react-dom', 'jquery', 'formik', '@material-ui', '@date-io', 'prop-types', 'date-fns', 'dom-helpers', 'lodash'])
mix.sourceMaps().version()
