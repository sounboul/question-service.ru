// webpack.config.js
var Encore = require('@symfony/webpack-encore');

// конфигурация для frontend
Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/assets/build/')
    .setPublicPath('/assets/build')
    .addEntry('js/jquery', './assets/assets/js/jquery.min.js')
    .addEntry('js/bootstrap', './assets/assets/js/bootstrap.min.js')
    .addEntry('js/nav-menu', './assets/assets/js/nav-menu.js')
    .addEntry('js/main', './assets/assets/js/main.js')
    .addStyleEntry('css/plugins', './assets/assets/css/plugins.css')
    .addStyleEntry('css/styles', './assets/assets/css/styles.scss')
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSourceMaps(false)
    .enableVersioning()
    .cleanupOutputBeforeBuild()
;

// использование CDN (опционально) - только для frontend
//if (Encore.isProduction()) {
//    Encore.setPublicPath('https://static.question-service.ru');
//}

const frontend = Encore.getWebpackConfig();
frontend.name = 'frontend';

// перезапустить Encore, чтобы построить вторую конфигурацию
Encore.reset();

// конфигурация для backend
Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/assets-backend/')
    .setPublicPath('/assets-backend')
    .addEntry('js/app', './assets/assets-backend/js/app.js')
    .addStyleEntry('css/main', './assets/assets-backend/css/main.scss')
    .addStyleEntry('css/app', './assets/assets-backend/css/app.css')
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSourceMaps(false)
    .enableVersioning()
    .cleanupOutputBeforeBuild()
;

const backend = Encore.getWebpackConfig();
backend.name = 'backend';

// экспортировать финальную конфигурацию в качестве массива множества конфигураций
module.exports = [frontend, backend];
