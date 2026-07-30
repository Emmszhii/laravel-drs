import { PageProps } from "@/Types/models";
import DashboardLayout from "../Layouts/DashboardLayout";
import IncidentMap from "../Components/Map/IncidentMap";

type Props = PageProps<{
  incidents: Array<{ id: string; name: string; lat: number; lng: number; signal: number }>;
  depots: Array<{ id: string; name: string; lat: number; lng: number }>;
}>;

export default function Dashboard({ incidents, depots }: Props) {
  return (
    <DashboardLayout
      rail={
        <ul>
          {incidents.map((inc) => (
            <li key={inc.id} className="border-b border-white/10 p-3 text-sm">
              {inc.name} · Signal {inc.signal}
            </li>
          ))}
        </ul>
      }
      statColumn={<div className="p-4 text-sm text-[var(--text-secondary)]">Utilization stats go here</div>}
    >
      <IncidentMap incidents={incidents} depots={depots} />
    </DashboardLayout>
  );
}
