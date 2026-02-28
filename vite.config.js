import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import eslint from "vite-plugin-eslint";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.less", "resources/js/app.js", "resources/css/admin.less", "resources/js/admin/app.js"],
      refresh: true,
    }),
    vue(),
    eslint({
      include: ["resources/js/**/*.js", "resources/js/**/*.vue"],
      emitWarning: true,
      emitError: true,
    }),
  ],
  build: {
    rollupOptions: {
      output: {
        manualChunks(id) {
          if (id.includes("node_modules")) {
            return "vendor";
          }
        },
      },
    },
  },
});
