const mix = require('laravel-mix');
const path = require('path');
const fs = require('fs');
const crypto = require('crypto');
const babel = require('@babel/core');
const MixClass = require('laravel-mix/src/Mix');
const mixInstance = MixClass.primary;

const transpileDependencies = [
    /node_modules[\\\/]@inertiajs[\\\/]inertia/,
    /node_modules[\\\/]@inertiajs[\\\/]vue3/,
    /node_modules[\\\/]@inertiajs[\\\/]progress/,
    /node_modules[\\\/]@inertiajs[\\\/]core/,
];

mix.babelConfig({
    plugins: [
        '@babel/plugin-transform-optional-chaining',
        '@babel/plugin-transform-nullish-coalescing-operator',
    ],
});

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .vue({ version: 3 })
    .sass('resources/sass/app.scss', 'public/css')
    .version();

mix.webpackConfig({
    module: {
        rules: [
            {
                test: /\.m?js$/,
                include: transpileDependencies,
                use: {
                    loader: 'babel-loader',
                    options: mixInstance.config.babel(),
                },
            },
        ],
    },
});

// Ensure Inertia packages get transpiled so legacy WebViews/iOS Safari don't choke on optional chaining.
mix.override(config => {
    const babelRule = config.module.rules.find(rule => {
        return rule.test && rule.test.toString().includes('jsx?');
    });

    if (!babelRule) {
        return;
    }

    const originalExclude = babelRule.exclude;

    babelRule.exclude = modulePath => {
        if (!modulePath) {
            return false;
        }

        if (transpileDependencies.some(pattern => pattern.test(modulePath))) {
            return false;
        }

        if (typeof originalExclude === 'function') {
            return originalExclude(modulePath);
        }

        if (Array.isArray(originalExclude)) {
            return originalExclude.some(pattern => {
                if (pattern instanceof RegExp) {
                    return pattern.test(modulePath);
                }
                return typeof pattern === 'string' ? modulePath.startsWith(pattern) : false;
            });
        }

        if (originalExclude instanceof RegExp) {
            return originalExclude.test(modulePath);
        }

        return /node_modules/.test(modulePath);
    };
});

mix.then(() => {
    if (!mix.inProduction()) {
        return;
    }

    const input = path.resolve(__dirname, 'public/js/app.js');
    const result = babel.transformFileSync(input, {
        babelrc: false,
        configFile: false,
        presets: [
            ['@babel/preset-env', { targets: { safari: '12' }, bugfixes: true, modules: false }],
        ],
        plugins: [
            '@babel/plugin-transform-optional-chaining',
            '@babel/plugin-transform-nullish-coalescing-operator',
        ],
    });

    fs.writeFileSync(input, result.code, 'utf8');

    const manifestPath = path.resolve(__dirname, 'public/mix-manifest.json');
    const manifest = JSON.parse(fs.readFileSync(manifestPath, 'utf8'));
    const hash = crypto.createHash('md5').update(result.code).digest('hex');
    manifest['/js/app.js'] = `/js/app.js?id=${hash}`;
    fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 4));
});
