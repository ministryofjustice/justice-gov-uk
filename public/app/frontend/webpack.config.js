const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const { WebpackManifestPlugin } = require('webpack-manifest-plugin');

module.exports = {
    experiments: {
        layers: true,
    },
    entry: {
        main: {
            import: './src/index.js',
            layer: 'main',
        },
    },
    output: {
        filename: '[name].js?ver=[contenthash]',
        path: path.resolve(__dirname, 'dist'),
        clean: true,
    },
    resolve: {
        modules: [path.resolve(__dirname, '/node_modules/')],
        alias: {
            '@components': path.resolve(__dirname, 'src/components'),
            '@layouts': path.resolve(__dirname, 'src/layouts'),
        }
    },
    module: {
        rules: [
            {
                test: /\.(js|jsx)$/,
                exclude: /node_modules/,
                use: {
                    loader: "swc-loader"
                }
            },
            {
                test: /\.twig$/,
                oneOf: [
                    {
                        issuerLayer: 'storybook',
                        loader: 'twigjs-loader'
                    },
                    {
                        issuerLayer: 'main',
                        loader: 'file-loader',
                        options: {
                            name: '[path]/[name].[ext]',
                        }
                    }
                ]
            },
            {
                test: /\.s?css$/i,
                use: [
                    MiniCssExtractPlugin.loader,
                    { loader: 'css-loader', options: { url: true, sourceMap: true } },
                    'postcss-loader',
                    { loader: "sass-loader", options: { api: "modern-compiler" } }
                ]
            },
            {
                test: /\.(jpg|png|svg|gif)$/,
                type: 'asset/resource'
            },
        ],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: '[name].css?ver=[contenthash]',
            chunkFilename: '[id].css',
        }),
        new WebpackManifestPlugin({
            filter: (file) => ['main.css', 'main.js'].includes(file.name),
            publicPath: '',
        })
    ],
}
