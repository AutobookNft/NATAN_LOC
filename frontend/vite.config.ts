import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
    root: './',
    publicDir: 'public',
    build: {
        outDir: '../laravel_backend/public/dist',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: resolve(__dirname, 'index.html'),
            },
        },
    },
    server: {
        port: 5173,
        proxy: {
            '/api': {
                target: 'http://localhost:8000',
                changeOrigin: true,
            },
        },
    },
    resolve: {
        alias: {
            '@': resolve(__dirname, './src'),
        },
    },
});




















