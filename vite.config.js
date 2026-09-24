import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
    build: {
        sourcemap: false,
        chunkSizeWarningLimit: 1500,
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[hash].js',
                chunkFileNames: 'assets/[hash].js',
                assetFileNames: 'assets/[hash].[ext]',
            },
        },
    },

    resolve: {
        alias: {
            '@': fileURLToPath(new URL('./resources/js/react', import.meta.url)),
        },
    },

    plugins: [
        laravel({
            input: [
                // React + shadcn/ui pages (Inertia). Blade pages left (login, lock
                // screen, errors, print) carry their own inline styles.
                'resources/css/react.css',
                'resources/js/react/app.tsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
});
