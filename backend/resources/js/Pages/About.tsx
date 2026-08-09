import { Link } from "@inertiajs/react";
import {
  Satellite,
  ShieldCheck,
  WifiOff,
  Route,
  ClipboardCheck,
} from "lucide-react";

/**
 * Public "about this system" page — originally sourced from INTEGRATION.md's
 * feature summary before those 5 features were actually built; copy below
 * now describes the real, shipped implementation instead. Opts out of
 * AppLayout like Login.tsx/ResetPassword.tsx, since this is reachable while
 * unauthenticated.
 */
const features = [
  {
    icon: Satellite,
    title: "Real-Time Geo Tracking",
    description:
      "Live GPS tracking of resource dispatches on the free Leaflet + OSRM map stack — no paid map API required.",
  },
  {
    icon: ShieldCheck,
    title: "Multi-Role Security (RBAC)",
    description:
      "Role-based access for admins, coordinators, and field respondents, plus a public read-only incident view.",
  },
  {
    icon: WifiOff,
    title: "Offline-First Capabilities",
    description:
      "PWA service worker with an IndexedDB-backed queue for logging incidents and resource requests through cellular dropouts.",
  },
  {
    icon: Route,
    title: "Algorithmic Allocation",
    description:
      "Linear-programming resource allocation, with a greedy nearest-depot heuristic as an automatic fallback if the solver is unreachable.",
  },
  {
    icon: ClipboardCheck,
    title: "Standard DRRM Compliance",
    description:
      "Casualty, damage, and evacuation-center tracking with NDRRMC/Sendai-aligned SitRep reports (PDF and CSV).",
  },
];

export default function About() {
  return (
    <div className="min-h-screen bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="border-b border-[var(--line)] px-8 py-6">
        <div className="mx-auto flex max-w-5xl items-center justify-between">
          <h1 className="text-lg font-semibold uppercase tracking-wide">
            Disaster<span className="text-[var(--amber)]">Resource+</span>
          </h1>
          <Link
            href="/login"
            className="text-xs uppercase tracking-wide text-[var(--text-secondary)] underline hover:text-[var(--amber)]"
          >
            Sign in
          </Link>
        </div>
      </header>

      <main className="mx-auto max-w-5xl px-8 py-16">
        <p className="text-xs uppercase tracking-wide text-[var(--amber)]">
          System overview
        </p>
        <h2 className="mt-2 max-w-2xl text-3xl font-semibold leading-tight">
          Resource optimization and visualization for disaster response.
        </h2>
        <p className="mt-4 max-w-2xl text-sm text-[var(--text-secondary)]">
          Coordinators log incidents, track depot inventory, and let an
          optimization engine suggest resource allocations for human
          approval — built around the following capabilities.
        </p>

        <div className="mt-12 grid grid-cols-1 gap-px overflow-hidden border border-[var(--line)] bg-[var(--line)] sm:grid-cols-2">
          {features.map(({ icon: Icon, title, description }) => (
            <div
              key={title}
              className="bg-[var(--bg-panel)] p-6 transition-colors hover:bg-[var(--bg-panel-raised)]"
            >
              <Icon className="h-5 w-5 text-[var(--amber)]" strokeWidth={1.75} />
              <h3 className="mt-3 text-sm font-semibold uppercase tracking-wide">
                {title}
              </h3>
              <p className="mt-2 text-sm text-[var(--text-secondary)]">
                {description}
              </p>
            </div>
          ))}
        </div>
      </main>

      <footer className="border-t border-[var(--line)] px-8 py-6">
        <p className="mx-auto max-w-5xl text-xs text-[var(--text-secondary)]">
          DisasterResource+ &middot; Built for LDRRMO coordination.
        </p>
      </footer>
    </div>
  );
}

About.layout = (page: JSX.Element) => page;
