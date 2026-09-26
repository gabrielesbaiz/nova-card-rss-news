import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue";
import { resolve } from "path";

/**
 * Nova loads the built file directly in the browser and already exposes Vue as
 * a global, so we build a single self-contained IIFE bundle with Vue external.
 * Output paths match what CardServiceProvider registers: dist/js/card.js and
 * dist/css/card.css.
 */
export default defineConfig({
    plugins: [vue()],
    define: {
        "process.env.NODE_ENV": JSON.stringify(
            process.env.NODE_ENV ?? "production",
        ),
    },
    build: {
        outDir: "dist",
        emptyOutDir: true,
        cssCodeSplit: false,
        sourcemap: false,
        lib: {
            entry: resolve(__dirname, "resources/js/card.js"),
            name: "NovaCardRssNews",
            formats: ["iife"],
            fileName: () => "js/card.js",
            cssFileName: "css/card",
        },
        rollupOptions: {
            external: ["vue"],
            output: {
                globals: { vue: "Vue" },
                assetFileNames: (asset) =>
                    asset.names?.[0]?.endsWith(".css")
                        ? "css/card.css"
                        : "[name][extname]",
            },
        },
    },
});
