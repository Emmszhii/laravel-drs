import "./bootstrap";
import "../css/app.css";
import "./bones/registry";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { SystemToastProvider } from "./Components/Toast";
import AppLayout from "./Layouts/AppLayout";

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
