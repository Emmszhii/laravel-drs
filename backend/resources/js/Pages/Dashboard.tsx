import { useState } from "react";
import { PageProps } from "@/Types/models";
import { sharesAdminAccess } from "@/api-service";
import AppLayout from "../Layouts/AppLayout";
import AdminLayout from "../Layouts/AdminLayout";
import DashboardLayout from "../Layouts/DashboardLayout";
import IncidentMap from "../Components/Map/IncidentMap";
import { DataTableCardField } from "@/Components/table/DataTableCardField";
import type { ColumnDef, CardActions } from "@/Components/table";
import type {
  DashboardStats,
  ResourceRequest,
  UtilizationRow,
} from "@/Types/models";

type Props = PageProps<{
  incidents: Array<{
    id: string;
    name: string;
    lat: number;
    lng: number;
    signal: number;
    zones?: Array<{ id: string }>;
  }>;
  depots: Array<{ id: string; name: string; lat: number; lng: number }>;
  stats: DashboardStats;
  utilization: UtilizationRow[];
}>;

const statusColor: Record<ResourceRequest["status"], string> = {
  fulfilled: "text-ok",
  enroute: "text-pending",
  pending: "text-pending",
  unmet: "text-critical",
};

const requestColumns: ColumnDef<ResourceRequest>[] = [
  {
    key: "status",
    label: "Status",
    filterable: true,
    type: "select",
    typeData: [
      { value: "pending", label: "Pending" },
      { value: "enroute", label: "En route" },
      { value: "unmet", label: "Unmet" },
      { value: "fulfilled", label: "Fulfilled" },
    ],
  },
  {
    key: "zone",
    label: "Area",
    render: (v) => (v as ResourceRequest["zone"])?.name ?? "—",
  },
  {
    key: "resource_type",
    label: "Resource",
    render: (v) => (v as ResourceRequest["resource_type"])?.name ?? "—",
  },
  { key: "quantity_requested", label: "Qty" },
];

function RequestCard(row: ResourceRequest, _actions: CardActions) {
  return (
    <div className="flex items-center justify-between border-b border-white/10 p-3 text-sm">
      <div>
        <div className="font-semibold">
          {row.zone?.disaster?.name ?? row.zone?.name ?? "Unknown area"}
        </div>
        <div className="text-xs text-[var(--text-secondary)]">
          {row.zone?.name} · {row.resource_type?.name ?? "—"} ·{" "}
          {row.quantity_requested} {row.resource_type?.unit ?? ""}
        </div>
      </div>
      <div
        className={`text-xs font-semibold uppercase ${statusColor[row.status]}`}
      >
        {row.status}
      </div>
    </div>
  );
}

function utilizationBarColor(pct: number): string {
  if (pct >= 70) return "bg-ok";
  if (pct >= 45) return "bg-pending";
  return "bg-critical";
}

function Dashboard({ incidents, depots, stats, utilization }: Props) {
  const [selectedIncidentId, setSelectedIncidentId] = useState<
    string | undefined
  >();

  return (
    <div className="flex h-full flex-col overflow-y-auto">
      <div className="shrink-0 lg:h-[520px]">
        <DashboardLayout
          rail={
            <div>
              {incidents.map((inc) => (
                <div
                  key={inc.id}
                  onClick={() => setSelectedIncidentId(inc.id)}
                  className={`cursor-pointer border-b border-white/10 p-3 text-sm ${
                    inc.id === selectedIncidentId
                      ? "border-l-2 border-l-[var(--amber)] bg-white/5"
                      : "hover:bg-white/5"
                  }`}
                >
                  <div className="flex items-center gap-2">
                    <span
                      className={`inline-block h-2 w-2 shrink-0 rounded-full ${
                        inc.signal >= 4 ? "bg-critical" : "bg-pending"
                      }`}
                    />
                    <span className="font-semibold">{inc.name}</span>
                  </div>
                  <div className="mt-1 pl-4 text-xs text-[var(--text-secondary)]">
                    Signal {inc.signal} · {inc.zones?.length ?? 0} areas
                    affected
                  </div>
                </div>
              ))}
            </div>
          }
          statColumn={
            <div className="grid grid-cols-1 gap-4 p-4 sm:grid-cols-2 lg:grid-cols-1">
              <div className="border border-white/10 p-3">
                <div className="text-xs tracking-wide text-[var(--text-secondary)] uppercase">
                  Unmet demand
                </div>
                <div className="mt-1 text-2xl font-bold text-critical">
                  {stats.unmet_pct}%
                </div>
              </div>
              <div className="border border-white/10 p-3">
                <div className="text-xs tracking-wide text-[var(--text-secondary)] uppercase">
                  Resources deployed
                </div>
                <div className="mt-1 text-2xl font-bold">{stats.deployed}</div>
              </div>
              <div className="border border-white/10 p-3 sm:col-span-2 lg:col-span-1">
                <div className="mb-2 text-xs tracking-wide text-[var(--text-secondary)] uppercase">
                  Utilization by type
                </div>
                <div className="flex flex-col gap-2.5">
                  {utilization.map((u) => (
                    <div key={u.id}>
                      <div className="mb-1 flex justify-between text-xs">
                        <span>{u.name}</span>
                        <span className="text-[var(--text-secondary)]">
                          {u.pct}%
                        </span>
                      </div>
                      <div className="h-1.5 w-full bg-white/10">
                        <div
                          className={`h-1.5 ${utilizationBarColor(u.pct)}`}
                          style={{ width: `${u.pct}%` }}
                        />
                      </div>
                    </div>
                  ))}
                </div>
              </div>
            </div>
          }
        >
          <div className="relative z-10 h-72 w-full sm:h-96 lg:h-full">
            <IncidentMap
              incidents={incidents}
              depots={depots}
              onSelectIncident={setSelectedIncidentId}
              selectedIncidentId={selectedIncidentId}
            />
            <div className="absolute right-3 bottom-3 z-[1000] border border-white/20 bg-[var(--bg-panel)] p-2.5 text-xs">
              <div className="mb-1.5 flex items-center gap-2">
                <span className="inline-block h-2.5 w-2.5 rounded-full bg-critical" />
                Incident zone
              </div>
              <div className="mb-1.5 flex items-center gap-2">
                <span className="inline-block h-2.5 w-2.5 rounded-full bg-ok" />
                Depot
              </div>
              <div className="flex items-center gap-2">
                <span className="inline-block h-2.5 w-2.5 rounded-full bg-[#4A9EFF]" />
                Your location
              </div>
            </div>
          </div>
        </DashboardLayout>
      </div>

      <div className="border-t border-white/10 p-6">
        <h2 className="mb-3 text-sm font-semibold tracking-wide uppercase">
          Resource requests
        </h2>
        <DataTableCardField<ResourceRequest>
          apiUrl="/api/requests"
          apiQueryKey="dashboard-requests"
          columns={requestColumns}
          renderCard={RequestCard}
          enableEdit={false}
        />
      </div>
    </div>
  );
}

Dashboard.layout = (page: JSX.Element) => {
  const role = (page.props as Props).auth.user?.role;

  return sharesAdminAccess(role) ? (
    <AdminLayout>{page}</AdminLayout>
  ) : (
    <AppLayout>{page}</AppLayout>
  );
};

export default Dashboard;
