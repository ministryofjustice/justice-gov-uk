const autoprefixer = require('autoprefixer');
const inlineSvg = require("postcss-inline-svg");

module.exports = {
    plugins: [
        autoprefixer(),
        inlineSvg(),
    ]
}