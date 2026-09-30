import "./bootstrap";
import "../css/app.css";
import "./bones/registry";
import { createRoot } from "react-dom/client";
import { createInertiaApp, router } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { SystemToastProvider } from "./Components/Toast";
import AppLayout from "./Layouts/AppLayout";
import { syncOutbox } from "./Offline/sync";
import { currentUserCanRecord, currentUserId, setCurrentUser } from "./Offline/outbox";
import { clearOfflinePages, warmOfflinePages } from "./Offline/pages";

// Service worker for the offline app shell and the offline Stock page (see vite.config.ts).
// Served from /sw.js so it controls the whole site; production builds only.
if (import.meta.env.PROD && "serviceWorker" in navigator) {
  navigator.serviceWorker.register("/sw.js", { scope: "/" }).catch(() => {
    // Plain-HTTP deployments other than localhost can't register a worker; the app still works online.
  });
}

// Retry the signed-in user's outbox whenever the app opens, reconnects or a user signs in, on any
// page (Background Sync is Chromium-only). Login and logout are Inertia visits, not reloads, so the
// user is re-read from every navigation; with nobody signed in, syncOutbox() sends nothing.
const onUserChange = async () => {
  syncOutbox();
  const id = currentUserId();
  if (!id) return;
  // Logout clears the cached pages, but a session can also just expire; if someone else signs
  // in next on a shared device, drop the previous person's pages (they hold borrower data).
  try {
    if (localStorage.getItem("drs-pages-owner") !== id) {
      await clearOfflinePages();
      localStorage.setItem("drs-pages-owner", id);
    }
  } catch {
    // Storage blocked: logout still clears the caches.
  }
  if (currentUserCanRecord()) warmOfflinePages(id);
};
onUserChange();
window.addEventListener("online", () => syncOutbox());
router.on("navigate", (event) => {
  const user = event.detail.page.props.auth?.user ?? null;
  if ((user?.id ?? null) === currentUserId()) return;
  setCurrentUser(user);
  onUserChange();
});

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
