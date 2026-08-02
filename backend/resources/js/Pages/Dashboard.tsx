import { useCallback, useState } from "react";
import { PageProps } from "@/Types/models";
import DashboardLayout from "../Layouts/DashboardLayout";
import IncidentMap from "../Components/Map/IncidentMap";
import DirectionsPanel from "../Components/Map/DirectionsPanel";

type Props = PageProps<{
  incidents: Array<{ id: string; name: string; lat: number; lng: number; signal: number }>;
  depots: Array<{ id: string; name: string; lat: number; lng: number }>;
}>;

export default function Dashboard({ incidents, depots }: Props) {
  const [routeGeometry, setRouteGeometry] = useState<[number, number][] | null>(null);
  const onRouteChange = useCallback((geometry: [number, number][] | null) => {
    setRouteGeometry(geometry);
  }, []);

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
      statColumn={
        <DirectionsPanel depots={depots} destinations={incidents} onRouteChange={onRouteChange} />
      }
    >
      <IncidentMap incidents={incidents} depots={depots} route={routeGeometry} />
    </DashboardLayout>
  );
}
