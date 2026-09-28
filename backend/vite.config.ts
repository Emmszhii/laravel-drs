import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import react from "@vitejs/plugin-react";
import { VitePWA } from "vite-plugin-pwa";

export default defineConfig({
  plugins: [
    laravel({
      input: ["resources/css/app.css", "resources/js/app.tsx"],
      refresh: true,
    }),
    react(),
    // Offline-first scope is deliberately narrow: precache the app shell
    // (JS/CSS) so a coordinator who already has the app open can keep
    // logging incidents/requests through a cellular dropout (queued in
    // IndexedDB, see resources/js/Offline/). No runtimeCaching rules are
    // registered, so /api/* GET responses are never cached/served stale —
    // this is not a general offline-browsing/read-cache feature.
    VitePWA({
      registerType: "autoUpdate",
      injectRegister: "auto",
      manifest: {
        name: "DisasterResource+",
        short_name: "DisasterResource+",
        description: "Resource optimization and visualization for disaster response.",
        theme_color: "#0B0D0C",
        background_color: "#0B0D0C",
        display: "standalone",
        icons: [
          {
            src: "/favicon.svg",
            sizes: "any",
            type: "image/svg+xml",
          },
        ],
      },
      workbox: {
        // App-shell precache only — no navigateFallback (this is a
        // server-rendered Inertia app, not a client-routed SPA) and no
        // runtimeCaching entries, so API responses are always fetched live.
        globPatterns: ["**/*.{js,css,svg,ico}"],
        // The one exception: the Stock page opens offline from its last
        // online visit (items/depots embedded in the Inertia page props), so
        // stock entries can be recorded into the IndexedDB outbox during an
        // outage. NetworkFirst: always fresh when online.
        runtimeCaching: [
          {
            urlPattern: ({ request, url }) => request.mode === "navigate" && url.pathname === "/stock",
            handler: "NetworkFirst",
            options: { cacheName: "stock-page", networkTimeoutSeconds: 4, expiration: { maxEntries: 1 } },
          },
        ],
      },
    }),
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
