let mix = require('laravel-mix')
require("@tinypixelco/laravel-mix-wp-blocks");

mix.setPublicPath('./dist/')

/*******************/
mix
    .block("src/js/block-editor.js", "dist")
    .js('src/js/app.js', 'dist/app.min.js')
    .sass('src/sass/app.scss', 'dist/css/app.min.css')
    .sass('src/sass/editor.scss', 'dist/css/editor.min.css')
    .copy('src/img/', 'dist/img/')
    .css('src/css/editor-style.css', 'dist/css/')
    .copy('dist/*.asset.php', 'dist/php')
    .options({ processCssUrls: false })

if (mix.inProduction()) {
    mix.version()
} else {
    mix.sourceMaps()
}
