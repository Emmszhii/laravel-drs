import { Link } from "@inertiajs/react";
import IncidentMap from "@/Components/Map/IncidentMap";

type PublicIncident = {
  id: string;
  name: string;
  type: string;
  signal: number;
  zone: string | null;
  lat: number;
  lng: number;
  status: string;
};

type PublicDepot = { id: string; name: string; lat: number; lng: number };

type Props = {
  incidents: PublicIncident[];
  depots: PublicDepot[];
};

/**
 * Unauthenticated read-only dashboard (the "Public users" RBAC tier) — active
 * incidents and depot locations only, no stock/contact data. Opts out of
 * AppLayout like About.tsx, since this is reachable while logged out.
 */
export default function PublicDashboard({ incidents, depots }: Props) {
  return (
    <div className="flex h-screen flex-col bg-[var(--bg-void)] text-[var(--text-primary)]">
      <header className="flex items-center justify-between border-b border-[var(--line)] px-8 py-4">
        <h1 className="text-lg font-semibold uppercase tracking-wide">
          Disaster<span className="text-[var(--amber)]">Resource+</span>
        </h1>
        <div className="flex items-center gap-4 text-xs uppercase tracking-wide text-[var(--text-secondary)]">
          <Link href="/login" className="underline hover:text-[var(--amber)]">
            Sign in
          </Link>
        </div>
      </header>

      <div className="border-b border-[var(--line)] px-8 py-3 text-xs text-[var(--text-secondary)]">
        Public view — active incidents and depot locations only.{" "}
        {incidents.length} active incident{incidents.length === 1 ? "" : "s"}.
      </div>

      <main className="min-h-0 flex-1">
        <IncidentMap incidents={incidents} depots={depots} />
      </main>
    </div>
  );
}

PublicDashboard.layout = (page: JSX.Element) => page;
