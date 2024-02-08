let mix = require('laravel-mix')

mix.setPublicPath('./dist/')

/*******************/
mix.js('src/js/app.js', 'dist/app.min.js')
    .js('src/js/admin.js', 'dist/admin.min.js')
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
