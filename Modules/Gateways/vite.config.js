const dotenvExpand = require('dotenv-expand');
dotenvExpand(require('dotenv').config({ path: '../../.env'/*, debug: true*/}));

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-Gateways',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-Gateways',
            input: [
                __dirname + '/Resources/public/assets/sass/app.scss',
                __dirname + '/Resources/public/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
