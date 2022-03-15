const mix = require('laravel-mix');
mix.autoload({
    jquery: ['$', 'global.jQuery', "jQuery", "global.$", "jquery", "global.jquery"]
});
mix.setPublicPath(__dirname);
mix.js('assets/src/main.js', 'assets/dist/js')
    .js('assets/src/preview.js', 'assets/dist/js')
    .js('assets/src/config.js', 'assets/dist/js')
    .js('assets/src/short.js', 'assets/dist/js')
    .sass('assets/src/main.scss', 'assets/dist/css')
    .sass('assets/src/config.scss', 'assets/dist/css')
    .sass('assets/src/front.scss', 'assets/dist/css')
    .version()
    .copy('assets/src/hyperdown.js', 'assets/dist/external')
    .copyDirectory('node_modules/highlight.js/styles', 'assets/dist/external/highlight.js');