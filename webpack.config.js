// webpack.config.js
var Encore = require('@symfony/webpack-encore');

// конфигурация для frontend
Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/assets/')
    .setPublicPath('/assets')
    .addEntry('app', './assets/assets/js/app.js')
    .addStyleEntry('main', './assets/assets/css/main.scss')
    .addStyleEntry('styles', './assets/assets/css/styles.css')
    .copyFiles({
        from: './assets/assets/images',
        to: 'images/[path][name].[ext]',
    })
    .autoProvidejQuery()
    .enableSassLoader()
    .enableSourceMaps(false)
    .enableVersioning()
    .cleanupOutputBeforeBuild()
;

// использование CDN (опционально) - только для frontend
if (false && Encore.isProduction()) {
    Encore.setPublicPath('https://static.question-service.ru');
}

const frontend = Encore.getWebpackConfig();
frontend.name = 'frontend';

// перезапустить Encore, чтобы построить вторую конфигурацию
Encore.reset();

// конфигурация для backend
Encore
    .enableSingleRuntimeChunk()
    .setOutputPath('public/assets-backend/')
    .setPublicPath('/assets-backend')
    .addEntry('app', './assets/assets-backend/js/app.js')
    .addStyleEntry('main', './assets/assets-backend/css/main.scss')
    .addStyleEntry('styles', './assets/assets-backend/css/styles.css')
    .copyFiles({
        from: './assets/assets-backend/images',
        to: 'images/[path][name].[ext]',
    })
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
