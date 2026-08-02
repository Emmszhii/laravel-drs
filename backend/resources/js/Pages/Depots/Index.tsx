import { PageProps } from "@/Types/models";
import AdminLayout from "../../Layouts/AdminLayout";
import IncidentMap from "../../Components/Map/IncidentMap";

type Depot = {
  id: string;
  name: string;
  address: string;
  status: string;
  lat: number;
  lng: number;
};

type Props = PageProps<{ depots: Depot[] }>;

const NO_INCIDENTS: never[] = [];

function DepotsIndex({ depots }: Props) {
  return (
    <div>
      <div className="h-[300px] border-b border-white/10">
        <IncidentMap incidents={NO_INCIDENTS} depots={depots} />
      </div>
      <div className="grid grid-cols-1 gap-px bg-white/10 sm:grid-cols-2 lg:grid-cols-3">
        {depots.map((d) => (
          <div key={d.id} className="bg-[var(--bg-void)] p-5">
            <div className="mb-1 text-[15px] font-medium">{d.name}</div>
            <div className="mb-2.5 text-xs text-[var(--text-secondary)]">{d.address}</div>
            <span className="inline-block bg-[var(--ok-dim,rgba(63,169,140,0.15))] px-2 py-0.5 font-mono text-xs text-[var(--ok,#3FA98C)]">
              {d.status}
            </span>
          </div>
        ))}
      </div>
    </div>
  );
}

DepotsIndex.layout = (page: JSX.Element) => <AdminLayout>{page}</AdminLayout>;

export default DepotsIndex;
