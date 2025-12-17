import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';
import autoprefixer from 'autoprefixer';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // CSS
                'resources/css/app.css',

                // Main JS entry points
                'resources/js/app.js',
                'resources/js/shared/index.js',

                // Role-specific bundles
                'resources/js/midwife/index.js',
                'resources/js/bhw/index.js',
                'resources/js/admin/index.js',

                // Page-specific entries
                'resources/js/pages/patients.js',
            ],
            refresh: true,
        }),
    ],
    css: {
        postcss: {
            plugins: [
                tailwindcss,
                autoprefixer,
            ],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
            '@shared': path.resolve(__dirname, 'resources/js/shared'),
            '@utils': path.resolve(__dirname, 'resources/js/shared/utils'),
            '@components': path.resolve(__dirname, 'resources/js/shared/components'),
            '@services': path.resolve(__dirname, 'resources/js/shared/services'),
            '@midwife': path.resolve(__dirname, 'resources/js/midwife'),
            '@bhw': path.resolve(__dirname, 'resources/js/bhw'),
            '@admin': path.resolve(__dirname, 'resources/js/admin'),
        },
    },
    build: {
        manifest: true,
        outDir: 'public/build',
        // FIX: Force manifest to be at root of build directory for Laravel compatibility
        rollupOptions: {
            output: {
                // Place manifest at root of outDir instead of .vite subdirectory
                assetFileNames: (assetInfo) => {
                    return 'assets/[name]-[hash][extname]';
                },
                chunkFileNames: 'assets/[name]-[hash].js',
                entryFileNames: 'assets/[name]-[hash].js',
                manualChunks: {
                    // Vendor chunk for third-party libraries
                    vendor: ['axios'],

                    // Shared utilities chunk
                    utils: [
                        'resources/js/shared/utils/validation.js',
                        'resources/js/shared/utils/api.js',
                        'resources/js/shared/utils/sweetalert.js',
                        'resources/js/shared/utils/formatters.js',
                        'resources/js/shared/utils/dom.js',
                    ].filter(file => {
                        // Only include files that exist
                        try {
                            return require.resolve(file);
                        } catch {
                            return false;
                        }
                    }),
                },
            },
        },
    },
    server: {
        hmr: {
            host: 'localhost',
        },
        watch: {
            usePolling: true,
        },
    },
});