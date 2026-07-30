import "./bootstrap";
import "../css/app.css";
import { createRoot } from "react-dom/client";
import { createInertiaApp } from "@inertiajs/react";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import AppLayout from "./Layouts/AppLayout";

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
    createRoot(el).render(<App {...props} />);
  },
});
