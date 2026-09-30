import "./bootstrap";
import "../css/app.css";
import "./bones/registry";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { SystemToastProvider } from "./Components/Toast";
import AppLayout from "./Layouts/AppLayout";
import { syncOutbox } from "./Offline/sync";

// Service worker for the offline app shell and the offline Stock page (see vite.config.ts).
// Served from /sw.js so it controls the whole site; production builds only.
if (import.meta.env.PROD && "serviceWorker" in navigator) {
  navigator.serviceWorker.register("/sw.js", { scope: "/" }).catch(() => {
    // Plain-HTTP deployments other than localhost can't register a worker; the app still works online.
  });
}

// Retry the offline outbox whenever the app opens or reconnects, on any page (Background Sync is Chromium-only).
syncOutbox();
window.addEventListener("online", () => syncOutbox());

const queryClient = new QueryClient({
  defaultOptions: { queries: { refetchOnWindowFocus: false } },
});

createInertiaApp({
  title: (title) => `${title} — DisasterResource+`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.tsx`,
      import.meta.glob("./Pages/**/*.tsx")
    ).then((module: any) => {
      module.default.layout = module.default.layout || ((page: JSX.Element) => <AppLayout>{page}</AppLayout>);
      return module;
    }),
  setup({ el, App, props }) {
    createRoot(el).render(
      <QueryClientProvider client={queryClient}>
        <SystemToastProvider>
          <App {...props} />
        </SystemToastProvider>
      </QueryClientProvider>
    );
  },
});
