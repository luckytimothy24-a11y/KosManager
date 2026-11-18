import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js", "resources/js/pwa.js"],
            refresh: true,
        }),
        VitePWA({
            strategies: "injectManifest",
            srcDir: "resources/js",
            filename: "sw.js",
            registerType: "autoUpdate",
            includeAssets: ["favicon.ico", "apple-touch-icon.png", "favicon.svg"],
            manifest: false,
            injectManifest: {
                globPatterns: ["**/*.{js,css,html,ico,png,svg,woff2}"],
                maximumFileSizeToCacheInBytes: 5 * 1024 * 1024,
            },
            devOptions: {
                enabled: true,
                type: "module"
            }
        })
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ["alpinejs", "chart.js/auto"],
                },
            },
        },
    },
});
