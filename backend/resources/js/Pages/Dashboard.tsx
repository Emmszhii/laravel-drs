import { PageProps } from "@/Types/models";
import AppLayout from "../Layouts/AppLayout";
import AdminLayout from "../Layouts/AdminLayout";
import DashboardLayout from "../Layouts/DashboardLayout";
import IncidentMap from "../Components/Map/IncidentMap";

type Props = PageProps<{
  incidents: Array<{ id: string; name: string; lat: number; lng: number; signal: number }>;
  depots: Array<{ id: string; name: string; lat: number; lng: number }>;
}>;

function Dashboard({ incidents, depots }: Props) {
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
    >
      <IncidentMap incidents={incidents} depots={depots} />
    </DashboardLayout>
  );
}

Dashboard.layout = (page: JSX.Element) => {
  const role = (page.props as Props).auth.user?.role;
  const isAdminOrDeveloper = role === "admin" || role === "developer";

  return isAdminOrDeveloper ? <AdminLayout>{page}</AdminLayout> : <AppLayout>{page}</AppLayout>;
};

export default Dashboard;
