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
    // (JS/CSS) and the three pages where stock moves (Stock, ICS 213-RR,
    // borrowed items), so Logistics keeps recording through an outage
    // (queued in IndexedDB, see resources/js/Offline/). /api/* GET responses
    // are never cached or served stale: this is not an offline read cache.
    //
    // Laravel serves no index.html, so the plugin's auto-injected register
    // script never loads: resources/js/app.tsx registers the worker itself,
    // from /sw.js (a routes/web.php route that serves public/build/sw.js)
    // so its scope is the whole site, not just /build/. Precache URLs are
    // made absolute and the Workbox runtime inlined for the same reason.
    VitePWA({
      registerType: "autoUpdate",
      injectRegister: null,
      manifest: {
        name: "DisasterResource+",
        short_name: "DisasterResource+",
        description: "Resource optimization and visualization for disaster response.",
        start_url: "/",
        scope: "/",
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
        // App-shell precache; no navigateFallback (this is a server-rendered
        // Inertia app, not a client-routed SPA).
        globPatterns: ["**/*.{js,css,svg,ico}"],
        // Must be explicit: the plugin defaults to "index.html", which Laravel never builds, and
        // binding a route to a non-precached URL throws while the worker is evaluated.
        navigateFallback: null,
        manifestTransforms: [
          async (entries) => ({
            manifest: entries.map((e) => ({ ...e, url: e.url.startsWith("/") ? e.url : `/build/${e.url}` })),
            warnings: [],
          }),
        ],
        inlineWorkboxRuntime: true,
        runtimeCaching: [
          // These pages open offline from their last online visit (their data is in the
          // Inertia page props), so entries can go into the outbox during an outage.
          // NetworkFirst: always fresh when online. Workbox serializes these matchers into
          // the worker, so the path list is repeated inline (no outer variables).
          // Full page loads, plus the plain GETs app.tsx uses to warm this cache (Offline/pages.ts).
          {
            urlPattern: ({ request, url }) =>
              !request.headers.has("X-Inertia") && ["/stock", "/ics-requests", "/ics-requests/borrowed"].includes(url.pathname),
            handler: "NetworkFirst",
            options: { cacheName: "offline-pages", networkTimeoutSeconds: 4, expiration: { maxEntries: 3 } },
          },
          // Visits through Inertia <Link> (XHR with X-Inertia): a separate cache, since the JSON
          // response differs from the HTML one. Partial reloads (router.reload({ only })) are left
          // out so a props subset never replaces the full page. Both caches are deleted on logout.
          {
            urlPattern: ({ request, url }) =>
              request.headers.get("X-Inertia") === "true" && !request.headers.has("X-Inertia-Partial-Data") &&
              ["/stock", "/ics-requests", "/ics-requests/borrowed"].includes(url.pathname),
            handler: "NetworkFirst",
            options: { cacheName: "offline-pages-xhr", networkTimeoutSeconds: 4, expiration: { maxEntries: 3 } },
          },
          // Background Sync (Chromium): an outbox upload that fails for lack of a
          // connection is kept by the worker and replayed when the connection returns,
          // even with the app closed. The app still retries on open and reconnect
          // (Offline/sync.ts); the server ignores device-made IDs it already holds.
          // ponytail: each failed retry queues another copy of the batch; harmless (deduplicated), trim if the queue grows.
          {
            urlPattern: ({ url }) => url.pathname === "/api/stock/sync",
            method: "POST",
            handler: "NetworkOnly",
            options: { backgroundSync: { name: "stock-outbox", options: { maxRetentionTime: 7 * 24 * 60 } } },
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
