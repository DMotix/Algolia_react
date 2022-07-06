var path = require('path');
var webpack = require('webpack');
const UglifyJSPlugin = require('uglifyjs-webpack-plugin');

module.exports = {
    entry: './views/js/main.js',
    output: {
        path: path.join(__dirname, 'views/js/build'),
        filename: '[name].adsaf_script.js'
    },
    mode: "production",
    watch: true,
    watchOptions: {
        poll: true
    },
    optimization: {
        minimize: false,
        splitChunks: {
            cacheGroups: {
                commons: {
                    test: /[\\/]node_modules[\\/]/,
                    name: "vendors",
                    chunks: "all"
                }
            }
        }
    },
    module: {
        rules: [
            {
                test: /.jsx?$/,
                use: [{
                        loader: 'babel-loader',
                        options: {
                            presets: ['es2015', 'react']
                        }
                    }],
                exclude: /node_modules/
            },
            {
                test: /\.js$/,
                use: [{
                        loader: 'babel-loader',
                        options: {
                            presets: ['es2015']
                        }
                    }],
                exclude: /node_modules/
            }
        ]
    },
    plugins: [
        new webpack.ProvidePlugin({
            React: 'react', // ReactJS module name in node_modules folder
            ReactDOM: 'react-dom',
            $: 'jquery',
            jQuery: 'jquery'
        }),
        new UglifyJSPlugin({
            uglifyOptions: {
                output: {
                    comments: false
                },
                compress: {
                    keep_classnames: false,
                    keep_fnames: false
                },
                mangle: {
                  keep_classnames: false,
                  keep_fnames: false
                }
            }
        })
                //new config.optimization.minimize()
    ]
};