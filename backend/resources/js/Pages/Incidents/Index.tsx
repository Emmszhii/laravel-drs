import { useState } from "react";
import { PageProps } from "@/Types/models";
import AdminLayout from "../../Layouts/AdminLayout";
import TriggerSolver from "../Optimization/TriggerSolver";

type Zone = { id: string; name: string; population_affected: number; severity_level: string };
type Incident = {
  id: string;
  name: string;
  type: string;
  signal: number;
  zone: string;
  status: string;
  zones: Zone[];
};

type Props = PageProps<{ incidents: Incident[] }>;

function IncidentsIndex({ incidents }: Props) {
  const [selectedId, setSelectedId] = useState<string | null>(incidents[0]?.id ?? null);
  const selected = incidents.find((inc) => inc.id === selectedId) ?? null;

  return (
    <div className="flex h-full">
      <div className="w-[280px] shrink-0 overflow-y-auto border-r border-white/10">
        {incidents.map((inc) => (
          <div
            key={inc.id}
            onClick={() => setSelectedId(inc.id)}
            className={`cursor-pointer border-b border-white/10 p-3 text-sm ${
              inc.id === selectedId ? "border-l-2 border-l-[var(--amber)] bg-white/5" : "hover:bg-white/5"
            }`}
          >
            <div className="font-semibold">{inc.name}</div>
            <div className="text-xs text-[var(--text-secondary)]">
              {inc.zone} · Signal {inc.signal} · {inc.status}
            </div>
          </div>
        ))}
      </div>
      <div className="flex-1 overflow-y-auto p-6">
        {selected ? (
          <>
            <h2 className="text-xl font-semibold">{selected.name}</h2>
            <p className="mb-4 text-sm text-[var(--text-secondary)]">
              {selected.type} · {selected.zone} · Signal {selected.signal} · {selected.status}
            </p>
            <table className="mb-6 w-full text-sm">
              <thead>
                <tr className="text-left text-xs uppercase text-[var(--text-secondary)]">
                  <th className="p-2">Area</th>
                  <th className="p-2">Population affected</th>
                  <th className="p-2">Severity</th>
                </tr>
              </thead>
              <tbody>
                {selected.zones.map((z) => (
                  <tr key={z.id} className="border-t border-white/10">
                    <td className="p-2">{z.name}</td>
                    <td className="p-2">{z.population_affected}</td>
                    <td className="p-2">{z.severity_level}</td>
                  </tr>
                ))}
              </tbody>
            </table>
            <TriggerSolver disasterId={selected.id} />
          </>
        ) : (
          <p className="text-sm text-[var(--text-secondary)]">No incidents logged yet.</p>
        )}
      </div>
    </div>
  );
}

IncidentsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default IncidentsIndex;
