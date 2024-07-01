const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

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
        publicPath: './dist',
        filename: '[name].js',
        path: path.resolve(__dirname, 'dist'),
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
                use: [MiniCssExtractPlugin.loader, { loader: 'css-loader', options: { url: true, sourceMap: true } }, 'postcss-loader', 'sass-loader']
            },
            {
                test: /\.(jpg|png|svg|gif)$/,
                type: 'asset/resource'
            },
        ],
    },
    plugins: [new MiniCssExtractPlugin({
        filename: '[name].css',
        chunkFilename: '[id].css',
    })],
}