import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.tsx"],
      refresh: true,
    }),
    react(),
  ],
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./resources/js", import.meta.url)),
    },
  },
  // Single .env lives at the repo root (see backend/bootstrap/app.php's
  // useEnvironmentPath()) — Vite otherwise only looks in its own root
  // (backend/), which would leave every VITE_* var undefined.
  envDir: fileURLToPath(new URL("..", import.meta.url)),
  server: {
    // Pin to IPv4 with a fixed port: Windows resolves "localhost" to ::1
    // first, so a host-less/auto-port Vite server can end up listening on
    // an address the browser doesn't try, or drift to a new port (5174+)
    // if 5173 was left stale — leaving public/hot pointing at a dead
    // address (ERR_CONNECTION_REFUSED) until the stale server is killed.
    host: "127.0.0.1",
    port: 5173,
    strictPort: true,
    watch: {
      ignored: ["**/storage/framework/views/**"],
    },
  },
});
