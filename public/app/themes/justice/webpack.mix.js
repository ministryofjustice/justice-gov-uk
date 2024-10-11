let mix = require('laravel-mix')
require("@tinypixelco/laravel-mix-wp-blocks");

mix.setPublicPath('./dist/')

mix
    .block("src/js/block-editor.js", "dist")
    .js('src/js/app.js', 'dist/app.min.js')
    .js('src/js/admin/index.js', 'dist/admin.min.js')
    .js('src/js/login.js', 'dist/js/login.min.js')
    /** patch code for CCFW **/
    .js('src/patch/js/ccfw-cookie-manage.js', 'dist/patch/js/ccfw-cookie-manage.js')
    .js('src/patch/js/ccfw-frontend.js', 'dist/patch/js/ccfw-frontend.js')
    /** -------------- **/
    .sass('src/sass/app.scss', 'dist/css/app.min.css')
    .sass('src/sass/admin.scss', 'dist/css/admin.min.css')
    .sass('src/sass/editor.scss', 'dist/css/editor.min.css')
    .sass('src/sass/login.scss', 'dist/css/login.min.css')
    .copy('src/archived/', 'dist/archived/')
    .copy('src/img/', 'dist/img/')
    .css('src/css/editor-style.css', 'dist/css/')
    .copy('dist/*.asset.php', 'dist/php')
    .options({ processCssUrls: false })

if (mix.inProduction()) {
    mix.version()
} else {
    mix.sourceMaps()
}
