// webpack.config.js
var Encore = require('@symfony/webpack-encore');

// конфигурация для frontend
Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/assets/build/')
    .setPublicPath('/assets/build')
    .addEntry('js/app', [
        './assets/assets/js/jquery.min.js',
        './assets/assets/js/bootstrap.min.js',
        './assets/assets/js/nav-menu.js',
        './assets/assets/js/recaptcha.js',
        './assets/assets/js/main.js',
    ])
    .addStyleEntry('css/app', [
        './assets/assets/css/plugins.css',
        './assets/assets/css/styles.scss'
    ])
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
