let mix = require('laravel-mix')
require("@tinypixelco/laravel-mix-wp-blocks");

mix.setPublicPath('./dist/')

/*******************/
mix
    .block("src/js/block-editor.js", "dist")
    .js('src/js/app.js', 'dist/app.min.js')
    .sass('src/sass/app.scss', 'dist/app.min.css')
    .copy('src/img/', 'dist/img/')
    .css('src/css/global.css', 'dist/css/')
    .css('src/css/media.queries.css', 'dist/css/')
    .css('src/css/editor-style.css', 'dist/css/')
    .css('src/css/wp-admin-override.css', 'dist/css/')
    .options({ processCssUrls: false })

if (mix.inProduction()) {
    mix.version()
} else {
    mix.sourceMaps()
}
