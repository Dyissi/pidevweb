const Encore = require('@symfony/webpack-encore');

// Ensure runtime environment is configured
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    .addEntry('app', './assets/app.js')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())

    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // ✅ This line enables auto-registration of Stimulus controllers
    .enableStimulusBridge('./assets/controllers.json')

    // Optional: SCSS
    //.enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
